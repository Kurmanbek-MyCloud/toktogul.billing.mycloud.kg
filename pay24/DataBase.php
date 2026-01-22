<?php

class DataBase
{

    public static function getConn()
    {

        $host = 'localhost';
        $user = 'test171122_admin';
        $pass = 'zBRp75XFTfbYqD8';
        $name = 'test171122';

        $conn = new mysqli($host, $user, $pass, $name);

        if ($conn->connect_errno)
        {
            throw new Exception('MySQL error: ' . $conn->connect_error);
        }

        $conn->query('set names utf8');

        return $conn;
    }

}