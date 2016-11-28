<?php
$file_dir = "files/";
$datafile = $file_dir . "plan.txt";
$logfile  = $file_dir . "logfile.txt";
$expfile  = $file_dir . "muster.txt";

include_once("include/oszframetest.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla.inc.php");

global $PHP_SELF;

if(isset($_POST['Skript']))
  $Skript = $_POST['Skript'];//Aufrufstatus Skript

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
if(!$db)
{
  echo "Keine Verbindung zu $DBName!";
  die();
}
mysql_select_db($DBname,$db);

echo ladeOszKopf_o("StuPla - Admin", "StuPla - Admin");

//------------------------------------------------------------------------------
//Anzahl der Versionen aendern
//------------------------------------------------------------------------------
if(isset($Skript) && $Skript == "AendereVersionen" && isset($_REQUEST['AnzVersionen']) && isset($_REQUEST['altAnzVersionen']))
{
  $AnzVersionen    = (int)$_REQUEST['AnzVersionen'];
  $altAnzVersionen = (int)$_REQUEST['altAnzVersionen'];
  $sql = "UPDATE T_StuPlaDaten SET AnzVersionen = $AnzVersionen WHERE AnzVersionen = $altAnzVersionen;";
  mysql_query($sql, $db);
}

//------------------------------------------------------------------------------
//Dateiupload
//------------------------------------------------------------------------------
if(isset($Skript) && $Skript == "StarteUpload")
{
  $name     = strtolower($_FILES['myfile']['name']);
  $tmp_name = $_FILES['myfile']['tmp_name'];
  $typ      = $_FILES['myfile']['type'];
  if(isset($_FILES['myfile']) && $typ == "text/plain" && $name == "plan.txt")
    move_uploaded_file($tmp_name,$file_dir . $name);
  else
    dieMsg("Das Hochladen dieser Datei ist nicht möglich!");
}

//------------------------------------------------------------------------------
//Gueltigkeitsdatum einer Version aendern
//------------------------------------------------------------------------------
if(isset($Skript) && $Skript == "AendereGueltigkeit" && isset($_REQUEST['GueltigAbTag']) && isset($_REQUEST['GueltigAbMonat']) && isset($_REQUEST['GueltigAbJahr']) && isset($_REQUEST['Version']))
{
  $GueltigAbTag   = (int)$_REQUEST['GueltigAbTag'];
  $GueltigAbMonat = (int)$_REQUEST['GueltigAbMonat'];
  $GueltigAbJahr  = (int)$_REQUEST['GueltigAbJahr'];
  $Version        = (int)$_REQUEST['Version'];
  $timestamp = mktime(0,0,0,$GueltigAbMonat,$GueltigAbTag,$GueltigAbJahr);
  if(!$GueltigAbTag && !$GueltigAbMonat && !$GueltigAbJahr)
    $timestamp = 0;
  if(date("w",$timestamp) != 1 && $timestamp != 0)
    dieMsgLink("Das Gültigkeitsdatum muss ein Montag sein!","index.php","StuPla-Admin");
  $sql = "SELECT GueltigAb FROM T_StuPla WHERE GueltigAb = $timestamp;";
  $rs = mysql_query($sql, $db);
  if(mysql_num_rows($rs) == 0)//Es darf keine zwei Versionen mit identischer Gueltigkeit geben
  {
    $sql = "UPDATE T_StuPla SET GueltigAb = $timestamp WHERE Version = $Version;";
    mysql_query($sql, $db);

    if($fplog = fopen($logfile,"a"))
    {
      $logstring = "\n\nAendere Gueltigkeitsdatum von Version: " . date("d.m.Y H:i:s",$Version) . " auf Gueltigkeitsdatum: " . date("d.m.Y",$timestamp);
      fputs($fplog,$logstring);
      fclose($fplog);
    }
  }
  //Es gibt schon eine Version mit diesem Gueltigkeitsdatum
  else
  {
    dieMsgLink("Es gibt schon eine andere Version mit diesem Gültigkeitsdatum!","index.php","StuPla-Admin");
  }
  mysql_free_result($rs);
}//Ende Gueltigkeitsdatum aendern

