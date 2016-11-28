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
$graph = new Graph($_REQUEST["x"], $_REQUEST["y"],"auto");
$graph->SetScale("textlin");
//$graph->xaxis->title->Set("Datum");
$graph->yaxis->title->Set("Anzahl");
$graph->xaxis->SetLabelAngle(90);
$graph->title->Set("E-Mails(schwarz)/Ham(grün)/Spam(rot)/Viren(gestrichelt)");

$ydata1 = array();
$ydata2 = array();
$ydata3 = array();
$viren = array();
$angriffe = array();
$xdata = array();
$dat = fopen("spamlog.txt","r");
while ( !feof($dat))
{
  $buffer = fgets($dat, 80);
  // Fri Jan 13 11:35:00 CET 2006 total ham spam
  $buffer = str_replace("  "," ",$buffer);
  $werte = explode(" ",$buffer);
  if ( is_numeric($werte[6]))
  {
    $ydata1[] = 0+$werte[6]; // 
    $ydata2[] = 0+$werte[7]; // 
    $ydata3[] = 0+$werte[8]; //
    $viren[] = 0+$werte[9]; //
    $angriffe[] = 0+$werte[10]; // 
    $Zeit = $werte[2].".".$werte[1];
    $xdata[] = $Zeit;
  }
}

$graph->xaxis->SetTickLabels($xdata);
// Create the linear plot
$lineplot1=new LinePlot($ydata1);
$lineplot2=new LinePlot($ydata2);
$lineplot2->setColor("green");

$lineplot3=new LinePlot($ydata3);
$lineplot3->setColor("red");
$lineplot4=new LinePlot($viren);
$lineplot4->setColor("red");
$lineplot4->SetStyle(2); // Linienart
$lineplot5=new LinePlot($angriffe);
$lineplot5->setColor("red");
$lineplot5->SetStyle(2); // Linienart
// Add the plot to the graph
if ( ! isset($_REQUEST["Angriffe"]))
{
  $graph->Add($lineplot1);
  $graph->Add($lineplot2);
  $graph->Add($lineplot3);
  $graph->Add($lineplot4);
}
else
{
  $graph->Add($lineplot5);
  $graph->title->Set("Unkorrekte SSH-Loginversuche");
  $graph->yaxis->title->Set("Anzahl");  
}
$graph->img->SetMargin(40,20,20,40);

// Display the graph
$graph->Stroke();
 
?>
