Get-ChildItem -Recurse -File | Select-String -Pattern "ch" | Select-Object -Unique Path | ForEach-Object {
    $file = $_.Path
    $content = Get-Content $file -Raw
    $newContent = $content -replace "ch", "ch"
    Set-Content $file $newContent
    Write-Host "Updated $file"
}

