<?php
/*
 * Verhinderung.php
 * Eingabe von Verhinderungen mit Anfangs- und Endzeitpunkt sowie einer Bemerkung
 * 
 * (c) 2006 Christoph Griep
 * 
 * Letzte Änderungen:
 * 17.01.06 C. Griep
 * 10.03.06 C. Griep - an neuen Server angepasst
 * 28.03.06 Angabe der einzelnen Blöcke
 * 18.08.06 Parameter nomail eingeführt - verhindert das Sender der Eintragungs-Mails
 */
$Ueberschrift = 'Verhinderungen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
define('USE_KALENDER', 1);
include('include/header.inc.php');
?>
<tr><td>
<?php
session_unregister('VertretungErledigt');
session_unregister('Datum');

include('include/helper.inc.php');
include('include/stupla.inc.php');
include('include/raeume.inc.php');
include('include/Lehrer.class.php');
include('include/Vertretungen.inc.php');
include('include/Vertretungsliste.inc.php');
include_once('include/Abteilungen.class.php');

$Abteilungen = new Abteilungen($db);

function FormularAnzeigen($Ueberschrift, $Was, $Eintrag, $sql, $Anzeige, $Auswahlwerte)
{
  global $Gruende;
  echo '<a name="Link'.$Was.'" id="Link'.$Was.'"></a>';
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="F'.$Was.
    '" class="Verhinderung">';
  if ( isset($Eintrag['Verhinderung_id']) && is_numeric($Eintrag['Verhinderung_id']) )
  {
    echo "<h1>Veränderung eines Verhinderungseintrages</h1>\n";
    echo '<div class="Hinweis">Stand: '.$Eintrag['Stand'].
        ', zuletzt bearbeitet durch '.$Eintrag['Bearbeiter'];
    echo '<input type="hidden" name="Verhinderung_id" value="'.$Eintrag['Verhinderung_id'].'" />';
    echo "</div>\n";
  }
  else
    echo "<h1>$Ueberschrift</h1>\n";
  echo $Was.' <select name="'.$Was.'">';
  $query = mysql_query($sql);
  while ( $lehrer = mysql_fetch_array($query) )
  {
    echo '<option value="'.$lehrer[$Was].'" ';
    if ( isset($Eintrag['Wer']) && $Eintrag['Wer'] == $lehrer[$Was] )
      echo 'selected="selected"';
    echo '>';
    foreach ($Anzeige as $key => $Feld)
    {
      if ( $key != 0 ) echo ', ';
      echo $lehrer[$Feld];
    }
    echo "</option>\n";
  }
  mysql_free_result($query);
  if ( ! isset($Eintrag['Von']) ) $Eintrag['Von'] = time();
  if ( ! isset($Eintrag['Bis']) ) $Eintrag['Bis'] = time();
  if ( ! isset($Eintrag['Grund']) ) $Eintrag['Grund'] = $Auswahlwerte[0];
  if ( ! isset($Eintrag['UE1']) )
  {
  	 for ( $i = 1; $i < 7; $i++)
  	   $Eintrag['UE'.$i] = true;
  }
  echo "</select> verhindert \n";
  echo 'von <input type="Text" name="Von" value="'.date("d.m.Y",$Eintrag["Von"]);
  echo '" size="10" maxlength="10" ';
  echo 'onClick="popUpCalendar(this,F'.$Was."['Von'],'dd.mm.yyyy')\" ";
  echo 'onBlur="autoCorrectDate(\'F'.$Was."','Von' , false )\"";
  echo '/>';
  echo "\n".' bis <input type="Text" name="Bis" value="'.date("d.m.Y",$Eintrag["Bis"]).
    '" size="10" maxlength="10"';
  echo 'onClick="popUpCalendar(this,F'.$Was."['Bis'],'dd.mm.yyyy')\" ";
  echo 'onBlur="autoCorrectDate(\'F'.$Was."','Bis' , false )\"";
  echo '/> ';
  echo 'in Block ';
  for ( $i = 1; $i< 7; $i++)
  {
  	echo $i.'. <input type="checkbox" name="UE'.$i.'" value="';
  	if ( $Eintrag['UE'.$i])
  	  echo 'checked="checked"';
    echo '"/> '."\n";
  }
  echo "<br />\n";
  foreach ( $Auswahlwerte as $i )
  {
    echo '<input type="radio" name="Grund" value="'.$i.'" ';
    if ( $Eintrag['Grund'] == $i ) echo 'checked="checked"';
    echo '/> '.$Gruende[$i].'&nbsp;&nbsp;'."\n";
  }
  echo "<br />Hinweise für den Vertretungsplan<br />\n";
  echo '<textarea name="Hinweis" cols="60" rows="5">';
  if ( isset($Eintrag['Hinweis']) ) echo stripslashes($Eintrag['Hinweis']);
  echo "</textarea>\n";
  echo '<br />';  
  echo '<input type="Submit" value="';
  if ( isset($Eintrag['Verhinderung_id']) && is_numeric($Eintrag['Verhinderung_id']) )
    echo 'Meldung aktualisieren';
  else
    echo 'Meldung eintragen';
  echo '" />';
  if ( isset($Eintrag['Verhinderung_id']) && is_numeric($Eintrag['Verhinderung_id']) )
    echo '&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'">Meldung nicht verändern, neue Meldung eintragen</a>';
  echo "</form>\n";
} 

