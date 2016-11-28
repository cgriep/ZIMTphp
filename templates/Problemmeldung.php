<?php
/*
 * Created on 30.01.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require 'Smarty/Smarty.class.php';
include_once("include/config.php");
include_once("include/Logins.inc.php");

$Wochentagnamen[0] = "Sonntag";
$Wochentagnamen[1] = "Montag";
$Wochentagnamen[2] = "Dienstag";
$Wochentagnamen[3] = "Mittwoch";
$Wochentagnamen[4] = "Donnerstag";
$Wochentagnamen[5] = "Freitag";
$Wochentagnamen[6] = "Samstag";

class OSZIMTSmarty extends Smarty {
	function OSZIMTSmarty()
	{
      session_start();
	  parent::Smarty();
      // Login festhalten
	  SaveLogin();
      // Footer bereitstellen
      if ( ! isset($_REQUEST["Print"]))
      {
        // Header: Login
        $this->Assign("getLastLogin", getLastLogin());	
 
        $PrintLink = $_SERVER["PHP_SELF"];
        $pre = "?";
        foreach ( $_REQUEST as $key => $value )
        {
          if ( is_array($value) )
            foreach ( $value as $kkey => $vvalue )
            {
              $PrintLink .= $pre.urlencode($kkey).'='.urlencode($vvalue);
              $pre = '&';
            }
          else
            $PrintLink .= $pre.urlencode($key).'='.urlencode($value);
          $pre = '&';
        }
        $PrintLink .= $pre."Print=1";
        $this->Assign("PrintLink",$PrintLink); 
      }
      $this->Assign("LastChange", date ("d.m.Y H:i", filemtime(basename($_SERVER["PHP_SELF"]))));
      // rechtsseitiges Navigationsmenü anzeigen.
      // Navigationsmenü ist in der Datei menu.txt enthalten
      // (Format: Text;Linkname)
      // Parameter Print zeigt an, dass das Menü nicht angezeigt werden soll
      if ( is_file("menu.txt") && ! isset($_REQUEST["Print"]) )
      {
        $menue = array();
        $file = fopen("menu.txt", "r");
        while ( $zeile = fgets($file) )
        {
          if ( trim($zeile) != "" )
          {
            $z = explode(";", $zeile);
            $menue["Link"] = $z[1];
            $menue["Titel"] = $z[0];
          }
        }
        fclose($file);
        $this->Assign("NavMenu", $menue);
      } // Menü anzeigen
      
	} // Konstruktor
	  
}

$smarty = new OSZIMTSmarty();
$smarty->compile_check = true;
$smarty->debugging = true;

/* ---- Ab hier spezifischer Quellcode */

$smarty->Assign("USE_KALENDER",true);
$smarty->Assign("Ueberschrift", "Problem mit der Computerhardware melden");
$smarty->Assign("HeaderZusatz", '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">');

