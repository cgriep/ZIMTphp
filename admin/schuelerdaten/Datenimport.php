<?php

 $Ueberschrift = 'Schuldaten importieren';
 include('include/header.inc.php');
/*-----------------------------------------------------------------------------------*/
/*------------------Lehrerdaten importieren------------------------------------------*/
/*-----------------------------------------------------------------------------------*/ 
 if ( isset($_FILES['Lehrer']) && $_FILES['Lehrer']['name'] != '' )
 {
   echo '<tr><td><strong>Lese Lehrerdatendatei ein...</strong></td></tr>';
   $dat = fopen($_FILES['Lehrer']['tmp_name'],'r');
   $anz = 0;
   // Überschriftenzeile
   $buffer = fgets($dat);         
   echo '<tr><td>';
   $anz = 0;
   $aanz = 0;
   $insertsql = 'INSERT INTO T_Lehrer (Kuerzel,Name,EMail,Vorname,Sollstunden,' .
   		'Ermaessigungsstunden,ErteilteStunden,Geschlecht) VALUES (';
   while ( !feof($dat))
   {
     $buffer = fgets($dat);
     if ( trim($buffer) != '' )
     {
         $Lehrer= explode(';',$buffer);
         if ( $Lehrer[0] == 'Kürzel')
         // Daten in Datenbank schreiben
         $sql = '';
         $usql = '';
         $nr = 0;
         foreach ($Lehrer as $key => $value)
         {
           $nr++;
           if ( $value != '' )
           {
             $value = mysql_real_escape_string(trim(str_replace('"','',$value)));
             if ( is_numeric(substr($value,strlen($value)-1,1)) )
               $value = str_replace(',','.',$value);
             $sql .= "'".$value."',";
             switch ( $nr )
             {
             	case 1: $wsql = "WHERE Kuerzel='$value'";
             	        break;
             	case 2: //$usql .= "Name='$value',";
             	        $sql .= "'".$value."',"; // Name 2x wegen EMail             	        
             	        break;
             	case 3: //$usql .= "Vorname='$value',";
             	        break;
             	case 4: $usql .= "Sollstunden='$value',";
             	        break;
             	case 5: $usql .= "Ermaessigungsstunden='$value',";
             	        break;
             	case 6: $usql .= "ErteilteStunden='$value',";
             	        break;
             	case 7: $usql .= "Geschlecht=";
             	        if ( $value == 'männlich')
             	          $usql .= '"M",';
             	        else
             	          $usql .= '"W",';                             	
             }
           }
           else
             $sql .= "'',";
         }
         // letztes Komma abschneiden
         $sql = substr($sql, 0, strlen($sql)-1). ')';
         $usql = substr($usql, 0, strlen($usql)-1);
         if ( ! mysql_query("UPDATE T_Lehrer SET $usql $wsql"))
         //    echo "Fehler: $insertsql/$sql:".mysql_error()."<br />$usql/$wsql<br />";
         {
           if ( ! mysql_query($insertsql.$sql))
           {
           	  echo mysql_error().'<br >'; 
           }
           $anz++;
         }
         else
         {
           $aanz++;
         }
       }
   }
   echo $anz.' Datensätze importiert, '.$aanz.' Datensätze aktualisiert.';
   echo '</td></tr>';
   fclose($dat);
   @unlink($_FILES['Lehrer']['tmp_name']);
 }  
