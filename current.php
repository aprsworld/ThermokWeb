<?
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
//*/
require $_SERVER['DOCUMENT_ROOT'] . '/world_config.php';
require 'ViewBuilderConfig.php';

$station_id=$_REQUEST["station_id"];

$pScript="";
$pRow="";
$db = _open_mysql('worldData');
$sql=sprintf("SELECT * FROM thermok4_labels WHERE serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$l=mysql_fetch_array($query);
//print_r($l);

require VIEW_BUILDER_LOC . "/pieces.php";

/* 

   place table row instructions here 
   if you use one argument, then the title will be the same as the column name
   if you use two arguments, then the title will be the second argument

*/

headline("Current Values");
simpleDataRow("localTime","Report Date:");
ageRow();


/* Temperatures */
if ("" != $l["r0L"]) headline("Temperature");

for ( $i = 0 ; $i < 4 ; $i++ ){
	if ("" != $l["t".$i."L"]) toFixedDataRow("t".$i,$l["t".$i."L"],1," &deg;C");
}

/* analogue channels */
if ( ""!=$l["v0L"] || ""!=$l["v1L"] || ""!=$l["v2L"] || ""!=$l["v3L"] ) headline("Voltage");

for ( $i=0 ; $i<4 ; $i++ ) {
	if ("" != $l["v".$i."L"]) toFixedDataRow("vin".$i,$l["v".$i."L"],2," VDC");
}

/* relays */
if ( ""!=$l["r0L"] || ""!=$l["r1L"] || ""!=$l["r2L"] ) headline("Relay States");

for ( $i = 0 ; $i < 4 ; $i++ ){
	if ("" != $l["r".$i."L"]) relayDataRow("relay".$i,$l["r".$i."L"]);
}

/* counters */
if ( ""!=$l["c0L"] ) headline("Event Counter");

if ("" != $l["c0L"]) toFixedDataRow("pulseCount",$l["c0L"],0," gallons");

$wideScreen = false;
if ( isset($_COOKIE["wideScreen"]) ) {
	$wideScreen = $_COOKIE["wideScreen"]=="true";
}

/* -+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+- */


$station_id=strtoupper($_REQUEST["station_id"]);


/* if not public, then we need to be authorized */
if ( 0==authPublic($station_id,$db) ) {
	require $_SERVER["DOCUMENT_ROOT"] . "/auth.php";
}



/* Determine our title and display name */
$sql=sprintf("SELECT * FROM deviceInfo WHERE serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query);

/* display displayName if it is not null */
if ( "" != $deviceInfo["displayName"] ) $displayName=$deviceInfo["displayName"]; else $displayName=$station_id;
$displayName=htmlspecialchars($displayName);

$title=$headline=$displayName . " <br />Current Conditions";
//$refreshable=1;

$headMessage='
	<link rel="stylesheet" href="http://data.aprsworld.com/world_style.css" type="text/css"/>

	<link rel="icon" type="image/gif" href="http://data.aprsworld.com/favicon.gif">';

require $_SERVER["DOCUMENT_ROOT"] . "/world_head.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/winddata/windFunctions.php";




?>

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script><script type="text/javascript" src="/data/jQueryRotate.2.2.js"></script>
	<script language="javascript" type="text/javascript" src="http://ian.aprsworld.com/javascript/timeFunctions.js"></script>
	<script language="javascript" type="text/javascript" src="/data/date.js"></script>
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/data/excanvas.min.js"></script><![endif]-->
	<script language="javascript" type="text/javascript" src="/data/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="/data/jquery.flot.threshold.js"></script>
	<script>

	<? 
	/* Chart related javascript */
	require "thermokScript.php"; 

	?>

	</script>

	<script>
		$(document).ready(function(){

			loadData();
			pageTimer();
	
		});
		var pageSeconds = 0;
		var chartAge = 0;
		var lastDate = "";
		function loadData(){
			
			//console.log("yeah");
			var url="<? printf(VIEW_BUILDER_ADDR); ?>/jsonNonView.php?station_id=<? echo $station_id; ?>";	
			$.getJSON(url, 
				function(data) {
					
					if(lastDate!=data.packet_date){
						pageSeconds=0;//data.ageSeconds;
						lastDate=data.packet_date;
						console.log("new current data");
					}
					<? echo $pScript; ?>

					if ( getCookie("deg") == "F" ) {
						console.log("change stuff");
						$('#t0').html(parseFloat(data.t0* 9 / 5 + 32).toFixed(1)+" &deg;F");
						$('#t1').html(parseFloat(data.t1* 9 / 5 + 32).toFixed(1)+" &deg;F");
						$('#t2').html(parseFloat(data.t2* 9 / 5 + 32).toFixed(1)+" &deg;F");
						$('#t3').html(parseFloat(data.t3* 9 / 5 + 32).toFixed(1)+" &deg;F");

					}
				});
			setTimeout(loadData,10000);
		}

		function pageTimer(){
			$("#pageTimer").html(secToTimeDate(pageSeconds));
			$("#chartTimer").html(secToTimeDate(chartAge));
			$("#pageAge").html(secToTimeDate(pageSeconds));
			pageSeconds++;
			chartAge++;
			setTimeout(pageTimer, 1000);
		}
	</script>

<? if($wideScreen) echo "<div style=\"margin-left:auto;margin-right:auto;display:table;border:none;\">" ?>
<table id="currentTable" style="margin-left: auto; margin-right:auto;padding-right:20px;<? if($wideScreen) echo "display:table-cell;border:none;" ?>">
<? echo $pRows; ?>
</table>

<!-- This is added specifically for Thermok -->

<div id="placeholder" style="width: 75%; height: 450px; font-size: 14px;line-height: 1em;overflow: visible; overflow-x: hidden;margin-left:auto;margin-right:auto;padding-left:40px;<? if($wideScreen) echo "display:table-cell;" ?>"></div>
<? if($wideScreen) echo "<div style=\"clear:both;\"></div></div>" ?>
<div style="margin: 20px; border: solid;border-width: 1px;margin-left: auto; margin-right: 10%;padding: 10px;display: table;">
	<table style="font-weight: bold; border: none; margin-left: auto; margin-right: auto; padding: 30px;">
		<tr>
			<td style="width: 25px;">
				<div style="background-color: red; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div>
			</td>

			<td style="font-size: .75em;"><? echo $l["t" . 0 . "L"]?></td>

			<td style="width:10px; border: none;"></td>

			<td style="width: 25px">
				<div style="background-color: blue; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div>
			</td>

			<td style="font-size: .75em;"><? echo $l["t" . 1 . "L"]?></td>

			<td style="width:10px; border: none;"></td>

			<td style="width: 25px">
				<div style="background-color: yellow; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div>
			</td>

			<td style="font-size: .75em;"><? echo $l["t" . 2 . "L"] ?></td>

			<td style="width:10px; border: none;"></td>

			<td style="width: 25px">
				<div style="background-color: green; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div>
			</td>

			<td style="font-size: .75em;"><? echo $l["t" . 3 . "L"] ?></td>

		<?
		 if ( ""!=$l["r0L"] ) { 
		?>
			<td style="width:10px; border: none;"></td>				
			<td style="width: 25px"><div style="background-color: rgba(128, 0, 128, 0.5); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div></td><td style="font-size: .75em;"><? echo $l["r" . 0 . "L"]?></td>
		<? }
		 if ( ""!=$l["r1L"] ) { ?>
			<td style="width:10px; border: none;"></td>
			<td style="width: 25px"><div style="background-color: rgba(178, 178, 178, 0.5); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div></td><td style="font-size: .75em;"><? echo $l["r" . 1 . "L"]?></td>
		<? } 
		 if ( ""!=$l["r2L"] && false ) { ?>
			<td style="width:10px; border: none;"></td>
			<td style="width: 25px"><div style="background-color: rgba(0, 153, 0, 0.5); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div></td><td style="font-size: .75em;"><? echo $l["r" . 2 . "L"]?></td>
		<? } 
		if ( ""!=$l["r1L"] && ""!=$l["r0L"] ) { ?>
			<td style="width:10px; border: none;"></td>
			<td style="width: 25px"><div style="background-color: rgba(121, 89, 121, 0.75); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div></td><td style="font-size: .75em;"><? echo $l["r" . 0 . "L"]." and ". $l["r" . 1 . "L"]?></td>
		<? } 
		?>
		</tr>
	</table>
</div>

<?

$deg = "F";
$oDeg = "C";

if (isset($_COOKIE["deg"])){
	$deg = $_COOKIE["deg"];
	if ( "C" == $deg ){
		$oDeg="F";
	} else {
		$oDeg="C";
	}
}	

?>

<?
/* settings defaults */
if ( !isset($_COOKIE["yScaleMode"]) ) {
	$_COOKIE["yScaleMode"] = "Auto";
}


if ( !isset($_COOKIE["refSec"]) ) {
	$_COOKIE["refSec"] = "60";
}

if ( !isset($_COOKIE["deg"]) ) {
	$_COOKIE["deg"] = "C";
}


if ( !isset($_COOKIE["wideScreen"]) ) {
	$_COOKIE["wideScreen"] = "false";
}

?>

		<table style="margin-left: auto; margin-right: auto;margin-bottom: 30px;">
			<tr>	
				<th onclick="collapseSettings()" colspan="3">Chart Settings<br><span style="font-size:.75em;" >(click to hide/show)</span></th>
			</tr>
			<tr class="collapse">
				<th rowspan="1">Hours:</th>
				<td colspan="2"> 
					<input type="number" min="1" max="168" value="<? echo $chartHours; ?>" id="hours" style="width: 50px;">
					<button class="controlButton" onclick="updateHours();">Update hours</button>
				</td>
			</tr>
			<tr class="collapse">
				<th rowspan="1">Y Scale Mode:</th>
				<td colspan="2" id="mode">
					<input onclick="yScaleSettings()" name="scaleRadio" type="radio" value="Auto" <? if ( "Auto"  == $_COOKIE["yScaleMode"] ) printf("checked") ?>>Auto</option><br>
					<input onclick="yScaleSettings()" name="scaleRadio" type="radio" value="Manual" <? if ( "Manual"  == $_COOKIE["yScaleMode"] ) printf("checked") ?>>Manual</option>					
				</td>
			</tr>
			<tr class="collapse">
				<th class="manualScale">Y min:</th><td id="tdwhite1" class="manualScale"><input  type="number"  value="<? printf($yMin); ?>" id="yScaleMin" style="width: 50px;"><span id="degUnit">&deg;<? echo $deg; ?></span></td>
				<td class="manualScale" rowspan="2" style="text-align: center;">
					<button id="buttonScale" onclick="setYMinMaxButton()" >Apply</button>
				</td>
			</tr>
			<tr class="collapse">			
				<th class="manualScale">Y max:</th><td id="tdwhite2" class="manualScale"><input  type="number"  value="<? printf($yMax); ?>" id="yScaleMax" style="width: 50px;" ><span id="degUnit1">&deg;<? echo $deg; ?></span></td>
				
			</tr>
			<tr class="collapse">
				<th>Update Chart:</th>
				<td colspan="2"> Every
					<select id="chartSetRefresh">
						<option value="30"  <? if ( 30  == $_COOKIE["refSec"] ) printf("selected") ?>>30 seconds</option>
						<option value="60"  <? if ( 60  == $_COOKIE["refSec"] ) printf("selected") ?>>1 minute</option>
						<option value="300" <? if ( 300 == $_COOKIE["refSec"] ) printf("selected") ?>>5 minute</option>
						<option value="600" <? if ( 600 == $_COOKIE["refSec"] ) printf("selected") ?>>10 minute</option>
					</select>
					<button onclick="chartSetRefresh()">Apply</button>
				</td>
				
			</tr>
			<tr class="collapse">
				<th>Temperature Units:</th>
				<td colspan="2">
					<input onclick="switchDegUnit()" name="tempRadio" type="radio" value="C" <? if ( "C"  == $_COOKIE["deg"] ) printf("checked") ?>>&deg;C</option><br>
					<input onclick="switchDegUnit()" name="tempRadio" type="radio" value="F" <? if ( "F"  == $_COOKIE["deg"] ) printf("checked") ?>>&deg;F</option>
				</td>
				
			</tr>
			<tr class="collapse">
				<th>Widescreen:</th>
				<td id="widescreen" colspan="2">
					<input onclick="goWideScreen('true')" type="radio" name="wideRadio" value="true" <? if ( "true"  == $_COOKIE["wideScreen"] ) printf("checked") ?>> True<br>
					<input onclick="goWideScreen('false')" type="radio" name="wideRadio" value="false"<? if ( "false"  == $_COOKIE["wideScreen"] ) printf("checked") ?>> False
				</td>
				
			</tr>
		</table>

	<div id="bar" style="width: 100%; position: fixed; bottom: 0; left: 0; z-index: 2; background: #e0e0e0; text-align: center;">
		<span id="bottomar" style="margin-left: 10px; float: left;" >Current Values updated approximately <span id="pageTimer">Loading...</span> ago</span>
		<span  style="margin-right: 10px; float: right;" >Chart updated approximately <span id="chartTimer">Loading...</span> ago</span>
	</div>


</body>
</html>
