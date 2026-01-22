<?php
// include_once('../config.inc.php');
class DataBase
{

    public static function getConn($user,$pass,$db)
    {

        $host = 'localhost';
        $user = $user;
        $pass = $pass;
        $name = $db;

        $conn = new mysqli($host, $user, $pass, $name);

        if ($conn->connect_errno)
        {
            throw new Exception('MySQL error: ' . $conn->connect_error);
        }

        $conn->query('set names utf8');

        return $conn;
    }

}
