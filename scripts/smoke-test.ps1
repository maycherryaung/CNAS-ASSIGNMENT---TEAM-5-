param(
    [string]$BaseUrl = "http://localhost:8080",
    [switch]$RunCrud
)

$BaseUrl = $BaseUrl.TrimEnd("/")

Write-Host "Checking app health at $BaseUrl/health.php"
Invoke-WebRequest -UseBasicParsing "$BaseUrl/health.php" | Out-Null

Write-Host "Checking database-backed readiness at $BaseUrl/health.php?deep=1"
Invoke-WebRequest -UseBasicParsing "$BaseUrl/health.php?deep=1" | Out-Null

Write-Host "Checking home page"
$home = Invoke-WebRequest -UseBasicParsing "$BaseUrl/"
if ($home.Content -notmatch "CNAS Assignment") {
    throw "Home page did not contain expected assignment heading."
}

if ($RunCrud) {
    $timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $email = "smoke-$timestamp@example.edu"
    Write-Host "Creating smoke-test member $email"
    Invoke-WebRequest -UseBasicParsing -Method Post -Body @{
        name = "Smoke Test"
        email = $email
    } "$BaseUrl/create.php" | Out-Null

    $updatedHome = Invoke-WebRequest -UseBasicParsing "$BaseUrl/"
    if ($updatedHome.Content -notmatch [regex]::Escape($email)) {
        throw "Smoke-test member was not found on the home page."
    }
}

Write-Host "Smoke test passed."
