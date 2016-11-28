<?php
/*
 * Created on 13.01.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require("include/jpgraph/jpgraph.php");
require("include/jpgraph/jpgraph_line.php");
// Create the graph. These two calls are always required
if ( !isset($_REQUEST["x"]) || ! is_numeric($_REQUEST["x"]) || ! is_numeric($_REQUEST["y"]))
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

$link = mysql_connect('localhost', 'oszimtstatistik', 'TbV6men,MtxuK:pW');
mysql_select_db("Systemlog");
$query = mysql_query("SELECT * FROM T_Prozessorlast WHERE Datum >= $aDatum");
while ( $buffer = mysql_fetch_array($query))
{
      $ydata1[] = $buffer["Last15"]; // Load Avg Mittelwert 15 min
      $ydata2[] = $buffer["Last5"]; // Load Avg Mittelwert 5 min
      $ydata3[] = $buffer["Last"]; // Load Avg
      if ( date("i",$buffer["Datum"]) == "00" )
        $xdata[] = date("H",$buffer["Datum"]);
      else
        $xdata[] = "";
}
mysql_free_result($query);
mysql_close($link);
$graph->xaxis->SetTickLabels($xdata);
// Create the linear plot
$lineplot1=new LinePlot($ydata1);
$lineplot2=new LinePlot($ydata2);
$lineplot3=new LinePlot($ydata3);
$lineplot1->setColor("green");
$lineplot2->setColor("yellow");
$lineplot3->setColor("red");

// Add the plot to the graph
$graph->Add($lineplot1);
$graph->Add($lineplot2);
$graph->Add($lineplot3);

$graph->img->SetMargin(40,20,20,40);

// Display the graph
$graph->Stroke();
 
?>
