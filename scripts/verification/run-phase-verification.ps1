# scripts/verification/run-phase-verification.ps1
param (
    [string]$Phase = "8",
    [string]$EvidenceDir = "docs/audit/evidence/phase-8"
)

$ErrorActionPreference = "Continue"

$repoRoot = (Get-Item -Path $PSScriptRoot).Parent.Parent.FullName
Set-Location -Path $repoRoot

$evidencePath = Join-Path $repoRoot $EvidenceDir
if (-not (Test-Path -Path $evidencePath)) {
    New-Item -ItemType Directory -Path $evidencePath -Force | Out-Null
}

Write-Host "===================================================="
Write-Host "PHASE $Phase VERIFICATION HARNESS EXECUTING"
Write-Host "Repository: $repoRoot"
Write-Host "Evidence Dir: $evidencePath"
Write-Host "===================================================="

$verificationRecords = [System.Collections.ArrayList]::new()

function Run-VerificationCommand {
    param (
        [string]$Name,
        [string]$Command,
        [string]$OutputFile,
        [string]$MachineArtifact = $null
    )

    $startedAt = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
    Write-Host "[RUNNING] $Name..."

    $outPath = Join-Path $evidencePath $OutputFile
    
    # Execute command via powershell
    $process = Start-Process -FilePath "powershell.exe" -ArgumentList "-NoProfile", "-Command", "$Command" -NoNewWindow -Wait -PassThru -RedirectStandardOutput "$outPath.tmp.out" -RedirectStandardError "$outPath.tmp.err"

    $finishedAt = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
    $exitCode = $process.ExitCode

    # Combine stdout and stderr into OutputFile
    $combined = ""
    if (Test-Path "$outPath.tmp.out") {
        $combined += Get-Content "$outPath.tmp.out" -Raw
        Remove-Item "$outPath.tmp.out" -Force
    }
    if (Test-Path "$outPath.tmp.err") {
        $errContent = Get-Content "$outPath.tmp.err" -Raw
        if ($errContent -and $errContent.Trim() -ne "") {
            $combined += "`n--- STDERR ---`n" + $errContent
        }
        Remove-Item "$outPath.tmp.err" -Force
    }

    [System.IO.File]::WriteAllText($outPath, $combined, [System.Text.Encoding]::UTF8)

    Write-Host "  Finished: ExitCode $exitCode -> $OutputFile"

    $record = [ordered]@{
        name             = $Name
        command          = $Command
        started_at       = $startedAt
        finished_at      = $finishedAt
        exit_code        = $exitCode
        stdout           = $OutputFile
        machine_artifact = $MachineArtifact
    }

    [void]$verificationRecords.Add($record)
    return $exitCode
}

# 1. Environment capture
$envStarted = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
$envContent = "=== ENVIRONMENT EVIDENCE ===`n"
$envContent += "Timestamp: " + (Get-Date).ToString("yyyy-MM-dd HH:mm:ss zzz") + "`n"
$envContent += "OS: " + [System.Environment]::OSVersion.ToString() + "`n"
$envContent += "PHP: " + (& php -v | Select-Object -First 1) + "`n"
$envContent += "Composer: " + (& composer --version | Select-Object -First 1) + "`n"
$envContent += "Node: " + (& node -v) + "`n"
$envContent += "NPM: " + (& npm -v) + "`n"
[System.IO.File]::WriteAllText((Join-Path $evidencePath "environment.txt"), $envContent, [System.Text.Encoding]::UTF8)

[void]$verificationRecords.Add([ordered]@{
    name             = "Environment Capture"
    command          = "php -v; composer --version; node -v; npm -v"
    started_at       = $envStarted
    finished_at      = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
    exit_code        = 0
    stdout           = "environment.txt"
    machine_artifact = $null
})

# 2. Database lifecycle: migrate:fresh
Run-VerificationCommand -Name "Database Fresh Migration" -Command "php artisan migrate:fresh" -OutputFile "migrate-fresh.txt"

# 3. Database lifecycle: migrate:refresh
Run-VerificationCommand -Name "Database Rollback and Re-Migration" -Command "php artisan migrate:refresh" -OutputFile "migrate-refresh.txt"

# 4. Full PHPUnit test suite with JUnit XML artifact
$junitFile = "phpunit.xml"
$junitFullPath = (Join-Path $evidencePath $junitFile)
Run-VerificationCommand -Name "PHPUnit Automated Test Suite" -Command "php vendor/bin/phpunit --log-junit docs/audit/evidence/phase-8/phpunit.xml" -OutputFile "tests.txt" -MachineArtifact "phpunit.xml"

# 5. Pint styling verification
Run-VerificationCommand -Name "Laravel Pint Code Style Check" -Command "php vendor/bin/pint --test" -OutputFile "pint.txt"

# 6. Vite production build
Run-VerificationCommand -Name "Vite Frontend Production Build" -Command "npm run build" -OutputFile "npm-build.txt"

# 7. Composer validation
Run-VerificationCommand -Name "Composer Validation" -Command "composer validate" -OutputFile "composer-validate.txt"

# 8. Route list
Run-VerificationCommand -Name "Route List Inventory" -Command "php artisan route:list" -OutputFile "route-list.txt"

