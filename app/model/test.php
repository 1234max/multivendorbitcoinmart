<?php

namespace Scam;

class TestModel extends  Model {
    public function getTests() {
        $q = $this->db->prepare('SELECT * FROM test');
        $q->execute();
        return $q->fetchAll();
    }
}