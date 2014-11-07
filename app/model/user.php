<?php

namespace Scam;

class UserModel extends Model {
    public function getUser($user_id) {
        $q = $this->db->prepare('SELECT * FROM users WHERE id = :user_id LIMIT 1');
        $q->execute([':user_id' => $user_id]);
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
        $ret = $query->execute(array(':name' => $name,
            ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ':profile_pin_hash' => password_hash($profilePin, PASSWORD_BCRYPT)));
        return $ret;
    }
}