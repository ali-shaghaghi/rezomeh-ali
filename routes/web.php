<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Load module routes
$moduleRoutes = [
    'Admin',
];

foreach ($moduleRoutes as $module) {
    $routeFile = base_path("Modules/{$module}/routes/web.php");
    if (file_exists($routeFile)) {
        require $routeFile;
    }
}
