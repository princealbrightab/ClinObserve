param([int]$Port = 8000)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
Set-Location -LiteralPath $projectRoot
$phpPath = $env:CLINOBSERVE_PHP
if (!$phpPath) {
    $installedPhp = 'D:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe'
    if (Test-Path -LiteralPath $installedPhp) { $phpPath = $installedPhp }
    else { $phpPath = (Get-Command php -ErrorAction Stop).Source }
}
if (Test-Path '.runtime/database.json') {
    $connection = Get-Content '.runtime/database.json' -Raw | ConvertFrom-Json
    $listening = Get-NetTCPConnection -LocalPort $connection.port -State Listen -ErrorAction SilentlyContinue
    if (!$listening) {
        $mysqlBin = 'D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe'
        $base = 'D:/laragon/bin/mysql/mysql-8.4.3-winx64'
        $data = ($projectRoot.Replace('\','/'))+'/.runtime/mysql-data'
        $mysqlProcess = Start-Process -FilePath $mysqlBin -ArgumentList @('--no-defaults',"--basedir=$base","--datadir=$data","--port=$($connection.port)",'--bind-address=127.0.0.1','--mysqlx=OFF',"--log-error=$data/server.log") -WindowStyle Hidden -PassThru
        Set-Content '.runtime/mysql.pid' -Value $mysqlProcess.Id
        Write-Host 'Project MySQL started. Allow a few seconds for startup.'
    }
}
& $phpPath artisan serve --host=127.0.0.1 --port=$Port

