

param(
    [string]$ProjectPath = (Get-Location).Path,
    [switch]$AddDefenderExclusion,
    [switch]$SkipRestartSearch
)

function Is-Admin {
    $current = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($current)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

Write-Host "Project path: $ProjectPath"

if (Is-Admin) {
    try {
        Write-Host "Stopping Windows Search (WSearch) to reduce file-lock conflicts..." -ForegroundColor Yellow
        Stop-Service -Name WSearch -ErrorAction SilentlyContinue
    } catch {
        Write-Host "Could not stop WSearch: $_" -ForegroundColor Red
    }
} else {
    Write-Host "Not running as Administrator — skipping WSearch stop. (Run as Admin to stop it)" -ForegroundColor Yellow
}

if ($AddDefenderExclusion) {
    if (-not (Is-Admin)) {
        Write-Host "Adding Defender exclusion requires Admin rights. Rerun the script as Administrator with -AddDefenderExclusion." -ForegroundColor Yellow
    } else {
        try {
            Write-Host "Adding Windows Defender exclusion for project folder..." -ForegroundColor Yellow
            Add-MpPreference -ExclusionPath $ProjectPath -ErrorAction Stop
            Write-Host "Defender exclusion added." -ForegroundColor Green
        } catch {
            Write-Host "Failed to add Defender exclusion: $_" -ForegroundColor Red
        }
    }
}

$composerTemp = Join-Path $ProjectPath 'vendor\composer' 
if (Test-Path $composerTemp) {
    Get-ChildItem -Path $composerTemp -Force -Directory | Where-Object { $_.Name -match '^[a-f0-9]{8,}$' -or $_.Name -match '^[A-Za-z0-9-]+$' } | ForEach-Object {
        try {
            Write-Host "Removing partial extraction: $($_.FullName)" -ForegroundColor Yellow
            Remove-Item -LiteralPath $_.FullName -Recurse -Force -ErrorAction SilentlyContinue
        } catch {
            Write-Host "Could not remove $($_.FullName): $_" -ForegroundColor Red
        }
    }
}

Write-Host "Clearing Composer cache..." -ForegroundColor Yellow
composer clear-cache 2>$null

Write-Host "Installing Composer dependencies (no scripts) -- prefer-dist..." -ForegroundColor Yellow
composer install --no-scripts --prefer-dist
if ($LASTEXITCODE -ne 0) {
    Write-Host "composer install failed. Run 'composer install -vvv' and inspect output." -ForegroundColor Red
    if (-not $SkipRestartSearch -and (Is-Admin)) { Start-Service -Name WSearch -ErrorAction SilentlyContinue }
    exit $LASTEXITCODE
}

Write-Host "Generating optimized autoload (skip scripts)..." -ForegroundColor Yellow
composer dump-autoload -o --no-scripts

Write-Host "Running post-install discovery (artisan package:discover)..." -ForegroundColor Yellow
php artisan package:discover --ansi

if (-not $SkipRestartSearch -and (Is-Admin)) {
    try {
        Write-Host "Starting Windows Search back up..." -ForegroundColor Yellow
        Start-Service -Name WSearch -ErrorAction SilentlyContinue
    } catch {
        Write-Host "Could not start WSearch: $_" -ForegroundColor Red
    }
}

Write-Host "Setup complete. You can now run: php artisan serve" -ForegroundColor Green
exit 0
