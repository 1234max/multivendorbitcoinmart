<?php

namespace Scam;

/**
 * Class Model
 * @package Scam
 * @author Matthias Winzeler <matthias.winzeler@gmail.com>
 *
 * Base class for all application models (see app/model).
 * Models are used in controllers and provide access to the database.
 */
class Model {

    protected $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    protected function getModel($modelName) {
        require_once '../app/model/' . lcfirst($modelName) . '.php';
        $className = 'Scam\\' . $modelName . 'Model';
        return new $className($this->db);
    }

    /* returns a secure random 32 char hex string (128 bit), assuming that /dev/urandom returns proper random values
    (case on unix derivates */
    public function getRandomStr() {
        return bin2hex(file_get_contents('/dev/urandom', 0, null, -1, 16));
    }

    /* provides access to bitcoind API */
    public function getBitcoinClient() {
        require_once '../app/lib/jsonRPCClient.php';
        return new \jsonRPCClient(BITCOIND_URL);
    }
}