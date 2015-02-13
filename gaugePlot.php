<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/jpgraph3/src/jpgraph.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/jpgraph3/src/jpgraph_odo.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/jpgraph3/src/jpgraph_iconplot.php");
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";


$station_id=strtoupper($_REQUEST["station_id"]);

$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
        require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}



$title="Forever and ever";
$sql=sprintf("SELECT * FROM thermok4_%s ORDER BY packet_date DESC LIMIT 1",$station_id);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query,MYSQL_ASSOC);

$width=$_REQUEST["width"];
$height=$_REQUEST["height"];
if ( $width < 100 || $width > 10000 ) 
	$width=800;
if ( $height < 50 || $height > 5000 ) 
	$height=450;

$nomargin=$_REQUEST["nomargin"];
if ( "1" == $nomargin ) {
	$nomargin=true;	
	$noxaxis=true;
} else {
	$nomargin=false;
	$noxaxis=false;
}

// Setup the basic graph
$graph = new OdoGraph($width,$height);
//if ( ! $nomargin ) {
//	$graph->SetMargin(50,50,30,75);	
//} else {
//	$graph->SetMargin(50,50,10,10);	
//}

$title="This is the title";
//$graph->title->Set($title);
$graph->SetAlphaBlending();

$odo[0]=new Odometer(ODO_HALF);
//$odo[0]->SetPos(225,225);
$odo[0]->scale->Set(-25,250);
$odo[0]->scale->SetTicks(25,2);
$odo[0]->scale->SetLabelPos(0.75);
$odo[0]->scale->label->SetFont(FF_ARIAL, FS_BOLD);
$odo[0]->scale->label->SetColor('white');
//$odo[0]->scale->label->SetFont(FF_ARIAL,FS_NORMAL,10);
$odo[0]->scale->SetLabelFormat('%d'.SymChar::Get('degree') . 'F');

$odo[0]->needle->Set(60);
$odo[0]->needle->SetStyle(NEEDLE_STYLE_MEDIUM_TRIANGLE);
$odo[0]->label->Set("Tank Temperature:");
$odo[0]->label->SetFont(FF_ARIAL,FS_BOLD);
$odo[0]->label->SetColor('white');

$odo[0]->AddIndication(-25,185,"green");
$odo[0]->AddIndication(185,250,"red");

$odo[1]=new Odometer();
$odo[1]->needle->Set(36.75);
$odo[1]->needle->SetStyle(NEEDLE_STYLE_MEDIUM_TRIANGLE);
$odo[1]->label->Set("Collector Temperature:");
$odo[1]->label->SetFont(FF_FONT2,FS_BOLD);



$row[0]=new LayoutHor($odo);
$row[1]=new LayoutHor($odo);
$col[0]=new LayoutVert($row);

//$icon = new IconPlot('/home/jjeffers/winddatalogger_com/images/aprsworld_logo.175.jpg',400,200,0.85,30);
//$icon->SetAnchor('center','top');
//$graph->Add($icon);

$graph->Add($col);

/* draw the thing */
$graph->Stroke();
?>
