<?php
if(isset($_REQUEST['sent']))
  $sent = $_REQUEST['sent'];//sent = true -> Zweiter Aufruf des Skripts ueber PHP_SELF
else
  $sent = false;
if(isset($_REQUEST['Fach']))
  $Fach = $_REQUEST['Fach'];
if(isset($_REQUEST['Klasse']))
  $Klasse = urldecode($_REQUEST['Klasse']);
if(isset($_REQUEST['Turnus']))
  $Turnus = $_REQUEST['Turnus'];
if(isset($_REQUEST['Version']))
  $OnlineVersion = (int)$_REQUEST['Version'];
if(isset($_REQUEST['Druck']))
  $Druck = $_REQUEST['Druck'];//$Druck = true -> Druckansicht
else
  $Druck = false;

//Cookie setzen (Speicherung der Auswahl fuer Fach und Turnus)
if(isset($Turnus) && isset($Fach) && $sent)
{
  $cookie_fach_string = implode(";",$Fach);
  $cookie_fach_string .= ";$Klasse";
  $cookie_turnus_string = implode(";",$Turnus);
  setcookie("cookie_fach",$cookie_fach_string,time() + 24*60*60*90);
  setcookie("cookie_turnus",$cookie_turnus_string,time() + 24*60*60*90);
}
//Cookie lesen  (Lesen evtl. vorhandener Auswahlen fuer Fach und Turnus)
if(isset($_COOKIE['cookie_fach']) && strpos($_COOKIE['cookie_fach'],$Klasse))//Cookie nur lesen wenn es fuer diese Klasse erstellt wurde
{
  $cookie_fach   = explode(";",$_COOKIE['cookie_fach']);
  $cookie_turnus = explode(";",$_COOKIE['cookie_turnus']);
}

//Include-Dateien
include_once("include/oszframe.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");
include_once("include/Vertretungsplan.inc.php");

//Initialisierungen
global $PHP_SELF;
$Wochentag = array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");
$BZeit     = array("8:00-9:30","9:45-11:15","11:45-13:15","13:30-15:00","15:15-16:45","17:00-18:30");
$sqlturnus = "";
$sqlfach   = "";

if(!isset($Klasse))
  dieMsgLink("Keine Angabe für Klasse!","KPlan.php","Klassenübersicht");
else
  if(pruefeZeichen($Klasse))
    dieMsgLink("Falsche Parameter!","KPlan.php","Klassenübersicht");
  
if($sent)//Zweiter Aufruf des Skripts ueber PHP_SELF
{
  if(!isset($Fach))
    dieMsgLink("Keine Angabe für Fach!","KPlan.php","Klassenübersicht");
  foreach($Fach as $Wert)
    if(pruefeZeichen($Wert))
      dieMsgLink("Falsche Parameter!","KPlan.php","Klassenübersicht");

  if(!isset($Turnus))
    dieMsgLink("Keine Angabe für Turnus!","KPlan.php","Klassenübersicht");
  foreach($Turnus as $Wert)
    if(pruefeZeichen($Turnus))
      dieMsgLink("Falsche Parameter!","KPlan.php","Klassenübersicht");

  //SQL-String fuer Turnus erzeugen
  $sqlturnus = " AND (Turnus = \"" . implode("\" OR Turnus = \"", $Turnus) . "\")";
  //SQL-String fuer Fach erzeugen
  $sqlfach = " AND (Fach = \"" . implode("\" OR Fach = \"", $Fach) . "\")";
}

//css-Einstellungen
if($Druck)
{
  $rahmen_farbe    = "ra_sw";
  $zelle_beschr_bg = "zelle_beschr_bg_dr";
  $zelle_inhalt_bg = "zelle_inhalt_bg_dr";
}
else
{
  $rahmen_farbe    = "ra_bl";
  $zelle_beschr_bg = "zelle_beschr_bg";
  $zelle_inhalt_bg = "zelle_inhalt_bg";
}

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
if(!$db)
  dieMsg("Keine Verbindung zur Datenbank möglich!");
