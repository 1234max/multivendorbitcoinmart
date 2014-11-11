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
}