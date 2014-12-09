<?php

namespace Scam;

class ConfigModel extends Model {
    public function setAdmin($bip32Key, $adminAddress) {
        $values = ['admin_bip32_extended_public_key' => $bip32Key,
            'admin_bip32_key_index' => 0,
            'admin_bitcoin_address' => $adminAddress ];

        $sql = 'INSERT INTO config (name, value) VALUES (:name, :value)';
        $query = $this->db->prepare($sql);
        foreach($values as $k => $v) {
            if(!$query->execute([':name' => $k, ':value' => $v])){
                return false;
            }
        }
        return true;
    }

    public function getAdminAddress() {
        return $this->getValueOrThrowException('admin_bitcoin_address');
    }

    public function getValueOrThrowException($name) {
        $q = $this->db->prepare('SELECT value FROM config WHERE name = :name LIMIT 1');
        $q->execute([':name' => $name]);
        $entry = $q->fetch();
        if(!$entry){
            throw new \Exception("Config key with name $name not found");
        }
        return $entry->value;
    }

    public function setValue($name, $value) {
        $ret = $this->db->prepare('UPDATE config SET value = :value WHERE name = :name')
            ->execute([':value' => $value, ':name' => $name]);
        if(!$ret) {
            throw new \Exception('Could not save config value');
        }
        return true;
    }

    public function tryLock(){
        # try insert, will throw an exception if lock is already there
        $sql = 'INSERT INTO config (name, value) VALUES (:name, :value)';
        return $this->db->prepare($sql)->execute([':name' => 'transaction_lock', ':value' => 'true']);
    }

    public function releaseLock() {
        return $this->db->prepare('DELETE FROM config WHERE name = :name')->execute([':name' => 'transaction_lock']);
    }
}