<?php

namespace MiniBanking\Models;

use MiniBanking\Config\Database;

class Transaction
{
    public static function getBalance(int $accountId): float
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) AS balance
            FROM transactions
            WHERE account_id = ?
        ");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (float)($row['balance'] ?? 0);
    }
    
    public static function addDeposit(int $accountId, float $amount, string $description): int
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            INSERT INTO transactions (account_id, type, amount, description) 
            VALUES (?, 'deposit', ?, ?)
        ");
        $stmt->bind_param('ids', $accountId, $amount, $description);
        $stmt->execute();
        return $mysqli->insert_id;
    }
    
    public static function addWithdrawal(int $accountId, float $amount, string $description): int
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            INSERT INTO transactions (account_id, type, amount, description) 
            VALUES (?, 'withdrawal', ?, ?)
        ");
        $stmt->bind_param('ids', $accountId, $amount, $description);
        $stmt->execute();
        return $mysqli->insert_id;
    }
    
    public static function getAll(int $accountId): array
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            SELECT id, type, amount, description, created_at 
            FROM transactions 
            WHERE account_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public static function findById(int $transactionId, int $accountId): ?array
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            SELECT id, type, amount, description, created_at 
            FROM transactions 
            WHERE id = ? AND account_id = ?
        ");
        $stmt->bind_param('ii', $transactionId, $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }
    
    public static function updateDescription(int $transactionId, int $accountId, string $description): bool
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            UPDATE transactions 
            SET description = ? 
            WHERE id = ? AND account_id = ?
        ");
        $stmt->bind_param('sii', $description, $transactionId, $accountId);
        return $stmt->execute();
    }

    public static function getLastTransaction(int $accountId): ?array
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            SELECT id, type, amount, created_at 
            FROM transactions 
            WHERE account_id = ? 
            ORDER BY created_at DESC, id DESC 
            LIMIT 1
        ");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public static function deleteTransaction(int $transactionId, int $accountId): bool
    {
        $mysqli = Database::getConnection();
        $stmt = $mysqli->prepare("
            DELETE FROM transactions 
            WHERE id = ? AND account_id = ?
        ");
        $stmt->bind_param('ii', $transactionId, $accountId);
        return $stmt->execute();
    }

}