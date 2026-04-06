$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$composerHome = Join-Path $projectRoot '.composer-home'
$caFile = Join-Path $projectRoot 'cacert.pem'
$composerPhar = 'C:\ProgramData\ComposerSetup\bin\composer.phar'

if (-not (Test-Path $composerHome)) {
    New-Item -ItemType Directory -Path $composerHome | Out-Null
}

if (-not (Test-Path $caFile)) {
    throw "Missing CA file: $caFile"
}

if (-not (Test-Path $composerPhar)) {
    throw "Missing Composer PHAR: $composerPhar"
}

$env:COMPOSER_HOME = $composerHome
$env:HTTP_PROXY = ''
$env:HTTPS_PROXY = ''
$env:ALL_PROXY = ''
$env:GIT_HTTP_PROXY = ''
$env:GIT_HTTPS_PROXY = ''
$env:SSL_CERT_FILE = $caFile
$env:COMPOSER_CAFILE = $caFile

& php -d "curl.cainfo=$caFile" -d "openssl.cafile=$caFile" $composerPhar @args
