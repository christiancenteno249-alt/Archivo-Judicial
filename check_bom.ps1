# Verify BOM is removed
$files = @(
    'c:\xampp\htdocs\archivo_judicial\conexion.php',
    'c:\xampp\htdocs\archivo_judicial\obtener_ubicacion.php'
)

foreach ($f in $files) {
    $bytes = [System.IO.File]::ReadAllBytes($f)
    $hasBom = ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF)
    Write-Output "${f}: first3bytes=$($bytes[0]),$($bytes[1]),$($bytes[2]) BOM=$hasBom"
}