if ( isset($_REQUEST["Save"]) && is_numeric($_REQUEST["F_Inventar_id"]) && 
           $_REQUEST["Secure"] == "x" && trim($_REQUEST["Bemerkungen"])!= "")
{
  $grund = "Problemmeldung von ".$_SERVER["REMOTE_USER"];
  $bemerkung = $_REQUEST["Bemerkungen"]."\nGemeldet durch ".$_SERVER["REMOTE_USER"];
  if ( $_REQUEST["F_Art_id"] == 2 )
  {
    // Monitor!!
    $bemerkung = "Achtung: Fehlermeldung betrifft den Monitor!\n\n".$bemerkung;
  }
  $query = mysql_query("SELECT * FROM (T_Inventar INNER JOIN T_Inventararten ".
        "ON F_Art_id=Art_id) INNER JOIN T_Raeume ON F_Raum_id=Raum_id WHERE Inventar_id=".
        $_REQUEST["F_Inventar_id"]);
  $artikel = mysql_fetch_array($query);
  mysql_free_result($query);
  mail($artikel["Meldungmail"],"[OSZIMT Hardware-Fehlermeldung]", "Am ".date("d.m.Y H:i").
    " wurde folgende Problemmeldung ins System gespeichert:\n\n".$bemerkung.
    "\n\n Betroffenes Inventar: ".$artikel["Bezeichnung"]." (".$artikel["Art"]."), Nr. ".
    $artikel["Inventar_Nr"]." in Raum ".$artikel["Raumnummer"].
    "\n\nUm zur Inventarseite zu gelangen, bitte diesem Link folgen:\n".
    "https://lehrer.oszimt.de/Inventar/Inventar.php?id=".$_REQUEST["F_Inventar_id"]."\n\n".
    "Dies ist eine automatisch generierte Nachricht.",
    "From: ".$_SERVER["REMOTE_USER"]."@oszimt.de\nBcc: griep@oszimt.de");
  mysql_query("INSERT INTO T_Reparaturen (Grund,Bemerkung,Datum,F_Status_id,F_Inventar_id) ".
    "VALUES ('".$grund."','".mysql_real_escape_string($bemerkung)."',".time().
        ",1,".$_REQUEST["F_Inventar_id"].")");
  unset($_REQUEST["Save"]);
  unset($_REQUEST["F_Art_id"]);
  unset($_REQUEST["F_Inventar_id"]);
  unset($_REQUEST["F_Raum_id"]);
  $smarty->Assign("Versandt", true); 
}
elseif (isset($_REQUEST["Save"]) && is_numeric($_REQUEST["F_Inventar_id"]) && 
        ! isset($_REQUEST["Secure"]))
{
	$smarty->Assign("Meldung", 
	'Sie müssen die Korrektheit Ihrer Angaben durch Anklicken des Kästchens bestätigen!');
}
elseif ( isset($_REQUEST["Save"]) && is_numeric($_REQUEST["F_Inventar_id"]) )
{
	$smarty->Assign("Meldung", 
	'Sie müssen einen Hinweis zum Fehler geben! Woran haben Sie den Fehler erkannt?');
}