function istSelbst($empfaenger)
{
  $name = explode('@', $empfaenger);	 
  if ( strtoupper($_SERVER['REMOTE_USER']) != strtoupper($name[0]) )
    return false;
  else
    return true;    
}

foreach ( array('Lehrer','Klasse','Raum') as $Feld )
{  
if ( isset($_REQUEST[$Feld]) )
{
	$Art = $Feld;
	if ( $Art == 'Raum')
	{
		// Auf Winschool-Bezeichnung trimmen
		
		$Art = RaumnummerOhnePunkt($Feld);
	}
}
}
$Version = getAktuelleVersion();

if ( isset($_REQUEST['DelVerhinderung']) && is_numeric($_REQUEST['DelVerhinderung']) )
{
  $query = mysql_query('SELECT * FROM T_Vertretungen WHERE F_Verhinderung_id='.$_REQUEST['DelVerhinderung']);
  if ( mysql_num_rows($query)>0)
  {
    echo '<div class="Fehler">Zu dieser Verhinderung gibt es bereits Vertretungen. Sie darf nicht gelöscht werden.</div>';
  }
  else
  {
    mysql_query('DELETE FROM T_Verhinderungen WHERE Verhinderung_id='.$_REQUEST['DelVerhinderung']);
    mysql_query('DELETE FROM T_Vertretung_Liste WHERE F_Verhinderung_id='.$_REQUEST['DelVerhinderung']);
    echo '<div class="Hinweis">Die Verhinderung wurde gelöscht.</div>';
  }
  mysql_free_result($query);
}
if ( ! session_is_registered('LastVon'))
  $_SESSION['LastVon'] = '';
if ( ! session_is_registered('LastBis'))
  $_SESSION['LastBis'] = '';
