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
if ( !isset($_REQUEST["x"]) || !isset($_REQUEST["y"]) ||
     (! is_numeric($_REQUEST["x"]) || ! is_numeric($_REQUEST["y"])))
{
  $_REQUEST["x"] = 1000;
  $_REQUEST["y"] = 600;
}
$graph = new Graph($_REQUEST["x"], $_REQUEST["y"],"auto");
$graph->SetScale("textlin");
//$graph->xaxis->title->Set("Datum");
$graph->yaxis->title->Set("Anzahl");
$graph->xaxis->SetLabelAngle(90);
$graph->title->Set("gesendet(blau)/empfangen(gelb)/Ham(gruen)/Spam(rot)/Viren(gestrichelt)/Angriffe(gepunktet)");

$ydata1 = array();
$ydata2 = array();
$ydata3 = array();
$viren = array();
$angriffe = array();
$xdata = array();
$link = mysql_connect('localhost', 'oszimtstatistik', 'TbV6men,MtxuK:pW');
mysql_select_db("Systemlog");
$query = mysql_query("SELECT * FROM T_Statistik WHERE Datum >= ".strtotime("-365 day"));
while ( $buffer = mysql_fetch_array($query))
{
      $ydata1[] = $buffer["Ham"]; // Load Avg Mittelwert 15 min
      $ydata2[] = $buffer["Spam"]; // Load Avg Mittelwert 5 min
      $ydata3[] = $buffer["Gesendet"]; // Load Avg
      $ydata4[] = $buffer["Empfangen"];
      $ydata5[] = $buffer["Viren"];
      $ydata6[] = $buffer["Angriffe"];
      $xdata[] = date("d.m.",$buffer["Datum"]);
}
mysql_free_result($query);
mysql_close($link);

$graph->xaxis->SetTickLabels($xdata);
// Create the linear plot
$lineplot1=new LinePlot($ydata1);
$lineplot2=new LinePlot($ydata2);
$lineplot1->setColor("green");
$lineplot2->setColor("red");

$lineplot3=new LinePlot($ydata3);
$lineplot3->setColor("blue");
$lineplot4=new LinePlot($ydata4);
$lineplot4->setColor("yellow");
$lineplot5=new LinePlot($ydata5);
$lineplot5->setColor("red");
$lineplot5->SetStyle(4); // Linienart
$lineplot6=new LinePlot($ydata6);
$lineplot6->setColor("red");
$lineplot6->SetStyle(3); // Linienart

// Add the plot to the graph
  $graph->Add($lineplot1);
  $graph->Add($lineplot2);
  $graph->Add($lineplot3);
$graph->Add($lineplot4);
  $graph->Add($lineplot5);
$graph->Add($lineplot6);

$graph->img->SetMargin(40,10,10,50);

// Display the graph
$graph->Stroke();
 
?>
