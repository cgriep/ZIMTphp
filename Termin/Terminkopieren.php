<?php
/**
 * Kopiert Termine von einem Jahr ins nächste
 * 12.03.06 - C. Griep: Beginn der Adaptierung auf den neuen Server
 */
 $von = time();
 if ( isset($_REQUEST["von"]) )
 {
   $von = $_REQUEST["von"];
   if ( ! is_numeric($von) )
   {
     $datum = explode(".",$von);
     if (Count($datum) == 3)
       $von = strtotime($datum[2]."-".$datum[1]."-".$datum[0]);
     else
       $von = time();
   }
 }
 $bis = mktime(0,0,0,8,1,date("Y",$von)+1);
 if ( isset($_REQUEST["bis"]) )
 {
   $bis = $_REQUEST["bis"];
   if ( ! is_numeric($bis) )
   {
     $datum = explode(".",$bis);
     if (Count($datum) == 3)
       $bis = strtotime($datum[2]."-".$datum[1]."-".$datum[0]);
   }
   else
     $bis = mktime(0,0,0,8,1,date("Y",$von)+1);
 }

 $Ueberschrift = "Termine kopieren";
 include("include/header.inc.php");
 include("include/Termine.class.php");
 $Termine = new Termine($db, true);
 $Termine->CheckScriptEinfuegen();

?>
<style type="text/css">
<!--
.untenRahmen { border-bottom-width:1pt; border-bottom-style:solid; border-bottom-color: black;}
.Termintable { border-spacing: 0pt; border-collapse: collapse;}
-->
</style>

<?php
  if ( isset($_REQUEST["Kopie"]) && is_array($_REQUEST["Kopie"]) )
  {
    // Kopieren
    $sql = "SELECT * FROM T_Termin_Termine WHERE Termin_id IN (";
    $terminids = implode(",",$_REQUEST["Kopie"]);
    $sql .= $terminids.")";
    $anz=0;
    if (! $query = mysql_query($sql)) echo "Kopiefehler $sql: ".mysql_error();
    while ( $termin = mysql_fetch_array($query) )
    {
       if ( mysql_query("INSERT INTO T_Termin_Termine (Bezeichnung, Bearbeiter, Beschreibung, ".
        "F_Klassifikation, Datum, Betroffene,Vorlaeufig) VALUES ('".
        mysql_real_escape_string($termin["Bezeichnung"])."','".
        $_SERVER["REMOTE_USER"]."','".mysql_real_escape_string($termin["Beschreibung"])."',".
        $termin["F_Klassifikation"].",".
        strtotime("+1 year", $termin["Datum"]).",'".$termin["Betroffene"]."',true)") )
         $anz++;
    }
    mysql_free_result($query);
    echo '<tr><td><strong>&gt;&gt;&gt; '.$anz.' Termine kopiert.</strong></td></tr>';
  }
 echo '<tr><td align="center">';
 echo "<strong>Stand: ";
 echo $Termine->holeStand(true)."</strong>";
 echo '</td></tr>';

 if ( ! isset($_REQUEST["Print"]) )
 {
   echo '<tr><td align="center"><a href="Termine.php">Zur Terminübersicht</a></td></tr>';
   echo '<tr><td align="center"><a href="/">Zurück zum internen Lehrerbereich</a></td></tr>';
 }
 echo '<tr><td>';
 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
 echo 'vorhandene Termine anzeigen von <input type="Text" name="von" value="'.
   date("d.m.Y",$von).'" size="10" maxlength="10" />';
 echo ' bis <input type="Text" name="bis" value="'.date("d.m.Y",$bis).
   '" size="10" maxlength="10" /><br />';
 echo '<input type="Submit" value="Anzeigen"/>';
 echo '</form>';
 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
 echo '<table class="Termintable">';
 if ( $Termine->istGefiltert() )
 {
   echo '<tr><td align="center" class="content-small-bold" colspan="4">';
   echo 'Angezeigt werden Termine für ';
   echo $Termine->holeFilternamen();
   echo '</td></tr>';
 }
 $sql = "SELECT * FROM T_FreiTage WHERE ersterTag <= $bis AND letzterTag >= $von ORDER BY ersterTag";
 $freiquery = mysql_query($sql, $db);
 if ( ! $frei = mysql_fetch_array($freiquery)) unset($frei);
 $query = mysql_query("SELECT * FROM T_Termin_Termine WHERE Datum BETWEEN ".$von." AND ".
   $bis." ORDER BY Datum", $db);
 $dieTermine = array();
 while ($termin = mysql_fetch_array($query) )
 {
   // Ferien einbauen
   if ( $Termine->istBetroffen($termin["Betroffene"]) )
   {
     while ( isset($frei) && $frei["ersterTag"] < $termin["Datum"] )
     {
       $fTermin["Datum"] = "";
       $fTermin["Bezeichnung"] = $frei["Kommentar"]." (".date("d.m.",$frei["ersterTag"]).
         " - ".date("d.m.",$frei["letzterTag"]).")";
       $fTermin["Betroffene"] = "";
       $fTermin["F_Klassifikation"] = -1;
       $fTermin["Beschreibung"] = "";
       $dieTermine[] = $fTermin;
       if ( ! $frei = mysql_fetch_array($freiquery)) unset($frei);
     }
     $dieTermine[] = $termin;
   }
 }
 mysql_free_result($query);
 mysql_free_result($freiquery);
 while ( list($nr, $termin) = each($dieTermine) )
 {
     echo '<tr ';
     if ( $termin["Datum"] == "" ) echo 'class="unterlegt"';
     echo '><td class="untenRahmen">';
     if ( $termin["Datum"] != "" )
       echo '<input type="Checkbox" name="Kopie[]" value="'.$termin["Termin_id"].'">';
     echo '</td>';
     echo '<td class="untenRahmen">';
     if ( $termin["Datum"] != "" )
     {
       echo date("d.m.Y",$termin["Datum"]);
       $Uhrzeit = date("H:i",$termin["Datum"]);
       if ( $Uhrzeit != "00:00" ) echo ' <span class="home-content-small">'.$Uhrzeit."</span>";
     }
     echo '&nbsp;</td><td class="home-content untenRahmen">&nbsp;';
     echo $Termine->BEtroffeneAnzeigen($termin["Betroffene"],"<br />", false);
     echo '&nbsp;</td><td class="untenRahmen"><span class="home-content-titel">'.
       stripslashes($termin["Bezeichnung"]);
     echo '</span> ';
     $klassifikation = $Termine->getKlassifikation($termin["F_Klassifikation"]);
     if ( $klassifikation != "" )
     {
       echo '<span class="home-content-small">(';
       echo $klassifikation;
       echo ')</span>';
     }
     echo '<br />';
     if ( $termin["Beschreibung"] != "" )
       echo nl2br(stripslashes($termin["Beschreibung"]));
     echo '</td></tr>';
 }
 echo '</table>';
 echo '<input type="Submit" value="markierte ins nächste Jahr kopieren" />';
 echo '</form>';
 echo '</td></tr>';
 if ( ! isset($_REQUEST["Print"] ) )
 {
   echo '<tr><td>';
   $Termine->zeigeTerminfilter();
   echo '</td></tr>';
   echo '<tr><td align="center"><a href="Termine.php">Zur Terminübersicht</a></td></tr>';
   echo '<tr><td align="center"><a href="/">Zurück zum internen Lehrerbereich</a></td></tr>';

 }
 include("include/footer.inc.php");
?>