# Fix BOM in PHP files
$files = @(
    'c:\xampp\htdocs\archivo_judicial\conexion.php',
    'c:\xampp\htdocs\archivo_judicial\obtener_ubicacion.php'
)

foreach ($f in $files) {
    $bytes = [System.IO.File]::ReadAllBytes($f)
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        $newBytes = $bytes[3..($bytes.Length - 1)]
        [System.IO.File]::WriteAllBytes($f, $newBytes)
        Write-Output "FIXED: $f (BOM removed)"
    } else {
        Write-Output "OK: $f (no BOM)"
    }
}
