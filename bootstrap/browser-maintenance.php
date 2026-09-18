<?php

use App\Services\BrowserMaintenanceService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

ini_set('display_errors', '0');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");

try {
    if (! isset($maintenancePage) || ! in_array($maintenancePage, ['migrations', 'seeder'], true)) {
        http_response_code(404);
        exit('Not found.');
    }

    require dirname(__DIR__).'/vendor/autoload.php';
    $app = require __DIR__.'/app.php';
    $app->make(Kernel::class)->bootstrap();
    $request = Request::capture();

    if (! $request->isSecure()) {
        http_response_code(403);
        exit('HTTPS is required.');
    }

    /** Native sessions keep setup independent of the not-yet-migrated sessions table. */
    session_name('clinobserve_maintenance');
    session_set_cookie_params(['secure' => true, 'httponly' => true, 'samesite' => 'Strict', 'path' => '/']);
    if (! session_start(['use_strict_mode' => 1, 'use_only_cookies' => 1])) {
        throw new RuntimeException('Maintenance session unavailable.');
    }
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    $csrfToken = $_SESSION['csrf'];
    session_write_close();

    $app->make(BrowserMaintenanceService::class)->handle($request, $maintenancePage, $csrfToken)->send();
} catch (Throwable $exception) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    error_log('ClinObserve maintenance bootstrap failed: '.$exception::class);
    echo 'Maintenance could not start. Check PHP requirements, uploaded vendor files, .env settings, and writable storage folders in File Manager.';
}
