<?php
namespace OimoV2;
class DataBase {
    public static function getConn() {
        require __DIR__ . '/../../config.inc.php';
        $host = $dbconfig['db_server'];
        $user = $dbconfig['db_username'];
        $pass = $dbconfig['db_password'];
        $name = $dbconfig['db_name'];
        $port = isset($dbconfig['db_port']) ? preg_replace('/^:/', '', $dbconfig['db_port']) : 3306;

        $conn = new \mysqli($host, $user, $pass, $name, $port);


        if ($conn->connect_errno) {
            throw new Exception('MySQL error: ' . $conn->connect_error);
        }

        $conn->query('set names utf8');


        return $conn;
    }

}