Get-ChildItem -Recurse | Where-Object { $_.Name -match 'ch' } | ForEach-Object {
    $newName = $_.Name -replace 'ch', 'ch'
    $newPath = Join-Path $_.DirectoryName $newName
    Rename-Item $_.FullName $newPath
    Write-Host "Renamed $($_.FullName) to $newPath"
}

