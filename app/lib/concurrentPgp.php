<?php

namespace Scam;

/**
 * Class ConcurrentPGP
 * @package Scam
 * @author Matthias Winzeler <matthias.winzeler@gmail.com>
 *
 * Simple wrapper arroung php gnupg (http://php.net/manual/de/book.gnupg.php)
 * - makes sure always an empty keyring is created and deleted
 * - fails if keyring is currently in use (ie. keyring is already created by another request)
 */
class ConcurrentPGP {
    private $gpg;
    public function __construct() {
        # since gnupg php needs keyring files, we make sure here that these are always created from scratch.
        $tmp = sys_get_temp_dir();
        $files = glob("$tmp/*.gpg*");
        if(!empty($files)) {
            throw new \Exception('GPG already in use');
        }

        putenv("GNUPGHOME=" . $tmp);
        $this->gpg = new \gnupg();
    }

    public function __destruct() {
        # delete keyring
        $tmp = sys_get_temp_dir();
        array_map("unlink", glob("$tmp/*.gpg*"));
    }

    # delegate all method calls to the gpg object
    public function __call($method, $args) {
        return call_user_func_array(array($this->gpg, $method), $args);
    }
}