<?php
// Benötigt ein schreibbares Verzeichnis 'Work' zur Bearbeitung der Dateien
// Parameter: Datei - Datei mit den Schülernamen für T_Schueler
// Da die Exportdateien bereits Strings in ' zu stehen haben, fehlen die bei der
// Datenübernahme.

if ( isset($_REQUEST['Print']) )
  $Ueberschrift = 'Aufsichtsplan, Stand '.date('d.m.Y');
else
 $Ueberschrift = 'Aufsichtsplan bearbeiten';
 $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
 
 include('include/header.inc.php');
 include('include/turnus.inc.php');
 include('include/stupla.inc.php');
 include('include/Lehrer.class.php');

 $Aufsichtszeit[1] = 'Frühaufsicht<br />(7:45-8:00)';
 $Aufsichtszeit[2] = '1. Pause<br />(9:30-9:45)';
 $Aufsichtszeit[3] = '2. Pause<br />(11:15-11:45)';
 $Aufsichtszeit[4] = '3. Pause<br />(13:15-13:30)';
 $Aufsichtszeit[5] = '4. Pause<br />(15:00-15:15)';

 $Version = getAktuelleVersion();
 $GueltigAb = getGueltigAb(-1, 'T_Aufsichten');
 $AGueltigAb = $GueltigAb;
 if ( isset($_REQUEST['GueltigAb']) )
 {
   if ( is_numeric($_REQUEST['GueltigAb']) )
   {
     $GueltigAb = $_REQUEST['GueltigAb'];
   }
   else
   {
     // Neues Datum eingeben!
     $AltGueltig = $GueltigAb;
     $datum = explode('.', $_REQUEST['GueltigAb']);
     $GueltigAb = mktime(0,0,0,$datum[1],$datum[0],$datum[2]);
   }
   if ( isset($_REQUEST['AltKopie']) )
   {
     // Alten Plan kopieren
     mysql_query('INSERT INTO T_Aufsichten (Lehrer, Wochentag, VorStunde, F_Ort_id, GueltigAb) '.
       ' SELECT Lehrer, Wochentag, VorStunde, F_Ort_id, '.$GueltigAb.
       ' FROM T_Aufsichten WHERE GueltigAb='.$AltGueltig);
   }
 }

function holeLehrername($Lehrer)
{
  if ( ! $query = mysql_query("SELECT Name, Vorname FROM T_StuPla WHERE Lehrer='".
    mysql_real_escape_string($Lehrer)."'")) echo mysql_error();
  $row = mysql_fetch_array($query);
  mysql_free_result($query);
  return $row[0]; // mit Vorname: $row[1].' '.
}

?>
<style>
 .Tabelle { width: 100%; border-spacing: collapse; border-spacing: 0pt}
 .Tabelle td { border-width:1px;border-style:solid; text-align:center;}
 .Tabelle th { border-width:1px;border-style:solid; text-align:center;}
 .ohneRahmen { border-width:0px;border-style:hidden;}
 .Trennlinie { border-bottom-width:2px;border-style:solid; }
</style>

<?php

