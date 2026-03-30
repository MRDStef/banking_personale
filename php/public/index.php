<?php

use Slim\Factory\AppFactory;
use MiniBanking\Controllers\BankingController;

require __DIR__ . '/../vendor/autoload.php';

// Crea l'app
$app = AppFactory::create();

// Aggiungi parsing JSON per le richieste
$app->addBodyParsingMiddleware();

// ROUTE DI TEST
$app->get('/test', function ($request, $response) {
    $response->getBody()->write(json_encode(['message' => 'Test OK', 'status' => 'working']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Endpoint per i movimenti
$app->get('/accounts/{id}/transactions', [BankingController::class, 'getTransactions']);
$app->get('/accounts/{id}/transactions/{tid}', [BankingController::class, 'getTransaction']);
$app->get('/accounts/{id}/balance', [BankingController::class, 'getBalance']);

$app->get('/accounts/{id}/balance/convert/fiat', [BankingController::class, 'convertToFiat']);
$app->get('/accounts/{id}/balance/convert/crypto', [BankingController::class, 'convertToCrypto']);



$app->post('/accounts/{id}/deposits', [BankingController::class, 'postDeposit']);
$app->post('/accounts/{id}/withdrawals', [BankingController::class, 'postWithdrawal']);



$app->put('/accounts/{id}/transactions/{tid}', [BankingController::class, 'updateTransaction']);



$app->delete('/accounts/{id}/transactions/{tid}', [BankingController::class, 'deleteTransaction']);

// Avvia l'app
$app->run();