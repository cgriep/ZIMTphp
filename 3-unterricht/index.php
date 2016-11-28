<?php
session_start();

require("include/oszframe.inc.php");
require("include/evaluation.inc.php");
require("include/evaluation.vars.inc.php");
include("include/helper.inc.php");
include("include/stupla.inc.php");

echo ladeOszKopf_o("OSZ-Umfrage","OSZ IMT - Ihre Meinung ist gefragt!");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

if ( isset($_REQUEST['Status']))
  $Status = $_REQUEST['Status'];
else
  $Status = '';

//-------------------------------------------------------------------------
//------------------Erster Aufruf: Passworteingabe-------------------------
//-------------------------------------------------------------------------
if(!$Status)
{
	echo "\n\n<div align = \"center\"><b>[Anmeldung]</b></div>";
	echo "\n<table width = \"100%\" cellpadding = \"20\" border = \"0\">";
	echo "\n\t<tr height = \"100\">";
	echo "\n\t\t<td colspan = \"3\">&nbsp;</td>";
	echo "\n\t</tr>";
	echo "\n\t<tr>";
	echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
	echo "\n\t<form method = \"POST\" action = \"{$_SERVER['PHP_SELF']}\">";
	echo "\n\t\t<td id = \"rahmen_obreunli\" class = \"oszRahmen_bg\" nowrap align = \"center\">";
	echo "\n\t\t\t<p><b>Passworteingabe</b></p>";
	echo "\n\t\t\t<p><input type = \"text\" name = \"pwd\"></p>";
	echo "\n\t\t\t<input type = \"hidden\" name = \"Status\" value = \"Umfrage\">";
	echo "\n\t\t\t<input type = \"submit\" value = \"Absenden\">";
	echo "\n\t\t\t<input type = \"reset\" value = \"Abbrechen\">";
	echo "\n\t\t</td>";
	echo "\n\t</form>";
	echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
	echo "\n\t</tr>";
	echo "\n\t<tr>";
	echo "\n\t\t<td colspan = \"3\">&nbsp;</td>";
	echo "\n\t</tr>";
	echo "\n</table>";
}//Erster Aufruf->Passworteingabe

