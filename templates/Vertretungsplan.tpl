<?php
/*
 * Vertretungsplan.php
 * Zeigt den Vertretungsplan für einen oder mehrere Klassen, Lehrer und Räume an
 * 
 * Parameter:
 * Mail - wenn gesetzt, werden die Pläne an die Betroffenen gemailt.
 * PDF - wenn gesetzt, werden die Pläne im PDF-Format ausgegeben.
 * 
 */
DEFINE("USE_KALENDER",1);

function VerschickeMail($Lehrer, $KW, $p)
{  
  global $db;
  require_once("phpmail.php");
  // $Plan ist ein PDF-Dokument
  // Dieses muss ausgelesen, beendet und für weitere Pläne neu gestartet werden
  PDF_close($p);
  $Plan = PDF_get_buffer($p);  
  $mail = new mime_mail();
  KuerzelToLehrer($Lehrer, $Name, $Vorname);
  $User = LehrerToUser($Name, $Vorname, $db);
  //$mail->to = $User."@oszimt.de";
  $mail->to = "griep@oszimt.de";
  $mail->from = $_SERVER["REMOTE_USER"].' <'.$_SERVER["REMOTE_USER"].'@oszimt.de>';
  $mail->headers = "Errors-To: ".$_SERVER["REMOTE_USER"]."@oszimt.de";
  $mail->subject = "[OSZIMT Vertretung] $KW. Kalenderwoche";
  $body = "Liebe/r Kollegin/Kollege $Name,\n".
    "anbei finden Sie aktuelle Änderungen an Ihrem Stundenplan für die " .
    "$KW. Kalenderwoche (Stand: ".date("d.m.Y H:i").")\n"; 
  $body .=  "\n".
    "mit freundlichen Grüßen\n".
    "ihre Abteilungsleitung am OSZ IMT\n\n".
    "bearbeitet durch: ".$_SERVER["REMOTE_USER"]."\n".
    "(automatisch generiert am ".date("d.m.Y H:i").". Aus technischen Gründen ist es ".
    "möglich, dass Sie mehrere E-Mails an einem Tag erhalten. Beachten Sie in diesem " .
    "Fall nur den aktuellsten Stand des Vertretungsplans. Wir bitten um Verständnis.)";
  $mail->add_attachment($Plan, "Vertretungsplan$Lehrer-$KW.KW.pdf", "application/pdf");
  $mail->body = $body;
  if ( ! $mail->send() )
    echo "Fehler beim Mailen: Lehrer ".$Lehrer."<br />";
  else
    echo '<span class="unterlegt">Mail an '.$mail->to." gesendet.</span><br />";
}

if ( isset($_REQUEST["PDF"]) || isset($_REQUEST["Mail"]))
{
  $PDF = true;
  include("include/config.php");
  include("include/pdf.inc.php");
  $p = PDF_new();
  PDF_open_file($p);
  PDF_set_info($p, "Creator", "OSZIMT");
  PDF_set_info($p, "Author", "Christoph Griep");
  $Fonts = LadeFonts($p);
  $Wochentagnamen[0] = "Sonntag";
  $Wochentagnamen[1] = "Montag";
  $Wochentagnamen[2] = "Dienstag";
  $Wochentagnamen[3] = "Mittwoch";
  $Wochentagnamen[4] = "Donnerstag";
  $Wochentagnamen[5] = "Freitag";
  $Wochentagnamen[6] = "Samstag";
}
else
{
  $PDF = false;
  $Ueberschrift = "Vertretungsplan";
  $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
  include("include/header.inc.php");
  echo '<tr><td>';
}
/* ----
  Vertretungen anzeigen
  Wochenübersicht für die gewählte Art
*/

include("include/helper.inc.php");
include("include/stupla.inc.php");
include("include/namen.inc.php");
include("include/Vertretungen.inc.php");