/*-----------------------------------------------------------------------------------*/
/*------------------Schuelerdaten importieren----------------------------------------*/
/*-----------------------------------------------------------------------------------*/ 
 if ( isset($_FILES['Schueler']) && $_FILES['Schueler']['name'] != '' )
 {
   echo '<tr><td><strong>Lese Schülerdatendatei ein...</strong></td></tr>';
   $dat = fopen($_FILES['Schueler']['tmp_name'],"r");
   $anz = 0;
   // Alle Schüler löschen
   mysql_query("DELETE FROM T_Schueler;");
   echo "<tr><td>";
   unset($Titel);
   unset($sql);
   $anz = 0;
   while ( !feof($dat))
   {
     $buffer = fgets($dat);
     if ( ! isset($Titel) )
     {
       $Titel = explode(";", $buffer);
       $insertsql = "INSERT INTO T_Schueler (";
       while ( list($key, $value) = each($Titel) )
         $insertsql .= str_replace('"',"",$value).",";
       $insertsql = substr($insertsql, 0, strlen($insertsql)-1).") VALUES (";
     }
     else
     {
       if ( trim($buffer) != "" )
       {
         $Schueler = explode(";",$buffer);
         // Daten in Datenbank schreiben
         $sql = "";
         while ( list($key, $value) = each($Schueler) )
         {
           if ( $value != "" )
           {
             if ( substr_count($value,".") == 2 && is_numeric(substr($value,1,1)))
             {
               // Geburtsdatum
               $tag = substr($value,1,strpos($value,".")-1);//" wegschneiden!
               $value = substr($value,strpos($value,".")+1);
               $monat = substr($value, 0, strpos($value,"."));
               $value = substr($value,strpos($value,".")+1);
               $jahr = substr($value,0,strlen($value)-1); // bei alter Version: mit Zeitangabe! substr($value, 0, strpos($value," "));
               echo "Geb: ".$jahr."-".$monat."-".$tag.'<br />';
               $sql .= strtotime($jahr."-".$monat."-".$tag).",";
             }
             else
               $sql .= "'".mysql_real_escape_string(trim(str_replace('"','',$value)))."',";
           }
           else
             $sql .= "'',";
         }
         // letztes Komma abschneiden
         $sql = substr($sql, 0, strlen($sql)-1). ")";
         if ( ! mysql_query($insertsql.$sql)) echo "Fehler: $insertsql/$sql:".mysql_error()."<br />";
         $anz++;
       }
     }
   }
   echo $anz." Datensätze importiert.";
   mysql_query("UPDATE T_Stand SET Stand = ".time().", Bearbeiter='".$_SERVER["REMOTE_USER"]."'");
   echo "</td></tr>";
   fclose($dat);
   @unlink($_FILES['Schueler']['tmp_name']);
 }
/*-----------------------------------------------------------------------------------*/
/*------------------Kursdaten importieren--------------------------------------------*/
/*-----------------------------------------------------------------------------------*/
 if ( isset($_FILES["Kurse"]) && $_FILES["Kurse"]["name"] != "" )
 {
   echo '<tr><td><strong>Lese Kursdatendatei ein...</strong></td></tr>';
   $dat = fopen($_FILES['Kurse']['tmp_name'],"r");
   $anz = 0;
   // Alle Kurse löschen
   mysql_query("DELETE FROM T_Kurse;");
   echo "<tr><td>";
   unset($Titel);
   unset($sql);
   $anz = 0;
   while ( !feof($dat))
   {
     $buffer = fgets($dat);
     if ( ! isset($Titel) )
     {
       $Titel = explode(";", $buffer);
       $insertsql = "INSERT INTO T_Kurse (Schueler_id, Kurs, Schuljahr, Art, Fach) VALUES (";
     }
     else
     {
       if ( trim($buffer) != "" )
       {
         $Schueler = explode(";",$buffer);
         // Daten in Datenbank schreiben
         if ( trim($Schueler[1]) != "" )
         {
           $Schueler[5]= str_replace('"',"",$Schueler[5]);
           $Schueler[1]= str_replace('"',"",$Schueler[1]);
           $Schueler[2]= str_replace('"',"",$Schueler[2]);
           $Schueler[3]= str_replace('"',"",$Schueler[3]);
           $Schueler[4]= str_replace('"',"",$Schueler[4]);
           echo "Schüler5: ".$Schueler[5]."/".substr($Schueler[5],0,1)."<br />";
           if ( substr($Schueler[5],0,1) != 'w' ) // Wiederholer- dann nicht einfügen
           {
             if ( ! mysql_query($insertsql.$Schueler[0].",'".$Schueler[1]."',".
             '"'.substr($Schueler[2],3). // Format: YY-I XXXX... schneidet 12- 11- usw. weg
             // muss mit " beginnen, da alle Daten maskiert sind und " weggeschnitten wird
             '"'.",'".$Schueler[3]."','".$Schueler[4]."')"))
             {
               $err = mysql_error();
               if ( substr($err,0,4) != "Dupl" )
                 echo mysql_error()."<br />";
             }
             $anz++;
           }
           else echo "Wiederholer!";
         }
       }
     }
   }
   echo $anz." Datensätze importiert.";
   echo "</td></tr>";
   fclose($dat);
   @unlink($_FILES['Kurse']['tmp_name']);
 }