@mysql_select_db($DBname,$db);

if(isset($OnlineVersion))//Ueberpruefung: Gibt es diese Version? 
{   
  $sql = "SELECT * FROM T_StuPla WHERE Version = $OnlineVersion;";
  $rs = @mysql_query($sql, $db);
  if(!@mysql_num_rows($rs))
    dieMsg("Zur Zeit sind keine Daten abrufbar!");	
}

$linkVersion = -1;
$aktuelleVersion = getAktuelleVersionWE();

if($aktuelleVersion == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");
if(!isset($OnlineVersion))//Wenn kein Parameter Version zeige aktuelle Version
  $OnlineVersion = $aktuelleVersion;

//Gueltigkeits-Timestamp der Online-Version aus DB holen
$OnlineGueltigAb = getGueltigAbVersion($OnlineVersion);

if($OnlineVersion == $aktuelleVersion)
  $linkVersion = getNaechsteVersion($OnlineVersion);
else
  $linkVersion = getAktuelleVersionWE();

//Gueltigkeits-Timestamp der Link-Version aus DB holen
if($linkVersion != -1)
  $naechsteGueltigAb = getGueltigAbVersion($linkVersion);

if(!$sent)//Erster Aufruf
{
  //Sind der Klasse mehrere Turnusse zugeordnet (z.B. a/b)?
  $sql = "SELECT DISTINCT Turnus FROM T_StuPla WHERE Klasse = \"" . $Klasse . "\" AND Version = " . $OnlineVersion;
  $rs = @mysql_query($sql, $db);
  $AnzTurnus = @mysql_num_rows($rs);
  @mysql_free_result($rs);
}

echo ladeOszKopf_o("OSZ IMT Stundenpläne der Klassen", "keine", $Druck); 

//------------------------------------------------------------------------------
//Aufbau der Auswahltabelle Turnus/Fach (z.B fuer OG), nur wenn der Klasse 
//mehrere Turnusse zugeordnet sind und das Skript zum ersten Mal aufgerufen wird.
//------------------------------------------------------------------------------
if(isset($AnzTurnus) && $AnzTurnus > 1 && !$sent) 
{
  $zeigeplan = false;
  //Ermittlung der Faecheranzahl
  $sql = "SELECT DISTINCT Fach FROM T_StuPla WHERE Klasse = \"" . $Klasse . "\" AND Version = " . $OnlineVersion . " ORDER BY Fach;";
  $rs = @mysql_query($sql, $db);
  $AnzFach=0;
  while($row = @mysql_fetch_row($rs))
  {
    $tmpFach[$AnzFach] = $row[0];
    $AnzFach++;
  }
  @mysql_free_result($rs);
  $AnzZeilen  = ceil(sqrt($AnzFach));
  $AnzSpalten = ceil($AnzFach / $AnzZeilen);
  //Ausgabe der Tabelle
  echo "\n\n<table width = \"100%\" height = \"1%\" border = \"0\" cellspacing = \"0\">";
  echo "\n\t<tr>";
  echo "\n\t\t<td height = \"1%\">&nbsp;</td>\n\t\t<td colspan = \"" . ($AnzSpalten+1) . "\" align = \"center\" valign = \"bottom\" nowrap><span class = \"ueberschrift\"><br>Auswahl für Klasse <b>$Klasse</b></span><br><br><span class = \"p_small\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$OnlineGueltigAb) . "</b></span></span></td>\n\t\t<td>&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n\t<tr>";
  echo "\n\t\t<td>&nbsp;</td>";
  echo "\n\t\t<td class = \"$zelle_beschr_bg $rahmen_farbe x1111\" id = \"form_ausw_beschr\">Turnus</td>";
  echo "\n\t\t<td colspan = \"" . ($AnzSpalten) . "\" class = \"$zelle_beschr_bg $rahmen_farbe x1110\" id = \"form_ausw_beschr\">Fach</td>";
  echo "\n\t\t<td>&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n\t<tr>";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t\t<td class = \"$zelle_inhalt_bg x0101 $rahmen_farbe\" id = \"form_ausw_inhalt\">";
  echo "\n\t\t\t<form action=\"". $PHP_SELF . "\" method=\"post\" name = \"Formular\">";
  $sql = "SELECT DISTINCT Turnus FROM T_StuPla WHERE Klasse = \"" . $Klasse . "\" AND Version = " . $OnlineVersion . " ORDER BY Turnus;";
  $rs = @mysql_query($sql, $db);
  while($row = @mysql_fetch_row($rs))
  {
    if(isset($cookie_turnus) && is_array($cookie_turnus) && in_array($row[0],$cookie_turnus))
      echo "\n\t\t\t<input type=\"checkbox\" name=\"Turnus[]\" value = \"" . $row[0] . "\" checked><span class = \"checkboxtext\">" . $row[0] . "</span><br>";
    else
      echo "\n\t\t\t<input type=\"checkbox\" name=\"Turnus[]\" value = \"" . $row[0] . "\"><span class = \"checkboxtext\">" . $row[0] . "</span><br>";
  }
  @mysql_free_result($rs);
  echo "<br>\n\t\t\t<input name=\"allbox2\" type=\"checkbox\" value=\"1\" onClick=\"CheckAll(2);\"><b>Alle</b>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td class = \"$zelle_inhalt_bg\" id = \"form_ausw_inhalt\">";
  $zeile  = 0;
  $spalte = 0;
  $rahmen = "";
  foreach($tmpFach as $data)
  {
    if($zeile == $AnzZeilen)
    {
      $zeile = 0;
      $spalte++;
      if($spalte == $AnzSpalten-1)
        $rahmen = "x0100 $rahmen_farbe";
      echo "\n\t\t</td>\n\t\t<td class = \"$zelle_inhalt_bg  $rahmen\" id = \"form_ausw_inhalt\">";
    }
    if(isset($cookie_fach) && is_array($cookie_fach) && in_array($data,$cookie_fach))//Fach gespeichert -> selektieren
      echo "\n\t\t\t<input type=\"checkbox\" name=\"Fach[]\" value = \"" . $data . "\" checked><span class = \"checkboxtext\">" . $data . "</span><br>";
    else
      echo "\n\t\t\t<input type=\"checkbox\" name=\"Fach[]\" value = \"" . $data . "\"><span class = \"checkboxtext\">" . $data . "</span><br>";
    $zeile++;
  }
  echo "<br>\n\t\t\t<input name=\"allbox1\" type=\"checkbox\" value=\"1\" onClick=\"CheckAll(1);\"><b><span class = \"checkboxtext\">Alle</span></b>";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n\t<tr height = \"40px\">";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t\t<td class = \"$zelle_inhalt_bg x0111 $rahmen_farbe\" align = \"center\" colspan = \"" . ($AnzSpalten+1) . ">";
  echo "\n\t\t\t<input type=\"hidden\" name=\"Klasse\" value=\"" . $Klasse ."\">";
  echo "\n\t\t\t<input type=\"hidden\" name=\"sent\" value=1>";
  echo "\n\t\t\t<input type=\"hidden\" name=\"Version\" value=$OnlineVersion>";
  echo "\n\t\t\t<input type = \"submit\" value = \"Stundenplan anzeigen\">";
  echo "\n\t\t</td>";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n\t<tr height = \"1%\">";
  echo "\n\t\t<td>&nbsp;</td>";
  echo "\n\t\t<td align = \"right\" valign = \"top\"  colspan = \"" . ($AnzSpalten+1) . "\"><span class = \"smallmessage_kl\">Version: " . date("d.m.Y",$OnlineVersion) . "</span></td>";
  echo "\n\t\t<td>&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n</table>";
  echo "\n\t</form>";
  echo "\n\n";
  }//if

//------------------------------------------------------------------------------
//Klasse hat nur einen Turnus oder zweiter Aufruf des Skripts (Ausgabe des Klassenplans)
//------------------------------------------------------------------------------	
else
{
  $zeigeplan = true;
  if(!$sent)//Klasse hat nur einen Turnus
  {
    $sql = "SELECT DISTINCT Turnus FROM T_StuPla WHERE Klasse = \"" . $Klasse . "\" AND Version = " . $OnlineVersion;
    $rs = @mysql_query($sql, $db);
    $row = @mysql_fetch_row($rs);
    $TurnusString = $row[0];
    @mysql_free_result($rs);
  }
  else//Klasse hat mehrere Turnusse
  {
    $counter = 0;
    foreach($Turnus as $value)
    {
      if($counter == 0)
        $TurnusString = $value; 
      else
        $TurnusString .= " / $value";
      $counter++; 
    }
  }
		
  //Wieviele Bloecke muessen angezeigt werden?
  $MaxStunde = 4;
  $sql = "SELECT MAX(Stunde) FROM T_StuPla WHERE Version = $OnlineVersion AND Klasse = \"$Klasse\" $sqlturnus $sqlfach;";
  $rs = @mysql_query($sql, $db);
  $row = @mysql_fetch_row($rs);
  if($row[0] > 4)
    $MaxStunde = $row[0];
  @mysql_free_result($rs);

  //Klassenlehrer ermitteln
  if (strpos($Klasse,"OG") === false) //In OG gibt es keine Klassenlehrer
  {
    $sql = "SELECT COUNT(*), Tutor FROM T_Schueler WHERE Tutor <> \"\" AND Klasse = \"" . $Klasse . "\" GROUP BY Tutor;";
    $rs = @mysql_query($sql, $db);
    $Treffer = 0;
    while($row = @mysql_fetch_row($rs)) //Wegen Fehleintraegen Lehrer mit den meisten Eintraegen filtern
      if($row[0] > $Treffer)
      {
        $Treffer = $row[0];
        $KLehrer = $row[1];
      }
    @mysql_free_result($rs);
    $sql = "SELECT DISTINCT Name FROM T_StuPla WHERE Lehrer = \"" . $KLehrer . "\";";
    $rs = @mysql_query($sql, $db);
    $row = @mysql_fetch_row($rs);
    $KLehrer = $row[0];
    @mysql_free_result($rs);
  }
			
  //Ausgabe Klassenplan
  echo "\n\n<table cellpadding = \"0\" cellspacing = \"0\" border = \"0\" height = \"1%\" width = \"100%\">";

  for($Block = 0; $Block <= $MaxStunde; $Block++)
  {
    if($Block==0)  //Tabellenueberschrift
    {
      echo "\n\t<tr height = \"1%\">\n\t\t<td>&nbsp;</td>";
      echo "\n\t\t<td align = \"center\" valign = \"bottom\" colspan = \"6\" nowrap>";
      echo "<br><span class = \"ueberschrift\">Stundenplan der Klasse <b>$Klasse</b></span>";
      echo "&nbsp;&nbsp;&nbsp;<span class = \"smallmessage_gr\">(Turnus:&nbsp;$TurnusString)</span>";
      if(isset($KLehrer))
        echo "<br><span class = \"p_small\"><span class = \"smallmessage_gr\">Klassenlehrer: <b>$KLehrer</b></span></span>";
      if($linkVersion == -1 || $Druck) //Keine weitere Version vorhanden
        echo "<br><br><span class = \"p_small\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$OnlineGueltigAb) . "</b></span></span>";	
      else
      {
        if($linkVersion != -1 && !$sent) //Es gibt eine weitere Version & Klasse hat nur einen Turnus
          echo "<br><br><span class = \"p_small\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$OnlineGueltigAb) . "</b></span></span>&nbsp;&nbsp;&nbsp;&nbsp;<a class = \"smalllink\" href = \"KPlan1.php?Klasse=". urlencode($Klasse) . "&Version=$linkVersion\">[Gültig ab: " . date("d.m.Y",$naechsteGueltigAb) . "]</a>";
        else //Es gibt eine weitere Version & Klasse hat mehrere Turnusse
        {
          foreach($Turnus as $wert)
            $linkTurnus .= "&Turnus[]=" . urlencode($wert);
          foreach($Fach as $wert)
            $linkFach .= "&Fach[]=" . $wert;
          echo "<br><br><span class = \"p_small\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$OnlineGueltigAb) . "</b></span></span>&nbsp;&nbsp;&nbsp;&nbsp;<a class = \"smalllink\" href = \"KPlan1.php?Klasse=" . urlencode($Klasse) . "&Version=$linkVersion&sent=1$linkTurnus$linkFach\">[Gültig ab: " . date("d.m.Y",$naechsteGueltigAb) . "]</a>";
        }
      }
      if ( vertretungVorhanden($Klasse) )
        echo "&nbsp;<a class = \"linkmessage\" href=\"VertretungsplanKlasse.php?Klasse=$Klasse\">&nbsp;&nbsp;<img src=\"http://img.oszimt.de/nav/neu.gif\" border = \"0\">&nbsp;Änderung&nbsp;</a>";
      echo "</td>";
      echo "\n\t\t<td>&nbsp;</td>\n\t</tr>";
    }
    echo "\n\t<tr>";
    for($Tag=0; $Tag<=5; $Tag++)
    {
      //Rahmeneinstellungen
      if($Druck)
        $rahmen = LayoutRahmen($Block, $MaxStunde, $Tag, 5, "druck");
      else
        $rahmen = LayoutRahmen($Block, $MaxStunde, $Tag, 5, "blau");
      
      if($Tag == 0)//Erste Spalte->Zeilenbeschriftung
      {
        echo "\n\t\t<td>&nbsp;</td>";
        if($Block == 0)//Zelle oben links ist leer
          echo "\n\t\t<td>&nbsp;</td>";
        else//Zeilenbeschriftung ab zweiter Spalte mit Blockzeiten
        {
          echo "\n\t\t<td nowrap align = \"center\" class = \"$zelle_beschr_bg  $rahmen $rahmen_farbe\" id = \"form_zeile_beschr\">";
          echo "<span class = \"tab_beschr\">&nbsp;" . $Block . ". Block&nbsp;</span><br>";
          echo "<span class = \"smallmessage_kl\">" . $BZeit[$Block-1] . "</span>";
          echo "</td>";
        }//else
      }//if
      else//Zweite Spalte bis Ende
      {
        if($Block == 0)//Erste Zeile->Spaltenbeschriftung mit Wochentagen
        {
          echo "\n\t\t<td align = \"center\" class = \"$zelle_beschr_bg $rahmen $rahmen_farbe\" id = \"form_spalte_beschr\">";
          echo "<span class = \"tab_beschr\">" . $Wochentag[$Tag-1] . "</span>";
          echo "</td>";
        }//if
        else//Ausgabe der Unterrichtsbloecke
        {
          $sqlteil = "FROM T_StuPla WHERE Version = " . $OnlineVersion . " AND Klasse = \"" . $Klasse . "\" AND Wochentag = " . $Tag . " AND Stunde = " . $Block;
         	echo "\n\t\t<td nowrap class = \"$zelle_inhalt_bg $rahmen $rahmen_farbe\" id = \"form_inhalt\" align = \"center\"><span class = \"smallmessage_gr\">";
          echo "<b>";
          if(isset($Turnus) && count($Turnus) > 1)//Wird mehr als ein Turnus ausgegeben, wird die Fachbezeichnung um den Turnus ergaenzt
          {
            $Ausgabe = false;
            foreach($Turnus as $data)
            {
              $sql = "SELECT DISTINCT Fach " . $sqlteil . " AND Turnus = \"" . $data . "\"" . $sqlfach;
              $rs = @mysql_query($sql, $db);
              while($row = @mysql_fetch_row($rs))
                if($row[0] != "NN")
                {
                  if($Ausgabe == true)
                    echo " / ";
                  if($data != "jede Woche")
                    $row[0] .= " </b>($data)<b>";
                  echo $row[0];
                  $Ausgabe = true;
                }
              @mysql_free_result($rs);
            }//foreach
          }//if
          else
          {
            $sql = "SELECT DISTINCT Fach " . $sqlteil . $sqlturnus . $sqlfach;
            $rs = @mysql_query($sql, $db);
            showdata($rs);
            @mysql_free_result($rs);
          }
          echo "</b><br>";
          $sql = "SELECT DISTINCT Raum " . $sqlteil . $sqlturnus . $sqlfach;
          $rs = @mysql_query($sql, $db);
          showdata($rs);
          @mysql_free_result($rs);
          echo "<br>";
          $sql = "SELECT DISTINCT Lehrer " . $sqlteil . $sqlturnus . $sqlfach;
          $rs = @mysql_query($sql, $db);
          showdata($rs);
          @mysql_free_result($rs);
          echo "</span></td>";
        }//else (Ausgabe der Unterrichtsbloecke)
      }//else (Zweite Spalte bis Ende)
      if($Tag == 5)
      {
        echo "\n\t\t<td>&nbsp;</td>";
      }
    }//for (Tag)
    echo "\n\t</tr>";
  }//for (Block)
  echo "\n\t<tr height = \"1%\">\n\t\t<td>&nbsp;</td>\n\t\t<td align = \"right\" valign = \"top\" colspan = \"6\"><span class = \"smallmessage_kl\">Version: " . date("d.m.Y",$OnlineVersion) . "</span></td>\n\t\t<td>&nbsp;</td>\n\t</tr>";
  echo "\n</table>\n";
}//else (Nur ein Turnus oder zweiter Aufruf)

@mysql_close($db);

echo ladeOszKopf_u($Druck);

if(!$Druck)
{
  $KlasseLink = "KPlan1.php?Klasse=" . urlencode($Klasse) . "&Druck=true&Version=$OnlineVersion";
  if(isset($Turnus) && isset($Fach))
  {
    $KlasseLink .= "&sent=true";
    foreach($Turnus as $turnus)
      $KlasseLink .= "&Turnus[]=$turnus";
    foreach($Fach as $fach)
      $KlasseLink .= "&Fach[]=$fach";
  }

  echo ladeLink("http://www.oszimt.de","<b>Home</b>");
  echo ladeLink("KPlan.php","Klassenübersicht");
  if ($zeigeplan)
  {
    echo ladeLink("http://www.oszimt.de/4-service/download/schule/raumplan.pdf", "Raumplan (pdf)", "target = \"_blank\"");
    echo ladeLink($KlasseLink, "Druckversion", "target = \"_blank\"");
  }
}

echo ladeOszFuss($Druck);
?>

<?php
function showdata($rs)
{
  $Ausgabe = false;
  while($row = @mysql_fetch_row($rs))
  {
    {
    if($Ausgabe == true)
      echo " / ";
    echo $row[0];
    $Ausgabe = true;
    }
  }//while
}//function
?>

<script type="text/javascript">
<!--
function CheckAll(wert)
{
  for (var i=0;i<document.Formular.elements.length;i++)
  {
    var e = document.Formular.elements[i];
    if (e.name == "Fach[]" && wert == 1)
      e.checked = document.Formular.allbox1.checked;
    if (e.name == "Turnus[]" && wert == 2)
      e.checked = document.Formular.allbox2.checked;
  }
}
//-->
</script>