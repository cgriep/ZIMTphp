<?php
$HELPER_INC = true;
//INHALT:
//(01) dieMsg()
//(02) dieMsgLink()
//(03) msgLink()
//(04) backLink()
//(05) Msg()
//(06) makePwd()
//(07) ersetzeUmlaute()
//(08) pruefeZeichen()
//(09) istZahl()
//(10) gibBrowser()
//(11) LayoutRahmen()
//(12) set_schrift()
//(13) is_empty()
//(14) erw_feld()
//(15) erz_Liste()
//(16) istLeer()
/*===========================================================================*/
/*dieMsg() beendet das Skript mit einer formatierten Fehlermeldung,*/
//Uebergabewerte: Fehlermeldung
function dieMsg($msgstring)
{
  die("<table width=\"100%\" height=\"60%\"><tr><td align=\"center\" valign=\"center\"><span style=\"font-size:20pt\"><font color=\"#FF0000\">Fehler: </font>" . $msgstring . "</span></td></tr></table>");
}
/*===========================================================================*/
/*dieMsgLink() beendet das Skript mit einer formatierten Fehlermeldung,*/
//Uebergabewerte: Fehlermeldung, URL, Beschreibung des Links die als Argument uebergeben wird.
function dieMsgLink($msgstring, $link, $linktext)
{
  die("<table width=\"100%\" height=\"60%\"><tr><td align=\"center\" valign=\"center\"><span style=\"font-size:20pt\"><font color=\"#FF0000\">Fehler: </font>" . $msgstring . "</span><span style=\"font-size:14pt\"><br><br><a href=\"" . $link . "\">" . $linktext. "</a></span></td></tr></table>");
}
/*===========================================================================*/
/*msgLink() erzeugt eine formatierte Meldung mit einem Link.*/
//Uebergabewerte: Meldung, URL, Beschreibung des Links
function msgLink($msgstring, $link, $linktext)
{
  echo "<table width=\"100%\" height=\"60%\"><tr><td align=\"center\" valign=\"center\"><span style=\"font-size:20pt\">" . $msgstring . "</span><span style=\"font-size:14pt\"><br><br><a href=\"" . $link . "\">" . $linktext. "</a></span></td></tr></table>";
}
/*===========================================================================*/
/*backLink() erzeugt einen formatierten Link.*/
//Uebergabewerte: URL, Beschreibung des Links
function backLink($link, $linktext)
{	
  echo "<p align=\"center\"><span style=\"font-size:12pt\"><br><br><a href=\"" . $link . "\">" . $linktext. "</a></span></p>";
}
/*===========================================================================*/
/*Msg() erzeugt eine formatierte Meldung*///Uebergabewerte: Meldung
function Msg($msgstring)
{
  echo "<table width=\"100%\" height=\"60%\"><tr><td align=\"center\" valign=\"center\"><span style=\"font-size:20pt\">" . $msgstring . "</span></td></tr></table>";
}
/*===========================================================================*/
//makePwd(), erzeugt ein Zufallspasswort (ohne Umlaute , ohne Null und ohne I,O,i,o)
//Rueckgabewert: Passwort
function makePwd($Laenge = 10)
{
  mt_srand((double)microtime()*1000000);//Startwert
  for($i = 0; $i < $Laenge; $i++)
  {
    $select = mt_rand() % 3 + 1;
    switch($select)
    {
      case 1:
        $zeichen[$i] = chr(mt_rand() % 9 + 49);//1-9
        break;
      case 2:
        do
        {
          $Nummer = mt_rand() % 26 + 65;
        }
        while ($Nummer == 73 || $Nummer == 79);
        $zeichen[$i] = chr($Nummer);//A-Z (ohne I,O,)
        break;
      case 3:
        do
        {
          $Nummer = mt_rand() % 26 + 97;
        }
        while ($Nummer == 108 || $Nummer == 111);
        $zeichen[$i] = chr($Nummer);//a-z (ohne l,o)
    }//switch
  }//for
  $pwd = implode("",$zeichen);
  return $pwd;
}
/*===========================================================================*/
//ersetzeUmlaute(), ersetzt in einem String alle dt. Umlaute
//Rueckgabewert: String mit ersetzten Zeichen
function ersetzeUmlaute($suchstring)
{
  $suchmuster = array ("/Ä/","/Ö/","/Ü/","/ä/","/ö/","/ü/","/ß/");
  $ersetzen   = array ("Ae","Oe","Ue","ae","oe","ue","ss");
  return preg_replace($suchmuster, $ersetzen, $suchstring);
}
/*===========================================================================*/
//pruefeZeichen(), prueft einen String auf das Vorhandensein bestimmter Zeichen
//Rueckgabewert: true wenn eines oder mehrere der Zeichen enthalten sind, sonst false
function pruefeZeichen($pruefstring)
{
  $ergebnis = false;
  $suchstring = array('/','\\','\"','\'','<','>');
  foreach($suchstring as $zeichen)
    if(strstr($pruefstring,$zeichen))
    {
      $ergebnis = true;
      break;
    }
  return $ergebnis;
}	
/*===========================================================================*/
//istZahl(), prueft ob eine Variable eine Zahl ist
//Rueckgabewert: true wenn Variable eine Zahl ist
function istZahl($var)
{
  if (preg_match("=^[0-9]+$=i",$var))
    return true;
  return false;
}
/*===========================================================================*/
//gibBrowser() liefert den Browser (fuer Browserweichen)
//Rueckgabewert: "IE", "Netscape" oder "?"
function gibBrowser()
{
  $browserstring = $_SERVER["HTTP_USER_AGENT"];
  if(eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})", $_SERVER["HTTP_USER_AGENT"]))
    return  "IE";
  else 
  {
    if(eregi("(netscape6)/(6.[0-9]{1,3})", $_SERVER["HTTP_USER_AGENT"]))
      return "Netscape";
    else
      return "?";
  }
}
/*===========================================================================*/
//LayoutRahmen() liefert Layouteinstellungen fuer css-Rahmendesign
//Rueckgabewert: Formatstring
function LayoutRahmen($Zeile, $MaxZeile, $Spalte, $MaxSpalte)
{
  $Rahmen = "";
  if($Zeile < $MaxZeile && $Spalte < $MaxSpalte)
    $Rahmen .= "x1001";
  else
  {
    if($Zeile == $MaxZeile && $Spalte < $MaxSpalte)
      $Rahmen .= "x1011";
    else
    {
      if($Zeile < $MaxZeile && $Spalte == $MaxSpalte)
        $Rahmen .= "x1101";
      else
        $Rahmen .= "x1111";
    }
  }
  return $Rahmen;
}
/*===========================================================================*/
//set_schrift() liefert Layouteinstellungen fuer css-Schriftdesign (fuer Vertretungen)
//$Druck = true  --> Schrift ohne Formatierung
//$Neu   = true  --> Schriftformat Vertretung
//$Neu   = false --> Schriftformat Normal
//$Bem   = true  --> Schriftformat fuer Bemerkung
//Rueckgabewert: Formatstring
function set_schrift($Druck, $Neu, $Bem = false)
{
  if($Druck)
  {
    if(!$Bem)
      $schrift = "";
    else
      $schrift = "schrift_bem_dr";    
  }
  else
  {
    if($Neu && !$Bem)
      $schrift = "schrift_ver";
    else
    {
      if($Bem)
        $schrift = "schrift_bem";
      else
        $schrift = "schrift_nor";
    }
  }
  return $schrift;
}
/*===========================================================================*/
//is_empty() prueft ob ein String leer ist
//Rueckgabewert: true/false
function is_empty($string)
{
  if(!isset($string) || strcmp(trim($string), "") == 0)
    return true;
  return false;
}
/*===========================================================================*/
//erw_feld() erweitert ein zweidim. Array um weitere Felder (fuer HTML-Ausgabe)
//Filtert Doppler aus und markiert Aenderungen.
//Aufbau: $feld[x]["Name"] --> Bezeichnung (Fach, Klasse, ...)
//        $feld[x]["Neu"]  --> Info ueber Aenderung (true/false)
//Rueckgabewert: --- (Aendert referenziertes Feld)
function erw_feld($string, &$feld)
{
  if(is_empty($string) || strcmp($string, "*") == 0)
    return;
  $groesse = count($feld);    
  //Ueberpruefung auf Doppler
  foreach($feld as $wert)
  {
    if(strcmp($wert["Name"], $string) == 0)
      return;
    if(strcmp($wert["Name"], ohneStern($string)) == 0)
    {
      $feld[$groesse - 1]["Neu"] = true;
      //echo "<br>HOPPLA<br>";
      return;
    }
  }
  //Anfuegen neuer Werte
  if(strpos($string,"*") === 0)
    $feld[$groesse]["Neu"] = true;
  else
    $feld[$groesse]["Neu"] = false;
  $feld[$groesse]["Name"] = ohneStern($string);
}
/*===========================================================================*/
//erz_Liste() macht aus dem zweidim. Feld wieder eine eindim.
//Rueckgabewert: eindim. Feld (ohne Aenderungsinfos)
function erz_Liste($feld)
{
  $liste = array();
  foreach($feld as $wert)
    $liste[] = $wert["Name"];
  return $liste;
}
/*===========================================================================*/
//istLeer() Ueberprueft das zweidim. Feld, ob Eintraege vorliegen
//Rueckgabewert: true/false
function istLeer($feld)
{
  if(is_array($feld))
    foreach($feld as $wert)
      if($wert["Name"] != "")
        return false;
  return true;
}
?>