<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $u) {
    echo "id={$u->id} name={$u->name} email={$u->email} tiktok_username={$u->tiktok_username}\n";
    echo "  tiktok_open_id={$u->tiktok_open_id}\n";
    echo "  tiktok_access_token=" . ($u->tiktok_access_token ? substr($u->tiktok_access_token, 0, 30) . '...' : 'null') . "\n";
    $token = $u->createToken('api-token');
    echo "  NEW TOKEN: {$token->plainTextToken}\n";
}
