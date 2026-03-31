<?php

namespace MiniBanking\Controllers;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MiniBanking\Models\Account;
use MiniBanking\Models\Transaction;

class BankingController
{
    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
    
    private function errorResponse(Response $response, string $message, int $status): Response
    {
        return $this->jsonResponse($response, ['error' => $message], $status);
    }
    
    public function getTransactions(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $transactions = Transaction::getAll($accountId);
        return $this->jsonResponse($response, $transactions);
    }
    
    public function getTransaction(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        $transactionId = (int)$args['tid'];
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $transaction = Transaction::findById($transactionId, $accountId);
        if (!$transaction) {
            return $this->errorResponse($response, 'Transaction not found', 404);
        }
        
        return $this->jsonResponse($response, $transaction);
    }
    
    public function postDeposit(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        $data = $request->getParsedBody();
        
        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            return $this->errorResponse($response, 'Amount must be greater than zero', 400);
        }
        
        if (empty($data['description'])) {
            return $this->errorResponse($response, 'Description is required', 400);
        }
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $amount = (float)$data['amount'];
        $description = htmlspecialchars($data['description']);
        
        $transactionId = Transaction::addDeposit($accountId, $amount, $description);
        
        return $this->jsonResponse($response, [
            'message' => 'Deposit successful',
            'transaction_id' => $transactionId,
            'amount' => $amount
        ], 201);
    }
    
    public function postWithdrawal(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        $data = $request->getParsedBody();
        
        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            return $this->errorResponse($response, 'Amount must be greater than zero', 400);
        }
        
        if (empty($data['description'])) {
            return $this->errorResponse($response, 'Description is required', 400);
        }
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $amount = (float)$data['amount'];
        $currentBalance = Transaction::getBalance($accountId);
        
        if ($amount > $currentBalance) {
            return $this->errorResponse($response, 'Insufficient balance', 422);
        }
        
        $description = htmlspecialchars($data['description']);
        $transactionId = Transaction::addWithdrawal($accountId, $amount, $description);
        
        return $this->jsonResponse($response, [
            'message' => 'Withdrawal successful',
            'transaction_id' => $transactionId,
            'amount' => $amount
        ], 201);
    }
    
    public function getBalance(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
    
        $balance = Transaction::getBalance($accountId);
        
        return $this->jsonResponse($response, [
            'account_id' => $accountId,
            'balance' => round($balance, 2),
            'currency' => $account['currency']
        ]);
    }
    public function updateTransaction(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        $transactionId = (int)$args['tid'];
        $data = $request->getParsedBody();
        
        if (empty($data['description'])) {
            return $this->errorResponse($response, 'Description is required', 400);
        }
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $transaction = Transaction::findById($transactionId, $accountId);
        if (!$transaction) {
            return $this->errorResponse($response, 'Transaction not found', 404);
        }
        
        $description = htmlspecialchars($data['description']);
        $updated = Transaction::updateDescription($transactionId, $accountId, $description);
        
        if (!$updated) {
            return $this->errorResponse($response, 'Failed to update transaction', 500);
        }
        
        return $this->jsonResponse($response, [
            'message' => 'Description updated successfully',
            'transaction_id' => $transactionId,
            'new_description' => $description
        ]);
    }

    public function deleteTransaction(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        $transactionId = (int)$args['tid'];
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $transaction = Transaction::findById($transactionId, $accountId);
        if (!$transaction) {
            return $this->errorResponse($response, 'Transaction not found', 404);
        }
        
        $lastTransaction = Transaction::getLastTransaction($accountId);
        
        if (!$lastTransaction || $lastTransaction['id'] != $transactionId) {
            return $this->errorResponse($response, 
                'You can only delete the last transaction', 
                422
            );
        }
        
        $deleted = Transaction::deleteTransaction($transactionId, $accountId);
        
        if (!$deleted) {
            return $this->errorResponse($response, 'Failed to delete transaction', 500);
        }
        
        $newBalance = Transaction::getBalance($accountId);
        
        return $this->jsonResponse($response, [
            'message' => 'Transaction deleted successfully',
            'transaction_id' => $transactionId,
            'new_balance' => round($newBalance, 2)
        ]);
    }

    public function convertToFiat(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        $params = $request->getQueryParams();
        $to = strtoupper($params['to'] ?? '');
        
        if (!$to) {
            return $this->errorResponse($response, 'Missing target currency', 400);
        }
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $from = strtoupper($account['currency']);
        $balance = Transaction::getBalance($accountId);
        
        $client = new \GuzzleHttp\Client();
        $url = "https://api.frankfurter.dev/v1/latest?base={$from}&symbols={$to}";
        
        try {
            $apiResponse = $client->get($url);
            $data = json_decode($apiResponse->getBody(), true);
            
            if (!isset($data['rates'][$to])) {
                return $this->errorResponse($response, 'Target currency not supported', 400);
            }
            
            $rate = (float)$data['rates'][$to];
            $converted = round($balance * $rate, 2);
            
            return $this->jsonResponse($response, [
                'account_id' => $accountId,
                'provider' => 'Frankfurter',
                'conversion_type' => 'fiat',
                'from_currency' => $from,
                'to_currency' => $to,
                'original_balance' => round($balance, 2),
                'converted_balance' => $converted,
                'rate' => $rate,
                'date' => $data['date'] ?? null
            ]);
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return $this->errorResponse($response, 'External exchange API unavailable', 502);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Error fetching exchange rate', 422);
        }
    }

    public function convertToCrypto(Request $request, Response $response, array $args): Response
    {
        $accountId = (int)$args['id'];
        $params = $request->getQueryParams();
        $to = strtoupper($params['to'] ?? '');
        
        if (!$to) {
            return $this->errorResponse($response, 'Missing target cryptocurrency', 400);
        }
        
        $account = Account::find($accountId);
        if (!$account) {
            return $this->errorResponse($response, 'Account not found', 404);
        }
        
        $from = strtoupper($account['currency']);
        $balance = Transaction::getBalance($accountId);
        
        $marketSymbol = $to . $from;
        
        $client = new \GuzzleHttp\Client();
        $url = "https://api.binance.com/api/v3/ticker/price?symbol={$marketSymbol}";
        
        try {
            $apiResponse = $client->get($url);
            $data = json_decode($apiResponse->getBody(), true);
            
            if (!isset($data['price'])) {
                return $this->errorResponse($response, 'Trading pair not found', 400);
            }
            
            $price = (float)$data['price'];
            $convertedAmount = $balance / $price;
            
            $convertedAmount = round($convertedAmount, 8);
            
            return $this->jsonResponse($response, [
                'account_id' => $accountId,
                'provider' => 'Binance',
                'conversion_type' => 'crypto',
                'from_currency' => $from,
                'to_crypto' => $to,
                'market_symbol' => $marketSymbol,
                'original_balance' => round($balance, 2),
                'price' => $price,
                'converted_amount' => $convertedAmount
            ]);
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getCode() == 404) {
                return $this->errorResponse($response, "Trading pair {$marketSymbol} not found on Binance", 400);
            }
            return $this->errorResponse($response, 'Binance API error', 422);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return $this->errorResponse($response, 'Binance API unavailable', 502);
        } catch (\Exception $e) {
            return $this->errorResponse($response, 'Error fetching crypto price', 500);
        }
    }

}