<?php

namespace Scam;

class UserModel extends Model {
    public function getUser($userId) {
        $q = $this->db->prepare('SELECT * FROM users WHERE id = :user_id LIMIT 1');
        $q->execute([':user_id' => $userId]);
        $user = $q->fetch();
        return $user ? $user : null;
    }

    public function getWithHash($hash) {
        $q = $this->db->prepare("SELECT * FROM users WHERE SHA2(name, '256') = :hash LIMIT 1");
        $q->execute([':hash' => $hash]);
        $user = $q->fetch();
        return $user ? $user : null;
    }

    public function checkLogin($name, $password) {
        require_once '../vendor/ircmaxell/password-compat/lib/password.php';

        $q = $this->db->prepare('SELECT * FROM users WHERE name = :name LIMIT 1');
        $q->execute([':name' => $name]);
        $user = $q->fetch();

        if(!$user) {
            return false;
        }

        $verify = password_verify($password, $user->password_hash);

        return $verify ? $user : false;
    }

    public function isNameFree($name) {
        $q = $this->db->prepare('SELECT * FROM users WHERE name = :name LIMIT 1');
        $q->execute([':name' => $name]);
        return $q->fetch() === false;
    }

    public function register($name, $password, $profilePin) {
        require_once '../vendor/ircmaxell/password-compat/lib/password.php';

        $sql = 'INSERT INTO users (name, password_hash, profile_pin_hash) VALUES (:name, :password_hash, :profile_pin_hash)';
        $query = $this->db->prepare($sql);
        return $query->execute([':name' => $name,
            ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ':profile_pin_hash' => password_hash($profilePin, PASSWORD_BCRYPT)]);
    }

    public function checkPassword($userId, $password) {
        require_once '../vendor/ircmaxell/password-compat/lib/password.php';

        $q = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $q->execute([':id' => $userId]);
        $user = $q->fetch();

        if(!$user) {
            return false;
        }

        return password_verify($password, $user->password_hash);
    }

