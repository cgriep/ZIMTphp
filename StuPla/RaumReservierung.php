<?php
/*
 * RaumReservierung.php
 * Erlaubt das Reservieren eines Raumes
 * Prüft bei der Reservierung, ob Unterricht da wäre und bietet dann die
 * Möglichkeit des Raumwechsels.
 * Prüft vor der Reservierung in jedem Fall, ob der Raum auch frei ist.
 * 
 * Parameter:
 * Entfernen - enthält einen iKey des Raums, dessen Reservierung entfernt werden
 *             soll. Muss bestätigt werden, dann wird SESSION[Entfernen] gesetzt
 * iKey      - iKey des Raumes, dess Reservierung gelöscht werden soll. Fragt 
 *             nach einer Bestätigung und setzt dann Parameter Entfernen
 * Tag
 * (Datum)   - Das Datum, an dem reserviert werden soll. Wenn Tag nicht gesetzt
 *             ist, wird Tag aus dem Parameter Datum gesetzt (timestamp)
 * Grund     - eine Begründung für die Reservierung. Ist Pflichtangabe
 * Stunde    - numerisch, Stunde für die reserviert werden soll.
 * Fach, Raum, Klasse - der Unterricht für den der Raum reserviert werden soll
 *                      bleiben leer wenn Raum ohne Unterrichtsbezug
 * 
 * Letzte Änderung:
 * 06.01.06 C. Griep
 */
$Ueberschrift = '';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/stupla.css">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
$OnLoad = "onLoad=\"javascript:window.resizeTo(750,550)\"";

include('include/header.inc.php');
include('include/Lehrer.class.php');
include('include/helper.inc.php');
include('include/stupla.inc.php');
include('include/Vertretungen.inc.php');
include_once('include/raeume.inc.php');

