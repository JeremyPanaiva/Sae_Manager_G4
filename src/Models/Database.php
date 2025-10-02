<?php
namespace Models;

class Database {
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {

            $servername = self::parseEnvVar("DB_HOST");
            $username = self::parseEnvVar("DB_USER");
            $password = self::parseEnvVar("DB_PASSWORD");
            $dbname = self::parseEnvVar("DB_NAME");


            self::$conn = new \mysqli($servername, $username, $password, $dbname);

            if (self::$conn->connect_error) {
                die("Connexion échouée : " . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }
    static function parseEnvVar(string $envVar)
    {
        if (file_exists(__DIR__. '/../../.env')) {
            $env = parse_ini_file(__DIR__. '/../../.env');
            return $env[$envVar];
        }
        return getenv($envVar);
    }
}