function AufsichtenBestimmen($GueltigAb)
{
  $query = mysql_query('SELECT Lehrer, Count(Lehrer) FROM T_Aufsichten WHERE VorStunde=3 '.
       "AND GueltigAb=$GueltigAb GROUP BY Lehrer");
  $AnzAufsichten = array();
  while ( $lehrer = mysql_fetch_row($query) )
  {
    $AnzAufsichten[$lehrer[0]] = $lehrer[1]*2;
  }
  mysql_free_result($query);
  $query = mysql_query('SELECT Lehrer, Count(Lehrer) FROM T_Aufsichten WHERE VorStunde<>3 '.
    "AND GueltigAb=$GueltigAb GROUP BY Lehrer");
  while ( $lehrer = mysql_fetch_row($query) )
  {
    if ( ! isset($AnzAufsichten[$lehrer[0]]))
      $AnzAufsichten[$lehrer[0]] = 0;
    $AnzAufsichten[$lehrer[0]] += $lehrer[1];
  }
   mysql_free_result($query);
   // Klassenlehrer feststellen
  $query = mysql_query('SELECT DISTINCT Tutor, T_Schueler.Klasse FROM T_Schueler INNER JOIN T_StuPla '.
       " ON T_Schueler.Klasse=T_StuPla.Klasse WHERE T_Schueler.Klasse NOT LIKE 'OG _'");
   while ( $lehrer = mysql_fetch_row($query) )
   {
     if ( ! isset($AnzAufsichten[$lehrer[0]]))
       $AnzAufsichten[$lehrer[0]] = 0;
     $AnzAufsichten[$lehrer[0]] = $AnzAufsichten[$lehrer[0]].'-'.$lehrer[1];
   }
   mysql_free_result($query);
   $query = mysql_query('SELECT DISTINCT Tutor, T_Schueler.Klasse FROM T_Schueler INNER JOIN T_StuPla '.
     " ON T_Schueler.Klasse=T_StuPla.Klasse WHERE T_Schueler.Klasse LIKE 'OG _'");
   while ( $lehrer = mysql_fetch_row($query) )
   {
     if ( ! isset($AnzAufsichten[$lehrer[0]]))
       $AnzAufsichten[$lehrer[0]] = 0;
     // Tutor?
     $AnzAufsichten[$lehrer[0]] = $AnzAufsichten[$lehrer[0]].'-T';
   }
   mysql_free_result($query);
   return $AnzAufsichten;
}


 echo '<tr><td>';
 $query = mysql_query('SELECT * FROM T_Aufsichtsorte ORDER BY Ort');
 $Orte = array();
 while ( $row = mysql_fetch_array($query) )
   $Orte[$row['Ort_id']] = $row;
 mysql_free_result($query);

 if ( isset($_REQUEST['Del']) && is_numeric($_REQUEST['Del']) )
 {
   mysql_query('DELETE FROM T_Aufsichten WHERE GueltigAb='.$_REQUEST['Del']);
   echo '<div class="content-bold">Aufsichtsplan gültig ab '.date('d.m.Y',$_REQUEST['Del']).
     ' gelöscht.</div>';
 }
 if ( isset($_REQUEST['Tag']) && is_numeric($_REQUEST['Tag']) )
 {
   $Stunde = $_REQUEST['Stunde'];
   $Wochentag=$_REQUEST['Tag'];
   if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
   {
     mysql_query('DELETE FROM T_Aufsichten WHERE Aufsicht_id='.$_REQUEST['id']);
   }
   if ( isset($_REQUEST['Save']) )
   {
     unset($_REQUEST['Save']);
     // Speichern
     mysql_query('INSERT INTO T_Aufsichten (F_Ort_id,Lehrer,'.
       'Wochentag,VorStunde,GueltigAb) '.
       'VALUES ('.$_REQUEST['Ort'].",'".$_REQUEST['Lehrer'].
       "',".$Wochentag.','.$Stunde.','.$GueltigAb.')');
   }
   // editieren einer Aufsicht
   if ( ! isset($_REQUEST['Lehrer']) && ! isset($_REQUEST['Print']) )
   {
     // Neue Aufsicht
     echo '<h2>Aufsicht für '.$Orte[$_REQUEST['Ort']]['Ort'].' an Tag '.$Wochentag.' vor Block '.
       $Stunde.'</h2>';
     $Raeume = explode(';',$Orte[$_REQUEST['Ort']]['Bereich']);
     reset($Raeume);
     foreach ( $Raeume as $Raum )
       if ( strpos($Raum,'.') === false && is_numeric(substr($Raum,0,1)) )
         $Raeume[] = substr($Raum,0,1).'.'.substr($Raum,1);
     reset($Raeume);
     $VorhandeneLehrer = array();
     $sql = "SELECT Lehrer FROM T_Aufsichten WHERE GueltigAb='$GueltigAb' AND ".
       "Wochentag=$Wochentag AND VorStunde=$Stunde ORDER BY Lehrer";
     if (! $query = mysql_query($sql)) echo mysql_error();
     while ( $lehrer = mysql_fetch_row($query) )
       $VorhandeneLehrer[] = $lehrer[0];
     mysql_free_result($query);
     $Vorhanden = "AND Lehrer NOT IN ('".implode("','",$VorhandeneLehrer)."')";
     $AnzAufsichten = AufsichtenBestimmen($GueltigAb);
     $Stundenwahl = "(Stunde=$Stunde OR Stunde=".($Stunde-1).")";
     if ( isset($_REQUEST["Alle"]) )
       $Stundenwahl = "1";
     $sql = "SELECT DISTINCT Lehrer, Raum, Turnus FROM T_StuPla WHERE Version=$Version AND ".
       "$Stundenwahl AND Wochentag=$Wochentag $Vorhanden ORDER BY Lehrer";
     // alle Kollegen an diesem Tag
     if ( ! $query = mysql_query($sql)) echo mysql_error();

     $Moeglich = array();
     $Turnusse = array();
     while ( $lehrer = mysql_fetch_array($query) )
     {
       if ( ! isset($AnzAufsichten[$lehrer["Lehrer"]]) )
         $AnzAufsichten[$lehrer["Lehrer"]] = 0;
       if ( ! in_array($lehrer["Lehrer"], $Moeglich) )
         $Moeglich[] = $lehrer["Lehrer"];
       if ( ! isset($Turnusse[$lehrer['Lehrer']]) ||
            ! is_array($Turnusse[$lehrer["Lehrer"]]) ||
            ! in_array($lehrer["Turnus"], $Turnusse[$lehrer["Lehrer"]]) )
         $Turnusse[$lehrer["Lehrer"]][] = $lehrer["Turnus"];
     }
     mysql_free_result($query);

     echo '<form action="'.$_SERVER["PHP_SELF"].'?Tag='.$Wochentag.
       '&Stunde='.$Stunde.'&Ort='.$_REQUEST["Ort"].'&GueltigAb='.$GueltigAb.'" method="post">';
     echo 'alle KollegInnen mit Unterricht um Block '.$Stunde.": ";
     echo '<select name="Lehrer">';
     foreach ( $Moeglich as $lehrer )
       echo '<option value="'.$lehrer.'">'.$lehrer.
         " (".implode("/",$Turnusse[$lehrer]).", ".
         $AnzAufsichten[$lehrer].")</option>\n";
     echo '</select>';
     echo '<input type="Submit" name="Save" value="Wählen" />';
     if ( ! isset($_REQUEST["Alle"]) )
     {
       echo '<br /><a href="'.$_SERVER["PHP_SELF"]."?Tag=$Wochentag&Stunde=$Stunde&";
       echo "Ort=".$_REQUEST["Ort"]."&Alle=1&GueltigAb=$GueltigAb";
       echo '">Alle Kollegen mit Unterricht anzeigen</a>';
     }
     echo '</form>';
     $suchRaum = "";
     foreach ( $Raeume as $Raum )
       $suchRaum .= "Raum LIKE '".$Raum."%' OR ";
     $sql = "SELECT DISTINCT Lehrer, Raum, Turnus FROM T_StuPla WHERE Version=$Version AND ".
       "Stunde=$Stunde AND Wochentag=$Wochentag AND (";
     // alle Kollegen an diesem Tag
     if ( ! $query = mysql_query($sql.$suchRaum." 0) $Vorhanden ORDER BY Lehrer")) echo mysql_error();

     $nachMoeglich = array();
     while ( $lehrer = mysql_fetch_array($query) )
     {
       if ( ! isset($AnzAufsichten[$lehrer["Lehrer"]]) )
         $AnzAufsichten[$lehrer["Lehrer"]] = 0;
       if ( ! in_array($lehrer["Lehrer"], $nachMoeglich) )
         $nachMoeglich[] = $lehrer["Lehrer"];
       if ( ! is_array($Turnusse[$lehrer["Lehrer"]]) ||
            ! in_array($lehrer["Turnus"], $Turnusse[$lehrer["Lehrer"]]) )
         $Turnusse[$lehrer["Lehrer"]][] = $lehrer["Turnus"];
     }
     mysql_free_result($query);
     $vorMoeglich = array();
     $gutMoeglich = array();
     $vorTurnusse = array();
     if ( $Stunde != 1 )
     {
       $sql = "SELECT Lehrer, Raum, Turnus FROM T_StuPla WHERE Version=$Version AND (Stunde=".
         ($Stunde-1)." OR Stunde=$Stunde) AND Wochentag=$Wochentag AND (";
       if ( ! $query = mysql_query($sql.$suchRaum." 0) $Vorhanden ORDER BY Lehrer")) echo mysql_error();
       while ( $lehrer = mysql_fetch_array($query) )
       {
         if ( ! isset($AnzAufsichten[$lehrer["Lehrer"]]) )
           $AnzAufsichten[$lehrer["Lehrer"]] = 0;
         if ( ! in_array($lehrer["Lehrer"],$nachMoeglich) &&
              ! in_array($lehrer["Lehrer"],$gutMoeglich) &&
              ! in_array($lehrer["Lehrer"],$vorMoeglich) )
         {
            $vorMoeglich[] = $lehrer["Lehrer"];
         }
         $vorTurnusse[$lehrer["Lehrer"]][] = $lehrer["Turnus"];
         if ( in_array($lehrer["Lehrer"],$nachMoeglich) )
         {
           $gutMoeglich[] = $lehrer["Lehrer"];
           $key = array_search($lehrer["Lehrer"],$nachMoeglich);
           unset($nachMoeglich[$key]);
         }
       }
       mysql_free_result($query);
     }
     echo '<h2>im gewählten Bereich ('.implode(",",$Raeume).') haben Unterricht</h2>';
     if ( Count($nachMoeglich) > 0 )
     {
       echo '<form action="'.$_SERVER["PHP_SELF"].'?Tag='.$Wochentag.
         '&Stunde='.$Stunde.'&Ort='.$_REQUEST["Ort"].'&GueltigAb='.$GueltigAb.'" method="post">';
       echo 'KollegInnen nur mit Unterricht in Block '.$Stunde.": ";
       echo '<select name="Lehrer">';
       foreach ( $nachMoeglich as $lehrer )
       {
         if ( ! isset($AnzAufsichten[$lehrer]) )
               $AnzAufsichten[$lehrer] = 0;
             echo '<option value="'.$lehrer.'">'.$lehrer." (";
             if ( is_array($Turnusse[$lehrer]) )
               echo implode("/",$Turnusse[$lehrer]).", ";
             echo $AnzAufsichten[$lehrer].")</option>\n";
       }
       echo "</select>\n";
       echo '<input type="Submit" name="Save" value="Wählen" />';
       echo "</form>\n";
     }
     if ( Count($vorMoeglich) > 0 )
     {
         echo '<form action="'.$_SERVER["PHP_SELF"].'?Tag='.$Wochentag.
           '&Stunde='.$Stunde.'&Ort='.$_REQUEST["Ort"].'&GueltigAb='.$GueltigAb.'" method="post">';
         echo 'KollegInnen mit Unterricht nur vor Block '.$Stunde.": ";
         echo '<select name="Lehrer" >';
         foreach ( $vorMoeglich as $lehrer )
         {
            if ( ! isset($AnzAufsichten[$lehrer]) )
               $AnzAufsichten[$lehrer] = 0;
             echo '<option value="'.$lehrer.'">'.$lehrer." (";
             if ( is_array($vorTurnusse[$lehrer]) )
               echo implode("/",$vorTurnusse[$lehrer]).", ";
             echo $AnzAufsichten[$lehrer].")</option>\n";
         }
         echo '</select>';
         echo '<input type="Submit" name="Save" value="Wählen" />';
         echo "</form>\n";
     }
     if ( is_array($gutMoeglich) && Count($gutMoeglich) > 0 )
     {
         echo '<form name=action="'.$_SERVER["PHP_SELF"].'?Tag='.$Wochentag.
          '&Stunde='.$Stunde.'&Ort='.$_REQUEST["Ort"].'&GueltigAb='.$GueltigAb.'" method="post">';
         echo 'KollegInnen mit Unterricht vor und in Block '.$Stunde.": ";
         echo '<select name="Lehrer" >';
         foreach ( $gutMoeglich as $lehrer )
         {
           if ( ! isset($AnzAufsichten[$lehrer]) )
             $AnzAufsichten[$lehrer] = 0;
           echo '<option value="'.$lehrer.'">'.$lehrer." (";
           if ( is_array($Turnusse[$lehrer]) )
             echo implode("/",$Turnusse[$lehrer]).", ";
           echo $AnzAufsichten[$lehrer].")</option>\n";
         }
         echo '</select>';
         echo '<input type="Submit" name="Save" value="Wählen" />';
         echo "</form>\n";
     }
   }
 }
 echo "<br />\n";
 echo '<div class="content-bold">Aufsichtsplan gültig ab '.
     date("d.m.Y",$GueltigAb)."</div>";

 if ( isset($_REQUEST["Liste"]) )
 {
    // Übersichtsliste der Kollegen
    $AnzAufsichten = AufsichtenBestimmen($GueltigAb);
    $query  = mysql_query("SELECT DISTINCT Lehrer FROM T_StuPla ".
      "WHERE Version=$Version ORDER BY Lehrer");
    echo '<table class="Liste">';
    while ( $Lehrer = mysql_fetch_array($query) )
    {
      echo '<tr><td>';
      $L = new Lehrer($Lehrer['Lehrer']);
      echo $L->Name;
      if ( $L->Vorname != '' )
        echo ', '.$L->Vorname;
      echo '</td><td style="text-align:left">';
      if ( isset($AnzAufsichten[$Lehrer['Lehrer']]))
        echo $AnzAufsichten[$Lehrer["Lehrer"]];
      echo "</td></tr>\n";
    }
    echo '</table>';
    mysql_Free_result($query);
 }
 else
 {
   $query = mysql_query("SELECT * FROM T_Aufsichten WHERE GueltigAb='$GueltigAb' ".
      "ORDER BY VorStunde, Wochentag");
   $Aufsichten = array();
   while ( $row = mysql_fetch_array($query) )
   {
     $Aufsichten[$row["VorStunde"]][$row["Wochentag"]][$row["F_Ort_id"]][] = $row;
   }
   mysql_free_result($query);
   // Neuen Aufsichtsplan erstellen
   if ( ! isset($_REQUEST["Print"]) )
   {
     echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
     echo 'Neuen Plan gültig ab ';
     echo '<input type="Text" name="GueltigAb" value="'.date("d.m.Y").'" size="7"/> ';
     if ( strtotime("+ 8h", $GueltigAb) < time() )
     {
       echo '(';
       echo '<input type="Checkbox" name="AltKopie" value="v" />';
       echo ' bisherigen Aufsichtsplan kopieren) ';
     }
     echo '<input type="Submit" value="Erstellen" />';
     echo '</form>';
     // evtl. weiterschalten zum nächsten Plan
     $query = mysql_query("SELECT DISTINCT GueltigAb FROM T_Aufsichten ".
       "WHERE GueltigAb > $GueltigAb ORDER BY GueltigAb");
     while ( $row = mysql_fetch_array($query) )
     {
       echo '<a href="'.$_SERVER["PHP_SELF"].'?GueltigAb='.$row["GueltigAb"];
       echo '">Aufsichtplan ab '.date("d.m.Y",$row["GueltigAb"])."</a> / ";
     }
     mysql_free_result($query);
     echo '&nbsp;&nbsp;angezeigten Aufsichtsplan unwiderruflich <a href="'.
       $_SERVER["PHP_SELF"].'?Del='.$GueltigAb.'">löschen</a>';
   }
   echo '<table class="Liste">';
   echo '<tr><th>Zeit</th><th>Ort</th>';
   echo '<th>Montag</th><th>Dienstag</th><th>Mittwoch</th><th>Donnerstag</th><th>Freitag</th></tr>';
   for ( $Stunde = 1; $Stunde < 5; $Stunde++)
   {
     $StundeDa = false;
     foreach ($Orte as $Ort )
     {
       echo '<tr>';
       if ( ! $StundeDa )
       {
         echo '<td rowspan="'.Count($Orte).'">'.$Aufsichtszeit[$Stunde].'</td>';
         $StundeDa = true;
       }
       // prüfen, ob überhaupt eine Aufsicht vorhanden ist
       $da = false;
       if ( isset($_REQUEST['Print']) )
       {
         for ( $Wochentag = 1; $Wochentag <= 5; $Wochentag++)
           if ( is_array($Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']]) )
             $da = true;
       }
       if ( $da || ! isset($_REQUEST['Print']) )
       {
         echo '<td>';
         echo $Ort['Ort'];
         echo '</td>';
         for ( $Wochentag = 1; $Wochentag <= 5; $Wochentag++)
         {
           echo '<td>';
           $da = false;
           if ( isset($Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']]) &&
                is_array($Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']]) )
             foreach ( $Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']] as $value )
             {
               if ( $da ) echo '<br />';
               if ( ! isset($_REQUEST['Print']) )
                 echo '<a href="'.$_SERVER['PHP_SELF'].'?Stunde='.$Stunde.'&Tag='.
                 $Wochentag.'&Ort='.$Ort['Ort_id'].'&id='.$value['Aufsicht_id'].
                 '&GueltigAb='.$GueltigAb.'" title="'.$value['Lehrer'].' entfernen">';
               if ( isset($_REQUEST['Print']) )
                 echo holeLehrername($value['Lehrer']);
               else
                 echo $value['Lehrer'];
               if ( ! isset($_REQUEST['Print']) )
                 echo '</a>';
               $da = true;
             }
           if ( ! isset($_REQUEST['Print']) )
           {
             echo ' <a href="'.$_SERVER['PHP_SELF'].'?Stunde='.$Stunde.'&Tag='.
                   $Wochentag.'&Ort='.$Ort['Ort_id'].'&GueltigAb='.$GueltigAb.
                   '" title="hinzufügen">';
             if ( !$da )
             //  echo '(+)'; // entfernt 19.09. - hinzufügen weiterer Aufsichtspersonen
               echo 'N.N.';
             echo '</a>';
           }
           echo '</td>';
         }
       } // leere Zeilen unterdrücken
       echo '</tr>';
     }
     echo '<tr class="Trennlinie"><td colspan="7"></td></tr>';
   }
   echo '</table>';
 }
 if ( ! isset($_REQUEST['Print']) )
 {
   if ( ! isset($_REQUEST['Liste']))
   {
     echo '<a href="'.$_SERVER['PHP_SELF'].'?Liste=1&GueltigAb='.date('d.m.Y',$GueltigAb).'">Übersicht aller Kollegen</a><br />';
     echo '<a href="../../StuPla/AufsichtPDF.php?GueltigAb='.$GueltigAb.
       '" target="_blank">Aufsichtsplan im Druckformat</a>';
   }
   else
     echo '<a href="'.$_SERVER['PHP_SELF'].'?GueltigAb='.$GueltigAb.'">Aufsichtsplan</a>';
 }
 echo '</td></tr>';
 include('include/footer.inc.php');
?>