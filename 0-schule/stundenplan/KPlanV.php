<?php
include_once("include/oszframe.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");

echo ladeOszKopf_o("OSZ IMT Vertretungspläne der Klassen","Vertretungspläne der Klassen");

$SPlanlink   = "VertretungsplanKlasse.php";

//css-Einstellungen
$rahmen_farbe    = "ra_bl";
$zelle_beschr_bg = "zelle_beschr_bg";
$zelle_inhalt_bg = "zelle_inhalt_bg";

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
if(!$db)
  dieMsg("Keine Verbindung zur Datenbank möglich!");
@mysql_select_db($DBname,$db);


//Versions-Timestamp der aktuellen Online-Version holen    
$OnlineVersion = getAktuelleVersionWE($vonWann = -1);
if($OnlineVersion == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");

//Gueltigkeits-Timestamp der aktuellsten Version aus DB holen    
$GueltigAb = getGueltigAbVersion($OnlineVersion);

//Klassennamen ermitteln
$sql = "SELECT DISTINCT Klasse FROM T_StuPla WHERE Version = $OnlineVersion;";
$rs = @mysql_query($sql, $db);
$AnzKlassen=0;
while($row = @mysql_fetch_row($rs))
{
  $Klassenliste[$AnzKlassen]=$row[0];
  $AnzKlassen++;
}
@mysql_free_result($rs);
asort($Klassenliste);

//Klassenmatrix mit sortierten Klassennamen fuellen
$MaxZeile=ceil(sqrt($AnzKlassen));
$MaxSpalte=ceil($AnzKlassen/$MaxZeile);

$spalte=0;
$zeile=0;

foreach($Klassenliste as $Klasse)
{
  $Klassenmatrix[$spalte][$zeile]=$Klasse;
  $zeile++;
  if($zeile == $MaxZeile)
  {
    $spalte++;
    $zeile=0;
  }
}

echo "\n\n<table border=\"0\" height = \"1%\" width = \"100%\" cellpadding = \"0\" cellspacing = \"0\">\n";
$span = $MaxSpalte;
echo "\t<tr height = \"40%\">\n\t\t<td>&nbsp;</td>\n\t\t<td align = \"center\" valign = \"bottom\" colspan = \"" . $span . "\"><span class = \"smallmessage_gr\"><b><span style = \"line-height:250%\">Gültig ab: " . date("d.m.Y",$GueltigAb) . "</span></b></span></td>\n\t\t<td>&nbsp;</td>\n\t</tr>\n";
for($zeile=0;$zeile<$MaxZeile;$zeile++)
{
  echo "\t<tr>\n";
  echo "\t\t<td width = \"40%\">&nbsp;</td>\n";
  for($spalte=0;$spalte<$MaxSpalte;$spalte++)
  {
    //Rahmeneinstellungen
    $rahmen = LayoutRahmen($zeile, $MaxZeile-1, $spalte, $MaxSpalte-1);
    
    echo "\t\t<td class = \"$zelle_inhalt_bg $rahmen $rahmen_farbe\">";
    if(isset($Klassenmatrix[$spalte][$zeile]))
    {
      $coded = urlencode($Klassenmatrix[$spalte][$zeile]);
      echo "<a class = \"LiKlasse\" href = \"" . $SPlanlink . "?Klasse=" . $coded . "\">" . $Klassenmatrix[$spalte][$zeile] . "</a>";
    }
    else
      echo "&nbsp;";
    echo "</td>\n";
  }
  echo "\t\t<td width = \"40%\">&nbsp;</td>\n";
  echo "\t</tr>\n";
}
echo "\t<tr height = \"1%\">\n\t\t<td>&nbsp;</td>\n\t\t<td align = \"right\" valign = \"top\" colspan = \"" . $span . "\"><span class = \"smallmessage_kl\">Version: " . date("d.m.Y",$OnlineVersion) . "</span></td>\n\t\t<td>&nbsp;</td>\n\t</tr>\n";
echo "</table>\n";
@mysql_close($db);

echo ladeOszKopf_u();

echo ladeLink("http://www.oszimt.de","<b>Home</b>");

echo ladeOszFuss();
?>