//------------------------------------------------------------------------------
//Version loeschen
//------------------------------------------------------------------------------
if(isset($Skript) && $Skript == "LoescheVersion"  && isset($_REQUEST['Version']))
{
  $Version = $_REQUEST['Version'];
  $sql = "DELETE FROM T_StuPla WHERE Version = $Version;";
  mysql_query($sql, $db);
}

//------------------------------------------------------------------------------
//Loggdatei leeren
//------------------------------------------------------------------------------
if(isset($Skript) && $Skript == "LeereLogdatei")
{
  $fplog = fopen($logfile,"w");
  fclose($fplog);
}

//------------------------------------------------------------------------------
//Datenimport
//------------------------------------------------------------------------------
if(isset($Skript) && $Skript == "StarteImport")
{
  $Datafile = true;
  $Logfile = true;
  $Import = true;

  //Logdatei checken
  if (!file_exists($logfile))
  {
    echo "<br><b>Kein Datenimport: Datei $logfile nicht vorhanden!</b><br>";
    $Logfile = false;
  }
  $fplog = fopen($logfile,"a");
  if (!$fplog)
  {
    echo "<br><b>Kein Datenimport: Fehler beim oeffnen von $logfile!</b><br>";
    $Logfile = false;
  }

  //Weitere Ueberpruefungen wenn Logfile OK
  if($Logfile)
  {
    //Datenfile checken
    if (!file_exists($datafile))
    {
      $logstring = "\n\nDatum: " . date("d.F Y H:i:s",time()) . "\n\tKein Datenimport moeglich: Datei $datafile nicht vorhanden!";
      fputs($fplog,$logstring);
      $Datafile = false;
    }
    $fpdata = fopen($datafile,"r");
    if (!$fpdata)
    {
      $logstring = "\n\nDatum: " . date("d.F Y H:i:s",time()) . "\n\tKein Datenimport moeglich: Datei $datafile kann nicht geoeffnet werden!";
      fputs($fplog,$logstring);
      $Datafile = false;
    }
    else//Datenfile geoeffnet -> erste Zeile ueberpruefen
    {
      $line = trim(fgets($fpdata,4096));
      $data = explode(";",$line);
      $data1 = array("Lehrer","Tag","Stunde","Blockgröße","Raum","Kurskürzel","Klasse","Turnus","Name","Vorname");
      for($i=0;$i<=9;$i++)
      {
        $data[$i] = str_replace("\"","",$data[$i]);//Anfuehrungszeichen entfernen
        if($data[$i] != $data1[$i])
        {
	  $logstring = "\n\nDatum: " . date("d.F Y H:i:s",time()) . "\n\tKein Datenimport moeglich: Erste Zeile in Datei $datafile enthaelt die unbekannte Spalte $data[$i] anstelle von $data1[$i]!";
          fputs($fplog,$logstring);
          $Datafile = false;
        }
      }
    }//else
    if($Datafile) //Versionsstand ueberpruefen (nur bei vorhandenem Datafile)
    {
      //Auf schon vorhandene Versions-Timestamps pruefen
      $filedate = filemtime($datafile);//Timestamp der Importdatei
      $sql = "SELECT Version FROM T_StuPla WHERE Version = $filedate";
      $rs = mysql_query($sql, $db);
      if(mysql_num_rows($rs) != 0)//Es gibt diese Version schon
      {
        $logstring = "\n\nDatum: " . date("d.F Y H:i:s",time()) . "\n\tKein Datenimport erfolgt: Es gibt schon eine Version vom " . date("d.F Y H:i:s",$filedate) . " !";
        fputs($fplog,$logstring);
        $Import = false;
      }
      mysql_free_result($rs);
    }//if($Datafile)
  }//if($Logfile)

  if($Import && $Logfile && $Datafile)
  {
    $logstring = "\n\nDatum: " . date("d.F Y H:i:s",time()) . "\n\tStarte Datenimport von Version: " . date("d.F Y H:i:s",filemtime($datafile));
    fputs($fplog,$logstring);

    //Anzahl vorhandener Versionen ermitteln
    $sql = "SELECT DISTINCT Version FROM T_StuPla";
    $rs = mysql_query($sql, $db);
    $AnzVers = mysql_num_rows($rs);
    mysql_free_result($rs);

    //Loeschen von Versionen mit Gueltigkeitsdatum = 0 (es kann nur eine geben)
    $sql = "DELETE FROM T_StuPla WHERE GueltigAb = 0;";
    mysql_query($sql, $db);

    //Max. Anzahl Versionen ermitteln
    $sql = "SELECT AnzVersionen FROM T_StuPlaDaten";
    $rs = mysql_query($sql, $db);
    $row = mysql_fetch_row($rs);
    mysql_free_result($rs);
    $MaxVers = $row[0];

    //Ueberpruefung Versionsanzahl und loeschen alter Versionen
    while ($AnzVers >= $MaxVers)//Max. Anzahl der Versionen ueberschritten.
    {
      //Gueltigkeit der aeltesten Version aus DB holen
      $sql = "SELECT min(GueltigAb) FROM T_StuPla";
      $rs = mysql_query($sql, $db);
      $row = mysql_fetch_row($rs);
      mysql_free_result($rs);
      $GueltigAb = $row[0];

      $sql = "SELECT DISTINCT Version FROM T_StuPla WHERE GueltigAb = $GueltigAb;";
      $rs = mysql_query($sql, $db);
      $row = mysql_fetch_row($rs);
      mysql_free_result($rs);
      $Version = $row[0];

      $sql = "DELETE FROM T_StuPla WHERE GueltigAb = $GueltigAb;";
      mysql_query($sql, $db);

      $logstring = "\n\tLoesche Version: " . date("d.F Y H:i:s",$Version);
      fputs($fplog,$logstring);

      $AnzVers-=1;
    }//while

    //Daten aus Textdatei in Datenbank uebertragen
    $countdataset=0;
    $countdatasetfail=0;

    $logstring = "\n\tImportiere neue Version in DB $DBname.";
    fputs($fplog,$logstring);

    while (!feof($fpdata))
    {
      $line = fgets($fpdata,4096);
      $data = explode(";",$line);
      $datasetOK = true;
      for($i=0;$i<=9;$i++)
      {
        $data[$i] = str_replace("\"","",$data[$i]);//Anfuehrungszeichen entfernen
        if(trim($data[$i]) == "" && $i != 7)//Leere Zelle -> Datensatz verwerfen
        {
          $datasetOK = false;
          $countdatasetfail++;
          break;
        }//if
      }//for
      if(!isset($data[7]) || trim($data[7]) == "" || trim($data[7]) == "alle" || trim($data[7]) == "Sem")//Kein Turnus angegeben -> Jede Woche
        $data[7] = "jede Woche";
      if($datasetOK)
      {
        for($i=0;$i<(int)$data[3];$i++)//Schleife ueber Laenge (in Bloecken) des Unterrichts -> Laenge immer gleich 1
        {
          $Stunde = (int)$data[2]+$i;
          $sql = "INSERT INTO T_StuPla (Lehrer, Wochentag, Stunde, Raum, Fach, Klasse, Turnus, Version, GueltigAB, Name, Vorname) VALUES (\"$data[0]\"," . (int)$data[1] . "," . $Stunde . ",\"$data[4]\",\"$data[5]\",\"$data[6]\",\"$data[7]\"," . (int)$filedate . ",0,\"$data[8]\",\"$data[9]\")";
          mysql_query($sql, $db);
        }
      }
      $countdataset++;
    }//while

    $logstring = "\n\tBeende Import: Von $countdataset Datensaetzen wurden $countdatasetfail verworfen!";
    fputs($fplog,$logstring);

    fclose($fpdata);
    fclose($fplog);
  }//if($Import && $Logfile && $Datafile)
}//Ende Datenimport

