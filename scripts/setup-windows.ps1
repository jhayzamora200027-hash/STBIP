<#
PowerShell helper to perform a safe Composer install on Windows
- stops Windows Search (optional)
- optionally adds Windows Defender exclusion (requires Admin)
- clears Composer cache, removes partially-extracted folders, runs `composer install` safely
Usage (run in project root):
  PowerShell -ExecutionPolicy Bypass -File .\scripts\setup-windows.ps1
  Or (recommended for Defender exclusion / stop/start of WSearch): Run PowerShell as Administrator
#>

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

# Stop Windows Search to avoid file-locks during extraction (if possible)
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

# Optionally add Windows Defender exclusion
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

# Remove partially extracted composer temp folders (safe)
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

# Clear composer cache (helpful after interrupted downloads)
Write-Host "Clearing Composer cache..." -ForegroundColor Yellow
composer clear-cache 2>$null

# Run safe composer install sequence
Write-Host "Installing Composer dependencies (no scripts) -- prefer-dist..." -ForegroundColor Yellow
composer install --no-scripts --prefer-dist
if ($LASTEXITCODE -ne 0) {
    Write-Host "composer install failed. Run 'composer install -vvv' and inspect output." -ForegroundColor Red
    if (-not $SkipRestartSearch -and (Is-Admin)) { Start-Service -Name WSearch -ErrorAction SilentlyContinue }
    exit $LASTEXITCODE
}

Write-Host "Generating optimized autoload (skip scripts)..." -ForegroundColor Yellow
composer dump-autoload -o --no-scripts

# Run package discovery / post-install scripts safely
Write-Host "Running post-install discovery (artisan package:discover)..." -ForegroundColor Yellow
php artisan package:discover --ansi

# Restart Windows Search if we stopped it earlier
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