/*-----------------------------------------------------------------------------------*/
/*------------------Raumdaten importieren--------------------------------------------*/
/*-----------------------------------------------------------------------------------*/ 
 if ( isset($_FILES["Raeume"]) && $_FILES["Raeume"]["name"] != "" )
 {
   echo '<tr><td><strong>Lese Raumdatendatei ein...</strong></td></tr>';
   $dat = fopen($_FILES['Raeume']['tmp_name'],"r");
   $anz = 0;
   echo "<tr><td>";
   unset($Titel);
   unset($sql);
   $anz = 0;
   // Titelzeile auslesen
   $buffer = fgets($dat);
   while ( !feof($dat))
   {
     $buffer = fgets($dat);
     if ( $buffer != "" )
     {
       $Titel = explode(";", $buffer);
       foreach ( $Titel as $key => $value )
         if ( $value == "" )
           if ( $key != 4 )
             $Titel[$key] = '""';
           else
             $Titel[$key] = "0";
       $insertsql = "UPDATE T_Raeume SET Kapazitaet=".$Titel[4].
         ", Verantwortlich=".$Titel[8].",Verwendung=".$Titel[9].
         ",Raumbezeichnung=".$Titel[5]." WHERE Raumnummer=".trim($Titel[7]);

       $fehler = ! mysql_query($insertsql);
       if ( mysql_affected_rows() == 0 || $fehler )
       {
         if ( $fehler ) echo "Updatefehler: ".mysql_error()."<br />";
         $insertsql = "INSERT INTO T_Raeume (Kapazitaet,Verwendung,Raumnummer,";
         $insertsql .= "Verantwortlich,Raumbezeichnung) VALUES (".$Titel[4].",".$Titel[9];
         $insertsql .= ",".$Titel[7].",".$Titel[8].",".$Titel[5].")";
         if ( ! mysql_query($insertsql) )
           echo "Einfügefehler: ".mysql_error()."<br />";
         else
           echo $Titel[7]." eingefügt.<br />";
       }
       else
         echo $Titel[7]." aktualisiert.<br />";
       $anz++;
     }
   }
   echo $anz." Datensätze importiert.";
   echo "</td></tr>";
   fclose($dat);
   @unlink($_FILES['Raeume']['tmp_name']);
 }
 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';
 echo '<tr><td>';
 echo 'Schülerdatei <input type="file" name="Schueler" />';
 echo '<input type="Submit" value="Schüler importieren" />';
 echo '<br />[Alle alten Schülerdatensätze werden beim Import gelöscht...]<br /><br />'; 
 echo '</td></tr>';
 echo '</form>';
 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';
 echo '<tr><td>';
 echo 'Kursdatei <input type="file" name="Kurse" />';
 echo '<input type="Submit" value="Kurse importieren" />';
 echo '<br />[Alle alten Kursdatensätze werden beim Import gelöscht...]<br /><br />'; 
 echo '</td></tr>';
 echo '</form>';
 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';
 echo '<tr><td>';
 echo 'Raumdatei <input type="file" name="Raeume" />';
 echo '<input type="Submit" value="Räume importieren" />';
 echo '<br />[Es erfolgt eine Aktualisierung der Raumdatensätze, keine Löschung...]<br /><br />'; 
 echo '</td></tr>';
 echo '</form>';
 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" enctype="multipart/form-data">';
 echo '<tr><td>';
 echo 'Lehrerdatei <input type="file" name="Lehrer" />';
 echo '<input type="Submit" value="Lehrer importieren" />';
 echo '<br />[Es erfolgt eine Aktualisierung der Lehrerdatensätze, keine Löschung...]<br /><br />'; 
 echo '</td></tr>';
 echo '</form>';
 include('include/footer.inc.php');
?>
