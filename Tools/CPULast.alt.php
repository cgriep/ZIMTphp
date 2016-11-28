<?php
/*
 * Created on 13.01.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
include("include/jpgraph/jpgraph.php");
include("include/jpgraph/jpgraph_line.php");

// Create the graph. These two calls are always required
if ( ! is_numeric($_REQUEST["x"]) || ! is_numeric($_REQUEST["y"]))
{
  $_REQUEST["x"] = 1000;
  $_REQUEST["y"] = 550;
}
if ( isset($_REQUEST["Ab"]))
{
  $aDatum = explode(".", $_REQUEST["Ab"]);
  $aDatum = mktime(0,0,0,$aDatum[1],$aDatum[0],$aDatum[2]);
}
else
  $aDatum = strtotime(date("Y-m-d"));

$graph = new Graph($_REQUEST["x"], $_REQUEST["y"],"auto");
$graph->SetScale("textlin");
//$graph->xaxis->title->Set("Datum");
$graph->yaxis->title->Set("Load Avg");
$graph->xaxis->SetLabelAngle(90);
$graph->title->Set("Wartende Prozesse");

$ydata = array();
$xdata = array();
$dat = fopen("loadavg","r");
while ( !feof($dat))
{
  $buffer = fgets($dat, 80);
  // Fri Jan 13 11:35:00 CET 2006 0.53 0.34 0.34 2/96 2775
  
  $werte = explode(" ",$buffer);
  if ( Count($werte) > 6)
  {
  switch ($werte[1])
  {
  	case "Jan": $monat = 1; break;
  	case "Feb": $monat = 2; break;
  	case "Mar": $monat = 3; break;
  	case "Apr": $monat = 4; break;
  	case "May": $monat = 5; break;
  	case "Jun": $monat = 6; break;
  	case "Jul": $monat = 7; break;
  	case "Aug": $monat = 8; break;
  	case "Sep": $monat = 9; break;
  	case "Oct": $monat = 10; break;
  	case "Nov": $monat = 11; break;
  	case "Dec": $monat = 12; break;
  }
  $zeit = strtotime($werte[5]."-".$monat."-".$werte[2]);
  if ( $zeit >= $aDatum || $aDatum == 0 )
  {
  	if ( isset($_REQUEST["Art"]) && $_REQUEST["Art"] == "15")
      $ydata[] = $werte[8]; // Load Avg Mittelwert 15 min
    elseif ( isset($_REQUEST["Art"]) && $_REQUEST["Art"] == "5")
      $ydata[] = $werte[7]; // Load Avg Mittelwert 5 min
    else
      $ydata[] = $werte[6]; // Load Avg
    $Datum = "";
    $Zeit = $werte[3];
    $dieZeit = explode(":",$Zeit);
    if ( $dieZeit[1] == 0 )
    {
      $Datum = $dieZeit[0];
      if ( $dieZeit[0] == 0 )
        $Datum = $werte[2].".".$werte[1];
    }
    $xdata[] = $Datum;
  }
  } // Count
}

$graph->xaxis->SetTickLabels($xdata);
// Create the linear plot
$lineplot=new LinePlot($ydata);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->img->SetMargin(40,20,20,40);

// Display the graph
$graph->Stroke();
 
?>