function neueTabelle()
{
      $Tabelle = array();
      $Tabelle[0][0]["value"] = "Block";
      $Tabelle[0][0]["Breite"] = 25;
      $Tabelle[0][0]["Fuellung"] = 0.8;
      $Tabelle[0][1]["value"] = "Klasse";
      $Tabelle[0][1]["Breite"] = 40;
      $Tabelle[0][1]["Fuellung"] = 0.8;
      $Tabelle[0][2]["value"] = "Fach";
      $Tabelle[0][2]["Breite"] = 50;
      $Tabelle[0][2]["Fuellung"] = 0.8;
      $Tabelle[0][3]["value"] = "Lehrer";
      $Tabelle[0][3]["Breite"] = 65;
      $Tabelle[0][3]["Fuellung"] = 0.8;
      $Tabelle[0][4]["value"] = "Lehrer neu";
      $Tabelle[0][4]["Breite"] = 65;
      $Tabelle[0][4]["Fuellung"] = 0.8;
      $Tabelle[0][5]["value"] = "Raum";
      $Tabelle[0][5]["Breite"] = 40;
      $Tabelle[0][5]["Fuellung"] = 0.8;
      $Tabelle[0][6]["value"] = "Bemerkung";
      $Tabelle[0][6]["Breite"] = 200;
      $Tabelle[0][6]["Fuellung"] = 0.8;
  return $Tabelle;
}

function fuelleTag($Datum, $Montag, $Tag, $Block)
{
  global $Wochentagnamen;
  global $PDF;
  global $p;
  global $Tabelle;
  global $Fonts;
  while ( $Tag != date("w",$Datum) )
  {
    // auf 6 Blöcke auffüllen
    if ( $Block < 6 && $Block != 0 )
    {
      for($i = $Block; $i < 7;$i++)
        // Normalen Stundenplan anzeigen?
        if ( $PDF)
        {
          $Tabelle[Count($Tabelle)][0]["value"] = $i;
          $Tabelle[Count($Tabelle)-1][1]["value"] = "";
          $Tabelle[Count($Tabelle)-1][2]["value"] = "";
          $Tabelle[Count($Tabelle)-1][3]["value"] = "";
          $Tabelle[Count($Tabelle)-1][4]["value"] = "";
          $Tabelle[Count($Tabelle)-1][5]["value"] = "";
          $Tabelle[Count($Tabelle)-1][6]["value"] = "";
          $Tabelle[Count($Tabelle)-1][6]["Breite"] = 200;
        }
        else
          echo "<tr><td>$i</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    }
    $Block = 1;
    $TagDatum = strtotime("+".$Tag." days",$Montag);
    if ( $Tag != 5 )
      if ( ! $PDF )
        echo '<tr><td class="Zwischenueberschrift" colspan="7">'.$Wochentagnamen[date("w",$TagDatum)].
         ", ".date("d.m.Y",$TagDatum)."</td></tr>\n";
      else
      {
        if ( Count($Tabelle) != 0 ) pdf_Tabelle($p, $Tabelle);
        pdf_continue_text($p,"");
        pdf_continue_text($p,"");
        pdf_setfont($p, $Fonts["Arial"]["B"], 12.0);
        pdf_show_xy($p,$Wochentagnamen[date("w",$TagDatum)].", ".date("d.m.Y",$TagDatum),
          50,pdf_get_value($p, "texty"));
        pdf_continue_text($p,"");
        pdf_setfont($p, $Fonts["Arial"]["N"], 8.0);        
        $Tabelle = neueTabelle();
      }
    $Tag++;
  }
  return $Tag;
}

$Felder = array("Klasse", "Lehrer", "Raum");

if ( ! isset($_REQUEST["Woche"]) && isset($_REQUEST["Datum"]))
{
  $Datum = explode(".",$_REQUEST["Datum"]);
  if ( ! checkdate($Datum[1],$Datum[0],$Datum[2]) )
    $_REQUEST["Woche"] = getID_Woche();
  else
    $_REQUEST["Woche"] = getID_Woche(mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]));
}
unset($Art);
foreach ( $Felder as $dieArt )
  if ( isset($_REQUEST[$dieArt]) )
    $Art = $dieArt;

