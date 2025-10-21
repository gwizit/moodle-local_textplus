# Manual Installation Helper for Moodle TextPlus Plugin
# Use this if ZIP upload doesn't work

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Moodle TextPlus - Manual Install" -ForegroundColor Cyan
Write-Host "By G Wiz IT Solutions" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Ask for Moodle path
$MoodlePath = Read-Host "Enter your Moodle installation path (e.g., C:\xampp\htdocs\moodle)"

if (-not (Test-Path $MoodlePath)) {
    Write-Host "ERROR: Moodle path not found: $MoodlePath" -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

# Verify it's a Moodle installation
if (-not (Test-Path "$MoodlePath\config.php")) {
    Write-Host "ERROR: This doesn't appear to be a Moodle installation (config.php not found)" -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

# Get script directory dynamically
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$SourceDir = Join-Path $ScriptDir "textplus"
$DestDir = "$MoodlePath\local\textplus"

Write-Host "Source: $SourceDir" -ForegroundColor Green
Write-Host "Destination: $DestDir" -ForegroundColor Green
Write-Host ""

# Check if source directory exists
if (-not (Test-Path $SourceDir)) {
    Write-Host "ERROR: Plugin source directory not found: $SourceDir" -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

# Check if destination already exists
if (Test-Path $DestDir) {
    Write-Host "WARNING: Plugin directory already exists at destination" -ForegroundColor Yellow
    $overwrite = Read-Host "Do you want to overwrite it? (yes/no)"
    if ($overwrite -ne "yes") {
        Write-Host "Installation cancelled." -ForegroundColor Yellow
        Write-Host "Press any key to exit..."
        $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
        exit 0
    }
    Write-Host "Removing existing plugin..." -ForegroundColor Yellow
    Remove-Item $DestDir -Recurse -Force
}

# Copy files
Write-Host "Copying plugin files..." -ForegroundColor Yellow
try {
    Copy-Item -Path $SourceDir -Destination $DestDir -Recurse -Force
    Write-Host "✓ Files copied successfully!" -ForegroundColor Green
} catch {
    Write-Host "✗ ERROR: Failed to copy files" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    exit 1
}

# Verify installation
Write-Host ""
Write-Host "Verifying installation..." -ForegroundColor Yellow

$requiredFiles = @(
    "version.php",
    "settings.php",
    "index.php",
    "classes\replacer.php",
    "db\install.xml",
    "db\access.php",
    "lang\en\local_textplus.php"
)

$allFound = $true
foreach ($file in $requiredFiles) {
    $filePath = "$DestDir\$file"
    if (Test-Path $filePath) {
        Write-Host "  ✓ $file" -ForegroundColor Green
    } else {
        Write-Host "  ✗ $file (MISSING!)" -ForegroundColor Red
        $allFound = $false
    }
}

Write-Host ""
if ($allFound) {
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "✓ Installation Complete!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Next Steps:" -ForegroundColor Yellow
    Write-Host "1. Log in to your Moodle site as administrator" -ForegroundColor White
    Write-Host "2. Go to: Site administration → Notifications" -ForegroundColor White
    Write-Host "3. Click 'Upgrade Moodle database now'" -ForegroundColor White
    Write-Host "4. Follow the on-screen instructions" -ForegroundColor White
    Write-Host ""
    Write-Host "After installation:" -ForegroundColor Yellow
    Write-Host "Access the tool at: Site administration → Server → TextPlus" -ForegroundColor White
} else {
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "⚠ Installation incomplete!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "Some files are missing. Please check for errors above." -ForegroundColor Red
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
