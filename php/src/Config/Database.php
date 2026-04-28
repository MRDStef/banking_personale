<?php

namespace MiniBanking\Config;

use mysqli;

class Database
{
    private static ?mysqli $connection = null;
    
    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {
            // Prima prova a prendere le variabili d'ambiente (Railway)
            $host = getenv('MYSQLHOST') ?: 'my_mariadb';
            $user = getenv('MYSQLUSER') ?: 'root';
            $password = getenv('MYSQLPASSWORD') ?: 'ciccio';
            $database = getenv('MYSQLDATABASE') ?: 'banking';
            $port = getenv('MYSQLPORT') ?: 3306;
            
            self::$connection = new mysqli($host, $user, $password, $database, $port);
            
            if (self::$connection->connect_error) {
                die("Connection failed: " . self::$connection->connect_error);
            }
        }
        
        return self::$connection;
    }
}