    public function updatePassword($userId, $password) {
        require_once '../vendor/ircmaxell/password-compat/lib/password.php';

        $sql = 'UPDATE users SET password_hash = :password_hash WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $userId,
            ':password_hash' => password_hash($password, PASSWORD_BCRYPT)]);
    }

    public function checkProfilePin($userId, $profilePin) {
        require_once '../vendor/ircmaxell/password-compat/lib/password.php';

        $q = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $q->execute([':id' => $userId]);
        $user = $q->fetch();

        if(!$user) {
            return false;
        }

        return password_verify($profilePin, $user->profile_pin_hash);
    }

    public function updateProfilePin($userId, $profilePin) {
        require_once '../vendor/ircmaxell/password-compat/lib/password.php';

        $sql = 'UPDATE users SET profile_pin_hash = :profile_pin_hash WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $userId,
            ':profile_pin_hash' => password_hash($profilePin, PASSWORD_BCRYPT)]);
    }

    public function becomeVendor($userId) {
        $sql = 'UPDATE users SET is_vendor = 1 WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $userId]);
    }

    public function checkAdminLogin($message, $signature) {
        try {
            $c = $this->getBitcoinClient();

            # return if no bitcoin server is running
            if(!$c->getinfo()) {
                throw new \Exception('No bitcoind running');
            }

            $adminAddress = $this->getModel('Config')->getAdminAddress();
            return $c->verifymessage($adminAddress, $signature, $message) == 'true';
        }
        catch(\Exception $e) {
            return false;
        }
    }

    public function isValidPGPPublicKey($pubKey) {
        require_once '../app/lib/concurrentPgp.php';
        $gpg = new ConcurrentPGP();
        $info = $gpg->import($pubKey);
        return isset($info['imported']) && $info['imported'] === 1;
    }

    public function isValidPGPSignatureOfMessage($pubKey, $signature, $clearMessage) {
        require_once '../app/lib/concurrentPgp.php';
        $gpg = new ConcurrentPGP();

        $keyInfo = $gpg->import($pubKey);
        if(!isset($keyInfo['imported']) || $keyInfo['imported'] !== 1) {
            throw new \Exception('Invalid public key');
        }

        $signInfo = $gpg->verify($signature, false, $clearMessage);
        return isset($signInfo[0]) && $signInfo[0]['fingerprint'] && $signInfo[0]['fingerprint'] === substr($keyInfo['fingerprint'], -strlen($signInfo[0]['fingerprint']));
    }

    public function setPGP($userId, $pubKey) {
        $sql = 'UPDATE users SET pgp_public_key = :pgp_public_key WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $userId, ':pgp_public_key' => $pubKey]);
    }

    public function encryptMessageForUser($userId, $message) {
        $user = $this->getUser($userId);
        if($user == null || !$user->pgp_public_key) {
            throw new \Exception('User or pgp public key not found');
        }

        require_once '../app/lib/concurrentPgp.php';
        $gpg = new ConcurrentPGP();

        $keyInfo = $gpg->import($user->pgp_public_key);
        if(!isset($keyInfo['imported']) || $keyInfo['imported'] !== 1) {
            throw new \Exception('Invalid public key');
        }

        if(!$gpg->addencryptkey($keyInfo['fingerprint'])) {
            throw new \Exception('Invalid public key');
        }

        return $gpg->encrypt($message);
    }

    /* parses and validates a BIP32 extended public key (returns false if invalid, or an array with infos if valid) */
    public function parseBip32ExtendedPK($publicKey) {
        require_once '../vendor/autoload.php';
        return \BitWasp\BitcoinLib\BIP32::import($publicKey);
    }

    public function setBip32ExtendedPublicKey($userId, $pubKey) {
        $sql = 'UPDATE users SET bip32_extended_public_key = :bip32_extended_public_key, bip32_key_index = 0 WHERE id = :id';
        $query = $this->db->prepare($sql);
        return $query->execute([':id' => $userId, ':bip32_extended_public_key' => $pubKey]);
    }

    /* gets the next child key from a user's public key; should be wrapped in a transaction. */
    public function getNextPublicKeyFromBip32($userId) {
        # get user (pk and key index)
        $user = $this->getUser($userId);
        $keyIndex = intval($user->bip32_key_index);

        # generate next key
        require_once '../vendor/autoload.php';
        $extendedKey = \BitWasp\BitcoinLib\BIP32::build_key($user->bip32_extended_public_key, $keyIndex);
        $publicKey = \BitWasp\BitcoinLib\BIP32::extract_public_key($extendedKey);

        # update key index in database
        if(!$this->db->prepare('UPDATE users SET bip32_key_index = :bip32_key_index, bip32_key_index = :bip32_key_index WHERE id = :id')
            ->execute([':id' => $userId, ':bip32_key_index' => $keyIndex + 1])) {
            throw new \Exception('Error while saving');
        }
        return [$keyIndex, $publicKey];
    }

    /* gets the next child key from the admin's public key; should be wrapped in a transaction. */
    public function getNextPublicKeyFromBip32OfAdmin() {
        # get admin pk and key index
        $configModel = $this->getModel('Config');
        $adminExtendedPK = $configModel->getValueOrThrowException('admin_bip32_extended_public_key');
        $adminKeyIndex = intval($configModel->getValueOrThrowException('admin_bip32_key_index'));

        # generate next key
        require_once '../vendor/autoload.php';
        $extendedKey = \BitWasp\BitcoinLib\BIP32::build_key($adminExtendedPK, $adminKeyIndex);
        $publicKey = \BitWasp\BitcoinLib\BIP32::extract_public_key($extendedKey);

        # update key index in database
        $configModel->setValue('admin_bip32_key_index', $adminKeyIndex + 1);
        return [$adminKeyIndex, $publicKey];
    }
}