<?php
/*
 * Problemmeldung.php
 * Dient der Meldung eines Problems mit einem Inventar.
 * Es werden nur die Inventare des gewählten Raumes angezeigt.
 * Der Benutzer wird durch die möglichen Geräte geführt und muss einen Hinweis
 * zum Fehler eingeben.
 * Danach wird automatisch eine Mail an die Techniker gesendet und das Problem
 * zum passenden Inventar abgespeichert.
 * (c) 2006 Christoph Griep
 * 
 */
DEFINE('USE_KALENDER',1);
$Ueberschrift = 'Problem mit der Computerhardware melden';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');

if ( isset($_REQUEST['Save']) && is_numeric($_REQUEST['F_Inventar_id']) && 
           $_REQUEST['Secure'] == 'x' && trim($_REQUEST['Bemerkungen'])!= '')
{
  $grund = 'Problemmeldung von ';
  include_once('include/Lehrer.class.php');
  $L = new Lehrer($_SERVER['REMOTE_USER'],LEHRERID_EMAIL);
  $grund .= $L->Anrede($LEHRER_HERRFRAU, false);
  $bemerkung = $_REQUEST['Bemerkungen']."\nGemeldet durch ".$_SERVER['REMOTE_USER'];
  if ( $_REQUEST['F_Art_id'] == 2 )
  {
    // Monitor!!
    $bemerkung = "Achtung: Fehlermeldung betrifft den Monitor!\n\n".$bemerkung;
  }
  $query = mysql_query('SELECT * FROM (T_Inventar INNER JOIN T_Inventararten '.
        'ON F_Art_id=Art_id) INNER JOIN T_Raeume ON F_Raum_id=Raum_id WHERE Inventar_id='.
        $_REQUEST['F_Inventar_id']);
  $artikel = mysql_fetch_array($query);
  mysql_free_result($query);
  mail($artikel['Meldungmail'],'[OSZIMT Hardware-Fehlermeldung]', 'Am '.date('d.m.Y H:i').
    " wurde folgende Problemmeldung ins System gespeichert:\n\n".$bemerkung.
    "\n\n Betroffenes Inventar: ".$artikel['Bezeichnung']." (".$artikel['Art'].'), Nr. '.
    $artikel['Inventar_Nr'].' in Raum '.$artikel['Raumnummer'].
    "\n\nUm zur Inventarseite zu gelangen, bitte diesem Link folgen:\n".
    "https://lehrer.oszimt.org/Inventar/Inventar.php?id=".$_REQUEST["F_Inventar_id"]."\n\n".
    "Dies ist eine automatisch generierte Nachricht.",
    "From: ".$_SERVER["REMOTE_USER"]."@oszimt.de",
    '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');
  mysql_query("INSERT INTO T_Reparaturen (Grund,Bemerkung,Datum,F_Status_id,F_Inventar_id) ".
    "VALUES ('".$grund."','".mysql_real_escape_string($bemerkung)."',".time().
        ",1,".$_REQUEST["F_Inventar_id"].")");
  unset($_REQUEST["Save"]);
  unset($_REQUEST["F_Art_id"]);
  unset($_REQUEST["F_Inventar_id"]);
  unset($_REQUEST["F_Raum_id"]);
  echo '<tr><td><div class="Hinweis">Ihre Meldung wurde an die Techniker versandt.';
  echo 'Das Problem wird schnellstmöglich bearbeitet werden.</div>';
  echo "</td></tr>\n";
}
elseif (isset($_REQUEST['Save']) && is_numeric($_REQUEST['F_Inventar_id']) && 
        ! isset($_REQUEST['Secure']))
{
	echo '<tr><td><div class="Fehler">';
	echo 'Sie müssen die Korrektheit Ihrer Angaben durch Anklicken des Kästchens bestätigen!</div></td></tr>';
}
elseif ( isset($_REQUEST['Save']) && is_numeric($_REQUEST['F_Inventar_id']) )
{
	echo '<tr><td><div style="background-color: red; color:white; font-weight:bold;">';
	echo "Sie müssen einen Hinweis zum Fehler geben! Woran haben Sie den Fehler erkannt?</div></td></tr>\n";
}

  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="Inv">';
  if ( isset($inventar) && $inventar['Inventar_id'] > 0)
  {
    echo '<input type="hidden" name="ip[Inventar_id]" value="'.$inventar['Inventar_id'];
    echo '" />';
  }
  echo "<tr><td>\n";
  echo "Hier können Sie Probleme mit der Computerhardware am OSZ IMT direkt an die Techniker melden. ";
  echo "Durch Ihre Mithilfe kann so gewährleistet werden, dass Probleme möglichst schnell ";
  echo "behoben werden können.";
  echo "</td></tr>\n";
  echo '<tr><td><table width="100%">';
  echo '<tr><td>Standort</td><td>';
  if ( ! isset($_REQUEST['F_Raum_id']) || ! is_numeric($_REQUEST['F_Raum_id']) )
  {
    echo '<select name="F_Raum_id">';
    $query = mysql_query("SELECT Raum_id, Raumnummer, Raumbezeichnung FROM T_Raeume ORDER BY Raumnummer");
    while ( $raum = mysql_fetch_array($query) )
    {
      echo '<option value="'.$raum['Raum_id'].'"';
      echo '>'.stripslashes($raum['Raumnummer']).' ('.stripslashes($raum['Raumbezeichnung']);
      echo ")</option>\n";
    }
    echo "</select>\n";
  }
  else
  {
    echo '<input type="hidden" name="F_Raum_id" value="'.$_REQUEST["F_Raum_id"].'" />';
    $query = mysql_query("SELECT Raumnummer, Raumbezeichnung FROM T_Raeume WHERE Raum_id=".
      mysql_real_escape_string($_REQUEST["F_Raum_id"]));
    if ( ! $raum = mysql_fetch_array($query) )
      echo 'Falsche Raum-id';
    else
      echo stripslashes($raum['Raumnummer']).' ('.stripslashes($raum['Raumbezeichnung']).')';
  }
  mysql_free_result($query);
  echo "</td></tr>\n";
  $GeraeteDa = false;
  if ( isset($_REQUEST['F_Raum_id']) && is_numeric($_REQUEST['F_Raum_id']) )
  {    
    echo '<tr><td>Art des Gerätes</td><td>';
    if ( !isset($_REQUEST['F_Art_id']) || ! is_numeric($_REQUEST['F_Art_id']) )
    {
      $query = mysql_query('SELECT DISTINCT Art, Art_id FROM T_Inventar INNER JOIN T_Inventararten '.
        'ON F_Art_id=Art_id WHERE F_Raum_id='.$_REQUEST['F_Raum_id'].
        ' AND Meldungmail<>"" ORDER BY Art');
      if ( mysql_num_rows($query) > 0 )
      {
        echo '<select name="F_Art_id">';
        while ( $Arten = mysql_fetch_array($query) )
        {
          echo '<option value="'.$Arten['Art_id'];
          echo '">'.$Arten['Art'].'</option>';
        }
        echo '</select>';
        $GeraeteDa = true;
      }
      else
      {
        echo '- Keine Geräte in diesem Raum -';
      }
    }
    else
    {
      $query = mysql_query('SELECT Art FROM T_Inventararten WHERE Art_id='.$_REQUEST['F_Art_id']);
      if ( $art = mysql_fetch_array($query) )
      {
        echo '<input type="hidden" name="F_Art_id" value="'.$_REQUEST['F_Art_id'].'" />';
        echo stripslashes($art['Art']);
        $GeraeteDa = true;
      }
      else
        echo 'Falsche Art-Id';
    }
    mysql_free_result($query);
  }
  echo "</td></tr>\n";
  // Material anzeigen
  if ( isset($_REQUEST['F_Art_id']) && is_numeric($_REQUEST['F_Art_id']) 
       && is_numeric($_REQUEST['F_Raum_id']) )
  {
    echo '<tr><td valign="top">Gerät/Platz</td><td valign="top">';
    if ( isset($_REQUEST['F_Inventar_id']) && is_numeric($_REQUEST['F_Inventar_id']) )
    {
      $query = mysql_query('SELECT * FROM T_Inventar WHERE Inventar_id='.$_REQUEST['F_Inventar_id']);
    }
    elseif ( $_REQUEST['F_Art_id'] == 2 )
    {
      // Sonderfall: Monitor - Rechner-Nummern anzeigen
      $query = mysql_query('SELECT * FROM T_Inventar WHERE F_Art_id=1 AND F_Raum_id='.$_REQUEST['F_Raum_id']);
    }
    else
    {
      $query = mysql_query('SELECT * FROM T_Inventar WHERE F_Art_id='.$_REQUEST['F_Art_id'].
        ' AND F_Raum_id='.$_REQUEST['F_Raum_id']);
    }
    if ( mysql_num_rows($query) == 0 )
    {
      echo 'Keine Geräte vorhanden!';
      $GeraeteDa = false;
    }
    elseif ( mysql_num_rows($query) == 1 )
    {
      $geraet = mysql_fetch_array($query);
      if ( $_REQUEST['F_Art_id'] != 2 )
        echo stripslashes($geraet['Bezeichnung']);
      if ( $geraet['Inventar_Nr'] != '' )
        echo ' ('.stripslashes($geraet['Inventar_Nr']).')';
      echo '<input type="hidden" name="F_Inventar_id" value="'.$geraet['Inventar_id'].'" />';
      $_REQUEST["F_Inventar_id"] = $geraet["Inventar_id"];
      echo "</td></tr><tr><td>\n";
      echo '<input type="checkbox" name="Secure" value="x"/> (Sicherheitscheck!)</td><td>';
      echo ' es ist wirklich <strong>'.$geraet['Bezeichnung'].' ';
      if ( $geraet['Inventar_Nr'] != '' )
        echo ' ('.stripslashes($geraet['Inventar_Nr']).')';
      echo '</strong> defekt, kein anderes! Die Meldung';
      echo ' kann nur bearbeitet werden, wenn das richtige Gerät ausgewählt ist.';
      echo ' Sollte das Gerät in der Liste fehlen, bitte keine Meldung absetzen ';
      echo ' sondern die Techniker direkt informieren!';      
    }
    else
    {
      echo '<select name="F_Inventar_id">';
      while ( $geraet = mysql_fetch_array($query) )
      {
        echo '<option value="'.$geraet["Inventar_id"].'">';
        // Bei Monitoren wird der Name des Rechners angeben!
        if ( $_REQUEST["F_Art_id"] != 2 )
          echo stripslashes($geraet["Bezeichnung"]);
        if ( $geraet["Inventar_Nr"] != "" )
          echo ' ('.stripslashes($geraet["Inventar_Nr"]).')';
        echo "</option>\n";
      }
      echo "</select>\n";
      switch ( $_REQUEST["F_Art_id"] )
      {
        case 1: // Computer
        case 2: // Monitore
          echo '<img align="right" border="0" width="300px" src="Rechnerlabel.jpg" />';
          break;
        /*
        case 2: // Monitor
          echo '<img align="right" border="0" width="300px" src="Monitor.jpg" />';
          break;
        case 8: // Drucker
          echo '<img align="right" border="0" width="300px" src="Drucker.jpg" />';
          break;
        case 9: // Projektor
          echo '<img align="right" border="0" width="300px" src="Beamer.jpg" />';
          break;
        */
      }
    }
    mysql_free_result($query);
    echo "</td></tr>\n";
  }
  if ( isset($_REQUEST['F_Inventar_id']) && is_numeric($_REQUEST['F_Inventar_id']))
  {
    // Bereits vorhandene Meldungen anzeigen (Status 1=gemeldet)
    // TODO: Monitore??
    $query = mysql_query('SELECT * FROM T_Reparaturen WHERE F_Inventar_id='.
      $_REQUEST['F_Inventar_id'].' AND F_Status_id=1 ORDER BY Datum');
    if ( mysql_num_rows($query) > 0 )
    {
      // Reparaturen vorhanden!
      echo '<tr><td><strong>Folgende Problemmeldungen liegen bereits vor:</strong></td></tr>';
      while ( $meldung = mysql_fetch_array($query) )
      {
        echo '<tr><td colspan="2">';
        echo date("d.m.Y",$meldung["Datum"]).": ".stripslashes($meldung["Grund"])."<br />";
        echo nl2br(stripslashes($meldung["Bemerkung"]));
        echo '</td></tr>';
      }
      echo '<tr><td colspan="2"><hr /></td></tr>';
    }
    mysql_free_result($query);
    echo '<tr><td>';
    echo 'Hinweise zum Fehler</td><td>';
    echo '<textarea name="Bemerkungen" cols="60" rows="5">';
    if ( isset($_REQUEST['Bemerkungen']))
      echo $_REQUEST['Bemerkungen'];
    echo '</textarea>';
    echo "</td></tr>\n";
  }
  echo '<tr><td>';
  if ( isset($_REQUEST["F_Art_id"]) && is_numeric($_REQUEST["F_Art_id"]) 
       && is_numeric($_REQUEST["F_Raum_id"]) 
       && isset($_REQUEST["F_Inventar_id"]) 
       && is_numeric($_REQUEST["F_Inventar_id"]) )
  {
    echo '<input type="Submit" name="Save" value="Meldung absenden" />';
  }
  elseif ( $GeraeteDa || ! isset($_REQUEST["F_Art_id"]) )
    echo '<input type="Submit" value="Weiter" />';
  echo "</td></tr></table>\n";
  echo "</form>\n";
  if ( isset($_REQUEST["F_Raum_id"]) )
    echo '[ <a href="'.$_SERVER["PHP_SELF"].'">Neue Meldung beginnen</a> ]';
  echo "</td></tr>\n";
  echo '<tr><td align="center"><a href="/">Zurück zum internen Bereich</a></td></tr>';
include("include/footer.inc.php");
?>