<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@congope.gob.ec')->first();
if ($user) {
    echo $user->createToken('scribe')->plainTextToken;
} else {
    echo "USER_NOT_FOUND";
}
