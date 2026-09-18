<?php

namespace App\Services;

use Database\Seeders\DeploymentSeeder;
use Database\Seeders\HostedDemoSeeder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

class BrowserMaintenanceService
{
    public function handle(Request $request, string $page, string $csrfToken): Response
    {
        $token = config('browser-maintenance.token');
        if (! config('browser-maintenance.enabled')) {
            return response('Maintenance is disabled. In the hosted clinobserve_app/.env, set BROWSER_MAINTENANCE_ENABLED=true. If settings are cached, remove clinobserve_app/bootstrap/cache/config.php in File Manager, then reload.', 404);
        }
        if (! is_string($token) || strlen($token) < 32) {
            return response('Maintenance token is not configured. Set BROWSER_MAINTENANCE_TOKEN in the hosted clinobserve_app/.env to a random value of at least 32 characters. Remove bootstrap/cache/config.php if present, then reload. Do not use APP_KEY as the maintenance token.', 503);
        }
        if (! $request->isSecure()) {
            return response('HTTPS is required.', 403);
        }
        $actions = match ($page) {
            'migrations' => [
                'status' => 'Check migration status',
                'migrate' => 'Run pending migrations',
                'fresh-migrate' => 'Delete all database tables and migrate without seeding',
                'fresh-seed' => 'Delete all database tables, migrate and seed',
                'clear-caches' => 'Clear configuration, route and view caches',
            ],
            'seeder' => ['initial-hod' => 'Create initial HOD', 'demo' => 'Add synthetic demo students and cases'],
            default => [],
        };
        if ($actions === []) {
            return response('Not found.', 404);
        }
        if (! $request->isMethod('GET') && ! $request->isMethod('POST')) {
            return response('Method not allowed.', 405, ['Allow' => 'GET, POST']);
        }

        $output = null;
        $status = 200;
        if ($request->isMethod('POST')) {
            $input = $request->request->all();
            $submittedToken = $input['maintenance_token'] ?? null;
            $submittedCsrf = $input['csrf_token'] ?? null;
            if (! is_string($submittedToken) || ! hash_equals($token, $submittedToken)) {
                return response('Invalid maintenance token.', 403);
            }
            if ($csrfToken === '' || ! is_string($submittedCsrf) || ! hash_equals($csrfToken, $submittedCsrf)) {
                return response('Session expired. Reload the page and try again.', 419);
            }
            $action = $input['action'] ?? null;
            if (! is_string($action) || ! isset($actions[$action])) {
                return response('Select an available action and confirm it.', 422);
            }
            if (in_array($action, ['demo', 'fresh-seed'], true) && ! config('browser-maintenance.allow_demo_seed')) {
                return response('Hosted demo seeding is disabled.', 403);
            }
            if (($input['confirm'] ?? null) !== 'yes') {
                return response('Select an available action and confirm it.', 422);
            }

            $lock = fopen(storage_path('framework/browser-maintenance.lock'), 'c');
            if ($lock === false) {
                return response('Cannot open the maintenance lock. Check storage permissions.', 503);
            }
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                fclose($lock);

                return response('Another maintenance operation is running. Try again after it finishes.', 409);
            }
            try {
                if ($action === 'fresh-migrate') {
                    $hod = Validator::make($input, [
                        'hod_name' => ['required', 'string', 'max:255'],
                        'hod_email' => ['required', 'email', 'max:255'],
                        'hod_password' => ['required', 'string', 'confirmed', Password::min(12)->letters()->numbers(), 'max:255'],
                    ])->validate();
                    config(['browser-maintenance.initial_hod' => ['name' => $hod['hod_name'], 'email' => $hod['hod_email'], 'password' => $hod['hod_password']]]);
                }

                if ($action === 'fresh-seed') {
                    $validated = Validator::make($input, [
                        'reset_confirmation' => ['required', 'in:RESET DATABASE'],
                        'database_name' => ['required', 'string'],
                    ])->validate();
                    if (! hash_equals(DB::connection()->getDatabaseName(), $validated['database_name'])) {
                        throw ValidationException::withMessages(['database_name' => 'The database name does not match the configured connection. No tables were deleted.']);
                    }
                }

                if ($action === 'initial-hod' && ! empty($input['hod_email'])) {
                    $hod = Validator::make($input, [
                        'hod_name' => ['required', 'string', 'max:255'],
                        'hod_email' => ['required', 'email', 'max:255'],
                        'hod_password' => ['required', 'string', 'confirmed', Password::min(12)->letters()->numbers(), 'max:255'],
                    ])->validate();
                    config(['browser-maintenance.initial_hod' => ['name' => $hod['hod_name'], 'email' => $hod['hod_email'], 'password' => $hod['hod_password']]]);
                }
                $commands = match ($action) {
                    'status' => [['migrate:status', []]],
                    'migrate' => [['migrate', ['--force' => true]]],
                    'fresh-migrate' => [
                        ['db:wipe', ['--drop-views' => true, '--force' => true]],
                        ['migrate', ['--force' => true]],
                    ],
                    'fresh-seed' => [
                        ['db:wipe', ['--drop-views' => true, '--force' => true]],
                        ['migrate', ['--force' => true]],
                        ['db:seed', ['--class' => \Database\Seeders\DemoSeeder::class, '--force' => true]],
                    ],
                    'clear-caches' => [['config:clear', []], ['route:clear', []], ['view:clear', []]],
                    'initial-hod' => [['db:seed', ['--class' => DeploymentSeeder::class, '--force' => true]]],
                    'demo' => [['db:seed', ['--class' => HostedDemoSeeder::class, '--force' => true]]],
                };
                $output = '';
                foreach ($commands as [$command, $arguments]) {
                    $buffer = new BufferedOutput(decorated: false);

                    if ($command === 'db:seed' && isset($arguments['--class'])) {
                        $class = $arguments['--class'];
                        if (! is_string($class) || ! class_exists($class)) {
                            $status = 500;
                            $output = 'The operation did not complete. Check database settings and uploaded migrations in File Manager. Check migration status before retrying; earlier steps may have completed.';
                            Log::warning('Browser maintenance seeder class not found.', ['class' => $class ?? null]);
                            break;
                        }

                        try {
                            app($class)->run();
                        } catch (ValidationException $exception) {
                            throw $exception;
                        } catch (Throwable $exception) {
                            $status = 500;
                            $output = 'The operation did not complete. Check database settings and uploaded migrations in File Manager. Check migration status before retrying; earlier steps may have completed.';
                            Log::warning('Browser maintenance seeder failed.', ['class' => $class, 'exception_type' => $exception::class]);
                            break;
                        }

                        $output .= $class.' .. DONE' . "\n";
                        continue;
                    }

                    $exitCode = Artisan::call($command, $arguments + ['--no-interaction' => true, '--no-ansi' => true], $buffer);
                    if ($exitCode !== 0) {
                        $status = 500;
                        $output = 'The operation did not complete. Check database settings and uploaded migrations in File Manager. Check migration status before retrying; earlier steps may have completed.';
                        Log::warning('Browser maintenance command failed.', ['command' => $command, 'exit_code' => $exitCode]);
                        break;
                    }
                    $output .= $buffer->fetch()."\n";
                }
            } catch (ValidationException $exception) {
                $status = 422;
                $output = implode("\n", $exception->validator->errors()->all());
            } catch (Throwable $exception) {
                $status = 500;
                $output = 'The operation failed. Check database settings, pending migrations and initial HOD configuration. Check migration status before retrying.';
                Log::warning('Browser maintenance failed.', ['action' => $action, 'exception_type' => $exception::class]);
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }

        return response()->view('maintenance', compact('page', 'actions', 'csrfToken', 'output'), $status, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
