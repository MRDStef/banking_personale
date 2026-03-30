<?php

namespace MiniBanking\Config;

use mysqli;

class Database
{
    private static ?mysqli $connection = null;
    
    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {
            self::$connection = new mysqli(
                'my_mariadb',  // host (nome del servizio in docker-compose)
                'root',
                'ciccio',
                'banking'
            );
            
            if (self::$connection->connect_error) {
                die("Connection failed: " . self::$connection->connect_error);
            }
        }
        
        return self::$connection;
    }
}