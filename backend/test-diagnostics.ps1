$body = @{
    email    = "admin@dhaka-transport.com"
    password = "password"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri "http://localhost:8001/api/auth/login" -Method Post -Body $body -ContentType "application/json"
$token = $loginResponse.token
$headers = @{
    Authorization = "Bearer $token"
    Accept        = "application/json"
}

Write-Host "Testing Fleet Summary..."
try {
    $summary = Invoke-RestMethod -Uri "http://localhost:8001/api/diagnostics/summary" -Method Get -Headers $headers
    Write-Host "Success! Total Vehicles: $($summary.total_vehicles)"
    Write-Host "Vehicles with Issues: $($summary.vehicles_with_issues)"
}
catch {
    Write-Host "Fleet Summary Failed: $($_.Exception.Message)"
}

Write-Host "`nTesting Vehicle Diagnostics (ID: 1)..."
try {
    $diagnostics = Invoke-RestMethod -Uri "http://localhost:8001/api/vehicles/1/diagnostics" -Method Get -Headers $headers
    Write-Host "Success! Health Score: $($diagnostics.health_score.score)"
    Write-Host "Status: $($diagnostics.health_score.status)"
}
catch {
    Write-Host "Vehicle Diagnostics Failed: $($_.Exception.Message)"
}