echo '<tr><td>';
if ( isset($_REQUEST['Entfernen']) && $_REQUEST['Entfernen'] == $_SESSION['Entfernen'])
{
  echo '<h1>Hebe Raumreservierung auf</h1>';
  // Entfernen einer Raumreservierung
  $query = mysql_query('SELECT * FROM T_Vertretungen WHERE iKey="'.$_SESSION['Entfernen'].'"');
  $message = '';
  while ( $eintrag = mysql_fetch_array($query))
  {
    $neuerRaum = $eintrag['Raum']; 
    if ( $eintrag['Art'] == VERTRETUNG_RAUMZUSATZ || $eintrag['Art'] == VERTRETUNG_RAUMWECHSEL )
    {
      if ( !mysql_query('DELETE FROM T_Vertretungen WHERE Vertretung_id='.$eintrag['Vertretung_id']))
        echo '<div class="Fehler">Beim Entfernen ist ein Fehler aufgetreten! '.mysql_error().'</div>';
    }
    else
    {
      // Vertretung mit Raumreservierung
      // Eintrag bleibt erhalten, iKey wird entfernt und Ausgangsraum wird
      // neuer Raum
      if ( ! mysql_query('UPDATE T_Vertretungen SET iKey=NULL, Raum_Neu=Raum '.'WHERE Vertretung_id='.$eintrag['Vertretung_id'])) 
        echo '<div class="Fehler">Beim Aktualisieren ist ein Fehler aufgetreten! '.mysql_error().'</div>';
    }
    echo '<div class="Hinweis">Die Raumreservierung für '.$eintrag['Raum_Neu'].' am '.date('d.m.Y',$eintrag['Datum']).', '.$eintrag['Stunde'].'. Block, wurde aufgehoben. ';
    $message .= "Die Raumreservierung für {$eintrag['Raum_Neu']} ".date('d.m.Y',$eintrag['Datum']).', '.$eintrag['Block'].". Block, wurde aufgehoben.\n";
    if ( $eintrag['Klasse'] != ''&& $neuerRaum != '')
    {
      echo "Der Unterricht von {$eintrag['Klasse']} am ".date('d.m.Y', $eintrag['Datum'])." im {$eintrag['Stunde']} Block " . "findet in Raum $neuerRaum statt.\n";
      $message .= "Der Unterricht von {$eintrag['Klasse']} am ".date('d.m.Y', $eintrag['Datum'])." im {$eintrag['Stunde']} Block " . "findet in Raum $neuerRaum statt.\n";
    }
    $empfaenger = KuerzelToUser($eintrag['Lehrer_Neu']);
    $Raeume[] = $eintrag['Raum_Neu'];
    echo '</div>';
  }
  mysql_free_result($query);
  $message .= "\n";
  if ( strtoupper($empfaenger) != strtoupper($_SERVER['REMOTE_USER']))
    $message .= "Ihrer Raumreservierung konnte leider nicht entsprochen werden.\n";
  $message .= "mit freundlichen Grüßen Ihre WebGroup am OSZIMT\n\n";
  $message .= 'Dies ist eine automatisch am '.date('d.m.Y H:i')." generierte Nachricht. Bei Fragen wenden Sie sich bitte an Kollege Seidel (seidel@oszimt.de).";
  if ( $empfaenger != '' )
    mail($empfaenger.'@oszimt.de', '[OSZIMT-Raumreservierung] Reservierungsaufhebung '.implode(',',$Raeume), $message, 'From: OSZIMT Raumreservierung <noreply@oszimt.de>'."\n".'Bcc: '.$_SERVER['REMOTE_USER'].'@oszimt.de', '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');
  $_REQUEST['iKey'] = $_SESSION['Entfernen'];
  session_unregister('Entfernen');
  echo 'Sie können dieses Fenster nun <a href="javascript:Uebertragen();">schließen</a>.';
  // *** parent-Document aktualisieren 
  echo '<script language="javascript">
  function Uebertragen()
  {
    opener.location.search = "?Raum=';
    echo $_REQUEST["Raum"];
    echo '&ID_Woche='.$Woche;
    echo '";
    this.close();
  }
</script>';
}
elseif ( isset($_REQUEST['iKey']) )
{
  $key = mysql_real_escape_string($_REQUEST['iKey']);
  $query = mysql_query('SELECT * FROM T_Vertretungen WHERE iKey="'.$key.'"');
  if ( mysql_num_rows($query) >= 1 )
  {
    echo 'Wollen Sie folgende Reservierungen tatsächlich löschen:<br />';
    echo '<ul>';
    while ( $eintrag = mysql_fetch_array($query) )
    {
      // Löschen
      echo '<li>';
      echo date('d.m.Y',$eintrag['Datum']).", {$eintrag['Stunde']}. Block ";
      echo "Raum {$eintrag['Raum_Neu']} freigeben ";
      if ( $eintrag['Raum'] != '' )
        echo " und Raum {$eintrag['Raum']} wieder belegen";
      if ( $eintrag['Klasse_Neu'] != '' )
        echo ' für Klasse '.$eintrag['Klasse_Neu']." ({$eintrag['Fach_Neu']})";
      echo "<br />\n";
      $freigabe = 0;
      if ( $eintrag['Datum'] < strtotime('-1 day') )
        $freigabe = 1;
      if ( $eintrag['Raum'] != '' && $eintrag['Raum'] != $eintrag['Raum_Neu'])
      {
        // vor einer Wiederbelegung prüfen ob der Raum noch frei ist!
        // im Stundenplan steht natürlich der Raum als belegt
        // prüfe, ob eine Raumreservierung vorliegt
        $sql = 'SELECT * FROM T_Vertretungen WHERE Raum_Neu="'.$eintrag['Raum'].'" AND Datum='.$eintrag['Datum'].' AND Stunde='.$eintrag['Stunde'];
        $query2 = mysql_query($sql);
        if ( $reservierung = mysql_fetch_array($query2))
          $freigabe = 2;
        mysql_free_result($query2);
      }
      if ( $freigabe == 0)
      {
        $_SESSION['Entfernen'] = $key;
        echo '<a href="'.$_SERVER['PHP_SELF'].'?Entfernen='.$key.'">Reservierung aufheben</a>';
      }
      elseif ( $freigabe == 1 )
        echo '<span class="Fehler">(Diese Reservierung liegt in der Vergangenheit ' . "und kann nicht aufgehoben werden.)</span>\n";
      else
        echo "<span class=\"Fehler\">(Der ursprüngliche Raum {$eintrag['Raum']} " . "ist durch {$reservierung['Lehrer_Neu']} belegt. " . "Die Reservierung kann daher nicht storniert werden)</span>\n";
      echo "</li>\n";
    }
    echo "</ul>\n";
  }
  else
    echo '<div class="Fehler">Der angegebene iKey ist nicht vorhanden!</div>';
  mysql_free_result($query);
  session_unregister($_SESSION['Raum']);
}
 
