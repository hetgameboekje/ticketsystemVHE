<#
.SYNOPSIS
    Interactief dev-onderhoudsmenu voor Ticketsysteem Leen van Punt.

.DESCRIPTION
    Pijltjes = navigeren, Spatie = selecteren, Enter = uitvoeren, Esc = annuleren.
    Geselecteerde acties worden altijd in een vaste, veilige volgorde uitgevoerd
    (ongeacht de volgorde waarin je ze aanvinkt).

.USAGE
    powershell -ExecutionPolicy Bypass -File scripts\dev-tools\dev-tools.ps1
#>

$ErrorActionPreference = 'Stop'
$RepoRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)

function Get-EnvMap {
    param([string]$Path)

    $map = @{}
    if (Test-Path $Path) {
        foreach ($line in Get-Content $Path) {
            if ($line -match '^\s*#' -or $line -match '^\s*$') { continue }
            if ($line -match '^([A-Za-z_][A-Za-z0-9_]*)=(.*)$') {
                $map[$Matches[1]] = $Matches[2].Trim().Trim('"').Trim("'")
            }
        }
    }
    return $map
}

function Show-CheckboxMenu {
    param([string[]]$Items)

    $selected = New-Object bool[] $Items.Count
    $index = 0

    while ($true) {
        Clear-Host
        Write-Host "Ticketsysteem Leen van Punt - dev-tools" -ForegroundColor Cyan
        Write-Host "Pijltjes = navigeren, Spatie = selecteren, Enter = uitvoeren, Esc = annuleren`n"
        for ($i = 0; $i -lt $Items.Count; $i++) {
            $marker = if ($selected[$i]) { '[x]' } else { '[ ]' }
            $prefix = if ($i -eq $index) { '>' } else { ' ' }
            $color = if ($i -eq $index) { 'Yellow' } else { 'White' }
            Write-Host "$prefix $marker $($Items[$i])" -ForegroundColor $color
        }

        $key = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
        switch ($key.VirtualKeyCode) {
            38 { $index = [Math]::Max(0, $index - 1) }                   # Up
            40 { $index = [Math]::Min($Items.Count - 1, $index + 1) }    # Down
            32 { $selected[$index] = -not $selected[$index] }            # Space
            13 { return @(0..($Items.Count - 1) | Where-Object { $selected[$_] }) }  # Enter
            27 { return @() }                                           # Escape
        }
    }
}

function Invoke-GitPull {
    Push-Location $RepoRoot
    try {
        git fetch --all --prune
        git pull
    } finally {
        Pop-Location
    }
}

function Invoke-RebuildEnv {
    $examplePath = Join-Path $RepoRoot '.env.example'
    $envPath = Join-Path $RepoRoot '.env'

    if (-not (Test-Path $examplePath)) {
        Write-Host "Geen .env.example gevonden - kan niet mergen." -ForegroundColor Red
        return
    }

    if (-not (Test-Path $envPath)) {
        Copy-Item $examplePath $envPath
        Write-Host ".env aangemaakt vanuit .env.example - vul de waarden in." -ForegroundColor Yellow
        return
    }

    $current = Get-EnvMap $envPath
    $outLines = @(Get-Content $envPath)
    $added = @()

    foreach ($line in Get-Content $examplePath) {
        if ($line -match '^([A-Za-z_][A-Za-z0-9_]*)=') {
            $key = $Matches[1]
            if (-not $current.ContainsKey($key)) {
                $outLines += ''
                $outLines += $line
                $added += $key
            }
        }
    }

    if ($added.Count -gt 0) {
        Set-Content -Path $envPath -Value $outLines -Encoding UTF8
        Write-Host "Nieuwe sleutels toegevoegd aan .env: $($added -join ', ')" -ForegroundColor Green
        Write-Host "Vul de waarden hiervoor handmatig in .env in." -ForegroundColor Yellow
    } else {
        Write-Host ".env is al up-to-date met .env.example." -ForegroundColor Green
    }

}

function Invoke-ParseDatabase {
    & php (Join-Path $RepoRoot 'database\parse.php')
}

function Invoke-ClearDatabase {
    $confirm = Read-Host "Dit verwijdert ALLE tabellen en data in de LOKALE database en herbouwt het schema. Typ 'ja' om door te gaan"
    if ($confirm -ne 'ja') {
        Write-Host "Geannuleerd." -ForegroundColor Yellow
        return
    }
    & php (Join-Path $RepoRoot 'database\clear.php') --force
}

