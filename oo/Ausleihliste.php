<?php
/**
 * Erstellt eine Ausleihliste mit Informationen aus dem Stundenplan
 * (c) 2006 Christoph Griep
 *  
 */
if ( ! isset($_REQUEST["Los"]) )
{
  $Ueberschrift = "Ausleihliste erstellen";
  $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
  include("include/header.inc.php");
?>
<tr><td>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
Anfangsdatum <input type="Text" name="Anfang" size="10" maxlength="10" />
(tt.mm.jjjj, leerlassen für aktuelles Datum)<br />
Überschrift (was wird verliehen) <input type="Text" name="Was" size="30" />
<input type="Submit" name="Los" value="Liste erstellen">
</form>
<em>Hinweis:</em> Die Ausleihliste wird im StarOffice/OpenOffice-Format angeboten. Sie
kann nicht mit Word geöffnet werden!
<div align="center"><a href="/">Zurück zum internen Lehrerbereich</a></div>
</td></tr>
<?php
  include("include/footer.inc.php");
}
else
{
  include("include/config.php");
  include("include/turnus.inc.php");
set_magic_quotes_runtime(0);

define('PCLZIP_INCLUDE_PATH',"");
define('ZIPLIB_INCLUDE_PATH',"");
define('POO_TMP_PATH',dirname($_SERVER["SCRIPT_FILENAME"])); // Pfad /home/httpd/bscw-oszimt.de/html/Lizenz/tmp
//$_ENV["UPLOAD_TMP_DIR"]);
require('phpOpenOffice.php');
$doc = new phpOpenOffice();
$anfang = time();
if ( isset($_REQUEST["Anfang"]) && $_REQUEST['Anfang'] != '')
{
  if ( strpos($_REQUEST["Anfang"],".") !== false )
  {
    $endp = strrpos($_REQUEST["Anfang"],".");
    $anfp = strpos($_REQUEST["Anfang"],".");
    $_REQUEST["Anfang"] = substr($_REQUEST["Anfang"],$endp+1).
      "-".substr($_REQUEST["Anfang"],$anfp+1,$endp-$anfp-1).
      "-".substr($_REQUEST["Anfang"],0,$anfp);
//    echo $_REQUEST["Anfang"];
  }
  $anfang = strtotime($_REQUEST["Anfang"]);
}

while ( date("w", $anfang) != 1 )
  $anfang = strtotime("+1 day", $anfang);
$Schuljahr = Schuljahr(true, $anfang);
$attribute = array();
for ( $i = 1; $i <= 4; $i++)
{
  $daten["MO$i"] = date("d.m.", $anfang);
  $daten["TURN$i"] = "";
  $sql = "SELECT Turnus FROM ".
            "(T_WocheTurnus INNER JOIN T_Turnus ON F_ID_Turnus = ID_Turnus) ".
            "INNER JOIN T_Woche ON ID_Woche=F_ID_Woche ".
            "WHERE Montag=$anfang AND T_Woche.SJahr='$Schuljahr' ORDER BY Turnus";
  if ( ! $query = mysql_query($sql,$db)) echo mysql_error($db);
  while ( $turnus = mysql_fetch_row($query) )
  {
    $daten["TURN$i"] .= $turnus[0]." ";
  }
  mysql_free_result($query);
  if ( sindFerien($anfang, $db) )
  {
    $daten["MO$i"] .= "FREI";
    $attribute["MO$i"] = "b";
  }
  $anfang = strtotime("+1 day",$anfang);
  $daten["DI$i"] = date("d.m.", $anfang);
  if ( sindFerien($anfang, $db) )
  {
    $daten["DI$i"] .= "FREI";
    $attribute["DI$i"] = "b";
  }
  $anfang = strtotime("+1 day",$anfang);
  $daten["MI$i"] = date("d.m.", $anfang);
  if ( sindFerien($anfang, $db) )
  {
    $daten["MI$i"] .= "FREI";
    $attribute["MI$i"] = "b";
  }
  $anfang = strtotime("+1 day",$anfang);
  $daten["DO$i"] = date("d.m.", $anfang);
  if ( sindFerien($anfang, $db) )
  {
    $daten["DO$i"] .= "FREI";
    $attribute["DO$i"] = "b";
  }
  $anfang = strtotime("+1 day",$anfang);
  $daten["FR$i"] = date("d.m.", $anfang);
  if ( sindFerien($anfang, $db) )
  {
    $daten["FR$i"] .= "FREI";
    $attribute["FR$i"] = "b";
  }
  $anfang = strtotime("+3 day",$anfang);
}
$daten["WAS"] = "";
if ( isset($_REQUEST["Was"]) ) $daten["WAS"] = $_REQUEST["Was"];
$doc->loadDocument("Vorlagen/Ausleihliste.sxw");
$doc->insertStyles();
$doc->parse($daten,$attribute);
$doc->download("Ausleihliste");
$doc->clean();
}

?>