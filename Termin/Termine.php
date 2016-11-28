<?php
/*
 * Termine.php
 * Zeigt alle Termine des aktuellen Jahres an.
 * Unterhalb der Tabelle kann man den Filter für die Termine anwählen.
 * (c) 2006 Christoph Griep
 */
 DEFINE("ANZ_TAGE", 12);
 DEFINE("USE_OVERLIB", 1);
 if ( isset($_REQUEST["Jahr"]) && is_numeric($_REQUEST["Jahr"]) )
   $jahr = $_REQUEST["Jahr"];
 elseif ( date("m") < 8 )
   $jahr = date("Y")-1;
 else
   $jahr = date("Y");
 $Schuljahr = sprintf("%02d",($jahr-2000))."/".sprintf("%02d",$jahr-1999);
 $Ueberschrift = "Terminplan ".$Schuljahr;
 $HeaderZusatz = '<link rel="stylesheet" type="text/css" media="screen" href="http://css.oszimt.de/kalender.css">
 <link rel="stylesheet" type="text/css" media="print" href="http://css.oszimt.de/kalenderdruck.css">';
/*
 $HeaderZusatz = '<style type="text/css">
<!--
@import url(http://css.oszimt.de/kalenderdruck.css) print, embossed;
@import url(http://css.oszimt.de/kalender.css) all;
-->
</style>';
*/
 include("include/header.inc.php");

 include_once("include/Termine.class.php");
 $Termine = new Termine($db, true);
 $Termine->CheckScriptEinfuegen();

 if ( $Termine->istGefiltert() )
 {
   echo '<tr><td align="center" class="content-small-bold">';
   echo 'Angezeigt werden Termine für ';
   echo $Termine->holeFilterNamen();
   echo '</td></tr>';
 }
 echo '<tr><td align="center">';
 echo "<strong>Stand: ";
 echo $Termine->holeStand()."</strong>";
 echo "</td></tr>\n";
 echo '<tr><td><table class="Termintable">';
 echo '<tr><td colspan="6" class="home-content-titel">';
 echo '<a href="'.$_SERVER["PHP_SELF"].'?Jahr='.($jahr-1);
 echo '" title="vorheriges Jahr">&lt;&lt;</a> ';
 echo $jahr."</td><td></td>\n";
 echo '<td colspan="8" class="home-content-titel">'.($jahr+1);
 echo ' <a href="'.$_SERVER["PHP_SELF"].'?Jahr='.($jahr+1);
 echo '" title="nächstes Jahr">&gt;&gt;</a> ';
 echo "</td></tr>\n";
 echo '<tr><td></td><td>August</td><td>September</td><td>Oktober</td><td>November</td>';
 echo "<td>Dezember</td><td></td><td>Januar</td><td>\n";
 echo 'Februar</td><td>März</td>'."\n";
 echo '<td>April</td><td>Mai</td><td>'."\n";
  echo 'Juni</td><td>Juli</td></tr>'."\n";
 // gleich wird wieder was abgezogen
 $jahr++;

 for ( $tag = 1; $tag < 32; $tag++ )
 {
   $monat = 8;
   $jahr--;
   echo "<tr>\n";
   echo '<td align="center">'.$tag."</td>\n";
   while ( $monat != 0 )
   {
     if ( date("t",mktime(0,0,0,$monat,1,$jahr)) >= $tag )
     {
       $Inhalt = "";
       echo '<td class="mitRahmen ';
       $tagzahl = mktime(0,0,0,$monat, $tag, $jahr);
       $t = date("w", $tagzahl);
       $sql = "SELECT Count(*) FROM T_FreiTage WHERE ersterTag <= $tagzahl AND ".
         "letzterTag >= $tagzahl";
       $query = mysql_query($sql, $db);
       $row = mysql_fetch_row($query);
       mysql_free_result($query);
       if ($row[0] > 0 )
         $Ferien = true;
       else
         $Ferien = false;
       if ( $t == 0 || $t == 6 )
         echo 'Wochenendetag'; // Wochenende
       elseif ( $Ferien )
         echo 'Ferien';
       echo '">';
       if ( $t == 1 )
       {
         // Turnus eintragen
         $t = '';
         $sql = 'SELECT Turnus FROM '.
            '(T_WocheTurnus INNER JOIN T_Turnus ON F_ID_Turnus = ID_Turnus) '.
            'INNER JOIN T_Woche ON ID_Woche=F_ID_Woche '.
            "WHERE Montag=$tagzahl AND T_Woche.SJahr='$Schuljahr' ORDER BY Turnus";
         if ( ! $query = mysql_query($sql,$db)) echo mysql_error($db);
         while ( $turnus = mysql_fetch_row($query) )
         {
           $t .= $turnus[0].' ';
         }
         mysql_free_result($query);
         $Inhalt = $Termine->InhaltAnfuegen($Inhalt, '<span class="Turnus">'.$t.'</span>');
       }
       if ( $Ferien ) {
         $sql = "SELECT Kommentar FROM T_FreiTage WHERE ersterTag = $tagzahl";
         $query = mysql_query($sql,$db);
         $row = mysql_fetch_row($query);
         mysql_free_result($query);
         $Inhalt = $Termine->InhaltAnfuegen($Inhalt, '<span class="Ferienname">'.$row[0].'</span>');
       }
       // Termine anfügen
       $Leitung = '';
       if ( !$Termine->vorlaeufigPerson() )
         $Leitung = 'NOT Vorlaeufig AND';
       if ( ! $query = mysql_query("SELECT *, DATE_FORMAT(Stand,'%d.%m.%Y %H:%i') AS St ".
         "FROM T_Termin_Termine WHERE $Leitung Datum BETWEEN ".
         $tagzahl.' AND '.mktime(23,59,59,$monat,$tag,$jahr),$db)) echo mysql_error($db);
       while ( $termin = mysql_fetch_array($query) )
       {
         $xt = '';
         $art = explode(',',$termin['Betroffene']);
         if ( $Termine->istBetroffen($art) )
         {
           if ( $Termine->istBearbeiter($termin['Bearbeiter'],$termin['Betroffene']) )
             $xt .= '<a href="Termineingabe.php?Termin_id='.$termin['Termin_id'].'">';
           $xt .= '<span onMouseOver="return overlib('."'";
           $xt .= htmlentities(stripslashes(str_replace("\n",'',
              str_replace("\r",'',nl2br($termin['Beschreibung'])))));
           $xt .= $Termine->BetroffeneAnzeigen($termin['Betroffene']);
           $xt .= '<br /><span class=Termininfo>'.$termin['Bearbeiter'].' / Stand: ';
           $xt .= $termin['St'].'</span>';
           $xt .= "',CAPTION,'".$termin['Bezeichnung'];
           if ( $termin['Vorlaeufig'] ) $xt .= ' VORLÄUFIG!';
           $xt .= ' ('.$Termine->getKlassifikation($termin['F_Klassifikation']).")');";
           $xt .= '" onMouseOut="return nd();" class="Termin">';
           $xt .= $termin['Bezeichnung'];
           $xt .= "</span>\n";
           if ( $Termine->istBearbeiter($termin['Bearbeiter'],$termin['Betroffene']) ) $xt .= '</a>';
           if ( $termin['Vorlaeufig'] )
             $xt = '<span class="vorlaeufig">'.$xt.'</span>';
           $Inhalt = $Termine->InhaltAnfuegen($Inhalt,$xt, $termin['Datum']);           
         } // Count > 0 (Terminfilter!)
       }
       mysql_free_result($query);
       echo $Inhalt;
       echo "</td>\n";
     }
     else
     {
       // Zelle ohne Rahmen
       echo "<td>&nbsp;</td>\n";
     }
     $monat++;
     if ( $monat == 13 )
     {
       echo "<td>&nbsp;</td>\n"; // Leere Spalte zum Trennen
       $monat = 1;
       $jahr++;
     }
     elseif ( $monat == 8 ) $monat = 0;
   }
   echo '<td align="center">'.$tag."</td></tr>\n";
 }
 echo "</table><br />\n";
 echo '<table><tr><td class="Wochenendetag">Wochenende</td>';
 echo '<td class="Ferien">Ferien</td></tr></table>';
 if ( ! isset($_REQUEST["Print"]) )
 {
   echo '<div class="Verwaltungskram">';
   echo "<br />\n";
   $Termine->zeigeTerminfilter();
   //include("Terminfilter.inc.php");
   echo "<br />\n";
   echo '<div align="center"><a href="Termineingabe.php">Neuen Termin eingeben</a></div>';
   if ( $Termine->vorlaeufigPerson() )
   {
     echo '<div align="center"><a href="Terminliste.php?Vorlaeufig=1">Terminliste mit vorläufigen Terminen</a></div>';
     echo '<div align="center"><a href="Terminliste.php">Terminliste ohne vorläufige Termine</a></div>';
     echo '<div align="center"><a href="TerminKategorien.php">Terminkategorien bearbeiten</a></div>';
   }
   else
     echo '<div align="center"><a href="Terminliste.php">Termine als Liste</a></div>';
   echo '<div align="center"><a href="TermineWoche.php">Wochenübersicht</a></div>';
   echo '<div align="center"><a href="TerminePDF.php?Jahr='.($jahr-1).'">Übersichtsplan zum Drucken</a></div>';
   echo '<div align="center"><a href="/">Zurück zum internen Lehrerbereich</a></div>';
   echo "</div>\n";
 }
 echo "</td></tr>\n";

 include("include/footer.inc.php");
?>