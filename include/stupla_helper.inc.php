<?php
//INHALT:
//(1) dieMsg()
//(2) dieMsgLink()
//(3) msgLink()
//(4) backLink()
//(5) Msg()
//(6) makePwd()
//(7) ersetzeUmlaute()
//(8) pruefeZeichen()
//(9) istZahl()
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
//makePwd(), erzeugt ein Zufallspasswort
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
//istZahl(), prueft ob eine Variable eine Zahl enthaelt
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
?>