<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BrowserMaintenanceService;
use App\UserRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BrowserMaintenanceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const TOKEN = 'test-maintenance-token-with-at-least-32-characters';

    private const CSRF = 'test-session-csrf-value';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'browser-maintenance.enabled' => true,
            'browser-maintenance.token' => self::TOKEN,
            'browser-maintenance.allow_demo_seed' => false,
            'browser-maintenance.initial_hod' => ['name' => '', 'email' => '', 'password' => ''],
        ]);
        Route::match(['GET', 'POST', 'PUT'], '/test-maintenance/{page}', fn (Request $request, string $page) => app(BrowserMaintenanceService::class)->handle($request, $page, self::CSRF));
    }

    public function test_page_visits_do_not_execute_commands_or_expose_credentials(): void
    {
        $this->get('https://localhost/test-maintenance/seeder?action=initial-hod&maintenance_token='.self::TOKEN)
            ->assertSee('Create initial HOD')->assertDontSee(self::TOKEN)->assertHeader('Cache-Control', 'no-store, private');
        $this->assertDatabaseCount('users', 0);
        $this->get('https://localhost/test-maintenance/migrations')->assertSee('Run pending migrations');
    }

    #[TestWith([false, self::TOKEN, 404])]
    #[TestWith([true, 'short', 503])]
    public function test_disabled_or_unconfigured_maintenance_is_not_available(bool $enabled, string $token, int $status): void
    {
        config(['browser-maintenance.enabled' => $enabled, 'browser-maintenance.token' => $token]);
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('initial-hod'))->assertStatus($status)->assertSee('BROWSER_MAINTENANCE_');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_insecure_requests_wrong_token_and_invalid_csrf_are_denied(): void
    {
        $this->post('http://localhost/test-maintenance/seeder', $this->payload('initial-hod'))->assertForbidden();
        $this->post('https://localhost/test-maintenance/seeder', array_replace($this->payload('initial-hod'), ['maintenance_token' => 'wrong']))->assertForbidden();
        $this->post('https://localhost/test-maintenance/seeder', array_replace($this->payload('initial-hod'), ['csrf_token' => 'wrong']))->assertStatus(419);
        $this->post('https://localhost/test-maintenance/seeder?maintenance_token='.self::TOKEN, ['action' => 'initial-hod', 'csrf_token' => self::CSRF, 'confirm' => 'yes'])->assertForbidden();
        $this->assertDatabaseCount('users', 0);
    }

    #[TestWith(['migrate:fresh'])]
    #[TestWith(['db:wipe'])]
    #[TestWith(['seed'])]
    public function test_unlisted_commands_cannot_be_executed(string $action): void
    {
        $hod = User::factory()->hod()->create();
        $this->post('https://localhost/test-maintenance/migrations', $this->payload($action))->assertUnprocessable();
        $this->assertModelExists($hod);
    }

    public function test_confirmation_is_required_and_operations_are_page_specific(): void
    {
        $this->post('https://localhost/test-maintenance/seeder', array_replace($this->payload('initial-hod'), ['confirm' => 'no']))->assertUnprocessable();
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('migrate'))->assertUnprocessable();
        $this->put('https://localhost/test-maintenance/migrations', $this->payload('migrate'))->assertStatus(405);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_initial_hod_seeding_validates_credentials_and_preserves_existing_accounts(): void
    {
        config(['browser-maintenance.initial_hod' => ['name' => 'Department Head', 'email' => 'head@example.test', 'password' => 'short']]);
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('initial-hod'))->assertUnprocessable();
        $this->assertDatabaseCount('users', 0);
        config(['browser-maintenance.initial_hod.password' => 'InitialPassword123!']);
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('initial-hod'))->assertSee('Initial HOD created')->assertDontSee('InitialPassword123!');
        $hod = User::where('email', 'head@example.test')->firstOrFail();
        $this->assertSame(UserRole::Hod, $hod->role);
        $this->assertTrue($hod->must_change_password);
        $this->assertTrue(Hash::check('InitialPassword123!', $hod->password));
        config(['browser-maintenance.initial_hod.password' => 'ReplacementPassword123!']);
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('initial-hod'))->assertSee('already exists');
        $this->assertSame($hod->password, $hod->fresh()->password);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_initial_hod_does_not_promote_an_existing_student(): void
    {
        $student = User::factory()->create(['email' => 'head@example.test']);
        config(['browser-maintenance.initial_hod' => ['name' => 'Head', 'email' => $student->email, 'password' => 'InitialPassword123!']]);
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('initial-hod'))->assertUnprocessable();
        $this->assertSame(UserRole::Student, $student->fresh()->role);
    }

    public function test_hod_credentials_can_be_supplied_in_the_browser_form(): void
    {
        $this->post('https://localhost/test-maintenance/seeder', array_replace($this->payload('initial-hod'), [
            'hod_name' => 'Browser Head', 'hod_email' => 'browser@example.test',
            'hod_password' => 'BrowserPassword123!', 'hod_password_confirmation' => 'BrowserPassword123!',
        ]))->assertSee('Initial HOD created')->assertDontSee('BrowserPassword123!');
        $this->assertDatabaseHas('users', ['email' => 'browser@example.test', 'role' => 'hod']);
    }

    #[TestWith(['reset_confirmation', 'wrong'])]
    #[TestWith(['database_name', 'another-database'])]
    public function test_reset_validates_all_inputs_before_deleting_anything(string $field, string $value): void
    {
        $hod = User::factory()->hod()->create();
        config(['browser-maintenance.allow_demo_seed' => true]);
        $input = array_replace($this->payload('fresh-seed'), [
            'reset_confirmation' => 'RESET DATABASE', 'database_name' => ':memory:',
        ], [$field => $value]);
        $this->post('https://localhost/test-maintenance/migrations', $input)->assertUnprocessable();
        $this->assertModelExists($hod);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_demo_seed_requires_explicit_enablement_and_an_active_hod(): void
    {
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('demo'))->assertForbidden();
        config(['browser-maintenance.allow_demo_seed' => true]);
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('demo'))->assertUnprocessable()->assertSee('Create or activate an HOD');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_full_reset_seed_does_not_require_hod_setup_but_reset_without_seed_does(): void
    {
        config(['browser-maintenance.allow_demo_seed' => true]);
        $databaseName = DB::connection()->getDatabaseName();

        $this->post('https://localhost/test-maintenance/migrations', array_replace($this->payload('fresh-seed'), [
            'reset_confirmation' => 'RESET DATABASE', 'database_name' => $databaseName,
        ]))->assertOk()->assertSee('migrat');

        $this->post('https://localhost/test-maintenance/migrations', array_replace($this->payload('fresh-migrate'), [
            'reset_confirmation' => 'RESET DATABASE', 'database_name' => $databaseName,
            'hod_name' => 'Head', 'hod_email' => 'head@example.test',
            'hod_password' => 'HeadPassword123!', 'hod_password_confirmation' => 'HeadPassword123!',
        ]))->assertUnprocessable()->assertSee('hod');
    }

    public function test_hosted_demo_seed_creates_realistic_clinical_cases(): void
    {
        $this->freezeTime();
        $hod = User::factory()->hod()->create();
        config(['browser-maintenance.allow_demo_seed' => true]);

        $this->post('https://localhost/test-maintenance/seeder', $this->payload('demo'))->assertOk()->assertSee('HostedDemoSeeder');
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('student_profiles', 1);
        $this->assertDatabaseCount('patients', 10);
        $this->assertDatabaseCount('patient_encounters', 0);
        $this->assertDatabaseCount('hod_reviews', 0);
        $this->assertDatabaseHas('patients', ['display_name' => 'Amit', 'condition' => 'Acute asthma exacerbation']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Riya', 'condition' => 'Lower respiratory tract infection']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Sanjay Kumar', 'condition' => 'Type 2 diabetes with poor glycaemic control']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Fatima Ali', 'condition' => 'Acute gastroenteritis']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Rahul', 'condition' => 'Acute myocardial infarction']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Ananya Bose', 'condition' => 'Acute appendicitis']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Nisha', 'condition' => 'Urinary tract infection']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Vinod', 'condition' => 'Stroke with hemiparesis']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Priya', 'condition' => 'Deep vein thrombosis']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Kabir', 'condition' => 'Acute hepatitis A']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Amit', 'confirmed_diagnosis' => 'Acute exacerbation of bronchial asthma']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Riya', 'confirmed_diagnosis' => 'Community-acquired pneumonia']);
        $this->assertDatabaseHas('patients', ['display_name' => 'Sanjay Kumar', 'confirmed_diagnosis' => 'Poorly controlled type 2 diabetes mellitus']);
        $student = User::where('email', 'student@clinobserve.test')->firstOrFail();
        $this->assertTrue($student->professor->isProfessor());
        $this->assertFalse($student->must_change_password);
        $this->assertTrue(Hash::check('Student@12345', $student->password));
        $this->post('https://localhost/test-maintenance/seeder', $this->payload('demo'))->assertOk();
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('patients', 10);
        $this->assertDatabaseCount('patient_encounters', 0);
        $this->assertDatabaseCount('hod_reviews', 0);
        $this->assertSame($student->password, $student->fresh()->password);
        $this->assertSame($hod->password, $hod->fresh()->password);
    }

    public function test_demo_seed_creates_missing_patient_cases_when_demo_users_exist(): void
    {
        $hod = User::factory()->hod()->create(['email' => 'hod@clinobserve.test']);
        $professor = User::factory()->professor()->create(['email' => 'professor@clinobserve.test', 'created_by' => $hod->id]);
        User::factory()->create(['email' => 'student@clinobserve.test', 'role' => UserRole::Student, 'created_by' => $hod->id, 'professor_id' => $professor->id]);
        config(['browser-maintenance.allow_demo_seed' => true]);

        $this->post('https://localhost/test-maintenance/seeder', $this->payload('demo'))->assertOk();
        $this->assertDatabaseCount('patients', 10);
        $this->assertDatabaseHas('users', ['email' => 'student@clinobserve.test', 'role' => 'student']);
    }

    public function test_application_login_does_not_replace_the_maintenance_token(): void
    {
        $this->actingAs(User::factory()->hod()->create())
            ->post('https://localhost/test-maintenance/migrations', ['action' => 'migrate', 'csrf_token' => self::CSRF, 'confirm' => 'yes'])
            ->assertForbidden();
    }

    public function test_command_failures_do_not_disclose_connection_details(): void
    {
        config(['database.default' => 'missing-private-connection-secret']);
        $this->post('https://localhost/test-maintenance/migrations', $this->payload('status'))
            ->assertStatus(500)->assertSee('Check database settings')->assertDontSee('missing-private-connection-secret')->assertDontSee(self::TOKEN);
    }

    public function test_concurrent_maintenance_is_rejected(): void
    {
        $lock = fopen(storage_path('framework/browser-maintenance.lock'), 'c');
        flock($lock, LOCK_EX);
        try {
            $this->post('https://localhost/test-maintenance/seeder', $this->payload('initial-hod'))->assertConflict();
            $this->assertDatabaseCount('users', 0);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function test_real_entry_points_migrate_without_database_sessions_and_seed_in_production(): void
    {
        $database = tempnam(sys_get_temp_dir(), 'clinobserve-maintenance-');
        $sessionDirectory = sys_get_temp_dir().'/clinobserve-session-'.bin2hex(random_bytes(8));
        mkdir($sessionDirectory);
        try {
            $environment = [
                'APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_URL' => 'https://localhost',
                'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database, 'DB_URL' => '',
                'SESSION_DRIVER' => 'database', 'CACHE_STORE' => 'file', 'BCRYPT_ROUNDS' => '4',
                'BROWSER_MAINTENANCE_ENABLED' => 'true', 'BROWSER_MAINTENANCE_TOKEN' => self::TOKEN,
                'BROWSER_MAINTENANCE_ALLOW_DEMO_SEED' => 'true',
                'INITIAL_HOD_NAME' => 'Hosted Head', 'INITIAL_HOD_EMAIL' => 'hosted-head@example.test', 'INITIAL_HOD_PASSWORD' => 'HostedHeadPassword123!',
                'APP_CONFIG_CACHE' => $sessionDirectory.'/config.php', 'APP_ROUTES_CACHE' => $sessionDirectory.'/routes.php',
                'VIEW_COMPILED_PATH' => $sessionDirectory,
            ];
            $this->entryPoint('run-migrations.php', [], $environment, $sessionDirectory)->assertSee('Run pending migrations');
            $this->assertSame(0, filesize($database));
            $this->entryPoint('run-migrations.php', $this->payload('migrate'), $environment, $sessionDirectory)->assertSee('HTTP_STATUS=200')->assertSee('add_professor_id_to_users_table');
            $this->entryPoint('run-migrations.php', $this->payload('status'), $environment, $sessionDirectory)->assertSee('HTTP_STATUS=200');
            $this->entryPoint('run-seeder.php', $this->payload('initial-hod'), $environment, $sessionDirectory)->assertSee('Initial HOD created')->assertDontSee('HostedHeadPassword123!');
            $this->entryPoint('run-seeder.php', $this->payload('demo'), $environment, $sessionDirectory)->assertSee('Created 1 demo professor');
            $resetInput = array_replace($this->payload('fresh-seed'), [
                'reset_confirmation' => 'RESET DATABASE', 'database_name' => $database,
            ]);
            $this->entryPoint('run-migrations.php', $resetInput, $environment, $sessionDirectory)
                ->assertSee('HTTP_STATUS=200')->assertDontSee('Initial HOD created');
            $connection = new \PDO('sqlite:'.$database);
            $this->assertSame(0, (int) $connection->query("select count(*) from users where email = 'hosted-head@example.test'")->fetchColumn());
            $this->assertSame(3, (int) $connection->query('select count(*) from users')->fetchColumn());
            $this->assertSame(0, (int) $connection->query('select count(*) from patient_encounters')->fetchColumn());
            $connection = null;
            $this->entryPoint('run-migrations.php', $this->payload('clear-caches'), $environment, $sessionDirectory)->assertSee('HTTP_STATUS=200')->assertSee('cleared');
        } finally {
            @unlink($database);
            foreach (glob($sessionDirectory.'/*') as $sessionFile) {
                unlink($sessionFile);
            }
            rmdir($sessionDirectory);
        }
    }

    /** @return array<string, string> */
    private function payload(string $action): array
    {
        return ['maintenance_token' => self::TOKEN, 'csrf_token' => self::CSRF, 'action' => $action, 'confirm' => 'yes'];
    }

    /** @param array<string, string> $input
     * @param  array<string, string>  $environment
     */
    private function entryPoint(string $file, array $input, array $environment, string $sessionDirectory): TestResponse
    {
        $script = '$_SERVER["HTTPS"]="on"; $_SERVER["HTTP_HOST"]="localhost"; $_SERVER["REQUEST_URI"]="/'.$file.'"; '
            .'$_SERVER["REQUEST_METHOD"]='.var_export($input === [] ? 'GET' : 'POST', true).'; '
            .'$_POST='.var_export($input, true).'; '
            .'session_save_path('.var_export($sessionDirectory, true).'); '
            .'session_name("clinobserve_maintenance"); session_start(); $_SESSION["csrf"]='.var_export(self::CSRF, true).'; session_write_close(); '
            .'require '.var_export(base_path($file), true).'; echo "HTTP_STATUS=".http_response_code();';
        $process = new Process([PHP_BINARY, '-r', $script], base_path(), $environment, timeout: 60);
        $process->mustRun();

        return TestResponse::fromBaseResponse(response($process->getOutput()));
    }
}