if ( isset($Art) &&  
     ($_SESSION['LastVon'] != $_REQUEST['Von'] ||
     $_SESSION[$Art] != $_REQUEST[$Art] || $_SESSION['LastBis'] != $_REQUEST['Bis']) )
{
  // Lehrerverhinderung speichern
  $Von = $_REQUEST['Von'];
  $_SESSION['LastVon']= $_REQUEST['Von'];
  $_SESSION['LastBis']= $_REQUEST['Bis'];
  $_SESSION[$Art] = $_REQUEST[$Art];
  $Bis = $_REQUEST['Bis'];
  // Datum umwandeln
  $D1 = explode('.',$Von);
  if ( checkdate($D1[1],$D1[0],$D1[2]))
    $Von = mktime(0,0,0,$D1[1],$D1[0],$D1[2]);
  elseif ( isset($_REQUEST['Von']))
    echo '<div class="Fehler">Kein gültiges Datum für Beginn</div>';
  $D1 = explode('.',$Bis);
  if ( checkdate($D1[1],$D1[0],$D1[2]))
    $Bis = mktime(0,0,0,$D1[1],$D1[0],$D1[2]);
  elseif ( isset($_REQUEST['Bis']))
    echo '<div class="Fehler">Kein gültiges Datum für Ende</div>';
  $Grund = $_REQUEST['Grund'];
  $Aktualisiert = false;
  if ( is_numeric($Grund) && is_numeric($Von) && is_numeric($Bis) )
  {
    if ( $Von > $Bis )
    {
      $Von = $Bis;
      echo '<div class="Fehler">Fehler: Anfangsdatum hinter Enddatum. Anfangsdatum angepasst.</div>';
    }
    $_REQUEST[$Art] = mysql_real_escape_string($_REQUEST[$Art]);
    $_REQUEST['Hinweis'] = trim(str_replace("\r",'',$_REQUEST['Hinweis']));
    if ( isset($_REQUEST['Verhinderung_id']) && is_numeric($_REQUEST['Verhinderung_id']) )
    {
      $sql = 'UPDATE T_Verhinderungen SET Wer="'.$_REQUEST[$Art].'",Art="'.$Art.'",Von='.$Von.','.
             'Bis='.$Bis.',Hinweis="'.mysql_real_escape_string(
             $_REQUEST['Hinweis']).'",Grund='.$Grund.',Bearbeiter="'.
             $_SERVER['REMOTE_USER'].'" ';
      $da = false;
      for ( $i = 1; $i< 7; $i++)
        if ( isset($_REQUEST['UE'.$i]))
          $da = true;
      if ( $da )
      {    
        for ( $i = 1; $i< 7; $i++)
          if ( isset($_REQUEST['UE'.$i]))
          {
            $sql .= ",UE$i=1";
          }
          else
            $sql .= ",UE$i=0";
      }
      else // wenn keine Blöcke angegeben sind gilt es als ganztägig
      {
        for ( $i = 1; $i< 7; $i++)
          $sql .= ",UE$i=1";              
      }
      $sql .= ' WHERE Verhinderung_id='.$_REQUEST['Verhinderung_id'];
      $Insert = $_REQUEST['Verhinderung_id'];
      // Cache aktualisieren
      mysql_query('DELETE FROM T_Vertretung_Liste WHERE F_Verhinderung_id='.
        $_REQUEST['Verhinderung_id']);  
      unset($_REQUEST['Verhinderung_id']);
      $Aktualisiert = true;      
    }
    else
    {
      // Sicherheitsprüfung: Gibt es eine Überschneidung der Verhinderungen?
      $sql = 'SELECT * FROM T_Verhinderungen WHERE Art="'.$Art.'" AND ' .
      		'Wer="'.$_REQUEST[$Art].'" '.
      		'AND ((Von <= '.$Von.' AND Bis >= '.$Von.') OR (Von <= '.$Bis.' AND Bis >= '.$Bis.'))';
      $query = mysql_query($sql);
      if (mysql_num_rows($query) > 0)
      {
        echo '<div class="Fehler">Es existiert bereits eine Verhinderung für ';
        $Lehrer = new Lehrer($_REQUEST[$Art], Lehrer::LEHRERID_KUERZEL);
        echo $Lehrer->Name;
        if ( $Lehrer->Vorname != '' ) echo ', '.$Lehrer->Vorname;
        echo ':<ul>';
        while ( $row = mysql_fetch_array($query))
        {
        	echo "<li><a href=\"{$_SERVER['PHP_SELF']}?Verhinderung_id=" .
        			"{$row["Verhinderung_id"]}#Link{$row["Art"]}\">von ".
        			date('d.m.Y',$row['Von']).' bis '.date('d.m.Y',$row['Bis']).
        	        '</a></li>';
        }
        echo "</ul></div>\n";
        $sql = '';
        $_SESSION['LastVon'] = '';
        $_SESSION['LastBis'] = '';
        $Eintrag['Wer'] = $_REQUEST[$Art];
        $Eintrag['Art'] = $Art;
        $Eintrag['Von'] = $Von;
        $Eintrag['Bis'] = $Bis;
        $Eintrag['Grund'] = $_REQUEST['Grund'];
        $Eintrag['Hinweis'] = $_REQUEST['Hinweis'];
      }
      else
      {
        $sql = 'INSERT INTO T_Verhinderungen (Art, Wer, Von, Bis, Hinweis, Grund,' .
        		'Bearbeiter,UE1,UE2,UE3,UE4,UE5,UE6) VALUES (';
        $sql .= '"'.$Art.'","'.$_REQUEST[$Art].'",'.$Von.','.$Bis.',"'.
           mysql_real_escape_string($_REQUEST['Hinweis']).'",'.$Grund.',';
        $sql .= '"'.$_SERVER['REMOTE_USER'].'"';
        for ( $i= 1; $i<7; $i++)
        {
          $sql .= ',';
          if ( isset($_REQUEST['UE'.$i]) )
          {
            $sql .= 1;
          }
          else
          {
            $sql .= 0;
          }
        }
        $sql .= ')';
        $Insert = -1;
      }
      mysql_free_result($query);
    }
    if ( $sql != '' )
    {
      if ( mysql_query($sql) )
      {
        echo "<div class=\"Hinweis\">Verhinderung für $Art {$_REQUEST[$Art]} " .
        		"wurde eingetragen.</div>";
        if ( $Insert < 0 ) 
        {
        	  $Insert = mysql_insert_id();
        }
        $query = mysql_query('SELECT * FROM T_Verhinderungen WHERE Verhinderung_id='.
          $Insert);
        $eintrag = mysql_fetch_array($query);
        mysql_free_result($query);
      }
      else
        echo '<div class="Fehler">Fehler: '.mysql_error()."</div>\n";
      // ** Mail an Abteilungsleiter schreiben
      $message = 'Verhinderung von ';
      if ( $Art == 'Lehrer')
      {
        $Lehrer = new Lehrer($_REQUEST[$Art], LEHRERID_KUERZEL);            
        $message .= $Lehrer->Anrede($LEHRER_LEHRER, true);
        $Kopf = $Lehrer->Anrede($LEHRER_LEHRER, true);
      }
      else      
      {
        $message .= $_REQUEST[$Art];
        $Kopf = $_REQUEST[$Art];
      }
      $message .= "\n";
      if ( $Aktualisiert ) $message .= "\nACHTUNG: Aktualisierung!\n";
      $message .= 'von '.date('d.m.Y',$Von).' bis '.date('d.m.Y',$Bis)."\n\n";
      $alle = true;
      $anzeige = array();     
      for ( $i = 1; $i < 7; $i++ )
      {
      	if ( isset($_REQUEST['UE'.$i]) )
        { 
    	    $anzeige[] = $i;
      	}
  	    else
  	    {
      	   $alle = false;
  	    } 
      }
      if ( !$alle )
      {
      	$message .= 'Verhinderung gilt nur ';
    	if ( Count($anzeige) == 1)
          $message .= 'in Block ';
  	    else
          $message .= 'in den Blöcken ';
        $message .= implode(',',$anzeige)."\n";
      }
      if ( trim($_REQUEST['Hinweis']) != '' ) 
        $message .= $_REQUEST['Hinweis']."\n";
      $message .= 'Grund: '.$Gruende[$Grund]."\n\n";
      $message .= "Betroffen:\n";
      $Vertretung = "Vertretung planen:\n";      
      $betroffen = berechneStunden($db, $eintrag); 
      if ( Count($betroffen) > 0 )
      {
        // Klassen einzeln mit Datum in die T_Vertretung_Liste speichern
        // Abteilung suchen: wenn Klassen -> Abteilung speichern,
        $dieWoche = getID_Woche($Tag); 

        $HTMLBetroffen = bestimmeBetroffeneAlsHTML($betroffen, false, 
           $eintrag, $Abteilungen);
             // Zeile und Betroffen aufbauen wie in VertretungPlanen
        $sql = 'INSERT INTO T_Vertretung_Liste (F_Verhinderung_id, ' .
           		'Woche, Aenderungen, Betroffen) VALUES ('.
           		$Insert.','.$dieWoche.',"","'.
           		mysql_real_escape_string($HTMLBetroffen).'")';
        if ( ! mysql_query($sql)) 
          echo '<div class="Fehler">Fehler beim Einfügen in Liste: '.$sql.'/'.
            mysql_error().'</div>';
      }
      foreach ( $betroffen as $Tag => $Klassen )
      {
        $message .= $Wochentagnamen[date('w',$Tag)].', '.date('d.m.Y',$Tag).': '.
          implode(',',$Klassen)."\n";  
        $Vertretung  .= date('d.m.Y',$Tag).': https://lehrer.oszimt.org/Vertretung/' .
      		  'VertretungPlanen.php?Verhinderung_id='.$Insert.'&Datum='.$Tag."\n";
      }
      $message .= "\n$Vertretung";
      $message .= "\nVertretungsübersicht: https://lehrer.oszimt.org/Vertretung/VertretungPlanen.php\n\n";
      $message .= 'Erfasst am '.date('d.m.Y H:i').' durch '.$_SERVER['REMOTE_USER']."\n";      
      if ( $Grund != KLASSESONSTIGES && $Grund != KLASSEPRUEFUNG && ! isset($_REQUEST['nomail']))
      {        
        for ( $i=1;$i<Count($Abteilungen->Abteilungen);$i++)
        {
          $empf = $Abteilungen->getAlleEmpfaenger($i);
          foreach ( $empf as $empfaenger ) //
            if ( ! istSelbst($empfaenger))
              mail($empfaenger, '[OSZIMT Verhinderung] '.$Kopf,
               $message, 'From: '.$_SERVER['REMOTE_USER'].'@oszimt.de',
               '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');
        }
        foreach ( array('klockow@oszimt.de', 'broesemann@oszimt.de', 'seidel@oszimt.de') as $empfaenger )
          if ( ! istSelbst($empfaenger))
            mail($empfaenger, '[OSZIMT Verhinderung] '.$Kopf,
                $empfaenger."\n".$message, 'From: '.$_SERVER['REMOTE_USER'].
                "@oszimt.de",
                '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');
      }                    
    }
  }
  else
  {
    $Eintrag['Wer'] = $_REQUEST[$Art];
    $Eintrag['Art'] = $Art;
    $D1 = explode('.',$Von);
    if ( checkdate($D1[1],$D1[0],$D1[2]))
      $Eintrag['Von'] = mktime(0,0,0,$D1[1],$D1[0],$D1[2]);
    $D1 = explode('.',$Bis);
    if ( checkdate($D1[1],$D1[0],$D1[2]))
      $Eintrag['Bis']= mktime(0,0,0,$D1[1],$D1[0],$D1[2]);
    $Eintrag['Grund'] = $_REQUEST['Grund'];
    $Eintrag['Hinweis'] = $_REQUEST['Hinweis'];
  }
}
elseif ( isset($_REQUEST['Verhinderung_id']) && is_numeric($_REQUEST['Verhinderung_id']) )
{
  $query = mysql_query('SELECT * FROM T_Verhinderungen WHERE Verhinderung_id='.
    $_REQUEST['Verhinderung_id']);
  $Eintrag = mysql_fetch_array($query);
  mysql_free_result($query);
  session_unregister('LastVon');
  session_unregister('LastBis');  
}
$LEintrag ='';
$KEintrag = '';
$REintrag = '';
if ( isset($Eintrag['Art']))
{
  if ( $Eintrag['Art'] == 'Lehrer' ) $LEintrag = $Eintrag;
  if ( $Eintrag['Art'] == 'Klasse' ) $KEintrag = $Eintrag;
  if ( $Eintrag['Art'] == 'Raum' ) $REintrag = $Eintrag;
}
// Lehrer anbieten
FormularAnzeigen('Lehrerfehlzeiten', 'Lehrer', $LEintrag,
  'SELECT DISTINCT Kuerzel AS Lehrer, Name, Vorname FROM T_Lehrer WHERE Kuerzel<>"" ORDER BY Name',
   array('Name', 'Vorname'), 
   array(LEHRERKRANK,LEHRERDIENST,LEHRERSONDER,LEHRERFORTBILDUNG));

