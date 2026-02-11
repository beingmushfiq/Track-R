<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('vehicles');
echo "Vehicle Columns: " . implode(', ', $columns) . "\n";

$routes = \Illuminate\Support\Facades\Route::getRoutes()->getRoutes();
$diagRoutes = array_filter($routes, function($r) {
    return str_contains($r->uri(), 'diagnostics');
});

echo "Diagnostic Routes Count: " . count($diagRoutes) . "\n";
foreach($diagRoutes as $r) {
    echo $r->methods()[0] . " " . $r->uri() . "\n";
}
