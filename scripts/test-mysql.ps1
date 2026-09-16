$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
Set-Location -LiteralPath $projectRoot
$connection = Get-Content '.runtime/database.json' -Raw | ConvertFrom-Json
$env:DB_CONNECTION = 'mysql'
$env:DB_HOST = $connection.host
$env:DB_PORT = [string]$connection.port
$env:DB_DATABASE = 'clinobserve_test'
$env:DB_USERNAME = $connection.username
$env:DB_PASSWORD = $connection.password
$env:DB_URL = ''
$phpPath = $env:CLINOBSERVE_PHP
if (!$phpPath) { $phpPath = 'D:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe' }
try { & $phpPath artisan test --compact; $testExitCode = $LASTEXITCODE }
finally {
    foreach ($key in @('DB_CONNECTION','DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','DB_PASSWORD','DB_URL')) { [Environment]::SetEnvironmentVariable($key,$null,'Process') }
}
exit $testExitCode

