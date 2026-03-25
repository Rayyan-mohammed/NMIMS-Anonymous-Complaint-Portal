param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('backup', 'restore')]
    [string]$Action,

    [string]$ArchivePath = '',
    [string]$BackupRoot = '',
    [string]$DbName = '',
    [string]$DbUser = '',
    [string]$MySqlBinPath = 'C:\xampp\mysql\bin',
    [string]$LogsPath = ''
)

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot

if ([string]::IsNullOrWhiteSpace($BackupRoot)) {
    $BackupRoot = Join-Path $projectRoot 'storage\backups'
}

if ([string]::IsNullOrWhiteSpace($LogsPath)) {
    $LogsPath = Join-Path $projectRoot 'storage\logs'
}

if ([string]::IsNullOrWhiteSpace($DbName)) {
    $DbName = if ($env:DB_NAME) { $env:DB_NAME } else { 'apnm6_db' }
}

if ([string]::IsNullOrWhiteSpace($DbUser)) {
    $DbUser = if ($env:DB_USER) { $env:DB_USER } else { 'root' }
}

$dbSecretFromEnv = if ($env:DB_PASS) { $env:DB_PASS } else { '' }

$mysqldumpExe = Join-Path $MySqlBinPath 'mysqldump.exe'
$mysqlExe = Join-Path $MySqlBinPath 'mysql.exe'

if ($Action -eq 'backup' -and !(Test-Path $mysqldumpExe)) {
    throw "mysqldump.exe not found at $mysqldumpExe"
}

if ($Action -eq 'restore' -and !(Test-Path $mysqlExe)) {
    throw "mysql.exe not found at $mysqlExe"
}

New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

function New-MySqlCliOptions {
    param([string]$Database)

    $mysqlCliOptions = New-Object System.Collections.ArrayList
    [void]$mysqlCliOptions.Add('-u')
    [void]$mysqlCliOptions.Add($DbUser)

    if (-not [string]::IsNullOrWhiteSpace($dbSecretFromEnv)) {
        [void]$mysqlCliOptions.Add("--password=$dbSecretFromEnv")
    }

    if (-not [string]::IsNullOrWhiteSpace($Database)) {
        [void]$mysqlCliOptions.Add($Database)
    }

    return [string[]]$mysqlCliOptions
}

if ($Action -eq 'backup') {
    $stamp = Get-Date -Format 'yyyyMMdd_HHmmss'
    $workDir = Join-Path $BackupRoot "backup_$stamp"
    $sqlPath = Join-Path $workDir 'database.sql'
    $logsSnapshot = Join-Path $workDir 'logs'
    $metaPath = Join-Path $workDir 'backup_meta.txt'
    $zipPath = Join-Path $BackupRoot "apnm6_backup_$stamp.zip"

    New-Item -ItemType Directory -Path $workDir -Force | Out-Null

    $dumpOptions = @('--single-transaction', '--routines', '--triggers') + (New-MySqlCliOptions -Database $DbName)
    & $mysqldumpExe @dumpOptions > $sqlPath

    if (Test-Path $LogsPath) {
        Copy-Item -Path $LogsPath -Destination $logsSnapshot -Recurse -Force
    }

    @(
        "created_at=$((Get-Date).ToString('s'))",
        "db_name=$DbName",
        "db_user=$DbUser",
        "logs_included=$([bool](Test-Path $LogsPath))",
        "source_logs_path=$LogsPath"
    ) | Set-Content -Path $metaPath -Encoding UTF8

    Compress-Archive -Path (Join-Path $workDir '*') -DestinationPath $zipPath -CompressionLevel Optimal -Force
    Remove-Item -Path $workDir -Recurse -Force

    Write-Output "Backup completed: $zipPath"
    exit 0
}

if ([string]::IsNullOrWhiteSpace($ArchivePath)) {
    throw 'ArchivePath is required for restore action.'
}

if (!(Test-Path $ArchivePath)) {
    throw "Backup archive not found: $ArchivePath"
}

$tempRestore = Join-Path $env:TEMP ("apnm6_restore_" + [Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $tempRestore -Force | Out-Null

try {
    Expand-Archive -Path $ArchivePath -DestinationPath $tempRestore -Force

    $sqlFile = Join-Path $tempRestore 'database.sql'
    if (!(Test-Path $sqlFile)) {
        throw 'database.sql not found in archive.'
    }

    $mysqlOptions = New-MySqlCliOptions -Database $DbName
    Get-Content -Path $sqlFile | & $mysqlExe @mysqlOptions

    $archivedLogsPath = Join-Path $tempRestore 'logs'
    if (Test-Path $archivedLogsPath) {
        $restoreStamp = Get-Date -Format 'yyyyMMdd_HHmmss'
        $targetLogPath = Join-Path $LogsPath ("restored_$restoreStamp")
        New-Item -ItemType Directory -Path $targetLogPath -Force | Out-Null
        Copy-Item -Path (Join-Path $archivedLogsPath '*') -Destination $targetLogPath -Recurse -Force
        Write-Output "Logs restored to: $targetLogPath"
    }

    Write-Output "Restore completed from: $ArchivePath"
}
finally {
    if (Test-Path $tempRestore) {
        Remove-Item -Path $tempRestore -Recurse -Force
    }
}
