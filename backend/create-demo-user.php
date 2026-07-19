<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::create([
    'name' => 'Demo User',
    'email' => 'demo@tiktok.com',
    'password' => bcrypt('password'),
]);
echo "User created: id={$user->id}\n";