//-------------------------------------------------------------------------
//------------------Zweiter Aufruf: Umfrage durchfuehren-------------------
//-------------------------------------------------------------------------
if($Status == "Umfrage")
{
	$pwd = $_REQUEST['pwd'];
	if(pruefeZeichen($pwd))
	    dieMsg("Ungültiges Passwort!");
	    
	echo "\n\n<div align = \"center\"><b>[Stimmabgabe]</b></div>";
	echo "<br>";
	$sql = "SELECT * FROM T_eval_Umfragen WHERE Passwort = \"$pwd\";";
	$rs = mysql_query($sql);
	if(mysql_num_rows($rs) == 0)
		dieMsg("Für das Passwort $pwd ist keine Umfrage vorhanden!");

	$row = mysql_fetch_array($rs, MYSQL_ASSOC);
	mysql_free_result($rs);
	if($row['Gesperrt'] == 1)
		dieMsg("Die Umfrage ist bereits abgelaufen!");
	
	if ( isset($_REQUEST['Frage']))
	  $exFrage = $_REQUEST['Frage']; //Bereits vorhandene Antworten (wenn Fragen vergessen wurden)
	$wdh = false;
	if(isset($exFrage))
		$wdh = true;
	
	echo "\n<table width = \"100%\" cellpadding = \"10\">";
	echo "\n\t<tr>";
	echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
	echo "\n\t\t<td id = \"rahmen_r_obreunli\" class = \"oszRahmen_bg\" nowrap align = \"center\"><b>Klasse:</b>&nbsp;$row[Klasse]&nbsp;&nbsp;&nbsp;<b>Fach:</b>&nbsp;$row[Fach]&nbsp;&nbsp;&nbsp;<b>Lehrer/in:</b>&nbsp;" . ucwords($row['Lehrer']) . "</td>";
	echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
	echo "\n\t</tr>";
	echo "\n</table>";

	//Fragen einlesen
	$sql = "SELECT * FROM T_eval_Gruppen ORDER BY Gruppennummer;";
	$rsGruppe = mysql_query($sql);
	$counterFrage = 1;
	
	echo "\n\t<form method = \"POST\" action = \"{$_SERVER['PHP_SELF']}\">";
	//Schleife ueber alle Fragengruppen
	while($rowGruppe = mysql_fetch_array($rsGruppe, MYSQL_ASSOC))
	{
		echo "\n\t\t\t\t\t<div id = \"GruppeText\"><span class = \"Text-fett\">$rowGruppe[GruppeText]</class></div>";
		$sql = "SELECT * FROM T_eval_Fragen WHERE F_ID_Gruppe = $rowGruppe[ID_Gruppe] AND IstAktiv = 1 ORDER BY Fragennummer;";
		$rsFrage = mysql_query($sql, $db);
		//Schleife ueber alle Fragen
		while($rowFrage = mysql_fetch_array($rsFrage, MYSQL_ASSOC))
		{
			echo "\n\t\t\t\t\t<table style = \"width:100%\"; cellpadding = \"0\" cellspacing = \"0\" border = \"0\">";
	
			echo "\n\t\t\t\t\t\t<tr id = \"HoeheFrage\">";
			echo "\n\t\t\t\t\t\t\t<td></td>";
			echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" style = \"vertical-align:top\">&nbsp;<span class = \"Text\">$counterFrage.</span></td>";
			echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" colspan = \"" . ($rowFrage['FrageSel'] + 2) . "\"><span class = \"Text\">{$rowFrage['FrageText']}</span></td>";
			echo "\n\t\t\t\t\t\t\t<td></td>";
			echo "\n\t\t\t\t\t\t</tr>";
	
			echo "\n\t\t\t\t\t\t<tr id = \"HoeheAntwort\">";
			echo "\n\t\t\t\t\t\t\t<td></td>";
			echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\"></td>";
			echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" width = \"49%\" style = \"text-align:right;\"><span class = \"Text\">{$rowFrage['LinksText']}</span></td>";

			if($wdh)// Es wurden schon Fragen beantwortet (unvollstaendig ausgefuellte Befragung)
				foreach($exFrage as $line)
					if($line[1] == $rowFrage['ID_Frage'] && isset($line[0])) //Alte Frage stimmt in ID ueberein
						$alterWert = $line[0];

			for($i = 1; $i <= $rowFrage['FrageSel']; $i++)
			{
				echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" align = \"center\">";
				if($rowFrage['ID_Frage'] == 99)
					echo "$i<br>";
				if($wdh && $i == $alterWert)
					echo "&nbsp;<input type = \"radio\" name = \"Frage[$counterFrage][0]\" value = \"$i\" checked>&nbsp;";
				else
					echo "&nbsp;<input type = \"radio\" name = \"Frage[$counterFrage][0]\" value = \"$i\">&nbsp;";
				echo "</td>";
				echo "\n\t\t\t\t\t\t\t<input type = \"hidden\" name = \"Frage[$counterFrage][1]\" value = \"{$rowFrage['ID_Frage']}\">";
			}
			echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" width = \"49%\"><span class = \"Text\">{$rowFrage['RechtsText']}</span></td>";
			echo "\n\t\t\t\t\t\t\t<td></td>";
			echo "\n\t\t\t\t\t\t<tr>";
	
			echo "\n\t\t\t\t\t\t<tr id = \"AbstandFrage\">";
			echo "\n\t\t\t\t\t\t\t<td><img src = \"pixel.gif\" id = \"Seitenabstand\"></td>";
			echo "\n\t\t\t\t\t\t\t<td><img src = \"pixel.gif\" width = \"30px\" height = \"1px\"></td>";
			echo "\n\t\t\t\t\t\t\t<td width =  \"99%\" colspan = \"" . ($rowFrage['FrageSel'] + 2) . "\"></td>";
			echo "\n\t\t\t\t\t\t\t<td><img src = \"pixel.gif\" id = \"Seitenabstand\"></td>";
			echo "\n\t\t\t\t\t\t</tr>";
	
			echo "\n\t\t\t\t\t</table>";
			$counterFrage++;
		}//Schleife ueber alle Fragen
	}//Schleife ueber alle Fragengruppen
	echo "\n\t\t<input type = \"hidden\" name = \"Status\" value = \"Votum\">";
	echo "\n\t\t<input type = \"hidden\" name = \"pwd\" value = \"$pwd\">";
	echo "\n\t\t<div align = \"center\"><input type = \"submit\" value = \"Absenden\"></div>";
	echo "\n\t</form>";
}//Zweiter Aufruf->Umfrage durchfuehren

