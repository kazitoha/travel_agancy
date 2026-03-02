<?php

use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Run the migrate command
Artisan::call('migrate', ['--force' => true]); // The --force flag is important for production environments

echo 'Migration complete.';
