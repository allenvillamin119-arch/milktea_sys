<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;

$transactions = Transaction::orderBy('id', 'desc')->limit(10)->get();

if ($transactions->isEmpty()) {
    echo "No transactions found\n";
    exit(0);
}

foreach ($transactions as $t) {
    echo sprintf("ID: %s | Number: %s | Created: %s | User: %s\n", $t->id, $t->transaction_number, $t->created_at ? $t->created_at->toDateTimeString() : 'NULL', $t->user_id);
}