//-------------------------------------------------------------------------
//------------------Dritter Aufruf: Ergebnisse pruefen und eintragen-------
//-------------------------------------------------------------------------
if($Status == "Votum")
{
	$pwd = $_REQUEST['pwd'];
	if(pruefeZeichen($pwd))
	    dieMsg("Ungültiges Passwort!");
		  
	echo "\n\n<div align = \"center\"><b>[Stimmabgabe]</b></div>";
	//ID der Umfrage ermitteln	
	$sql = "SELECT ID_Umfrage, Gesperrt FROM T_eval_Umfragen WHERE Passwort = \"$pwd\";";
	$rs = mysql_query($sql);
	if(mysql_num_rows($rs) != 1)
		dieMsg("Das Eintragen der Daten ist fehlgeschlagen!");
	$row = mysql_fetch_array($rs, MYSQL_ASSOC);
	if($row[Gesperrt] == 1)
		dieMsg("Die Stimmabgabe für diese Umfrage ist gesperrt!");	
	$ID_Umfrage = $row[ID_Umfrage];
	mysql_free_result($rs);
	
	$Frage = $_REQUEST['Frage'];
	
	$NumFrage = 1;
	$i = 0;
	foreach($Frage as $line)
	{	    
	    if($line[0] != "" && (!istZahl($line[0]) || !istZahl($line[1])))//Votum abgegeben aber Antwortwert oder ID_Frage ist keine Zahl
		dieMsg("Das Eintragen der Daten ist fehlgeschlagen!");

	    if($line[0] == "")//kein Votum abgegeben
	    {
		$fehlendeFrage[$i] = $NumFrage;
		$i++;
	    }
	    else//Ueberpruefung: Ist ID_Frage und Antwortwert ok
	    {	
		$sql = "SELECT FrageSel FROM T_eval_Fragen WHERE ID_Frage = $line[1];";
		$rs = mysql_query($sql);	
		if(mysql_num_rows($rs) != 1)//Es gibt keine oder mehrere Fragen mit dieser ID
		    dieMsg("Das Eintragen der Daten ist fehlgeschlagen!");
	        $row = mysql_fetch_row($rs);
		mysql_free_result($rs);
		if($line[0] > $row[0] || $line[0] < 1)//Der Wert der Antwort liegt ausserhalb des Wertebereichs
		    dieMsg("Das Eintragen der Daten ist fehlgeschlagen!");
	    }
	    $NumFrage++;
	}
	if($i != 0)//Fragen wurden nicht beantwortet
	{
	    echo "\n<p>&nbsp;</p>";
	    echo "\n<table width = \"100%\" cellpadding = \"20\">";
	    echo "\n\t<tr>";
	    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
	    echo "\n\t\t<td id = \"rahmen_r_obreunli\" class = \"oszRahmen_bg\" nowrap>";
	    echo "Folgende Fragen wurden nicht beantwortet:";
	    foreach($fehlendeFrage as $fFrage)
		echo "&nbsp;[$fFrage]";
	    echo "<br><br><div align = \"center\"><b>Zum beantworten der fehlenden Fragen gehen Sie bitte mit dem unteren Button zurück!</b></div>";
	    echo "</td>";
	    echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
	    echo "\n\t</tr>";
	    echo "\n</table>";
	    
	    echo "\n\t<form method = \"POST\" action = \"{$_SERVER['PHP_SELF']}\">";
           echo "\n\t\t<input type = \"hidden\" name = \"Status\" value = \"Umfrage\">";
	    echo "\n\t\t<input type = \"hidden\" name = \"pwd\" value = \"$pwd\">";
	    $numFrage = 0;
	    foreach($Frage as $line)
	    {
	    	echo "\n\t\t<input type = \"hidden\" name = \"Frage[$numFrage][0]\" value = \"$line[0]\">";
	    	echo "\n\t\t<input type = \"hidden\" name = \"Frage[$numFrage][1]\" value = \"$line[1]\">";
		$numFrage++;
	    }
           echo "\n\t\t<div align = \"center\"><input type = \"submit\" value = \"Zurück\"></div>";
	    echo "\n\t</form>";
	}	
	else//Daten in DB uebernehemn
	{
		if(session_is_registered(voted))
			dieMsg("Sie haben schon abgestimmt!"); 	
		session_register("voted");

		foreach($Frage as $line)
		{
			if($line[1] != 99)//Frage nach Benotung des Unterrichts wird gesondert behandelt
			{
				$sql = "SELECT * FROM T_eval_Ergebnisse WHERE F_ID_Umfrage = $ID_Umfrage AND F_ID_Frage = $line[1] AND NumSel = $line[0];";
				$rs = mysql_query($sql);
				if(mysql_num_rows($rs) == 0)//Erstes Votum
				{
					mysql_free_result($rs);
					$sql = "INSERT INTO T_eval_Ergebnisse (F_ID_Umfrage, F_ID_Frage, NumSel, AnzStimmen) VALUES ($ID_Umfrage, $line[1], $line[0], 1);";
					mysql_query($sql);
				}
				else
				{
					mysql_free_result($rs);
					$sql = "UPDATE T_eval_Ergebnisse SET AnzStimmen = AnzStimmen +1 WHERE F_ID_Umfrage = $ID_Umfrage AND F_ID_Frage = $line[1] AND NumSel = $line[0];";
					mysql_query($sql);
				}
			}
	   		else//Frage nach Benotung (Nr.99)
	    		{
				$sql = "UPDATE T_eval_Umfragen SET Anz_Note_" . $line[0] . " = Anz_Note_" . $line[0] . " + 1 WHERE ID_Umfrage = $ID_Umfrage;";
				mysql_query($sql);
	    		}
		}//foreach
		$sql = "UPDATE T_eval_Umfragen SET AnzStimmen = AnzStimmen + 1 WHERE ID_Umfrage = $ID_Umfrage;";
		mysql_query($sql);
	    	echo "\n<p>&nbsp;</p>\n\t<table width = \"100%\" cellpadding = \"25\">\n\t\t<tr><td width = \"49%\">&nbsp;</td><td align = \"center>\" id = \"rahmen_r_obreunli\" class = \"oszRahmen_bg\" nowrap><b>Die Daten wurden übernommen, vielen Dank für Ihre Teilnahme!</b></td><td width = \"49%\">&nbsp;</td></tr>\n\t</table>";
	}//else (Daten in DB uebernehmen)
}//Dritter Aufruf->Ergebnisse pruefen und eintragen

echo ladeOszKopf_u();
//echo ladeLink("index.html","Home");
//echo ladeLink("dahin.html","Dahin");
echo ladeOszFuss();
?>