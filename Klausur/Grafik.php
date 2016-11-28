<?php
/**
 * Letzte Änderungen:
 * 10.03.06 C. Griep - angepasst an den neuen Server
 */
include ("include/jpgraph/jpgraph.php");
include ("include/jpgraph/jpgraph_line.php");
include("include/config.php");
include("include/Klausur.inc.php");
include("include/Abteilungen.class.php");
$dieAbteilungen = new Abteilungen($db);

// Create the graph. These two calls are always required
if ( !isset($_REQUEST["x"]) || ! isset($_REQUEST["y"]) || 
     ! is_numeric($_REQUEST["x"]) || ! is_numeric($_REQUEST["y"]))
{
  $_REQUEST["x"] = 300;
  $_REQUEST["y"] = 200;
}
$graph = new Graph($_REQUEST["x"], $_REQUEST["y"],"auto");
$graph->SetScale("textlin");
//$graph->xaxis->title->Set("Datum");
$graph->yaxis->title->Set("Durchschnitt");
$graph->xaxis->SetLabelAngle(90);
$Ueberschrift = "";
$sql = "WHERE ";
if ( isset($_REQUEST["Abteilung"]) ) {
  $sql .= " Abteilung= ".$_REQUEST["Abteilung"]." AND ";
  $Ueberschrift .= $dieAbteilungen->getAbteilung($_REQUEST["Abteilung"]);
}
if ( isset($_REQUEST["Fach"]) ) {
  $sql .= " Fach LIKE '".$_REQUEST["Fach"]."' COLLATE 'latin1_german1_ci' AND ";
  $Ueberschrift .= " Fach ".str_replace("%","",$_REQUEST["Fach"]);
}
if ( isset($_REQUEST["Klasse"]) ) {
  $sql .= " Klasse LIKE '".$_REQUEST["Klasse"]."' AND ";
  $Ueberschrift .= " Klasse ".str_replace("%","",$_REQUEST["Klasse"]);
}
if ( isset($_REQUEST["Schuljahr"]) ) {
  $sql .= " Schuljahr = '".$_REQUEST["Schuljahr"]."' AND ";
  $Ueberschrift .= " ".str_replace("%","",$_REQUEST["Schuljahr"]);
}
$sql .= " 1";

$graph->title->Set($Ueberschrift);

if ( ! $query = mysql_query("SELECT * FROM T_Klausurergebnisse $sql ORDER BY Datum"))
{
	die(mysql_error());
}
$ydata = array();
$xdata = array();
$Gesamt = 0;
$Anzahl = 0;
while ( $Klausur = mysql_fetch_array($query) )
{
  $d = Durchschnitt($Klausur); //= array(11,-3,-8,7,5,-1,9,13,5,-7);
  if (is_numeric($d)) 
  {
  	$ydata[] = $d;
  }
  $xdata[] = date("d.m.",strtotime($Klausur["Datum"]));
}
mysql_free_result($query);
mysql_close();

$graph->xaxis->SetTickLabels($xdata);
//$graph->xaxis->SetLabelSide(SIDE_DOWN);
// Create the linear plot
$lineplot=new LinePlot($ydata);

// Einzelne Werte anzeigen
//$lineplot->value->Show();
//$lineplot->value->SetColor("red");
//$lineplot->value->SetFont(FF_FONT1,FS_BOLD);

// Add the plot to the graph
$graph->Add($lineplot);

$graph->img->SetMargin(40,20,20,40);

// Display the graph
$graph->Stroke();
?>