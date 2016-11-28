<?php
// Benötigt ein schreibbares Verzeichnis "Work" zur Bearbeitung der Dateien
// Parameter: Datei - Datei mit den Schülernamen für T_Schueler

// Aufbau: Bezeichnung ISBN Jahr Verlag Menge Fachbereich

 $Ueberschrift = "Bücherdaten importieren";
 include("include/header.inc.php");
 if ( isset($_FILES["Datei"]) && $_FILES["Datei"]["name"] != "" )
 {
   echo '<tr><td><strong>Lese Bücherdatendatei ein...</strong></td></tr>';
   //@unlink("Work/".$_FILES['Datei']['name']);
   //if ( ! move_uploaded_file($_FILES['Datei']['tmp_name'], "Work/".$_FILES['Datei']['name'].".txt"))
   //   die ("Beim Verschieben der Datei ist ein Fehler aufgetreten!");
   $dat = fopen($_FILES['Datei']['tmp_name'],"r");
   $anz = 0;
   echo "<tr><td>";
   unset($Titel);
   unset($sql);
   $anz = 0;
   $insertsql = "INSERT INTO T_Inventar ".
     "(Bezeichnung, F_Lieferant_id, Seriennummer,Herstellungsjahr,F_Art_id,F_Raum_id,Bearbeiter)";
   $insertsql .= " VALUES (";
   while ( !feof($dat))
   {
     $buffer = fgets($dat);
     $feld = 0;
     if ( trim($buffer) != "" )
     {
       $buch = explode(";",$buffer);
       // Daten in Datenbank schreiben
       $sql = "";
       while ( list($key, $value) = each($buch) )
       {
         if ( $feld < 6 )
         {
           if ( $value != ""  )
           {
             $sql .= "'".mysql_real_escape_string(trim(str_replace('"','',$value)))."',";
           }
           else
             $sql .= "'',";
         }
         else
         {
           if ( $feld == 6 )
             mysql_query("INSERT INTO T_Inventardaten (F_Art_id,F_Inventar_id,Inhalt) VALUES (".
               "6,$idnr,'".$value."')"); // Verlag
           if ( $feld == 7 )
             mysql_query("INSERT INTO T_Inventardaten (F_Art_id,F_Inventar_id,Inhalt) VALUES (".
               "7,$idnr,'".$value."')"); // Menge
           if ( $feld == 8 )
             mysql_query("INSERT INTO T_Inventardaten (F_Art_id,F_Inventar_id,Inhalt) VALUES (".
               "8,$idnr,'".$value."')"); // Fachbereich
         }
         $feld++;
         if ( $feld == 1 )
         {
           $sql .= "7,";  // Lieferant unbekannt
           $feld++;
         }
         if ( $feld == 4 )
         {
           $sql .= "4,";  // Art Buch
           if ( ! is_numeric($_REQUEST["Raum"] ) )
             $sql .= "781,'"; // Raum  unbekannt
           else
             $sql .= $_REQUEST["Raum"].",'";
           $sql.= $_SERVER["REMOTE_USER"]."'";
           $feld++;
         }
         if ( $feld == 5 )
         {
           if ( ! mysql_query($insertsql.$sql.")")) echo "Fehler: $insertsql/$sql:".mysql_error()."<br />";
           $idnr = mysql_insert_id();
           $feld++;
         }
         $anz++;
       }
     }
   }
   echo $anz." Datensätze importiert.";
   mysql_query("UPDATE T_Stand SET Stand = ".time().", Bearbeiter='".$_SERVER["REMOTE_USER"]."'");
   echo "</td></tr>";
   fclose($dat);
   @unlink($_FILES['Datei']['tmp_name']);
 }

 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';
 echo '<tr><td>';
 echo 'Datendatei <input type="file" name="Datei" />';
 echo 'Raumid <input type="text" name="Raum" />';
 echo '<input type="Submit" value="Bücher importieren" />';
 echo '</td></tr>';
 echo '</form>';
 include("include/footer.inc.php");
?>