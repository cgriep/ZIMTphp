<?php
include_once("include/stupla_vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframetest.inc.php");

global $PHP_SELF;

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

if(!$db)
  dieMsgLink("Keine Verbindung mit DB $DBname!", "index.php", "Zurück zum Admin-Skript");

if(isset($_REQUEST['Skript']))
  $Skript = $_REQUEST['Skript'];//Aufrufstatus Skript
else
  $Skript = "";

if(isset($_REQUEST['SJahr']))
  $SJahr  = $_REQUEST['SJahr'];
else
  dieMsgLink("Unvollständige Eingabedaten!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

echo ladeOszKopf_o("StuPla - Admin", "Turnus - Admin (Wochenplan ändern für Schuljahr $SJahr)");

//------------------------------------------------------------------------------
//Erster Aufruf: Tabelle zur Datenaenderung aufbauen
//------------------------------------------------------------------------------
if($Skript == "")
{
  //Alle vorhandenen Turnusse einlesen
  $sql = "SELECT T.Turnus, T.ID_Turnus FROM T_Turnus AS T , T_TurnusGruppe AS TG WHERE T.SJahr = \"$SJahr\" AND T.F_ID_Gruppe = TG.ID_Gruppe ORDER BY TG.Nummer, T.Turnus";
  $rs = mysql_query($sql, $db);
  $AnzTurnus = mysql_num_rows($rs);
  if(!$AnzTurnus)
    dieMsgLink("Sie müssen zuerst eine Turnusliste für das Schuljahr anlegen!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");
  $counter = 0;
  while($row = mysql_fetch_row($rs))
  {
    $Turnus[$counter]=$row[0];
    $ID_Turnus[$counter]=$row[1];
    $counter++;
  }
  //Sind fuer dieses Schuljahr schon Daten vorhanden?
  $sql = "SELECT * FROM T_Woche WHERE SJahr = \"$SJahr\"";
  $rs = mysql_query($sql, $db);
  if(mysql_num_rows($rs) == 0)
    dieMsgLink("Für dieses Schuljahr sind keine Daten vorhanden!", "StuPlaAdmin.php", "Zurück zum Admin-Skript");

  //Erste und letzte Woche ermitteln
  $sql = "SELECT MIN(Woche) FROM T_Woche WHERE SJahr = \"$SJahr\" AND Jahr = 0;";
  $rs = mysql_query($sql, $db);
  $row = mysql_fetch_row($rs);
  $startWoche=$row[0];
  $sql = "SELECT MAX(Woche) FROM T_Woche WHERE SJahr = \"$SJahr\" AND Jahr = 1;";
  $rs = mysql_query($sql, $db);
  $row = mysql_fetch_row($rs);
  $stopWoche=$row[0];

  //Tabelle aufbauen
  echo "\n<form action = \"$PHP_SELF\" method = \"post\">";
  echo "\n<table border = \"0\">";
  echo "\n<tr>";
  echo "\n<td width = \"40%\">&nbsp;</td>";
  echo "\n<td align = \"center\" bgcolor = \"#d1d1d1\"><b>KW</b></td>";
  echo "\n<td align = \"center\" bgcolor = \"#d1d1d1\"><b>Datum</b></td>";

  for($count = 0; $count < $AnzTurnus; $count++)
    echo "\n<td nowrap align = \"center\" bgcolor = \"#d1d1d1\"><b>$Turnus[$count]</b></td>";

  echo "\n<td width = \"40%\">&nbsp;</td>";
  echo "\n</tr>";
  //Schleife ueber beide Jahre
  for($i=0; $i<=1; $i++)
  {
    if($i==0)
    {
      $start = $startWoche;
      $stop = 52;
    }
    else
    {
      $start = 1;
      $stop = $stopWoche;
    }
    for($Woche = $start; $Woche <= $stop; $Woche++)
    {
      //Turnusse der Woche einlesen
      $sql = "SELECT WT.F_ID_Turnus FROM T_Woche AS W, T_WocheTurnus AS WT WHERE W.ID_Woche = WT.F_ID_Woche AND W.Woche = $Woche AND  W.SJahr = \"$SJahr\" AND Jahr = $i;";
      $rs = mysql_query($sql, $db);
      $counter = 0;
      $ID_Turnus_in_Woche = array();
      while($row = mysql_fetch_row($rs))
      {
        $ID_Turnus_in_Woche[$counter]=$row[0];
        $counter++;
      }

      //Montag einlesen
      $sql = "SELECT Montag FROM T_Woche WHERE Woche = $Woche AND SJahr = \"$SJahr\" AND Jahr = $i;";
      $rs = mysql_query($sql, $db);
      $row = mysql_fetch_row($rs);
      $Montag=$row[0];
      $Sonntag = strtotime("+6 day", $Montag);

      echo "\n<tr><b>";
      echo "\n<td>&nbsp;</td>";
      echo "\n<td align = \"center\" bgcolor = \"#ffffff\">";
      echo "$Woche";
      echo "\n</td>";
      echo "\n<td align = \"center\" nowrap bgcolor = \"#ffffff\">";
      echo date("d:m:Y",$Montag) . " - ". date("d:m:Y",$Sonntag);
      echo "</td>";
      for($counter = 0; $counter < $AnzTurnus; $counter++)
      {
        if(in_array($ID_Turnus[$counter],$ID_Turnus_in_Woche))//Turnus in aktueller Woche vertreten
          echo "\n<td align = \"center\" bgcolor = \"#ffffff\"><input type=\"checkbox\" name=\"Turnus[]\" value=\"$i:$SJahr:$Woche:$Turnus[$counter]\" checked></td>";
        else
          echo "\n<td align = \"center\" bgcolor = \"#ffffff\"><input type=\"checkbox\" name=\"Turnus[]\" value=\"$i:$SJahr:$Woche:$Turnus[$counter]\"></td>";
      }
      echo "\n<td>&nbsp;</td>";
      echo "\n</b></tr>";
    }//for($Woche)
  }//for($i)
  echo "\n</table>";
  echo "\n<input type = \"hidden\" name = \"ersteSchulwoche\" value = \"$startWoche\">";
  echo "\n<input type = \"hidden\" name = \"letzteSchulwoche\" value = \"$stopWoche\">";
  echo "\n<input type = \"hidden\" name = \"SJahr\" value = \"$SJahr\">";
  echo "\n<input type = \"hidden\" name = \"Skript\" value = \"EintragDB\">";
  echo "\n<p align = \"center\"><input type=\"submit\" value=\"Änderungen übernehmen\"></p>";
  echo "\n</form>";
}

//------------------------------------------------------------------------------
//Zweiter Aufruf: Zuordnung Woche <-> Turnus in T_WocheTurnus aendern
//------------------------------------------------------------------------------
if($Skript == "EintragDB" && isset($_REQUEST['Turnus']) && isset($_REQUEST['ersteSchulwoche']) && isset($_REQUEST['letzteSchulwoche']))
{
  $Turnus           = $_REQUEST['Turnus'];
  $ersteSchulwoche  = $_REQUEST['ersteSchulwoche'];
  $letzteSchulwoche = $_REQUEST['letzteSchulwoche'];
  if($Turnus == "" || $ersteSchulwoche == "" || $letzteSchulwoche == "" || $SJahr == "")
    dieMsgLink("Unvollständige Eingabedaten!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

  //Alte Zuordnung Turnus <-> Woche loeschen
  $sql = "SELECT ID_Woche FROM T_Woche WHERE SJahr = \"$SJahr\";";
  $rs = mysql_query($sql, $db);
  while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  {
    $sql = "DELETE FROM T_WocheTurnus WHERE F_ID_Woche = $row[ID_Woche];";
    mysql_query($sql, $db);
  }
  //Neue Zuordnung Turnus <-> Woche eintragen
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
MsgLink("Daten wurden erfolgreich übertragen!", "index.php", "Zurück zum Adminbereich");
}//if

echo ladeOszKopf_u();

echo ladeLink("index.php", "<b>StuPla-Admin</b>");
echo ladeLink("TurnusAdmin.php", "Turnus-Admin");
echo ladeLink("FerienAdmin.php", "Ferien-Admin");

echo ladeOszFuss();
?>