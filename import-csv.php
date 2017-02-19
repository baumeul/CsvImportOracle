<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Csv-Files</title>
</head>
<body>

<?php
/**
 * Importiert Csv-Dateien im UTF-8 Format
 * TODO: An den Dateiname (z.B. DASS83.csv eine Erweiterung hängen, damit der
 * Dateiname eindeutig wird. Z.B. DASS83_160115
 * für Umsätze vom 1.1.-15.1.16.Die Dateien können dann in einem Directory
 * gespeichert werden.
 */

$dir = "/Users/ulrichbaumeister/Sites/CsvImportOracle/csv/20170131";
$postfix = ".20170131";
// Verbindungsaufbau
// 1. Vagrant Umgebung: Projects/95_VagrantCentOS7 (s20.vag.cen)
// $conn = oci_connect('immo', 'pw', 's20.vag.cen/XE', 'AL32UTF8');
// 2. OVH-Installation Ubuntu
// $conn = oci_connect('immo', 'pw', 'ovh/XE', 'AL32UTF8');
// 3. OVH-Installation CentOS7
$conn = oci_connect('immo', 'pw', 's25.ovh.cen/XE', 'AL32UTF8');

$cnt_read = 0;
$cnt_insert = 0;

if (!$conn) {
    echo "Fehler! Verbindung nicht möglich.<br>";
} else {
    echo "<h3>Verbindungsaufbau erfolgreich.</h3>";
}

chdir($dir);
echo "<h3>Verzeichnis: $dir</h3>";
//echo "<h4>Diese Dateien werden verarbeitet.</h4>";
$dp = opendir($dir);

while ($dname = readdir($dp)) {
    if (substr($dname, -3) == 'csv') {
        //echo "$dname<br>";
        require "lesen-csv-utf8.inc.php";
    } else {
        continue;
    }
}
closedir($dp);
oci_close($conn);
echo "Anzahl Datensätze gelesen:   {$cnt_read}<br>";
echo "Anzahl Datensätze eingefügt: {$cnt_insert}<br>";
echo "<h4>Verarbeitung beendet. Datenbankverbindung geschlossen.</h4>";
?>

</body>
</html>