//------------------------------------------------------------------------------
//Aufbau des Admin-Frontend
//------------------------------------------------------------------------------
//Max. Anzahl Versionen ermitteln
$sql = "SELECT AnzVersionen FROM T_StuPlaDaten";
$rs = mysql_query($sql, $db);
$row = mysql_fetch_row($rs);
mysql_free_result($rs);
$MaxAnzVersionen = $row[0];

$sql = "SELECT DISTINCT Version, GueltigAb FROM T_StuPla ORDER BY Version DESC";
$rs = mysql_query($sql, $db);
echo "\n<table border = \"0\" cellpadding = \"10\" cellspacing = \"0\" width = \"100%\">";
echo "\n\t<tr>\n\t\t<td width = \"49%\">&nbsp;</td>\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#ffffff\"><b>Versionsdatum</b></td>\n\t\t<td colspan = \"4\" align = \"center\" bgcolor = \"#ffffff\"><b>Gültig ab</b></td>\n\t\t<td>&nbsp;</td>\n\t\t<td width = \"49%\">&nbsp;</td>";
echo "\n\t</tr>";
$count = 0;
while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
{
    if($count%2)
    {
	$bgcolor1 = "#eeeeee";
	$bgcolor2 = "#eeeeee";
    }
    else
    {
	$bgcolor2 = "#d1d1d1";
	$bgcolor1 = "#d1d1d1";
    }
    echo "\n\t<form method = \"post\" action = \"$PHP_SELF\">";
    echo "\n\t\t<input type = \"hidden\" name = \"Skript\" value = \"AendereGueltigkeit\">";
    echo "\n\t\t<input type = \"hidden\" name = \"Version\" value = \"$row[Version]\">";
    echo "\n\t<tr align =\"center\" valign =\"center\">";
    echo "\n\t\t<td>&nbsp;</td>";
    echo "\n\t\t<td bgcolor = \"$bgcolor1\">" . date("d.m.Y",$row['Version']) . "</td>";
    echo "\n\t\t<td bgcolor = \"$bgcolor1\">" . date("H:i",$row['Version']) . "&nbsp;Uhr</td>";
    if($row['GueltigAb'] != 0)
	{
	$dateTag = date("d",$row['GueltigAb']);
	$dateMonat = date("m",$row['GueltigAb']);
	$dateJahr = date("Y",$row['GueltigAb']);
	}
    else
	$dateTag = $dateMonat = $dateJahr = "--";
    echo "\n\t\t<td bgcolor = \"$bgcolor2\">$dateTag<br><input type = \"text\" name = \"GueltigAbTag\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">TT</font></td>";
    echo "\n\t\t<td bgcolor = \"$bgcolor2\">$dateMonat<br><input type = \"text\" name = \"GueltigAbMonat\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">MM</font></td>";
    echo "\n\t\t<td bgcolor = \"$bgcolor2\">$dateJahr<br><input type = \"text\" name = \"GueltigAbJahr\" size = \"4\" maxlength=\"4\"><br><font size=\"-2\">JJJJ</font></td>";
    echo "\n\t\t<td bgcolor = \"$bgcolor2\"><input type=\"Submit\" value=\"Ändern\"></td>";
    echo "\n\t</form>";
    echo "\n\t<form method = \"post\" action = \"$PHP_SELF\">";
    echo "\n\t\t<td>";
    echo "\n\t\t<input type=\"Submit\" value=\"Löschen\">";
    echo "\n\t\t<input type = \"hidden\" name = \"Skript\" value = \"LoescheVersion\">";
    echo "\n\t\t<input type = \"hidden\" name = \"Version\" value = \"$row[Version]\">";
    echo "\n\t\t</td>";
    echo "\n\t</form>";
    echo "\n\t\t<td>&nbsp;</td>";
    echo "\n\t</tr>";
    $count++;
}//while
mysql_free_result($rs);
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"6\" bgcolor = \"#eeeeee\"><span class = \"smallmessage_mi\"><b>Achtung:&nbsp;</b>Sobald das Gültigkeitsdatum einer Version geändert wird, erfolgt eine Überprüfung, ob es zu Überschneidungen mit vorhandenen Reservierungen kommt. Falls das der Fall sein sollte, werden diese automatisch gelöscht. Der betroffene Lehrer erhält eine Benachrichtigung per E-Mail. Die gelöschten Reservierungen werden in der Logdatei angezeigt.</span></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</table>";

