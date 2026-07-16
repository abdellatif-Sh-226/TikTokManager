$envPath = Join-Path $PSScriptRoot ".env"
$envContent = Get-Content $envPath -Raw

try {
    $tunnels = Invoke-RestMethod -Uri "http://127.0.0.1:4040/api/tunnels" -ErrorAction Stop
    $httpsUrl = ($tunnels.tunnels | Where-Object { $_.public_url -like "https://*" }).public_url

    if (-not $httpsUrl) {
        Write-Host "❌ Ma l9itech tunnel https f ngrok. Taked menou enou ngrok khdem ?" -ForegroundColor Red
        exit 1
    }

    $newAppUrl = $httpsUrl
    $newRedirectUri = "$httpsUrl/api/auth/tiktok/callback"

    if ($envContent -match "APP_URL=.*") {
        $envContent = $envContent -replace "APP_URL=.*", "APP_URL=$newAppUrl"
    }
    if ($envContent -match "TIKTOK_REDIRECT_URI=.*") {
        $envContent = $envContent -replace "TIKTOK_REDIRECT_URI=.*", "TIKTOK_REDIRECT_URI=$newRedirectUri"
    }

    Set-Content $envPath $envContent

    Write-Host "✅ Ngrok URL tbeddel: $newAppUrl" -ForegroundColor Green
    Write-Host "✅ TIKTOK_REDIRECT_URI tbeddel: $newRedirectUri" -ForegroundColor Green
    Write-Host ""
    Write-Host "⚠️ Matensa tbeddel l'URL f TikTok Developer Portal (https://developers.tiktok.com)" -ForegroundColor Yellow
    Write-Host "   App → Scopes → Redirect URI → $newRedirectUri" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "▶️  Baad ma tbeddel, restarty laravel: php artisan serve" -ForegroundColor Cyan
} catch {
    Write-Host "❌ Ma najemtech nraj3 l URL mngrok. Taked menou enou ngrok khdem w API mte3ou 4040 mawjoud ?" -ForegroundColor Red
    Write-Host "   (ngrok chghlou w sayen `ngrok http 8000`)" -ForegroundColor Yellow
}