if ( ! isset($Art) && isset($_REQUEST["Art"]) )
{
  $Art = $_REQUEST["Art"];
  $_REQUEST[$Art] = $_REQUEST["Wofuer"];
}
if ( ! isset($_REQUEST["Print"]) && ! $PDF )
{
  foreach ( $Felder as $dieArt )
  {
    echo '<form action="'.$_SERVER["PHP_SELF"].'" name="Eingabe'.$dieArt.'" method="post" class="Verhinderung">';
    echo "Vertretungplan von ".$Art.' <select name="'.$dieArt.'" size="1">';
    if ( $dieArt == "Lehrer") 
      $FeldArt ="Name, Vorname, Lehrer";
    else
      $FeldArt = $dieArt;
    $query = mysql_query("SELECT DISTINCT $FeldArt FROM T_StuPla ORDER BY $FeldArt");
    while ($inhalt = mysql_fetch_array($query)) 
    {
    	echo '<option ';
    	if ( $dieArt == "Lehrer")
    	{
    		echo 'value="'.$inhalt[2].'">';
    		echo trim($inhalt[0].", ".$inhalt[1]);
    	}  
    	else
    	  echo ">".$inhalt[0];
    	echo "</option>\n";
    } // while
    mysql_free_result($query);
    echo "</select>\n";
    echo 'für Datum <input type="Text" name="Datum" value="'.date("d.m.Y").'" size="10" maxlength="10"';
    echo " onClick=\"popUpCalendar(this,Eingabe{$dieArt}['Datum'],'dd.mm.yyyy')\" ";
    echo "onBlur=\"autoCorrectDate('Eingabe$dieArt','Datum' , false )\"";
    echo '/>
  <input type="Submit" value="Anzeigen"/>
  </form>';
  } // foreach
} // if
// TODO
if ( isset($_REQUEST["Verhinderung_id"]) && is_numeric($_REQUEST["Verhinderung_id"]) )
{
  $Wochen = array();
  $Plaene = array();
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE F_Verhinderung_id={$_REQUEST["Verhinderung_id"]} ORDER BY Datum");
  if ( isset($_REQUEST["Tag"]) && is_numeric($_REQUEST["Tag"]))
      $wid = getID_Woche($_REQUEST["Tag"]);
  while ( $v = mysql_fetch_array($query))
  {
    $w = getID_Woche($v["Datum"]);
    if ( $w == $wid ) foreach ( array("Klasse", "Lehrer") as $Feld )
    {
      $a = array($Feld, $v[$Feld]);
      if ( ! in_array($a, $Plaene) && $v[$Feld] != "" )
        $Plaene[] = $a;
      $a = array($Feld, $v[$Feld."_Neu"]);
      if ( ! in_array($a, $Plaene) && $v[$Feld."_Neu"] != "" )
        $Plaene[] = $a;
      if ( !is_array($Wochen[$Feld][$v[$Feld]]) || ! in_array($w, $Wochen[$Feld][$v[$Feld]]) )
        $Wochen[$Feld][$v[$Feld]][] = $w;
      if ( !is_array($Wochen[$Feld][$v[$Feld."_Neu"]]) || ! in_array($w, $Wochen[$Feld][$v[$Feld."_Neu"]]) )
        $Wochen[$Feld][$v[$Feld."_Neu"]][] = $w;
    }
  }
  mysql_free_result($query);
}
elseif ( isset($_REQUEST["Datum"]) && ! isset($Art) )
{
  $Wochen = array();
  $Plaene = array();
  // alle Vertretung an einem bestimmten Tag
  $Datum = explode(".",$_REQUEST["Datum"]);
  $Datum = mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]);
  $w = getID_Woche($Datum);
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Datum=$Datum");
  while ( $v = mysql_fetch_array($query))
  {
    foreach ( array("Klasse", "Lehrer") as $Feld )
    {
      $a = array($Feld, $v[$Feld]);
      if ( ! in_array($a, $Plaene) && $v[$Feld] != "")
        $Plaene[] = $a;
      $a = array($Feld, $v[$Feld."_Neu"]);
      if ( ! in_array($a, $Plaene) && $v[$Feld."_Neu"] != "")
        $Plaene[] = $a;
      if ( !is_array($Wochen[$Feld][$v[$Feld]]) || ! in_array($w, $Wochen[$Feld][$v[$Feld]]) )
        $Wochen[$Feld][$v[$Feld]][] = $w;
      if ( !is_array($Wochen[$Feld][$v[$Feld."_Neu"]]) || ! in_array($w, $Wochen[$Feld][$v[$Feld."_Neu"]]) )
        $Wochen[$Feld][$v[$Feld."_Neu"]][] = $w;
    }
  }
  mysql_free_result($query);
}
elseif ( isset($Art) && isset($_REQUEST[$Art]))
{
  $Plaene[] = array($Art, $_REQUEST[$Art]);
  foreach (explode(",",$_REQUEST["Woche"]) as $Woche )
    $Wochen[$Art][$_REQUEST[$Art]][] = $Woche;
}
$LehrerDabei = false;
include_once("include/Abteilungen.class.php");
$Abteilung = new Abteilungen($db);

