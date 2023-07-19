<?php
include ("./jpgraph4/src/jpgraph.php");
include ("./jpgraph4/src/jpgraph_line.php");

// $tempers = file($_GET['logfile']);
$tagName = $_GET['tagName'];
$tagValue = file_get_contents("_data/" . $tagName . ".txt");
$obj = json_decode($tagValue);
$datay = $obj->{'sersorData'};

// A nice graph with anti-aliasing
$graph = new Graph(1250,450,"auto");
$graph->img->SetMargin(40,100,40,80);	
//$graph->img->SetAntiAliasing();
$graph->SetScale("textlin");
$graph->SetShadow();
$graph->title->Set("Room noise Sersor 10 minitus Report");

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Slightly adjust the legend from it's default position in the
// top right corner. 
$graph->legend->SetPos(0.03,0.5,"right","center");
$graph->legend->SetColumns(1);

// Setup X-scale
$graph->xaxis->SetTextTickInterval(100, -1); 

// Create the first line
$p1 = new LinePlot($datay);
$p1->mark->SetType(MARK_UTRIANGLE);
$p1->mark->SetFillColor("blue");
$p1->mark->SetWidth(4);
$p1->SetColor("blue");
$p1->SetCenter();
$p1->SetLegend("Vol");
$graph->Add($p1);

// Output line
$graph->Stroke();

?>


