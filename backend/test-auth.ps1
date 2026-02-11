$body = @{
    email = "admin@dhaka-transport.com"
    password = "password"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" -Method Post -Body $body -ContentType "application/json"
Write-Host "Token: $($response.token)"
Write-Host "User: $($response.user.name)"