$smarty->Assign("inventar", $inventar);
if ( ! isset($_REQUEST["F_Raum_id"]) || ! is_numeric($_REQUEST["F_Raum_id"]) )
{
  $query = mysql_query("SELECT Raum_id, Raumnummer, Raumbezeichnung FROM T_Raeume ORDER BY Raumnummer");
  while ( $raum = mysql_fetch_array($query) )
  {
  	$smarty->Append("Raeumeid", $raum["Raum_id"]);
  	$smarty->Append("RaeumeRaum", stripslashes($raum["Raumnummer"])." (".
  	  stripslashes($raum["Raumbezeichnung"].")"));
  }
}
else
{
    $smarty->Assign("F_Raum_id", $_REQUEST["F_Raum_id"]);
    $query = mysql_query("SELECT Raumnummer, Raumbezeichnung FROM T_Raeume WHERE Raum_id=".
      mysql_real_escape_string($_REQUEST["F_Raum_id"]));
    if ( ! $raum = mysql_fetch_array($query) )
      echo 'Falsche Raum-id';
    else
      $smarty->Assign("Standort", stripslashes($raum["Raumnummer"])." (".
        stripslashes($raum["Raumbezeichnung"]).")");
}
mysql_free_result($query);
if ( isset($_REQUEST["F_Raum_id"]) && is_numeric($_REQUEST["F_Raum_id"]) )
{
  if ( !isset($_REQUEST["F_Art_id"]) || ! is_numeric($_REQUEST["F_Art_id"]) )
  {
    $query = mysql_query("SELECT DISTINCT Art, Art_id FROM T_Inventar INNER JOIN T_Inventararten ".
      "ON F_Art_id=Art_id WHERE F_Raum_id=".$_REQUEST["F_Raum_id"].
      " AND Meldungmail<>'' ORDER BY Art");
    if ( mysql_num_rows($query) > 0 )
    {
      while ( $Arten = mysql_fetch_array($query) )
      {
        $smarty->Append("Artid", $Arten["Art_id"]);
        $smarty->Append("Art", $Arten["Art"]);
      }
    }
    else
    {
      $smarty->Assign("KeinGeraet", '- Keine Geräte in diesem Raum -');
    }
  }
  else
  {
      $query = mysql_query("SELECT Art FROM T_Inventararten WHERE Art_id=".$_REQUEST["F_Art_id"]);
      if ( $art = mysql_fetch_array($query) )
      {
        $smarty->Assign("Art", stripslashes($art["Art"]));
        $GeraeteDa = true;
      }
      else
        $smarty->Assign("Art", 'Falsche Art-Id');
  }
  mysql_free_result($query);
}  
  /*
  echo "</td></tr>\n";
  // Material anzeigen
  if ( is_numeric($_REQUEST["F_Art_id"]) && is_numeric($_REQUEST["F_Raum_id"]) )
  {
    echo '<tr><td valign="top">Gerät/Platz</td><td valign="top">';
    if ( is_numeric($_REQUEST["F_Inventar_id"]) )
    {
      $query = mysql_query("SELECT * FROM T_Inventar WHERE Inventar_id=".$_REQUEST["F_Inventar_id"]);
    }
    elseif ( $_REQUEST["F_Art_id"] == 2 )
    {
      // Sonderfall: Monitor - Rechner-Nummern anzeigen
      $query = mysql_query("SELECT * FROM T_Inventar WHERE F_Art_id=1 AND F_Raum_id=".$_REQUEST["F_Raum_id"]);
    }
    else
    {
      $query = mysql_query("SELECT * FROM T_Inventar WHERE F_Art_id=".$_REQUEST["F_Art_id"].
        " AND F_Raum_id=".$_REQUEST["F_Raum_id"]);
    }
    if ( mysql_num_rows($query) == 0 )
    {
      echo 'Keine Geräte vorhanden!';
      $GeraeteDa = false;
    }
    elseif ( mysql_num_rows($query) == 1 )
    {
      $geraet = mysql_fetch_array($query);
      if ( $_REQUEST["F_Art_id"] != 2 )
        echo stripslashes($geraet["Bezeichnung"]);
      if ( $geraet["Inventar_Nr"] != "" )
        echo " (".stripslashes($geraet["Inventar_Nr"]).")";
      echo '<input type="hidden" name="F_Inventar_id" value="'.$geraet["Inventar_id"].'" />';
      $_REQUEST["F_Inventar_id"] = $geraet["Inventar_id"];
      echo "</td></tr><tr><td>\n";
      echo '<input type="checkbox" name="Secure" value="x"/> (Sicherheitscheck!)</td><td>';
      echo ' es ist wirklich <strong>'.$geraet["Bezeichnung"].' ';
      if ( $geraet["Inventar_Nr"] != "" )
        echo " (".stripslashes($geraet["Inventar_Nr"]).")";
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
      
      }
    }
    mysql_free_result($query);
    echo "</td></tr>\n";
  }
  if ( isset($_REQUEST["F_Inventar_id"]) && is_numeric($_REQUEST["F_Inventar_id"]))
  {
    // Bereits vorhandene Meldungen anzeigen (Status 1=gemeldet)
    // TODO: Monitore??
    $query = mysql_query("SELECT * FROM T_Reparaturen WHERE F_Inventar_id=".
      $_REQUEST["F_Inventar_id"]." AND F_Status_id=1 ORDER BY Datum");
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
    echo '<textarea name="Bemerkungen" cols="60" rows="5"></textarea>';
    echo "</td></tr>\n";
  }

*/

$smarty->display('Problemmeldung.tpl');

// Datenbank schließen
mysql_close($db); 
 
?>
