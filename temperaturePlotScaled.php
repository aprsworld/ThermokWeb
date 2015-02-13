<?php
include $_SERVER["DOCUMENT_ROOT"] . "/jpgraph/src/jpgraph.php";
include $_SERVER["DOCUMENT_ROOT"] . "/jpgraph/src/jpgraph_line.php";
include $_SERVER["DOCUMENT_ROOT"] . "/jpgraph/src/jpgraph_scatter.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
require $_SERVER["DOCUMENT_ROOT"] . "/datamart/geoFunctions.php";


$station_id=strtoupper($_REQUEST["station_id"]);

$db=_open_mysql("worldData");

/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
        require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}



$week=$_REQUEST["week"];
if ( $week < 0 || $week > 53 )
	$week=NULL;

$day=$_REQUEST["day"];
if ( $day < 1 || $day > 366 )
	$day=NULL;

$year=$_REQUEST["year"];
if ( $year < 2000 || $year > 2099 )
	$year=date('Y');

$scale[0]=$scale[1]=$scale[2]=$scale[3]=$scale[4]=$scale[5]=1.0;
if ( is_numeric($_REQUEST["scale0"]) )
	$scale[0]=$_REQUEST["scale0"];
if ( is_numeric($_REQUEST["scale1"]) )
	$scale[1]=$_REQUEST["scale1"];
if ( is_numeric($_REQUEST["scale2"]) )
	$scale[2]=$_REQUEST["scale2"];
if ( is_numeric($_REQUEST["scale3"]) )
	$scale[3]=$_REQUEST["scale3"];

$hours=$_REQUEST["hours"];


if ( $hours>0 ) {
	$sql = sprintf("SELECT UNIX_TIMESTAMP(DATE_SUB(packet_date,INTERVAL 6 HOUR)) AS packet_time, t0,t1,t2,t3,relay0,relay1 FROM thermok4_%s WHERE packet_date>=DATE_SUB(now(),INTERVAL %d HOUR) ORDER BY packet_date",$station_id,$hours);
	$title=sprintf("Last %d Hours",$hours);
} else {
	$sql = sprintf("SELECT UNIX_TIMESTAMP(DATE_SUB(packet_date,INTERVAL 6 HOUR)) AS packet_time, t0,t1,t2,t3,relay0,relay1 FROM thermok4_%s WHERE packet_date>='2009-09-01 00:00:00' ORDER BY packet_date",$station_id);
	$title="Forever and ever";
}

$query=mysql_query($sql,$db);



/* get field names for everything after packet_time */
$fn=array();
for ( $i=1 ; $i<mysql_num_fields($query) ; $i++ ) {
	$fn[$i-1]=mysql_field_name($query,$i);
}



$max=-400000;
$min=400000;
$n=0;
while ( $r=mysql_fetch_array($query) ) {
	$datax[$n]=$r["packet_time"];

	for ( $i=0 ; $i<count($fn) ; $i++ ) {
		$datay[$i][$n]=$r[$i+1]*$scale[$i];

		if ( $i<4 ) {
			if ( $datay[$i][$n] > $max ) 
				$max=$datay[$i][$n];
			if ( $datay[$i][$n] < $min && $datay[$i][$n] != -99.9 && $datay[$i][$n] != -1000.0 ) 
				$min=$datay[$i][$n];
		}
	}

	$n++;
}

if ( 0 == $max )
	$max=1;
$max=1.1*$max;
$max=100.0;


if ( $min < 0 ) {
	$min=round($min-10.0,-1);
} else {
	$min=0.0;
}


for ( $i=0 ; $i<$n ; $i++ ) {
	$datay[4][$i]=$datay[4][$i]*$max;
	$datay[5][$i]=$datay[5][$i]*$max;
}

$min=-10;
$max=35;

if ( false ) {
	header("Content-type: text/plain"); 
	print_r($datay);  
	printf("min=%lf max=%lf\n",$min,$max); 
	die();
}

function CtoFCallback($aVal) {
    return round(($aVal*9.0/5.0)+32.0,2);
} 

function TimeCallback($aVal) {
	return Date('Y-m-d',$aVal);
}

$width=$_REQUEST["width"];
$height=$_REQUEST["height"];
if ( $width < 100 || $width > 10000 ) 
	$width=700;
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
$graph = new Graph($width,$height);
if ( ! $nomargin ) {
	$graph->SetMargin(50,50,30,75);	
} else {
	$graph->SetMargin(50,50,10,10);	
}

/* allow override of units */
$u[0]=$r["count0U"]="CH0";
$u[1]=$r["count1U"]="CH1";
$u[2]=$r["count2U"]="CH2";
$u[3]=$r["count3U"]="CH3";
if ( isset($_REQUEST["unit0"]) ) $u[0]=$_REQUEST["unit0"];
if ( isset($_REQUEST["unit1"]) ) $u[1]=$_REQUEST["unit1"];
if ( isset($_REQUEST["unit2"]) ) $u[2]=$_REQUEST["unit2"];
if ( isset($_REQUEST["unit3"]) ) $u[2]=$_REQUEST["unit3"];


//$title="This is the title";
$graph->title->Set($title);
$graph->SetAlphaBlending();

$graph->SetScale("intlin",$min,$max,$datax[0],$datax[$n-1]);
$graph->SetY2Scale("int",$min,$max,$datax[0],$datax[$n-1]);
//$graph->SetY2Scale("lin"); // Y2 axis


if ( ! $noxaxis ) {
	// Setup the x-axis with a format callback to convert the timestamp
	// to a user readable time
	$graph->xaxis->SetTextLabelInterval(1); 
	$graph->xaxis->SetTextTickInterval(1,2);
	$graph->xaxis->SetLabelFormatCallback('TimeCallback');
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');
} else {
	$graph->xaxis->Hide();
}
$graph->yaxis->SetTextLabelInterval(1);
$graph->yaxis->SetTextTickInterval(1,2);
$graph->yaxis->SetTitle(sprintf("Temperature (degrees C)"),'middle'); 
$graph->y2axis->SetTitle(sprintf("Temperature (degrees F)"),'middle'); 
$graph->y2axis->SetLabelFormatCallback('CtoFCallback');

/* add the series */
$colors=array("red","blue","yellow","green","#bc8af4","#e5e5e5","#009900");


for ( $i=count($fn)-1 ; $i>=0 ; $i-- ) {
	$lp[$i] = new LinePlot($datay[$i],$datax);
	$lp[$i]->SetColor($colors[$i]);
	$lp[$i]->SetWeight(2);
	if ( $i > 3 ) {
		$lp[$i]->SetFillColor($colors[$i]);
	}
	$graph->Add($lp[$i]);
	
	$l2[$i] = new LinePlot($datay[$i],$datax);
	$l2[$i]->SetWeight(0); // Optimize
	$graph->AddY2($l2[$i]);
}


/* draw the thing */
$graph->Stroke();
?>
