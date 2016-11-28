<?php

include_once("include/oszframe.inc.php");
include_once("include/evaluation.inc.php");
include_once("include/evaluation.vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla.inc.php");
include_once("include/Lehrer.class.php");

global $PHP_SELF;

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

//User ermitteln
$User   = $_SERVER['REMOTE_USER'];
$Lehrer = new Lehrer($User, LEHRERID_EMAIL);

echo ladeOszKopf_o("OSZ IMT - Anfangsumfrage","OSZ IMT - Anfangsumfrage");
echo "\n\n<div align = \"center\"><b>[Neue Umfrage anlegen fuer: $Lehrer->Vorname $Lehrer->Name]</b></div>";

//--------------------------------------------------------------------------
//-------------------------------INHALT-ANFANG------------------------------
//--------------------------------------------------------------------------
if(isset($_REQUEST['Schritt']))
  $Schritt = $_REQUEST['Schritt'];
else
  $Schritt = false;

//Timestamp Online-Version ermitteln
$OnlineVersion = getAktuelleVersion();
if($OnlineVersion == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");

//--------------------------------------------------------------------------
//--------------------Erster Schritt: Schulart auswaehlen---------------------
//--------------------------------------------------------------------------
if(!$Schritt)
{
  //Schularten
  $sql = "SELECT  ID_SArt, Bezeichnung FROM T_Schularten ORDER BY Bezeichnung;";

  if($rs = mysql_query($sql,$db))
  {
    echo "\n<form method = \"POST\" action = \"$PHP_SELF\">";
    echo "\n<table width = \"100%\" cellpadding = \"20\" border = \"0\">";
    echo "\n\t<tr height = \"50\">";
    echo "\n\t\t<td colspan = \"3\">&nbsp;</td>";
    echo "\n\t</tr>";
    echo "\n\t<tr>";	
    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
    echo "\n\t\t<td class = \"oszrahmen_bg ra_ro x1111\" nowrap align = \"center\">";	
    echo "\n\t\t\t<p><b>1. Schritt: Auswahl der Schulart</b></p>";	
    echo "\n\t\t\t<select name = \"SArt\" size = \"10\">";
    while($row = mysql_fetch_row($rs))
      echo "\n\t\t\t\t<option value = \"$row[0]\">$row[1]</option>";
    mysql_free_result($rs);
    echo "\n\t\t\t</select>";
    echo "\n\t\t\t<input type = \"hidden\" name = \"Schritt\" value = \"2\">";
    echo "\n\t\t\t<p><input type = \"submit\" value = \"Nächster Schritt\"></p>";
    echo "\n\t\t</td>";
    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";	
    echo "\n\t</tr>";
    echo "\n</table>";
    echo "\n</form>";		    
  }
  else
    dieMsg("Zur Zeit sind keine Daten abrufbar!");
}

//--------------------------------------------------------------------------
//--------------------Zweiter Schritt: Bildungsgang-------------------------
//--------------------------------------------------------------------------
if($Schritt == 2)
{
  $SArt  = (int)$_REQUEST['SArt'];
  if($SArt == "")
    dieMsgLink("Sie müssen eine Schulart auswählen","neuUmfrage.php","Zurück");
    
  //Bildungsgaenge einlesen
  $sql = "SELECT ID_Bg, Bezeichnung FROM T_Bildungsgaenge WHERE F_ID_SArt = $SArt ORDER BY Bezeichnung;";

  if($rs = mysql_query($sql,$db))
  {
    echo "\n<form method = \"POST\" action = \"$PHP_SELF\">";
    echo "\n<table width = \"100%\" cellpadding = \"20\" border = \"0\">";
    echo "\n\t<tr height = \"50\">";
    echo "\n\t\t<td colspan = \"3\">&nbsp;</td>";
    echo "\n\t</tr>";
    echo "\n\t<tr>";	
    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
    echo "\n\t\t<td class = \"oszrahmen_bg ra_ro x1111\" nowrap align = \"center\">";	
    echo "\n\t\t\t<p><b>2. Schritt: Auswahl des Bildungsgangs</b></p>";	    	
    echo "\n\t\t\t<select name = \"Bg\" size = \"5\">";
    while($row = mysql_fetch_row($rs))
      echo "\n\t\t\t\t<option value = \"$row[0]\">$row[1]</option>";
    mysql_free_result($rs);
    echo "\n\t\t\t</select>";
    echo "\n\t\t\t<input type = \"hidden\" name = \"Schritt\" value = \"3\">";
    echo "\n\t\t\t<input type = \"hidden\" name = \"SArt\" value = \"$SArt\">";
    echo "\n\t\t\t<p><input type = \"submit\" value = \"Nächster Schritt\"></p>";
    echo "\n\t\t</td>";
    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";	
    echo "\n\t</tr>";
    echo "\n</table>";
    echo "\n</form>";	    
  }
  else
    dieMsg("Zur Zeit sind keine Daten abrufbar!");
}

//--------------------------------------------------------------------------
//--------------------Dritter Schritt: Klasse auswaehlen--------------------
//--------------------------------------------------------------------------
if($Schritt == 3)
{ 
  if(isset($_REQUEST['SArt']))    
    $SArt  = (int)$_REQUEST['SArt'];
  else
    dieMsgLink("Sie müssen eine Schulart auswählen","neuUmfrage.php","Zurück");

  if(isset($_REQUEST['Bg']))    
    $Bg = (int)$_REQUEST['Bg'];
  else
    dieMsgLink("Sie müssen einen Bildungsgang auswählen","neuUmfrage.php","Zurück");

 //Klassen einlesen
  $sql = "SELECT DISTINCT Klasse FROM T_StuPla WHERE Version = $OnlineVersion ORDER BY Klasse;";

  if($rs = mysql_query($sql,$db))
  {
    echo "\n<form method = \"POST\" action = \"$PHP_SELF\">";
    echo "\n<table width = \"100%\" cellpadding = \"20\" border = \"0\">";
    echo "\n\t<tr height = \"50\">";
    echo "\n\t\t<td colspan = \"3\">&nbsp;</td>";
    echo "\n\t</tr>";
    echo "\n\t<tr>";	
    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
    echo "\n\t\t<td class = \"oszrahmen_bg ra_ro x1111\" nowrap align = \"center\">";	
    echo "\n\t\t\t<p><b>3. Schritt: Auswahl der Klasse</b></p>";	
    echo "\n\t\t\t<select name = \"Klasse\" size = \"10\">";
    while($row = mysql_fetch_row($rs))
      echo "\n\t\t\t\t<option>$row[0]</option>";
    mysql_free_result($rs);
    echo "\n\t\t\t</select>";
    echo "\n\t\t\t<input type = \"hidden\" name = \"Schritt\" value = \"4\">";
    echo "\n\t\t\t<input type = \"hidden\" name = \"SArt\" value = \"$SArt\">";    
    echo "\n\t\t\t<input type = \"hidden\" name = \"Bg\" value = \"$Bg\">";    
    echo "\n\t\t\t<p><input type = \"submit\" value = \"Nächster Schritt\"></p>";
    echo "\n\t\t</td>";
    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";	
    echo "\n\t</tr>";
    echo "\n</table>";
    echo "\n</form>";		    
  }
  else
    dieMsg("Zur Zeit sind keine Daten abrufbar!");
}    

//--------------------------------------------------------------------------
//--------------------Vierter Schritt: Bestaetigen--------------------------
//--------------------------------------------------------------------------
if($Schritt == 4)
{
  if(isset($_REQUEST['SArt']))    
    $SArt  = (int)$_REQUEST['SArt'];
  else
    dieMsgLink("Sie müssen eine Schulart auswählen","neuUmfrage.php","Zurück");

  if(isset($_REQUEST['Bg']))    
    $Bg = (int)$_REQUEST['Bg'];
  else
    dieMsgLink("Sie müssen einen Bildungsgang auswählen","neuUmfrage.php","Zurück");

  if(isset($_REQUEST['Klasse']))    
    $Klasse  = $_REQUEST['Klasse'];
  else
    dieMsgLink("Sie müssen eine Klasse auswählen","neuUmfrage.php","Zurück");
  if(pruefeZeichen($Klasse))
    dieMsg("Ungültige Klasse!");

  echo "\n<table width = \"100%\" cellpadding = \"0\" cellspacing = \"0\" border = \"0\">";
  echo "\n\t<tr height = \"50\">";
  echo "\n\t\t<td colspan = \"4\">&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n\t<tr>";	
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
  echo "\n\t\t<td colspan = \"2\" class = \"oszrahmen_bg ra_ro x1101\" nowrap align = \"center\">";	
  echo "\n\t\t\t<div id = \"Absatz1\"><b>&nbsp;&nbsp;&nbsp;4. Schritt: Eingabedaten bestätigen&nbsp;&nbsp;&nbsp;</b></div>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";    
  echo "\n\t</tr>";
  echo "\n\t<tr>";	
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
  echo "\n\t\t<td align = \"right\" class = \"oszrahmen_bg ra_ro x0001\" nowrap>";
  echo "\n\t\t\tUmfrage erzeugen für:&nbsp;<br>";
  echo "Datum:&nbsp;<br>";    
  echo "Schulart:&nbsp;<br>";
  echo "Bildungsgang:&nbsp;<br>";
  echo "Klasse:&nbsp;";
  echo "\n\t\t</td>";
  echo "\n\t\t<td class = \"oszrahmen_bg ra_ro x0100\" nowrap>";
  $Datum = time();
  echo "\n\t\t\t<b>$Lehrer->Vorname $Lehrer->Name<br>" . date("d.m.Y",$Datum);
  
  //Schulart einlesen
  $sql = "SELECT Bezeichnung FROM T_Schularten WHERE ID_SArt = $SArt;";
  if($rs = mysql_query($sql,$db))
  {
    $row = mysql_fetch_row($rs);
    echo "<br><b>$row[0]</b>";
    mysql_free_result($rs);
  }
  
  //Bildungsgang einlesen
  $sql = "SELECT Bezeichnung FROM T_Bildungsgaenge WHERE ID_Bg = $Bg;";
  if($rs = mysql_query($sql,$db))
  {
    $row = mysql_fetch_row($rs);
    echo "<br><b>$row[0]</b>";
    mysql_free_result($rs);
  }  
  echo "<br>$Klasse<br></b>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
  echo "\n\t</tr>";    
  echo "\n\t<form method = \"POST\" action = \"index.php\">";
  echo "\n\t<tr>";    
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";    
  echo "\n\t\t<td colspan = \"2\"class = \"oszrahmen_bg ra_ro x0111\" align = \"center\">";    
  echo "\n\t\t\t<input type = \"hidden\" name = \"Status\" value = \"neuUmfrage\">";
  echo "\n\t\t\t<input type = \"hidden\" name = \"Bg\" value = \"$Bg\">";
  echo "\n\t\t\t<input type = \"hidden\" name = \"Klasse\" value = \"$Klasse\">";
  echo "\n\t\t\t<input type = \"hidden\" name = \"Lehrer\" value = \"$User\">";
  echo "\n\t\t\t<input type = \"hidden\" name = \"Datum\" value = \"$Datum\">";
  echo "\n\t\t\t<br><div id = \"Absatz1\"><input type = \"submit\" value = \"Umfrage anlegen\"></div><br>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";	
  echo "\n\t</tr>";
  echo "\n\t</form>";
  echo "\n</table>";
}
//--------------------------------------------------------------------------
//-------------------------------INHALT_ENDE--------------------------------
//--------------------------------------------------------------------------

echo ladeOszKopf_u();

echo ladeLink("../","<b>Interner&nbsp;Bereich</b>");
echo ladeLink("index.php","Umfragen");
//echo ladeLink("teilnehmer.php","Teilnehmer");
//echo ladeLink("hilfe.php","Hilfe");

echo ladeOszFuss();
?>