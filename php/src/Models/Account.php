<?php

namespace MiniBanking\Models;

use MiniBanking\Config\Database;

class Account
{
    public static function find(int $id): ?array
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("SELECT id, owner_name, currency FROM accounts WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }
    
    public static function create(string $ownerName, string $currency = 'EUR'): int
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("INSERT INTO accounts (owner_name, currency) VALUES (?, ?)");
        $stmt->bind_param('ss', $ownerName, $currency);
        $stmt->execute();
        return $mysqli->insert_id;
    }
    
}