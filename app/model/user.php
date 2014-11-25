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
        $q = $this->db->prepare('SELECT * FROM users WHERE SHA1(name) = :hash LIMIT 1');
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
}