echo "<h1>Vorhandene Verhinderungen <a class=\"small\" " .
		"href=\"{$_SERVER['PHP_SELF']}?Sort=Stand DESC\">(nach Aktualität)</a>" .
		"</span></h1>\n";
echo "<table class=\"Liste\">\n";
echo '<tr><th>Art</th>';
echo "<th><a href=\"{$_SERVER['PHP_SELF']}?Sort=Art,Wer,Von\">Name</a></th>";
echo "<th><a href=\"{$_SERVER['PHP_SELF']}?Sort=Von,Wer\">Von</a></th>";
echo "<th><a href=\"{$_SERVER['PHP_SELF']}?Sort=Bis,Wer\">Bis</a></th>";
echo "<th>Grund</th><th>Hinweis</th>";
echo "<th></th></tr>\n";

if ( ! session_is_registered("Sort"))
  $_SESSION['Sort'] = 'Art,Wer,Von';
if ( isset($_REQUEST['Sort']))
{
	$_SESSION['Sort'] = mysql_real_escape_string($_REQUEST['Sort']);
}
$query = mysql_query('SELECT * FROM T_Verhinderungen WHERE Bis>='.strtotime('-1 day',time()).
  " ORDER BY {$_SESSION["Sort"]}");
while ( $eintrag = mysql_fetch_array($query) )
{
  echo "<tr>\n";
  echo "<td>{$eintrag['Art']}</td><td>";
  $Lehrer = new Lehrer($eintrag['Wer'],LEHRERID_KUERZEL);
  echo $Lehrer->Name;
  if ( $Lehrer->Vorname != '' ) echo ', '.$Lehrer->Vorname;
  echo '</td>';
  echo '<td>'.date('d.m.Y',$eintrag['Von']).'</td><td>'.date('d.m.Y',$eintrag['Bis'])."</td>\n";
  echo '<td>';
  echo $Gruende[$eintrag['Grund']];
  echo "</td>\n";
  echo '<td>';
  $alle = true;
  $anzeige = array();
  for ( $i = 1; $i < 7; $i++ )
  {
  	if ( $eintrag['UE'.$i] ) 
  	  $anzeige[] = $i;
  	else
  	  $alle = false; 
  }
  if ( !$alle )
  {
  	echo '<div style="font-style:italic">';
  	if ( Count($anzeige) == 1)
  	  echo 'Block: ';
  	else
      echo 'Blöcke: ';
    echo implode(',',$anzeige).'</div>';
  }
  echo nl2br(stripslashes($eintrag['Hinweis']));
  if ( $eintrag['Hinweis'] != '' ) echo '<br />';
  echo '<span class="mini">'.$eintrag['Bearbeiter'].'/'.$eintrag['Stand'].'</small>';
  echo "</td>\n";
  echo '<td><a href="'.$_SERVER['PHP_SELF'].'?Verhinderung_id='.$eintrag['Verhinderung_id'];
  echo '#Link'.$eintrag['Art'].'"';
  echo '>Bearbeiten</a>&nbsp; ';
  $q2 = mysql_query('SELECT Count(*) FROM T_Vertretungen WHERE F_Verhinderung_id='.$eintrag['Verhinderung_id']);
  $anz = mysql_fetch_row($q2);
  mysql_free_result($q2);
  if ( $anz[0] == 0 )
    echo '<a href="'.$_SERVER['PHP_SELF'].'?DelVerhinderung='.$eintrag['Verhinderung_id'].
         '"><img src="http://img.oszimt.de/nav/delete.gif" title="Löschen" alt="rotes Kreuz"/>Löschen</a>';
  //else
  //  echo "&rarr; {$anz[0]} Änderungen";
  echo '</td>';
  echo "</tr>\n";
}
mysql_free_result($query);
echo "</table>\n";
//echo '<p><a href="VertretungPlanen.php">Vertretungen planen</a></p>';

// Klasseneinträge
FormularAnzeigen('Klassenfehlzeiten', 'Klasse', $KEintrag,
  'SELECT DISTINCT Klasse FROM T_StuPla WHERE Version='.$Version.' ORDER BY Klasse',
   array('Klasse'), array(KLASSEWEG, KLASSESONSTIGES, KLASSEPRUEFUNG));

// Raumsperrungen
FormularAnzeigen('Raumsperrungen', 'Raum', $REintrag,
  'SELECT Raumnummer AS Raum, Raumbezeichnung FROM T_Raeume ORDER BY Raumnummer',
   array('Raum', 'Raumbezeichnung'), array(RAUMZUSATZ));

?>
<br/>
</td></tr>
<script language="javascript">
document.forms[0].elements[0].focus();
</script>
<?php
include('include/footer.inc.php');
?>
