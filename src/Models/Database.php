<?php
namespace Models;

class Database
{
    private static $conn;

    public static function getConnection()
    {
        if (!self::$conn) {
            $servername = "mysql-sae-manager-g4.alwaysdata.net";
            $username = "432905_jeremy";
            $password = "saemanager-g4!";
            $dbname = "sae-manager-g4_db";

            self::$conn = new \mysqli($servername, $username, $password, $dbname);

            if (self::$conn->connect_error) {
                die("Connexion échouée: " . self::$conn->connect_error);
            }
        }

        return self::$conn;
    }
}