if ( isset($Plaene) && is_array($Plaene) )
foreach ( $Plaene as $planeintrag )
{
  $Art = $planeintrag[0];
  $Wer = $planeintrag[1];
  if ( $Art == "Lehrer")
  {
  	   $LehrerDabei = true;
       KuerzelToLehrer($Wer,$LehrerName, $LehrerVorname);
  }
  else
    $LehrerName = $Wer;
  if ( $PDF )
    PDF_set_info($p, "Title", "Vertretungsplan $Art $LehrerName");
  $AlleWochen = $Wochen[$Art][$Wer];
  foreach ( $AlleWochen as $Woche )
  {
    $Montag = getMontag($Woche);
    $Version = getAktuelleVersion($Montag);
    $Turnusliste = array();
    getTurnusliste($Woche, $Turnusliste);
    $sql = "SELECT * FROM T_Vertretungen WHERE (".
      $Art."='".mysql_real_escape_string($Wer)."' OR ".
      $Art."_Neu='".mysql_real_escape_string($Wer)."') ";
    $sql .= " AND Datum BETWEEN $Montag AND ".
      strtotime("+5 days",$Montag)." ORDER BY Datum,Stunde";
    $query = mysql_query($sql);
    if ( ! $PDF )
    {
      echo "<hr />\n";
      echo '<table class="Liste">';
      echo '<tr><th colspan="7"><span class="ueberschrift">Vertretungen für '.$Art." ";
      echo $LehrerName;
      echo '</span> <span class="smallmessage_gr">';
      echo getKW($Woche).". KW (Turnus ";
      echo implode(",",$Turnusliste).")";
      echo '<br />';
      echo "Stand ".date("d.m.Y H:i");
      $sql = "SELECT * FROM T_Verhinderungen WHERE Von <= ".strtotime("+5 days",$Montag).
        " AND Bis >= ".
        $Montag." AND Art='$Art' AND Wer='$Wer' ORDER BY Von";
      $q = mysql_query($sql);
      while ( $v = mysql_fetch_array($q) )
      {
        echo "<br />".$Gruende[$v["Grund"]];
        if ( $v["Von"] != $v["Bis"] )
          echo " vom ";
        else
          echo " am ";
        echo date("d.m.Y",$v["Von"]);
        if ( $v["Von"] != $v["Bis"] )
          echo " bis ".date("d.m.Y",$v["Bis"]);
      }
      mysql_free_result($q);
      echo "</span>\n";
      echo "</th></tr>\n";
      echo '<tr><th>Block</th><th>Klasse</th><th>Fach</th><th>Lehrer</th><th>Lehrer neu';
      echo '</th><th>Raum</th><th>Bemerkung</th>';
      echo "</tr>\n";
    }
    else
    {
      // PDF-Kopf
      PDF_begin_page($p, 595, 842);
      $bb = "oszimtlogo300.jpg";
      if ( file_exists($bb) ) {
        $pim = pdf_open_image_file($p, "jpeg", $bb);
        pdf_place_image($p, $pim, 475, 800, 0.2);
        pdf_close_image($p, $pim);
      }
      pdf_setfont($p, $Fonts["Arial"]["N"], 6.0);
      pdf_show_xy($p, "Berufliches Gymnasium, Berufsoberschule,",450, 795);
      pdf_continue_text($p, "Fachoberschule, Berufsfachschule,");
      pdf_continue_text($p, "Fachschule und Berufsschule");      
      pdf_continue_text($p, "Haarlemer Straße 23-27, 12359 Berlin-Neukölln");
      pdf_continue_text($p, "Tel.: 030-606-4097     Fax: 030-606-2808");
      pdf_continue_text($p, "http://www.oszimt.de");
      pdf_setfont($p, $Fonts["Arial"]["B"], 16.0);
      pdf_show_xy($p, "Vertretungsplan für $Art $LehrerName",50, 810);
      pdf_continue_text($p,"");
      pdf_setfont($p, $Fonts["Arial"]["N"], 10.0);
      pdf_show($p,getKW($Woche).". KW (Turnus ".implode(",",$Turnusliste).")");
      pdf_continue_text($p, "Stand: ".date("d.m.Y H:i"));
      pdf_setfont($p, $Fonts["Arial"]["O"], 10.0);
      $sql = "SELECT * FROM T_Verhinderungen WHERE Von <= ".strtotime("+5 days",$Montag).
        " AND Bis >= ".
        $Montag." AND Art='$Art' AND Wer='{$Wer}' ORDER BY Von";
      $q = mysql_query($sql);
      while ( $v = mysql_fetch_array($q) )
      {
        pdf_continue_text($p, $Gruende[$v["Grund"]]);
        if ( $v["Von"] != $v["Bis"] )
          pdf_show($p," vom ");
        else
          pdf_show($p," am ");
        pdf_show($p,date("d.m.Y",$v["Von"]));
        if ( $v["Von"] != $v["Bis"] )
          pdf_show($p," bis ".date("d.m.Y",$v["Bis"]));
      }
      mysql_free_result($q);
      pdf_setfont($p, $Fonts["Arial"]["N"], 10.0);
      $Tabelle = array();
    }
    $Datum = 0;
    $Block = 0;
    $Tag = 0;
    $LBlock = 0;
    while ( $row = mysql_fetch_array($query) )
    {
      if ($Datum != $row["Datum"] )
      {
        $Tag = fuelleTag($row["Datum"], $Montag, $Tag, $Block);
        $Block = 1;
        $LBlock = 0;
        $Datum = $row["Datum"];
      }
      for($i = $Block; $i < $row["Stunde"];$i++)
        // Normalen Stundenplan anzeigen?
        if ( $PDF )
        {
          $Tabelle[Count($Tabelle)][0]["value"] = $i;
          $Tabelle[Count($Tabelle)-1][1]["value"] = "";
          $Tabelle[Count($Tabelle)-1][2]["value"] = "";
          $Tabelle[Count($Tabelle)-1][3]["value"] = "";
          $Tabelle[Count($Tabelle)-1][4]["value"] = "";
          $Tabelle[Count($Tabelle)-1][5]["value"] = "";
          $Tabelle[Count($Tabelle)-1][6]["value"] = "";
          $Tabelle[Count($Tabelle)-1][6]["Breite"] = 200;
        }
        else
          echo "<tr><td>$i</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
              <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
      if ( ! $PDF )
      {
        echo "<tr>\n";
        echo '<td>';
        if ( $LBlock != $row["Stunde"] )
          echo $row["Stunde"];
        $LBlock = $row["Stunde"];
        echo "</td>\n";
        echo '<td>';
        if ( $row["Klasse"] == "" )
          echo $row["Klasse_Neu"];
        else
          echo $row["Klasse"];
        echo "</td>\n";
        echo '<td>';
        echo $row["Fach"];
        if ( $row["Fach_Neu"] != "" && $row["Fach"]!= $row["Fach_Neu"])
          echo "&rarr;".$row["Fach_Neu"];
        echo "</td>\n";
        KuerzelToLehrer($row["Lehrer"],$Name, $Vorname);
        echo '<td>'.$Name."</td>\n";
        echo '<td>';
        KuerzelToLehrer($row["Lehrer_Neu"], $Name, $Vorname);
        if ( $row["Lehrer_Neu"] != $row["Lehrer"] )
          echo $Name;
        echo "</td>\n";
        echo "<td>";
        if ( $row["Raum_Neu"] != $row["Raum"] && $row["Raum_Neu"] != "")
          echo $row["Raum"]."&rarr;";
        echo "{$row["Raum_Neu"]}</td>\n";
        echo' <td>';
        $s = "";
        if ( $row["Raum_Neu"] == "" &&
             $row["Klasse_Neu"] == "" &&
             $row["Lehrer_Neu"] == "" &&
             $row["Fach_Neu"] == "" )
          $s = "entfällt\n";
        else
        {
          if ( $row["Lehrer"] != $row["Lehrer_Neu"] )
          {
            if ( $row["Art"] == VERTRETUNG_TEILUNGAUFHEBEN )
              $s .= $row["Lehrer_Neu"]." allein\n";
            if ( $row["Art"] == VERTRETUNG_ZUSATZKLASSE )
              $s .= "neben regulärem Unterricht von ".$row["Lehrer_Neu"]."\n";
          }
          if ( $row["Fach"] != "" && $row["Fach"] != $row["Fach_Neu"] )
            $s .= 'Unterrichtsänderung: '.$row["Fach_Neu"]."\n";
          if ( $row["Fach"] == "" && $row["Fach"] != $row["Fach_Neu"] )
            $s .= 'Zusätzlicher Block: '.$row["Fach_Neu"]."\n";
          /*
          if ( $row["Raum"] != $row["Raum_Neu"] )
          {
            if ( $row["Fach"] != "" )
              $s .= 'Verlegung nach '.$row["Raum_Neu"]."\n";
            else
              $s .= 'Reservierung '.$row["Raum_Neu"]."\n";
          }
          */
        }
        if ($row["Bemerkung"]!= "" )
          $s .= trim($row["Bemerkung"]);
        echo '<div style="float:right"><a href="VertretungBemerkung.php';
        echo "?Vertretung_id=".$row["Vertretung_id"];
        echo '" target="_blank">';
        echo '<img border="0" src="edit_small.gif"/></a></div>'."\n";
        echo nl2br(trim($s));
        echo "</td>\n";
        echo "</tr>\n";
      }
      else
      {
        if ( $LBlock != $row["Stunde"] )
          $Tabelle[Count($Tabelle)][0]["value"] = $row["Stunde"];
        else
          $Tabelle[Count($Tabelle)][0]["value"] = "";
        $LBlock = $row["Stunde"];
        if ( $row["Klasse"] == "" )
          $Tabelle[Count($Tabelle)-1][1]["value"] = $row["Klasse_Neu"];
        else
          $Tabelle[Count($Tabelle)-1][1]["value"] = $row["Klasse"];
        $Tabelle[Count($Tabelle)-1][2]["value"] = $row["Fach"];
        if ( $row["Fach_Neu"] != "" && $row["Fach"]!= $row["Fach_Neu"])
          $Tabelle[Count($Tabelle)-1][2]["value"] = " {Symbol:174} ".$row["Fach_Neu"];
        $Tabelle[Count($Tabelle)-1][2]["Breite"] = 50;
        KuerzelToLehrer($row["Lehrer"],$Name, $Vorname);
        $Tabelle[Count($Tabelle)-1][3]["value"] = $Name;
        $Tabelle[Count($Tabelle)-1][3]["Breite"] = 65;          
        KuerzelToLehrer($row["Lehrer_Neu"], $Name, $Vorname);
        if ( $row["Lehrer_Neu"] != $row["Lehrer"] )
          $Tabelle[Count($Tabelle)-1][4]["value"] = $Name;
        else
          $Tabelle[Count($Tabelle)-1][4]["value"] = "";
        $Tabelle[Count($Tabelle)-1][4]["Breite"] = 65;
        $Tabelle[Count($Tabelle)-1][5]["value"] = $row["Raum_Neu"];        
        $Tabelle[Count($Tabelle)-1][5]["Breite"] = 40;
        if ( $row["Raum_Neu"] != $row["Raum"] && $row["Raum_Neu"] != "")
          $Tabelle[Count($Tabelle)-1][5]["value"] = $row["Raum"]." {Symbol:174} ".$row["Raum_Neu"];
        $Tabelle[Count($Tabelle)-1][6]["value"] = "";
        $Tabelle[Count($Tabelle)-1][6]["Breite"] = 200;
        if ( $row["Raum_Neu"] == "" &&
             $row["Klasse_Neu"] == "" &&
             $row["Lehrer_Neu"] == "" &&
             $row["Fach_Neu"] == "" )
          $Tabelle[Count($Tabelle)-1][6]["value"] .= 'entfällt';
        else
        {
          if ( $row["Lehrer"] != $row["Lehrer_Neu"] )
          {
            if ( $row["Art"] == VERTRETUNG_TEILUNGAUFHEBEN )
              $Tabelle[Count($Tabelle)-1][6]["value"].= $row["Lehrer_Neu"]." allein";
            if ( $row["Art"] == VERTRETUNG_ZUSATZKLASSE )
              $Tabelle[Count($Tabelle)-1][6]["value"].= "neben regulärem Unterricht von ".$row["Lehrer_Neu"];

            /*
            if ( $row["Lehrer_Neu"] != "" )
              $Tabelle[Count($Tabelle)-1][6]["value"].= 'Vertretung durch '.$row["Lehrer_Neu"];
            else
            {
              // Teilung aufgehoben
              $eintrag = liesStundenplanEintragMitVertretung($db, "Klasse",
                $row["Klasse_Neu"], $row["Datum"], $row["Stunde"]);
              foreach ($eintrag as $turnus )
                if ( $turnus["Lehrer"] != "*" )
                  $Tabelle[Count($Tabelle)-1][6]["value"].= $turnus["Lehrer"]." allein";
            }
            */
          }
          /*
          if ( $row["Fach"] != "" && $row["Fach"] != $row["Fach_Neu"] )
            $Tabelle[Count($Tabelle)-1][6]["value"] .= 'Unterrichtsänderung: '.$row["Fach_Neu"];
          if ( $row["Fach"] == "" && $row["Fach"] != $row["Fach_Neu"] )
            $Tabelle[Count($Tabelle)-1][6]["value"] .= 'Zusätzlicher Block: '.$row["Fach_Neu"];
          if ( $row["Raum"] != $row["Raum_Neu"] )
          {
            if ( $row["Fach"] != "" )
              $Tabelle[Count($Tabelle)-1][6]["value"] .= 'Verlegung nach '.$row["Raum_Neu"];
            else
              $Tabelle[Count($Tabelle)-1][6]["value"] .= 'Reservierung '.$row["Raum_Neu"];
          }
          */
        }
        if (trim($row["Bemerkung"])!= "" )
          $Tabelle[Count($Tabelle)-1][6]["value"] .= " (".trim($row["Bemerkung"]).")";
      }
      $Block = $row["Stunde"]+1;
    }
    fuelleTag(strtotime("+5 days",$Montag), $Montag, $Tag, $Block);
    if ( ! $PDF )
      echo "</table>\n";
    else
    {
      pdf_Tabelle($p, $Tabelle);
      PDF_end_page($p);
      if ( isset($_REQUEST["Mail"]) )
      {
      	if ( $Art == "Lehrer" ) 
          VerschickeMail($Wer, getKW($Woche), $p);
        else
          PDF_close($p);
        PDF_delete($p);
        $p = PDF_new();
        PDF_open_file($p);
        PDF_set_info($p, "Creator", "OSZIMT");
        PDF_set_info($p, "Author", "Christoph Griep");
        $Fonts = LadeFonts($p);
      }
    }
  } // Wochendurchlauf
  if ( ! $PDF )
  {
    echo '<a href="'.$_SERVER["PHP_SELF"]."?PDF=1";
    foreach ( $_REQUEST as $key => $value )
      echo "&$key=$value";
    if ( is_array($_REQUEST["Woche"]) )
      echo "&Woche=".implode(",",$_REQUEST["Woche"]);
    echo '">PDF-Version zum Ausdrucken</a> ';
    if ( $LehrerDabei )
    {
      if ( $Abteilung->isAbteilungsleitung() || $_SERVER["REMOTE_USER"]=="Seidel")
      {	  
        echo '/ <a href="'.$_SERVER["PHP_SELF"]."?Mail=1";
        foreach ( $_REQUEST as $key => $value )
          echo "&$key=$value";
        if ( is_array($_REQUEST["Woche"]) )
          echo "&Woche=".implode(",",$_REQUEST["Woche"]);
        echo '" target="_blank">Pläne an die angezeigten Lehrer per Mail senden</a> ';
      }
    }
  } // wenn nicht PDF
}
if ( ! $PDF )
{
  echo '</td></tr>';
  include("include/footer.inc.php");
}
else
{
  @PDF_close($p);	
  if ( !isset($_REQUEST["Mail"]))
  {
    
    $buf = PDF_get_buffer($p);
    $len = strlen($buf);
    header("Content-type: application/pdf");
    header("Content-Length: $len");
    header("Content-Disposition: inline; filename=Vertretungsplan$LehrerName.pdf");
    print $buf;
  }
  else
    echo '<br />Sie können dieses Fenster nun <a href="javascript:window.close()">schließen</a>.';
  PDF_delete($p);
}
?>