//Versionsanzahl aendern
echo "\n<form method = \"post\" action = \"$PHP_SELF\">";
echo "\n<br>";
echo "\n<table border = \"0\" cellpadding = \"10\" cellspacing = \"0\" width = \"100%\">";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\" width = \"300\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"Skript\" value = \"AendereVersionen\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"altAnzVersionen\" value = \"$MaxAnzVersionen\">";
echo "\n\t\t\t<b>Anzahl Versionen:</b>&nbsp;$MaxAnzVersionen&nbsp;&nbsp;<input type = \"text\" name = \"AnzVersionen\" size = \"2\" maxlength=\"2\">";
echo "\n\t\t\t&nbsp;<input type=\"Submit\" value=\"Ändern\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"300\">";
echo "<span class = \"smallmessage_mi\"><b>Versionsanzahl ändern:&nbsp;</b>Ändert die Anzahl der Online verfügbaren Versionen (wird erst mit dem nächsten Datenimport wirksam).</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Dateiupload
echo "\n<br>";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n<form enctype = \"multipart/form-data\" method = \"post\" action = \"$PHP_SELF\">";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"Skript\" value = \"StarteUpload\">";
echo "\n\t\t<p><input type=\"file\" name = \"myfile\"></p>";
echo "\n\t\t<input type = \"submit\" value = \"Datei hochladen\">";
echo "\n\t\t</td>";
echo "\n\t</form>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Datei hochladen:&nbsp;</b>Dialog zum Hochladen der Datei 'plan.txt' auf den Server.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Datenimport
echo "\n<br>";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n<form method = \"post\" action = \"$PHP_SELF\">";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"Skript\" value = \"StarteImport\">";
echo "\n\t\t<input type=\"Submit\" value=\"Datenimport\">";
echo "\n\t\t</td>";
echo "\n</form>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Datenimport:&nbsp;</b>Startet den Datenimport aus der Datei 'plan.txt'.<br>[Erstellungsdatum der aktuellen Datei: " . date("d.m.y / H:i:s",@filemtime($datafile)) . "]</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Musterdatei Plandaten anzeigen
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t<form action = \"$expfile\" target=\"_blank\">";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t<input type=\"Submit\" value=\"Anzeigen\">";
echo "\n\t\t</td>";
echo "\n\t</form>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Musterdatei:&nbsp;</b>Die Plandatei 'plan.txt' enthält die Informationen der Unterrichtspläne. Über den Aufruf des Datenimports werden diese Daten in die Datenbank überführt.<br><br><font color=\"#FF0000\">ACHTUNG: </font>Der Aufbau dieser Datei muss <u>strikt</u> eingehalten werden, damit der Datenimport fehlerfrei abläuft.<br>Ein Datensatz wird beim Import verworfen, wenn er Leerfelder beinhaltet (Ausnahme: Feld 'Turnus'). Im Feld 'Turnus' wird ein leerer Eintrag bzw. der Eintrag 'alle' oder 'Sem' dem Turnus 'jede Woche' zugeordnet.<br>Insbesondere die erste Zeile muss wortgenau in der Datei enthalten sein.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Logdatei anzeigen/leeren
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t<form action = \"$logfile\" target=\"_blank\">";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t<input type=\"Submit\" value=\"Anzeigen\">";
echo "\n\t\t</td>";
echo "\n\t</form>";
echo "\n\t<form method = \"post\" action = \"$PHP_SELF\">";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t<input type = \"hidden\" name = \"Skript\" value = \"LeereLogdatei\">";
echo "\n\t\t<input type=\"Submit\" value=\"Leeren\">";
echo "\n\t\t</td>";
echo "\n\t</form>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Logdatei:&nbsp;</b>Die Logdatei enthält Informationen über den Verlauf der ausgeführten Aktionen.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Turnusplanung aufrufen
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t<form action = \"TurnusAdmin.php\">";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t<input type=\"Submit\" value=\"Turnusplanung\">";
echo "\n\t\t</td>";
echo "\n\t</form>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Turnusplanung:&nbsp;</b>In der Turnusplanung kann die Zuordnung der Turnusse zu den Kalenderwochen erstellt und geändert werden.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Ferienplanung aufrufen
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t<form action = \"FerienAdmin.php\">";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t<input type=\"Submit\" value=\"Ferienplanung\">";
echo "\n\t\t</td>";
echo "\n\t</form>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Ferienplanung:&nbsp;</b>In der Ferienplanung können die freien Tage (Feiertage, Ferien etc.) eines Schuljahres angelegt werden.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

echo "\n</table>";

mysql_close($db);

echo ladeOszKopf_u();

echo ladeLink("TurnusAdmin.php", "Turnus-Admin");
echo ladeLink("FerienAdmin.php", "Ferien-Admin");

echo ladeOszFuss();
?>