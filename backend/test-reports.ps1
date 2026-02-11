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

Write-Host "Testing Report Subscriptions..."
$subUri = "http://localhost:8001/api/reports/subscriptions"

# List Subscriptions (Should be empty initially)
Write-Host "1. Listing Subscriptions..."
try {
    $subs = Invoke-RestMethod -Uri $subUri -Method Get -Headers $headers
    Write-Host "Count: $($subs.Count)"
}
catch {
    Write-Host "List Failed: $($_.Exception.Message)"
}

# Create Subscription
Write-Host "`n2. Creating Subscription..."
$newSub = @{
    report_type     = "daily"
    delivery_method = "email"
    delivery_time   = "08:00"
} | ConvertTo-Json

try {
    $created = Invoke-RestMethod -Uri $subUri -Method Post -Body $newSub -Headers $headers -ContentType "application/json"
    Write-Host "Created Subscription ID: $($created.id)"
}
catch {
    Write-Host "Create Failed: $($_.Exception.Message)"
    Write-Host "Response: $($_.ErrorDetails.Message)"
}

# Test Daily Summary
Write-Host "`n3. Testing Daily Summary..."
try {
    $summary = Invoke-RestMethod -Uri "http://localhost:8001/api/reports/daily-summary" -Method Get -Headers $headers
    Write-Host "Summary Date: $($summary.date)"
    Write-Host "Total Vehicles: $($summary.summary.total_vehicles)"
}
catch {
    Write-Host "Summary Failed: $($_.Exception.Message)"
}
