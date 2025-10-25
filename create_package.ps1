# Moodle TextPlus Plugin - Package Creation Script
# Run this script from PowerShell to create a distribution ZIP

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Moodle TextPlus Plugin Packager" -ForegroundColor Cyan
Write-Host "By G Wiz IT Solutions" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Set locations dynamically based on script location
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$PluginDir = Join-Path $ScriptDir "textplus"
$VersionFile = Join-Path $PluginDir "version.php"
$OutputDir = $ScriptDir

# Read version from version.php
$Version = "1.0.0"  # Default fallback
if (Test-Path $VersionFile) {
    try {
        $VersionContent = Get-Content $VersionFile -Raw
        if ($VersionContent -match "\`$plugin->release\s*=\s*'v?([^']+)'") {
            $Version = $matches[1]
            Write-Host "Detected version: $Version" -ForegroundColor Green
        } else {
            Write-Host "WARNING: Could not parse version from version.php, using default" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "WARNING: Error reading version.php, using default version" -ForegroundColor Yellow
    }
} else {
    Write-Host "WARNING: version.php not found, using default version" -ForegroundColor Yellow
}

$OutputFile = "$OutputDir\moodle-local_textplus-v$Version.zip"

# Check if plugin directory exists
if (-not (Test-Path $PluginDir)) {
    Write-Host "ERROR: Plugin directory not found at $PluginDir" -ForegroundColor Red
    exit 1
}

Write-Host "Plugin directory: $PluginDir" -ForegroundColor Green
Write-Host "Output file: $OutputFile" -ForegroundColor Green
Write-Host ""

# Remove old ZIP if exists
if (Test-Path $OutputFile) {
    Write-Host "Removing existing ZIP file..." -ForegroundColor Yellow
    try {
        # Wait a moment for any file handles to close
        Start-Sleep -Milliseconds 500
        Remove-Item $OutputFile -Force -ErrorAction Stop
    } catch {
        Write-Host "Could not remove existing file. Trying alternate name..." -ForegroundColor Yellow
        $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
        $OutputFile = "$OutputDir\moodle-local_textplus-v$Version-$timestamp.zip"
        Write-Host "New output file: $OutputFile" -ForegroundColor Cyan
    }
}

# Create ZIP file
Write-Host "Creating ZIP package..." -ForegroundColor Yellow

try {
    # Load ZIP assemblies
    Add-Type -Assembly System.IO.Compression
    Add-Type -Assembly System.IO.Compression.FileSystem
    
    # Remove temp file if exists
    $tempZip = "$OutputDir\temp_plugin.zip"
    if (Test-Path $tempZip) { Remove-Item $tempZip -Force }
    
    # Create ZIP manually to ensure proper structure
    # Moodle expects: zipfile/textplus/version.php
    $zip = [System.IO.Compression.ZipFile]::Open($tempZip, 'Create')
    
    # Add all files from textplus folder
    $files = Get-ChildItem -Path "$OutputDir\textplus" -Recurse -File
    foreach ($file in $files) {
        $relativePath = $file.FullName.Substring($OutputDir.Length + 1).Replace('\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $file.FullName, $relativePath, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
    }
    
    $zip.Dispose()
    
    # Move to final location
    if (Test-Path $OutputFile) { Remove-Item $OutputFile -Force }
    Move-Item $tempZip $OutputFile
    
    Write-Host "Package created successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "ZIP File: $OutputFile" -ForegroundColor Cyan
    
    # Get file size
    $FileSize = (Get-Item $OutputFile).Length / 1KB
    Write-Host "File Size: $([math]::Round($FileSize, 2)) KB" -ForegroundColor Cyan
    
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "Next Steps:" -ForegroundColor Yellow
    Write-Host "1. Upload the ZIP to your Moodle site" -ForegroundColor White
    Write-Host "   (Site admin -> Plugins -> Install plugins)" -ForegroundColor White
    Write-Host "2. Or extract and copy 'textplus' folder" -ForegroundColor White
    Write-Host "   to [moodle]/local/ directory" -ForegroundColor White
    Write-Host "========================================" -ForegroundColor Cyan
    
} catch {
    Write-Host "ERROR: Failed to create ZIP package" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
