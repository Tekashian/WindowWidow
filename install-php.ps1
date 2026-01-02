# Quick PHP 8.3 Installation Script for Windows

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  PHP 8.3 Installation Script" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

$phpVersion = "8.3.14"
$downloadUrl = "https://windows.php.net/downloads/releases/php-$phpVersion-Win32-vs16-x64.zip"
$installPath = "C:\php83"
$zipFile = "$env:TEMP\php-$phpVersion.zip"

Write-Host "[1/5] Downloading PHP $phpVersion..." -ForegroundColor Yellow
try {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $zipFile -UseBasicParsing
    Write-Host "      Download complete!" -ForegroundColor Green
} catch {
    Write-Host "      ERROR: Could not download PHP. Please check your internet connection." -ForegroundColor Red
    Write-Host "      Manual download: https://windows.php.net/download" -ForegroundColor Yellow
    exit 1
}

Write-Host "[2/5] Creating installation directory..." -ForegroundColor Yellow
if (Test-Path $installPath) {
    Write-Host "      Directory already exists, cleaning..." -ForegroundColor Yellow
    Remove-Item -Path $installPath -Recurse -Force
}
New-Item -Path $installPath -ItemType Directory -Force | Out-Null
Write-Host "      Directory created: $installPath" -ForegroundColor Green

Write-Host "[3/5] Extracting PHP files..." -ForegroundColor Yellow
Expand-Archive -Path $zipFile -DestinationPath $installPath -Force
Write-Host "      Extraction complete!" -ForegroundColor Green

Write-Host "[4/5] Configuring PHP..." -ForegroundColor Yellow
$phpIniDev = "$installPath\php.ini-development"
$phpIni = "$installPath\php.ini"
if (Test-Path $phpIniDev) {
    Copy-Item $phpIniDev $phpIni
    
    # Enable required extensions
    $iniContent = Get-Content $phpIni
    $iniContent = $iniContent -replace ';extension=fileinfo', 'extension=fileinfo'
    $iniContent = $iniContent -replace ';extension=gd', 'extension=gd'
    $iniContent = $iniContent -replace ';extension=mbstring', 'extension=mbstring'
    $iniContent = $iniContent -replace ';extension=openssl', 'extension=openssl'
    $iniContent = $iniContent -replace ';extension=pdo_mysql', 'extension=pdo_mysql'
    $iniContent = $iniContent -replace ';extension=curl', 'extension=curl'
    $iniContent = $iniContent -replace ';extension=zip', 'extension=zip'
    $iniContent | Set-Content $phpIni
    
    Write-Host "      PHP configuration complete!" -ForegroundColor Green
}

Write-Host "[5/5] Setting up PATH (for current session)..." -ForegroundColor Yellow
$env:Path = "$installPath;$env:Path"
Write-Host "      PATH updated for current session!" -ForegroundColor Green

# Cleanup
Remove-Item $zipFile -Force

Write-Host ""
Write-Host "==================================" -ForegroundColor Green
Write-Host "  Installation Complete!" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green
Write-Host ""
Write-Host "PHP Version:" -ForegroundColor Cyan
& "$installPath\php.exe" -v | Select-Object -First 1
Write-Host ""
Write-Host "IMPORTANT: To use PHP permanently, add to System PATH:" -ForegroundColor Yellow
Write-Host "  $installPath" -ForegroundColor White
Write-Host ""
Write-Host "For now, you can use PHP in THIS terminal session." -ForegroundColor Green
Write-Host ""
Write-Host "To start Laravel server, run:" -ForegroundColor Cyan
Write-Host "  cd C:\Users\Admin\Desktop\vueLavarell\backend" -ForegroundColor White
Write-Host "  C:\php83\php.exe artisan serve" -ForegroundColor White
Write-Host ""