function Invoke-PullLive {
    $envMap = Get-EnvMap (Join-Path $RepoRoot '.env')
    $url = $envMap['LIVE_DB_EXPORT_URL']
    $apiKey = $envMap['LIVE_DB_EXPORT_KEY']

    if ([string]::IsNullOrWhiteSpace($url) -or [string]::IsNullOrWhiteSpace($apiKey)) {
        Write-Host "LIVE_DB_EXPORT_URL en/of LIVE_DB_EXPORT_KEY ontbreken in .env (zie .env.example)." -ForegroundColor Red
        Write-Host "Maak een API-sleutel aan via Beheer > API-sleutels op de live server, met scope 'database_export'." -ForegroundColor Yellow
        return
    }

    $confirm = Read-Host "Dit vervangt de LOKALE database volledig met een kopie van de live database. Typ 'ja' om door te gaan"
    if ($confirm -ne 'ja') {
        Write-Host "Geannuleerd." -ForegroundColor Yellow
        return
    }

    $mysql = Get-Command mysql -ErrorAction SilentlyContinue
    if (-not $mysql) {
        Write-Host "mysql-client niet gevonden in PATH - kan de dump niet automatisch importeren." -ForegroundColor Red
        return
    }

    $dumpFile = Join-Path $env:TEMP "leenvanpunt-live-dump-$(Get-Date -Format 'yyyyMMdd-HHmmss').sql"
    Write-Host "Dump downloaden van $url ..."
    try {
        Invoke-WebRequest -Uri $url -Headers @{ 'X-Api-Key' = $apiKey } -OutFile $dumpFile -UseBasicParsing
    } catch {
        Write-Host "Download mislukt: $_" -ForegroundColor Red
        return
    }

    $dbHost = $envMap['DB_HOST']
    $dbPort = $envMap['DB_PORT']
    $dbName = $envMap['DB_DATABASE']
    $dbUser = $envMap['DB_USERNAME']
    $dbPass = $envMap['DB_PASSWORD']

    Write-Host "Importeren in lokale database '$dbName' ..."
    $prevPwd = $env:MYSQL_PWD
    if ($dbPass) { $env:MYSQL_PWD = $dbPass }
    try {
        cmd /c "mysql --host=$dbHost --port=$dbPort --user=$dbUser $dbName < `"$dumpFile`""
    } finally {
        if ($null -eq $prevPwd) { Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue } else { $env:MYSQL_PWD = $prevPwd }
    }

    if ($LASTEXITCODE -eq 0) {
        Write-Host "Live database geimporteerd." -ForegroundColor Green
        Remove-Item $dumpFile -ErrorAction SilentlyContinue
    } else {
        Write-Host "Import mislukt (exitcode $LASTEXITCODE) - dump staat nog in $dumpFile" -ForegroundColor Red
    }
}

$items = @(
    'Database parsen (schema.sql genereren uit database/xml)',
    'Git pull & fetch',
    'Rebuild .env (nieuwe sleutels uit .env.example toevoegen)',
    'Database legen + schema herbouwen (LOKAAL, alle data verloren)',
    'Live database ophalen van bergthaler.dev (LOKAAL overschrijven)'
)

$selectedIndexes = Show-CheckboxMenu -Items $items
if ($selectedIndexes.Count -eq 0) {
    Write-Host "Niets geselecteerd - afgesloten." -ForegroundColor Yellow
    exit
}

Clear-Host
Write-Host "Uitvoeren...`n" -ForegroundColor Cyan

# Vaste, veilige volgorde ongeacht selectievolgorde in het menu.
if ($selectedIndexes -contains 1) { Write-Host "== Git pull & fetch ==" -ForegroundColor Cyan; Invoke-GitPull }
if ($selectedIndexes -contains 2) { Write-Host "`n== Rebuild .env ==" -ForegroundColor Cyan; Invoke-RebuildEnv }
if ($selectedIndexes -contains 3) { Write-Host "`n== Database legen + herbouwen ==" -ForegroundColor Cyan; Invoke-ClearDatabase }
if ($selectedIndexes -contains 0) { Write-Host "`n== Database parsen ==" -ForegroundColor Cyan; Invoke-ParseDatabase }
if ($selectedIndexes -contains 4) { Write-Host "`n== Live database ophalen ==" -ForegroundColor Cyan; Invoke-PullLive }

Write-Host "`nKlaar." -ForegroundColor Green
