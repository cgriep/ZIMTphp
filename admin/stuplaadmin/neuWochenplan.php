<?php
include("include/stupla_vars.inc.php");
include("include/helper.inc.php");
include("include/oszframetest.inc.php");

global $PHP_SELF;

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

if(!$db)
  dieMsgLink("Keine Verbindung mit DB $DBname!", "index.php", "Zurück zum Admin-Skript");

if(isset($_REQUEST['sent']))
  $sent = $_REQUEST['sent'];
else
  $sent = "";

echo ladeOszKopf_o("StuPla - Admin", "Turnus - Admin (Anlegen eines Wochenplans)");

//------------------------------------------------------------------------------
//Erster Aufruf: Dateneingabe Schuljahresdaten
//------------------------------------------------------------------------------
if ($sent == "")
{
  $SJahrliste = array();
  $SJahrliste1 = array();
  //Turnusliste einlesen
  $sql = "SELECT DISTINCT SJahr FROM T_Woche ORDER BY SJahr;";
  $rs = mysql_query($sql, $db);
  $counter = 0;
  while($row = mysql_fetch_row($rs))
  {
    $SJahrliste1[$counter]=$row[0];
    $counter++;
  }

  $sql = "SELECT DISTINCT SJahr FROM T_Turnus ORDER BY SJahr;";
  $rs = mysql_query($sql, $db);
  $counter = 0;
  while($row = mysql_fetch_row($rs))
    if(!in_array($row[0],$SJahrliste1))//Fuer dieses SJahr ist noch kein Wochenplan vorhanden
    {
      $SJahrliste[$counter]=$row[0];
      $counter++;
    }

  $AnzJahre = $counter;
  if(!$AnzJahre)
    dieMsgLink("Sie müssen zuerst eine Turnusliste anlegen!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

  echo "\n<form action = \"$PHP_SELF\" method = \"post\">";
  echo "\n<table border = \"0\" cellpadding = \"10\" cellspacing = \"0\" width = \"100%\">";
  echo "\n\t<tr>";
  echo "\n\t\t<td width = \"20%\">&nbsp;</td>";
  echo "\n\t\t<td width = \"1%\" align = \"center\" bgcolor = \"#eeeeee\" colspan = \"2\">";
  echo "\n\t\t<select name=\"SJahr\" size=\"$AnzJahre\">";

  foreach($SJahrliste as $Jahr)
    echo "\n\t\t<option>$Jahr</option>";

  echo "\n\t\t</select>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
  echo "<span class = \"smallmessage_mi\"><b>Schuljahr:&nbsp;</b>Auswahl des Schuljahres, für das der Wochenplan angelegt werden soll.</span>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"20%\">&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n\t<tr>";
  echo "\n\t\t<td width = \"20%\">&nbsp;</td>";
  echo "\n\t\t<td width = \"1%\" align = \"center\" bgcolor = \"#eeeeee\">";
  echo "\n\t\t\t<input type = \"text\" name = \"startTag\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">TT</font><br>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"1%\" align = \"center\" bgcolor = \"#eeeeee\">";
  echo "\n\t\t\t<input type = \"text\" name = \"startMonat\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">MM</font><br>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
  echo "<span class = \"smallmessage_mi\"><b>Erster Schultag:&nbsp;</b>Erster Schultag des Schuljahres.</span>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"20%\">&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n\t<tr>";
  echo "\n\t\t<td>&nbsp;</td>";
  echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\">";
  echo "\n\t\t\t<input type = \"text\" name = \"stopTag\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">TT</font><br>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\">";
  echo "\n\t\t\t<input type = \"text\" name = \"stopMonat\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">MM</font><br>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
  echo "<span class = \"smallmessage_mi\"><b>Letzter Schultag:&nbsp;</b>Letzter Schultag des Schuljahres.</span>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td>";
  echo "\n<input type = \"hidden\" name = \"sent\" value = \"AufbauTabelle\"><br>";
  echo "\n<input type=\"submit\" value=\"Weiter\">";
  echo "\n\t\t</td>";
  echo "\n\t</tr>";
  echo "\n</table>";
  echo "\n</form>\n";
}

//------------------------------------------------------------------------------
//Zweiter Aufruf: Zuordnung KW <-> Schuljahr in T_Woche uebertragen
//Tabelle zur Dateneingabe der Turnuszuordnung aufbauen
//------------------------------------------------------------------------------
if($sent == "AufbauTabelle" && isset($_REQUEST['startTag']) && isset($_REQUEST['startMonat']) && isset($_REQUEST['stopTag']) && isset($_REQUEST['stopMonat']) && isset($_REQUEST['SJahr']))
{
  $startTag   = $_REQUEST['startTag'];
  $startMonat = $_REQUEST['startMonat'];
  $stopTag    = $_REQUEST['stopTag'];
  $stopMonat  = $_REQUEST['stopMonat'];
  $SJahr      = $_REQUEST['SJahr'];

  if($startTag == "" || $startMonat == "" || $stopTag == "" || $stopMonat == "" || $SJahr == "")
    dieMsgLink("Unvollständige Eingabedaten!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

  $Jahr = explode("/",$SJahr);
  $startJahr = $Jahr[0];
  $stopJahr  = $Jahr[1];

  $ersteSchulwoche = date("W",mktime(0,0,0,$startMonat,$startTag,$startJahr));
  $letzteSchulwoche = date("W",mktime(0,0,0,$stopMonat,$stopTag,$stopJahr));

  //Sind fuer dieses Schuljahr schon Daten vorhanden?
  $sql = "SELECT * FROM T_Woche WHERE SJahr = \"$SJahr\"";
  $rs = mysql_query($sql, $db);
  if(mysql_num_rows($rs) != 0)
    dieMsgLink("Für dieses Schuljahr sind schon Daten vorhanden, bitte die vorhandenen Daten bearbeiten oder zuerst löschen!", "TurnusAdmin.php", "Zurück zum Admin-Skript");

  //Turnus einlesen
  $sql = "SELECT T.Turnus, T.ID_Turnus FROM T_Turnus AS T, T_TurnusGruppe AS TG WHERE SJahr = \"$SJahr\" AND T.F_ID_Gruppe = TG.ID_Gruppe ORDER BY TG.Nummer, T.Turnus";
  $rs = mysql_query($sql, $db);
  $AnzTurnus = mysql_num_rows($rs);
  if(!$AnzTurnus)
    dieMsgLink("Sie müssen zuerst eine Turnusliste für das Schuljahr anlegen!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");
  $count = 0;
  while($row = mysql_fetch_row($rs))
  {
    $Turnus[$count]=$row[0];
    $count++;
  }

  //Tabelle aufbauen
  echo "\n<form action = \"$PHP_SELF\" method = \"post\">";
  echo "\n<table border = \"0\">";
  echo "\n<tr>";
  echo "\n<td width = \"40%\">&nbsp;</td>";
  echo "\n<td align = \"center\" bgcolor = \"#d1d1d1\"><b>KW</b></td>";
  echo "\n<td align = \"center\" bgcolor = \"#d1d1d1\"><b>Datum</b></td>";

  for($count = 0; $count < $AnzTurnus; $count++)
    echo "\n<td align = \"center\" bgcolor = \"#d1d1d1\"><b>$Turnus[$count]</b></td>";

  echo "\n<td width = \"40%\">&nbsp;</td>";
  echo "\n</tr>";
  //Schleife ueber beide Jahre
  for($i=0; $i<=1; $i++)
  {
    if($i==0)
    {
      $start = $ersteSchulwoche;
      $stop = 52;
      $Jahr = $startJahr;
    }
    else
    {
      $start = 1;
      $stop = $letzteSchulwoche;
      $Jahr = $stopJahr;
    }
    for($Woche = $start; $Woche <= $stop; $Woche++)
    {
      $Montag = mondaykw($Woche,$Jahr);
      $Sonntag = strtotime("+6 day", $Montag);
      //DB-Eintrag: Wochen in T_Woche eintragen
      $sql = "INSERT INTO T_Woche (Woche, Montag, SJahr, Jahr) VALUES ($Woche, $Montag, \"$SJahr\", $i);";
      mysql_query($sql, $db);

      echo "\n<tr><b>";
      echo "\n<td>&nbsp;</td>";
      echo "\n<td align = \"center\" bgcolor = \"#ffffff\">";
      echo "$Woche";
      echo "\n</td>";
      echo "\n<td align = \"center\" nowrap bgcolor = \"#ffffff\">";
      echo date("d:m:Y",$Montag) . " - ". date("d:m:Y",$Sonntag);
      echo "</td>";

      for($countTurnus = 0; $countTurnus < $AnzTurnus; $countTurnus++)
        echo "\n<td align = \"center\" bgcolor = \"#ffffff\"><input type=\"checkbox\" name=\"Turnus[]\" value=\"$i:$SJahr:$Woche:$Turnus[$countTurnus]\"></td>";

      echo "\n<td>&nbsp;</td>";
      echo "\n</b></tr>";
    }//for($Woche)
  }//for($i)
  echo "\n</table>";
  echo "\n<input type = \"hidden\" name = \"ersteSchulwoche\" value = \"$ersteSchulwoche\">";
  echo "\n<input type = \"hidden\" name = \"letzteSchulwoche\" value = \"$letzteSchulwoche\">";
  echo "\n<input type = \"hidden\" name = \"SJahr\" value = \"$SJahr\">";
  echo "\n<input type = \"hidden\" name = \"sent\" value = \"EintragDB\">";
  echo "\n<p align = \"center\"><input type=\"submit\" value=\"Änderungen übernehmen\"></p>";
  echo "\n</form>";
}

//------------------------------------------------------------------------------
//Dritter Aufruf: Zuordnung Woche <-> Turnus in T_WocheTurnus uebertragen
//------------------------------------------------------------------------------
if($sent == "EintragDB" && isset($_REQUEST['Turnus']) && isset($_REQUEST['ersteSchulwoche']) && isset($_REQUEST['letzteSchulwoche']) && isset($_REQUEST['SJahr']))
{
  $Turnus           = $_REQUEST['Turnus'];
  $ersteSchulwoche  = $_REQUEST['ersteSchulwoche'];
  $letzteSchulwoche = $_REQUEST['letzteSchulwoche'];
  $SJahr            = $_REQUEST['SJahr'];

  if($Turnus == "" || $ersteSchulwoche == "" || $letzteSchulwoche == "" || $SJahr == "")
    dieMsgLink("Unvollständige Eingabedaten!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

  //Zuordnung Turnus <-> Woche eintragen
  foreach($Turnus as $value)
  {
    $tmpString = explode(":",$value);
    $Jahr   = $tmpString[0];
    $SJahr  = $tmpString[1];
    $Woche  = $tmpString[2];
    $Turnus = $tmpString[3];
    //Primaerschluessel Turnus ermitteln
    $sql = "SELECT ID_Turnus FROM T_Turnus WHERE Turnus = \"$Turnus\" AND SJahr = \"$SJahr\";";
    $rs = mysql_query($sql, $db);
    $row = mysql_fetch_row($rs);
    $psTurnus = $row[0];
    //Primaerschluessel Woche ermitteln
    $sql = "SELECT ID_Woche FROM T_Woche WHERE Woche = $Woche AND SJahr = \"$SJahr\" AND Jahr = $Jahr;";
    $rs = mysql_query($sql, $db);
    $row = mysql_fetch_row($rs);
    $psWoche = $row[0];
    //Eintrag in T_WocheTurnus
    $sql = "INSERT INTO T_WocheTurnus (F_ID_Turnus, F_ID_Woche) VALUES ($psTurnus, $psWoche);";
    mysql_query($sql, $db);
  }//foreach
  MsgLink("Die Daten wurden erfolgreich übertragen!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");
}//if

echo ladeOszKopf_u();

echo ladeLink("index.php", "<b>StuPla-Admin</b>");
echo ladeLink("TurnusAdmin.php", "Turnus-Admin");
echo ladeLink("FerienAdmin.php", "Ferien-Admin");

echo ladeOszFuss();

//------------------------------------------------------------------------------
//Funktionen
//------------------------------------------------------------------------------
//Liefert den Timestamp des Montags einer bestimmten KW
function mondaykw($kw,$jahr)
{
  $firstmonday = firstkw($jahr);
  $mon_monat = date('m',$firstmonday);
  $mon_jahr = date('Y',$firstmonday);
  $mon_tage = date('d',$firstmonday);
  $tage = ($kw-1)*7;
  $mondaykw = mktime(0,0,0,$mon_monat,$mon_tage+$tage,$mon_jahr);
  return $mondaykw;
}

//Liefert den Timestamp des ersten Montags eines Jahres
function firstkw($jahr)
{
  $erster = mktime(0,0,0,1,1,$jahr);
  $wtag = date('w',$erster);
  if ($wtag <= 4)
    $montag = mktime(0,0,0,1,1-($wtag-1),$jahr);
  else
    $montag = mktime(0,0,0,1,1+(7-$wtag+1),$jahr);
  return $montag;
}
?>