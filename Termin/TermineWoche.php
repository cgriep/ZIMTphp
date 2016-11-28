<?php
/**
 * Zeigt eine Wochenübersicht der Termine an
 * (c) 2006 Christoph Griep
 * 
 */
 DEFINE('ANZ_TAGE', 9);
 DEFINE('USE_OVERLIB', 1);
 if ( isset($_REQUEST['Woche']) && is_numeric($_REQUEST['Woche']) )
   $woche = $_REQUEST['Woche'];
 else
   $woche = strtotime(date('Y-m-d', time()));
 if ( date('m', $woche) < 8 )
   $jahr = date('Y', $woche)-1;
 else
   $jahr = date('Y', $woche);
 $Schuljahr = sprintf('%02d',($jahr-2000)).'/'.sprintf('%02d',$jahr-1999);
 $Ueberschrift = date('d.m.Y', $woche).' - Woche '.date('W', $woche).' ('.$Schuljahr.')';
  $HeaderZusatz = '<link rel="stylesheet" type="text/css" media="screen" href="http://css.oszimt.de/kalender.css">
 <link rel="stylesheet" type="text/css" media="print" href="http://css.oszimt.de/kalenderdruck.css">';

 include('include/header.inc.php');
 include('include/Termine.class.php');

 $Termine = new Termine($db, true);
 $Termine->CheckScriptEinfuegen();

/*
<style type="text/css">
<!--
@import url(http://css.oszimt.de/kalenderdruck.css) print, embossed;
@import url(http://css.oszimt.de/kalender.css) screen;
-->
</style>
*/
 if ( $Termine->istGefiltert() )
 {
   echo '<tr><td align="center" class="content-small-bold">';
   echo 'Angezeigt werden Termine für ';
   echo $Termine->holeFilterNamen($Filter);
   echo '</td></tr>';
 }
 echo '<tr><td align="center">';
 echo '<strong>Stand: ';
 echo $Termine->holeStand().'</strong>';
 echo '</td></tr>';
 echo '<tr><td><table class="Termintable" align="center" width="100%">';
 echo '<tr><td class="home-content-titel">';
 echo '<a href="'.$_SERVER['PHP_SELF'].'?Woche='.strtotime('-7 day',$woche);
 echo '" title="vorherige Woche">&lt;&lt;</a> ';
 echo '</td><td colspan="'.(ANZ_TAGE-2).'"></td>';
 echo '<td class="home-content-titel">';
 echo ' <a href="'.$_SERVER['PHP_SELF'].'?Woche='.strtotime('+7 day', $woche);
 echo '" title="nächste Woche">&gt;&gt;</a> ';
 echo '</td></tr>';
 echo '<tr>';
 for ( $tag = 0; $tag < ANZ_TAGE; $tag++)
   echo '<td width="'.(100/ANZ_TAGE).'%">'.$Wochentagnamen[date('w',strtotime("+$tag day",$woche))].'<br />'.
     date("d.m.",strtotime("+$tag day",$woche)).'</td>';
 echo '</tr>';
 echo '<tr>';
 for ( $tag = 0; $tag < ANZ_TAGE; $tag++ )
 {
   echo '<td class="mitrahmen ';
   $tagzahl = strtotime("+$tag day", $woche);
   $t = date('w', $tagzahl);
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
     echo 'Wochenende'; // Wochenende
   elseif ( $Ferien )
     echo 'Ferien';
   echo '">';
   if ( $t == 1 )
   {
     // Turnus eintragen
     $t = "";
     $sql = "SELECT Turnus FROM ".
            "(T_WocheTurnus INNER JOIN T_Turnus ON F_ID_Turnus = ID_Turnus) ".
            "INNER JOIN T_Woche ON ID_Woche=F_ID_Woche ".
            "WHERE Montag=$tagzahl AND T_Woche.SJahr='$Schuljahr' ORDER BY Turnus";
     if ( ! $query = mysql_query($sql,$db)) echo mysql_error($db);
     while ( $turnus = mysql_fetch_row($query) )
     {
       $t .= $turnus[0].' ';
     }
     mysql_free_result($query);
     echo '<span class="Turnus">'.$t.'</span><br />';
   }
   if ( $Ferien ) {
     $sql = "SELECT Kommentar FROM T_FreiTage WHERE ersterTag = $tagzahl";
     $query = mysql_query($sql,$db);
     $row = mysql_fetch_row($query);
     mysql_free_result($query);
     echo '<span class="Ferienname">'.$row[0].'</span><br />';
   }
   // Termine anfügen
   if ( ! $query = mysql_query("SELECT *, DATE_FORMAT(Stand,'%d.%m.%Y %H:%i') AS St ".
       "FROM T_Termin_Termine WHERE NOT Vorlaeufig AND Datum BETWEEN ".
       $tagzahl." AND ".mktime(23,59,59,date("m",$tagzahl),date("d",$tagzahl),
         date("Y",$tagzahl)." ORDER BY Datum"),$db)) echo mysql_error($db);
    $anz = 0;
    while ( $termin = mysql_fetch_array($query) )
    {
      $anz++;
      $xt = "";
      $art = explode(",",$termin['Betroffene']);
      if ( $Termine->istBetroffen($art) )
      {
        if ( $Termine->istBearbeiter($termin['Bearbeiter'], $termin['Betroffene']) )
          $xt .= '<a href="Termineingabe.php?Termin_id='.$termin["Termin_id"].'">';
        $xt .= '<span onMouseOver="return overlib('."'";
        $xt .= htmlentities(stripslashes(str_replace("\n","",
             str_replace("\r",'',nl2br($termin['Beschreibung'])))));
        $xt .= $Termine->BetroffeneAnzeigen($termin['Betroffene']);
        $xt .= '<br /><span class=Termininfo>'.$termin['Bearbeiter'].' / Stand: ';
        $xt .= $termin['St'].'</span>';
        $xt .= "',CAPTION,'".$termin['Bezeichnung'].' ('.
            $Termine->getKlassifikation($termin['F_Klassifikation']).")');";
        $xt .= '" onMouseOut="return nd();">';
        $xt .= $termin['Bezeichnung'];
        $xt .= '</span>';
        if ( $Termine->istBearbeiter($termin['Bearbeiter'],$termin['Betroffene']) ) $xt .= '</a>';
        if ( $anz > 1 ) echo '<hr />';
        $Inhalt = '';
        echo $Termine->InhaltAnfuegen($Inhalt,$xt, $termin['Datum']).'<br />';
      } // Count > 0 (Terminfilter!)
    }
    mysql_free_result($query);
    echo '</td>';
 } // for
 echo '</tr></table></td></tr>';
 if ( ! isset($_REQUEST['Print']) )
 {
   echo '<tr><td>';
   echo '<div class="Verwaltungskram">';
   echo '<br />';
   $Termine->zeigeTerminfilter();
   echo '<br />';
   echo '<div align="center"><a href="Termineingabe.php">Neuen Termin eingeben</a></div>';
   echo '<div align="center"><a href="Termine.php">Jahresübersicht</a></div>';
   echo '<div align="center"><a href="Terminliste.php">Termine als Liste</a></div>';
   echo '<div align="center"><a href="/">Zurück zum internen Lehrerbereich</a></div>';
   echo '</div>';
   echo '</td></tr>';
 }
 include('include/footer.inc.php');
?>