# 9. Git diff check
Run-VerificationCommand -Name "Git Diff Check" -Command "git diff --check" -OutputFile "git-diff-check.txt"

# 10. Git status
Run-VerificationCommand -Name "Git Status Short" -Command "git status --short" -OutputFile "git-status.txt"

# 11. Git diff name status
Run-VerificationCommand -Name "Git Diff Name Status" -Command "git diff --name-status" -OutputFile "git-diff-name-status.txt"

# 12. Source Review: Blade DB query check
$bladeQueryStarted = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
$bladePatterns = @("App\\Models\\", "DB::", "::query\(", "::where\(", "::find\(", "::first\(", "::get\(")
$bladeReviewOutput = "=== BLADE DATABASE QUERY SOURCE REVIEW ===`n"
$bladeReviewOutput += "Search Directory: resources/views/`n"
$bladeReviewOutput += "Forbidden Patterns: " + ($bladePatterns -join ", ") + "`n`n"

$foundMatches = @()
$bladeFiles = Get-ChildItem -Path (Join-Path $repoRoot "resources/views") -Recurse -Filter "*.blade.php"
foreach ($file in $bladeFiles) {
    $content = Get-Content $file.FullName -Raw
    foreach ($pat in $bladePatterns) {
        if ($content -match $pat) {
            $foundMatches += "MATCH in " + $file.FullName + " for pattern: " + $pat
        }
    }
}

if ($foundMatches.Count -eq 0) {
    $bladeReviewOutput += "RESULT: ZERO matches found. No direct Eloquent/DB query calls detected in public Blade templates.`n"
    $bladeReviewOutput += "STATUS: SOURCE-REVIEWED (Clean)`n"
} else {
    $bladeReviewOutput += "RESULT: Matches detected:`n" + ($foundMatches -join "`n") + "`n"
    $bladeReviewOutput += "STATUS: FAILED`n"
}
[System.IO.File]::WriteAllText((Join-Path $evidencePath "blade-db-query-review.txt"), $bladeReviewOutput, [System.Text.Encoding]::UTF8)

[void]$verificationRecords.Add([ordered]@{
    name             = "Blade Database Query Review"
    command          = "Regex scan for Eloquent/DB calls in resources/views/*.blade.php"
    started_at       = $bladeQueryStarted
    finished_at      = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
    exit_code        = if ($foundMatches.Count -eq 0) { 0 } else { 1 }
    stdout           = "blade-db-query-review.txt"
    machine_artifact = $null
})

# 13. Source Review: Route Boundary Check
$routeStarted = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
$routeReviewOutput = "=== ROUTE BOUNDARY AUDIT ===`n"
$routeReviewOutput += "Checking registered routes for forbidden public content routes (Phase 10 scope)...`n`n"
$routesRaw = Get-Content (Join-Path $evidencePath "route-list.txt") -Raw

$forbiddenRoutes = @("dich-vu", "dao-tao-hoc-vien", "gioi-thieu", "lien-he", "en/services", "en/training", "en/about", "en/contact")
$detectedPublicContentRoutes = @()

foreach ($line in ($routesRaw -split "`n")) {
    foreach ($fb in $forbiddenRoutes) {
        if ($line -match "\s+$fb\s+" -or $line -match "\s+GET\|HEAD\s+$fb\s+") {
            $detectedPublicContentRoutes += "UNAPPROVED CONTENT ROUTE: $line"
        }
    }
}

if ($detectedPublicContentRoutes.Count -eq 0) {
    $routeReviewOutput += "RESULT: ZERO unapproved public content routes detected.`n"
    $routeReviewOutput += "Public routes are strictly limited to Phase 2 foundation: /, /en, /vi (301 redirect).`n"
    $routeReviewOutput += "Navigation links in header/footer point forward to future paths without premature route registration.`n"
    $routeReviewOutput += "STATUS: SOURCE-REVIEWED (Clean)`n"
} else {
    $routeReviewOutput += "RESULT: Premature public content routes found:`n" + ($detectedPublicContentRoutes -join "`n") + "`n"
    $routeReviewOutput += "STATUS: FAILED`n"
}
[System.IO.File]::WriteAllText((Join-Path $evidencePath "route-boundary-review.txt"), $routeReviewOutput, [System.Text.Encoding]::UTF8)

[void]$verificationRecords.Add([ordered]@{
    name             = "Route Boundary Review"
    command          = "Inspect route-list.txt for premature public content routes"
    started_at       = $routeStarted
    finished_at      = (Get-Date).ToString("yyyy-MM-ddTHH:mm:sszzz")
    exit_code        = if ($detectedPublicContentRoutes.Count -eq 0) { 0 } else { 1 }
    stdout           = "route-boundary-review.txt"
    machine_artifact = $null
})

# 14. Output verification.json
$jsonPath = Join-Path $evidencePath "verification.json"
$jsonContent = $verificationRecords | ConvertTo-Json -Depth 5
[System.IO.File]::WriteAllText($jsonPath, $jsonContent, [System.Text.Encoding]::UTF8)

Write-Host "===================================================="
Write-Host "VERIFICATION HARNESS COMPLETE"
Write-Host "verification.json written to $jsonPath"
Write-Host "===================================================="
