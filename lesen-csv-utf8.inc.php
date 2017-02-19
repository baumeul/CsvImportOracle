<?php

$csvFile = $dname;

if(!file_exists($csvFile)) {
    exit("<h1>Datei konnte nicht gefunden werden</h1>");
}
$fp = @fopen($csvFile, "r");

if(!$fp) {
    exit("<h1>Datei steht nicht zum Lesen bereit.</h1>");
}
// Aus dem Dateinamen obj_name_kurz extrahieren und damit obj_id lesen
$objNameKurz = strtok($csvFile, '.');

$sql = "SELECT obj_id FROM v_obj_id_kontonummer where obj_name_kurz = '{$objNameKurz}'";
$stmt = oci_parse($conn, $sql);
if (oci_execute($stmt)) {
    $result = oci_fetch_assoc($stmt);
    //echo  $objNameKurz . " hat obj_id " . $result['OBJ_ID'] . "<br>";
} else {
    echo "<h1>Fehler beim Execute.</h1>";
}

$obj_id = intval($result['OBJ_ID']);

// Skip first line
$line = fgets($fp, filesize($csvFile));

while(!feof($fp)) {
    $line = fgets($fp, filesize($csvFile));

    while(ord(substr($line, strlen($line)-1)) == 13 || ord(substr($line,strlen($line)-1)) == 10) {
        $line = substr($line, 0, strlen($line)-1);
    }

    if (!(feof($fp) && $line == "")) {
        $cnt_read += 1;
        $words = explode(";", $line);
        //var_dump($words);

        $sql  = "INSERT INTO im_umsaetze (ums_buchungstag, ums_wertstellung, ums_kategorie, ums_auftraggeber, 
                  ums_verwendungszweck, ums_kontonummer, ums_bank, ums_betrag, ums_waehrung, ums_datum_hochgeladen, 
                  ums_obj_id, ums_filename) 
                  VALUES ( TO_DATE(:p1, 'dd.mm.yy'), TO_DATE(:p2, 'dd.mm.yy'), NULL, :p3, :p4, :p5, :p6, :p7, :p8, SYSDATE, :p9, :P10)";
        $stmt = oci_parse($conn, $sql);
        $pn = [
            "buchungstag"       => $words[0],
            "wertstellung"      => $words[1],
            "auftraggeber"      => $words[3],
            "verwendungszweck"  => $words[4],
            "kontonummer"       => $words[5],
            "bank"              => $words[6],
            "betrag"            => str_replace(',', '.', (str_replace('.', '', $words[7]))),
            "waehrung"          => $words[8],
            "obj_id"            => $obj_id,
            "filename"          => $csvFile . $postfix
        ];
        //var_dump($pn);

        oci_bind_by_name($stmt, ":p1", $pn['buchungstag']);
        oci_bind_by_name($stmt, ":p2", $pn['wertstellung']);
        oci_bind_by_name($stmt, ":p3", $pn['auftraggeber']);
        oci_bind_by_name($stmt, ":p4", $pn['verwendungszweck']);
        oci_bind_by_name($stmt, ":p5", $pn['kontonummer']);
        oci_bind_by_name($stmt, ":p6", $pn['bank']);
        oci_bind_by_name($stmt, ":p7", $pn['betrag']);
        oci_bind_by_name($stmt, ":p8", $pn['waehrung']);
        oci_bind_by_name($stmt, ":p9", $pn['obj_id']);
        oci_bind_by_name($stmt, ":p10", $pn['filename']);


        if (oci_execute($stmt)) {
            oci_commit($conn);
            //echo "Daten eingefügt.<br>";
            $cnt_insert += 1;
        } else {
            echo "<h1>Fehler beim Insert.</h1>";
        }
    }
}

fclose($fp);
?>