if ( ! isset($_REQUEST['Tag']) && isset($_REQUEST['Datum']) )
  $_REQUEST['Tag'] = $_REQUEST['Datum'];
if ( isset($_REQUEST['iKey']))
  echo '';
else
{
  session_unregister('Entfernen');
  if ( ! isset($_REQUEST['Save']) && (! isset($_REQUEST['Raum']) || ! isset($_REQUEST['Stunde']) || ! isset($_REQUEST['Tag']) || ! is_numeric($_REQUEST['Stunde']) || ! is_numeric($_REQUEST['Tag'])))
  {
    echo '<div class="Fehler">Es wurden falsche Parameter übergeben!</div>';
  }
  elseif ( ((!isset($_REQUEST['Save']) || trim($_REQUEST['Grund']) == '') || strlen(trim($_REQUEST['Grund'])) < 10 ))
  {
    session_unregister('Raum');
    if ( isset($_REQUEST['Grund']) )
      echo '<div class="Fehler">Fehler: Bitte einen ausführlichen Grund angegeben!</div>'."\n";
    $Lehrer = new Lehrer($_SERVER['REMOTE_USER'], LEHRERID_EMAIL);
    $Raum = $_REQUEST['Raum'];
    $Stunde = $_REQUEST['Stunde'];
    $Datum = $_REQUEST['Tag'];
    $ID_Woche = getID_Woche($Datum); //$_REQUEST['ID_Woche'];
    echo '<h1>Raumreservierung für '.$Raum.' am '.date('d.m.Y',$Datum) . ' im '.$Stunde.'. Block durch '.$Lehrer->Vorname.' '.$Lehrer->Name.' (' . $Lehrer->Username.')</h1>';
    $LehrerPlan = liesPlanEin($db, 'Lehrer', $Lehrer->Kuerzel, $ID_Woche, false);
    $RaumPlan = liesPlanEin($db, 'Raum', $Raum, $ID_Woche, false);
    $Enddatum = strtotime('+5 week +2 day');
    if ( $Datum > $Enddatum )
    {
      // Langfristige Reservierung
      if ($query = mysql_query('SELECT * FROM T_Raeume WHERE Raumnummer="' . Raumbezeichnung($Raum).'"'))
      {
        if ( $raum = mysql_fetch_array($query))
        {
          if ( $raum['Langfristig'] )
            $Enddatum = strtotime('+6 month +2week');
        }
      }
      mysql_free_result($query);
    }
    if ( isset($RaumPlan[date('w',$Datum)][$Stunde]) && Count($RaumPlan[date('w',$Datum)][$Stunde])>0 )
    {
      echo "<div class=\"Fehler\">Raum $Raum ist bereits belegt: ";
      foreach ($RaumPlan[date('w',$Datum)][$Stunde] as $turnus => $eintraege )
        echo implode(',',$eintraege['Lehrer']);
      echo "</div>\n";
      echo '<a href="javascript:window.close();">Zurück</a> zum Raumplan.';
    }
    elseif ( istVerhindert($Datum, $Raum, $Stunde, 'Raum') || istGesperrt($Datum, $Raum, $Stunde) )
    {
      echo '<div class="Fehler">Zu diesem Zeitpunkt liegt eine Sperrung vor. ' . 'Bitte ggf. Rücksprache mit Koll. Seidel halten.</div>';
      echo '<a href="javascript:window.close();">Zurück</a> zum Raumplan.';
    }
    elseif ( $Datum > $Enddatum )
    {
      echo '<div class="Fehler">Der Zeitraum liegt zu weit in der Zukunft. Sie ' . 'können diesen Raum heute nur bis zum '.date('d.m.Y',$Enddatum) . ' reservieren.</div>';
      echo '<a href="javascript:window.close();">Zurück</a> zum Raumplan.';
    }
    elseif ( !darfReservieren($Raum))
    {
      echo '<div class="Fehler">Sie dürfen diesen Raum nicht online reservieren. ' . 'Bitte wenden Sie sich an Koll. Seidel.</div>';
      echo '<a href="javascript:window.close();">Zurück</a> zum Raumplan.';
    }
    else
    {
      $Klassen = array();
      $user = strtoupper($_SERVER['REMOTE_USER']);
      if ( isset($LehrerPlan[date('w',$Datum)][$Stunde]) && Count($LehrerPlan[date('w',$Datum)][$Stunde]) > 0 )
      {
        // Lehrer hat Unterricht
        foreach ($LehrerPlan[date('w',$Datum)][$Stunde] as $turnus => $eintraege )
        {
          echo '<form action="'.$_SERVER["PHP_SELF"].'" name="Form1" method="post">'."\n";
          echo '<input type="hidden" name="Stunde" value="'.$Stunde.'" />'."\n";
          echo '<input type="hidden" name="Datum" value="'.$Datum.'" />'."\n";
          echo '<input type="hidden" name="Raum" value="'.$Raum.'" />'."\n";
          echo '<input type="hidden" name="Fach" value="'.implode(",",$eintraege["Fach"]).'" />'."\n";
          echo '<h2>Für Klasse ';
          echo implode(',',$eintraege['Klasse']);
          echo ' ('.implode(',',$eintraege['Fach']).')';
          echo "</h2>\n";
          $Klassen = array_merge($Klassen, $eintraege['Klasse']);
          echo '<input type="hidden" name="Klasse" value="';
          echo implode(',',$eintraege['Klasse']);
          echo '" />'."\n";
          if ( $user == 'SEIDEL' || $user == 'GRIEP')
            echo 'für Lehrer <input type="text" name="Lehrer" value="'.$Lehrer->Kuerzel . '" size="5" maxlength="5"/>'."<br/>\n";
          else
            echo '<input type="hidden" name="Lehrer" value="'.$Lehrer->Kuerzel.'" />'."\n";
          $da = false;
          foreach ( $eintraege['Raum'] as $derRaum )
          {
            echo '<input type="Checkbox" name="Frei[]" value="';
            echo $derRaum;
            echo '" checked="checked" /> Raum '.$derRaum. "\n";
            if ( $da )
	      echo " / ";
            $da = true;
          }
          echo 'freigeben';
          echo "<br />Grund der Reservierung/Hinweis für den Stundenplan:<br />\n";
          echo '<textarea name="Grund" cols="60" rows="5"></textarea>';
          echo ' <input type="Submit" name="Save" value="Reservieren" />'."\n";
          echo "</form>\n";
          echo "<hr />\n";
        }
      }
      echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
      echo "<h2>Zusätzlicher Raumbedarf ";
      if ( Count($Klassen) > 1 )
        echo 'nicht für die Klassen '.implode(',',$Klassen);
      elseif ( Count($Klassen) > 0 )
        echo 'nicht für die Klasse '.implode(',',$Klassen);
      echo "</h2>\nGrund der Reservierung:<br />\n";
      echo '<textarea name="Grund" cols="60" rows="5"></textarea>';
      if ( $user == "SEIDEL" || $user == "GRIEP")
        echo ' für Lehrer <input type="text" name="Lehrer" value="'.$Lehrer->Kuerzel . '" size="5" maxlength="5" />'."<br/>\n";
      else
        echo '<input type="hidden" name="Lehrer" value="'.$Lehrer->Kuerzel.'" />'."\n";
      echo '<input type="hidden" name="Stunde" value="'.$Stunde.'" />'."\n";
      echo '<input type="hidden" name="Datum" value="'.$Datum.'" />'."\n";
      echo '<input type="hidden" name="Raum" value="'.$Raum.'" />'."\n";
      echo ' <input type="Submit" name="Save" value="Reservieren" />'."\n";
      echo "</form>\n";
      echo '<script language="javascript">document.Form1.Grund.focus();</script>';
    }
  }
  elseif ( isset($_REQUEST['Datum']) && isset($_REQUEST['Lehrer']) && isset($_REQUEST['Raum']) && isset($_REQUEST['Stunde']) && is_numeric($_REQUEST['Stunde']) && $_SESSION['Raum'] != $_REQUEST['Raum'].$_REQUEST['Datum'].$_REQUEST['Stunde'])
  {  // Reservierung sichern
    $_SESSION['Raum'] = $_REQUEST['Raum'].$_REQUEST['Datum'].$_REQUEST['Stunde'];
    $Klasse = $_REQUEST['Klasse'];
    $Lehrer = $_REQUEST['Lehrer'];
    if ( $Lehrer == '' ) $Lehrer = $_SERVER['REMOTE_USER'];
    $Raum = $_REQUEST['Raum'];
    $Fach = $_REQUEST['Fach'];
    $Stunde = $_REQUEST['Stunde'];
    $Datum = $_REQUEST['Datum'];
    $Hinweis = $_REQUEST['Grund'];
    $Woche = getID_Woche($_REQUEST['Datum']);
//  if ( $Klasse == '' )
//    $KlasseN = '(Zusatz)';
//  else
    $KlasseN = $Klasse;
    mt_srand;
    do//Doppelte IDs erkennen
    {
      $doppelt = false;
      $ID_Res = makePwd(20);
      $sql = "SELECT iKey FROM T_Vertretungen WHERE iKey = \"$ID_Res\"";
      if ( ! $rs = mysql_query($sql))
        echo mysql_error();
      if (mysql_num_rows($rs) != 0)
        $doppelt = true;
      }
    while($doppelt);

    $Lehrer = ohneStern($Lehrer);
    $Raum = ohneStern($Raum);
    $Klasse = ohneStern($Klasse);
    $Faecher = array();
    $dieFaecher = explode(',',$Fach);
    foreach ( $dieFaecher as $f)
    {
      $Faecher[] = ohneStern($f);
    }
    if ( isset($_REQUEST['Frei']) && is_array($_REQUEST['Frei']) )
    {
      //$Hinweis = 'Raumwechsel';
      $Art = VERTRETUNG_RAUMWECHSEL;
      $Grund = RAUMWECHSEL;
      foreach ( $_REQUEST['Frei'] as $key => $derRaum )
      {
        echo '<h1>Ändere Raum '.$derRaum.' auf '.$Raum."</h1>\n";
        $derRaum = ohneStern($derRaum);
        // Fach feststellen
        if ( isset($Faecher[$key]))
          $Fach = $Faecher[$key];
        else
          $Fach = $Faecher[0];
        echo 'für Klasse '.$Klasse.' in Fach '.$Fach.'<br />';
        trageVertretungEin('Raum', $derRaum, $Datum, $Stunde, array('Raum'=>$Raum, 'Lehrer'=>$Lehrer, 'Klasse'=>$Klasse,'Fach'=>$Fach), $Grund, $Hinweis, -1, $Art, $ID_Res);
      }
    }
    else
    {
      $RaumArray = array();
      //$Hinweis = 'Raumreservierung';
      $Art = VERTRETUNG_RAUMZUSATZ;
      $Grund = RAUMZUSATZ;
      echo '<h1>Reserviere Zusatzraum '.$Raum.' für ';
      if ( $Klasse != '' )
        echo 'Klasse '.$Klasse.', ';
      echo 'Lehrer '.$Lehrer;
      if ( $Fach != '' )
        echo ' im Fach '.$Fach;
      echo "</h1>\n";                 
      foreach ( $Faecher as $f )
        trageVertretungEin('Raum', $Raum, $Datum, $Stunde, array('Raum'=>$Raum, 'Lehrer'=>$Lehrer,'Klasse'=>$Klasse, 'Fach'=>$f), $Grund, $Hinweis, -1, $Art, $ID_Res);
    }
    echo '<div class="Hinweis">Die Raumreservierung am '.date('d.m.Y',$Datum) . ' im '.$Stunde.'.Block für '.$Raum.' wurde eingetragen.</div>';
    $message = 'Der Raum '.$Raum.' wurde am '.date('d.m.Y',$Datum).' im '.$Stunde.'. Block ';
    if ( $Klasse != '' )
      $message .= 'für Klasse '.$Klasse.' (Fach '.$Fach.') ';
    $message .= "reserviert.\n";
    if ( $Klasse != '' )
      $message .= "Die Reservierung wird im Klassenstundenplan automatisch ausgewiesen.\n";
    $message .= 'Ihr Hinweis: '.stripslashes($Hinweis)."\n";  
    if ( isset($_REQUEST['Frei']) && is_array($_REQUEST['Frei']) )
      $message .= 'Freigegeben wurde(n): '.implode(',',$_REQUEST['Frei'])."\n";
    $message .= "Der iKey lautet: $ID_Res\n\nSollten Sie keine Rückmeldung erhalten, können Sie davon ausgehen, dass der Raum Ihnen zur Verfügung steht.";
    $message .= "\n\nÜber folgenden Link können Sie die Reservierung rückgängig machen:";
    $message .= "\nhttps://lehrer.oszimt.org/StuPla/RaumReservierung.php?iKey=$ID_Res";
    $message .= "\nDie Liste Ihrer aktuellen Reservierungen können Sie auch im internen Bereich einsehen.";
    $message .= "\n\nMit freundlichen Grüßen\nWebGroup am OSZ IMT\n\n";
    $message .= "Dies ist eine automatisch am ".date("d.m.Y \u\m H:i")." Uhr generierte Nachricht.\nBei Fragen wenden Sie sich bitte an Kollege Seidel (seidel@oszimt.de).";
    mail($_SERVER['REMOTE_USER'].'@oszimt.de', "[OSZIMT-Raumreservierung] Reservierung $Raum", $message, "From: OSZIMT Raumreservierung <noreply@oszimt.de>", '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');
    echo nl2br($message);
    echo "<br />Sie erhalten diese Information auch per E-Mail.<br /><br />\n";
    echo 'Sie können dieses Fenster nun <a href="javascript:Uebertragen();">schließen</a>.';
    // *** parent-Document aktualisieren 
    echo "\n<script language=\"javascript\">";
    echo "\n\tfunction Uebertragen()\n\t{";
    echo "\n\t\topener.location.search = \"?Raum=";
    echo $_REQUEST['Raum'];
    echo '&ID_Woche='.$Woche;
    echo "\"\n\t\tthis.close();\n\t}\n</script>\n";
  }
  elseif (isset($_REQUEST['Raum']) && $_SESSION['Raum'] == $_REQUEST['Raum'].$_REQUEST['Datum'].$_REQUEST['Stunde'])
  {
    echo "<div class=\"Hinweis\">Diese Raumreservierung wurde bereits durchgeführt!</div>\n";
  }
  else
    echo '<div class="Fehler">Unbekannte Parameter</div>';
}
//schreibePlan($LehrerPlan, $db);
//schreibePlan($RaumPlan, $db);
// Schaltet Navigationsmenü ab
$_REQUEST['Print'] = 1;

echo '</td></tr>';
include('include/footer.inc.php');
?>