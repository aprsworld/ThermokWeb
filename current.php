<?
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
//*/
$station_id=strtoupper($_REQUEST["station_id"]);

require_once $_SERVER["DOCUMENT_ROOT"] . "/world_config.php";
$db=_open_mysql("worldData");

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

$head_message=sprintf('<script src="/sweetalert-master/lib/sweet-alert.min.js"></script> <link rel="stylesheet" type="text/css" href="/sweetalert-master/lib/sweet-alert.css">');
$title=$headline=$displayName . " <br />Current Conditions";
$refreshable=1;
require $_SERVER["DOCUMENT_ROOT"] . "/world_head.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/winddata/windFunctions.php";


$chartHours = 24;
if ( isset($_COOKIE["chartHours"]) ) {
	$chartHours = $_COOKIE["chartHours"];
}


$yScaleMode="auto";
$yMin=-10;
$yMax=100;


if ( isset( $_COOKIE["yScaleMode"] ) ) $yScaleMode = $_COOKIE["yScaleMode"];

if ( isset( $_COOKIE["yMin"] ) ) $yMin = $_COOKIE["yMin"];

if ( isset( $_COOKIE["yMax"] ) ) $yMax = $_COOKIE["yMax"];


?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="http://ian.aprsworld.com/javascript/timeFunctions.js"></script>
<script language="javascript" type="text/javascript" src="/data/date.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/data/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="/data/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="/data/jquery.flot.threshold.js"></script>
<script>

var plot;

function switchDegUnit(){
	//console.log($("#degUnit").html());
	if( -1 != $("#degUnit").html().indexOf("C") ){
		$("#degUnit").html("&deg;F");
		$("#degUnit1").html("&deg;F");
		$("#degUnit2").html("&deg;C");
	} else {
		$("#degUnit").html("&deg;C");
		$("#degUnit1").html("&deg;C");
		$("#degUnit2").html("&deg;F");
	}

}

function setYMinMaxButton(){
	if( -1 != $("#degUnit").html().indexOf("C") ){
		setYMinMax($("#yScaleMin").val(),$("#yScaleMax").val());
		setCookie('deg',"C",365);
	} else {
		setYMinMax((($("#yScaleMin").val() -32) * 5/9) ,(($("#yScaleMax").val() -32) * 5/9));
		setCookie('deg',"F",365);
	}

	setCookie('yMin',$("#yScaleMin").val(),365);
	setCookie('yMax',$("#yScaleMax").val(),365);

}

function setYMinMax(a, b) {
	
	plot.getOptions().yaxes[0].min = a;
	plot.getOptions().yaxes[0].max = b;
	plot.setupGrid();
	plot.draw();

}

function yScaleSettings(){
//	console.log($("#yscale").html());
	if ( "auto" == $("#yscale").html()) {
		$("#yscale").html("manual");
		$(".yScaleSettings").show();
		setCookie('yScaleMode',"manual",365);
		setYMinMaxButton();
	} else {
		$("#yscale").html("auto");
		$(".yScaleSettings").hide();
		setCookie('yScaleMode',"auto",365);
		setYMinMax(-10,100);		
	}
}

function toggleAlarmPos(){


	if ( "0px" == $("#chartControl").css("bottom") ) {

		alarmUp();

	} else {

		alarmDown();

	}
}


function setCookie(c_name,value,exdays){
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + 30);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
}

function updateHours(){

	var hours = $("#hours").val();

	if ( "" == hours || hours < 1 || hours > 168 ) {
		//alert("Numbers between 1 and 168 only");
		swal("Value outside of range!", "Only numbers between 1 and 168 are allowed")
		$("#hours").val("");
		return;
	}

	$("#titleChartHours").html(hours);	

	plotGraph(hours);

}

function obToAr(obj){
	var ar = [];
	for (key in obj) {
		if (obj.hasOwnProperty(key)) ar.push([key,obj[key]]);
	}
	return ar;
}

function plotGraph(hours){

	if ( null == hours ) hours = 24;

	setCookie('chartHours',hours,365);


	var url="json.php?hours="+hours+"&station_id=<? echo $station_id; ?>";
	$.getJSON(url, function(data) {
		//console.log(data[0]);
		
		var ext = obToAr(data[0]);
		var ce = obToAr(data[1]);
		var topSh = obToAr(data[2]);
		var botSh = obToAr(data[3]);
		var relayA = obToAr(data[4]);
		var relayB = obToAr(data[5]);
		var relayC = obToAr(data[6]);

		var lineObj = {fill: 0,lineWidth: 1};
		var switchLine = {fill: .5,lineWidth: 0};

		plot = $.plot("#placeholder", [{
			data: relayA,
			lines: switchLine,
			color: 'purple'
			
	
		},{
			data: relayB,
			lines: switchLine,
			color: '#B2B2B2'
	
		},{
			data: relayC,
			lines: switchLine,
			color: '#009900'
	
		},{
			data: ext,
			lines: lineObj,
			color: 'red'
	
		},{
			data: ce,
			lines: lineObj,
			color: 'blue'
		},{
			data: topSh,
			lines: lineObj,
			color: 'yellow'
	
		},{
			data: botSh,
			lines: lineObj,
			color: 'green'
	
		}], {
			grid: { hoverable: false, clickable: false },
					
			xaxis: {
				ticks: 6,
				tickFormatter: function (val) {
					var xdate = new Date(val * 1000)
					return xdate.toString("HH:mmtt<br>M/d")
				}
			},
			yaxis: {
				position: "left",
				tickFormatter: function (val) {
					
					return val+"&deg;C / "+(val* 9 / 5 + 32)+"&deg;F";
				},
				
				<?
				if ( "auto" == $yScaleMode ) {
				?>
				min: -10,
				max: 100 
				<?
				} else {
				?>
				min: <? printf($yMin); ?>,
				max: <? printf($yMax); ?> 
				<?
				}

				?>
			}
		});
	});


}




$( document ).ready(function(){
	plotGraph(<? echo $chartHours; ?>);
});
</script>


<?

$sql=sprintf("SELECT status.packet_date, sec_to_time(unix_timestamp()-unix_timestamp(packet_date)) AS ageTime,(unix_timestamp()-unix_timestamp(packet_date)) AS ageSeconds,deviceInfo.owner, deviceInfo.updateRate, deviceInfo.timeZone, deviceInfo.timeZoneOffsetHours, DATE_ADD(status.packet_date,INTERVAL deviceInfo.timeZoneOffsetHours HOUR) AS packet_date_local FROM status LEFT JOIN (deviceInfo) ON (status.serialNumber=deviceInfo.serialNumber) WHERE status.serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query);

/* calculate a human readable report received at */
$s="";
if ( $deviceInfo["ageSeconds"] > 59 ) {
	$rr=sprintf("Report received %s (hours:minutes:seconds) ago.",$deviceInfo["ageTime"]);
} else {
	if ( 1 != $deviceInfo["ageSeconds"] ) 
		$s="s";
	else
		$s='';

	$rr=sprintf("Report received %d second%s ago.",$deviceInfo["ageSeconds"],$s);
}
?>

<? if ( $deviceInfo["ageSeconds"] > 15*60 ) { ?>
<div class="caution">
<p>
This station is marked as supplying live data, however the data appears to be old. Please check the age of the data carefully before using it!
</p>
</div>
<?
} 

/* pull actual last record */
$sql=sprintf("SELECT * FROM thermok4_%s WHERE packet_date='%s'",$station_id,$deviceInfo["packet_date"]);
$sql=sprintf("SELECT * FROM thermok4_%s ORDER BY packet_date DESC LIMIT 1",$station_id);
$query=mysql_query($sql,$db);
$r=mysql_fetch_array($query);

/* column labels and units */
$sql=sprintf("SELECT * FROM thermok4_labels WHERE serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$l=mysql_fetch_array($query);

?>
<div align="center">
<table>
	<tr>
		<th colspan="2">Current Values</th>
	</tr>
	<tr>
		<th>Report Date:</th>
		<td>
			<? echo $deviceInfo["packet_date_local"] . " " . $deviceInfo["timeZone"]; ?>
			(<? echo $r["packet_date"]; ?> UTC)<br />
			<? echo $rr; ?>
		</td>
	</tr>
<? 
/* anemometers / pulse measurement */
if ( ""!=$l["t0L"] || ""!=$l["t1L"] || ""!=$l["t2L"] || ""!=$l["t3L"] ) { 
?>
	<tr>
		<th colspan="2">Temperature</th>
	</tr>
<? 
} 
$colors=array("Red","Blue","Yellow","Green");
for ( $i=0 ; $i<4 ; $i++ ) {
	$colLabel=$l["t" . $i . "L"];
	$colUnits=$l["t" . $i . "U"];

	if ( ""==$colLabel )
		continue;
?>
	<tr>
		<th><? printf("%s (%s): ",$colLabel,$colors[$i]); ?></th>
		<td>
<? 
$c=sprintf("%0.1f",$r["t" . $i]);
$k=sprintf("%0.1f",$r["t" . $i]+273.15);
$f=sprintf("%0.1f",1.8*$r["t" . $i]+32);


/* replace our units string with our temperature values */
$t=str_replace('c',$c,$colUnits);
$t=str_replace('f',$f,$t);
$t=str_replace('k',$k,$t);

if ( "-99.9" == $r["t" . $i] || "-1000.0" == $r["t" . $i] )
	echo "Not Connected";
else
	echo $t;

//	printf("%.1f %s %s",$r["t" . $i],$colUnits,$du); 
?>
		</td>
	</tr>
<?
}

/* analog channels */
if ( ""!=$l["v0L"] || ""!=$l["v1L"] || ""!=$l["v2L"] || ""!=$l["v3L"] ) { 
?>
	<tr>
		<th colspan="2">Voltage</th>
	</tr>
<? 
} 

/* analog channels */
for ( $i=0 ; $i<4 ; $i++ ) {
	$colLabel=$l["v" . $i . "L"];
	$colUnits=$l["v" . $i . "U"];

	if ( ""==$colLabel )
		continue;

	if ( 1 == $l["dualUnits"] ) 
		$colUnits="";

?>
	<tr>
		<th><? echo $colLabel; ?>:</th>
		<td><? printf("%0.2f %s",$r["vin" . $i],$colUnits);  ?></td>
	</tr>
<?
}

/* relay channels */
if ( ""!=$l["r0L"] || ""!=$l["r1L"] || ""!=$l["r2L"] ) { 
?>
	<tr>
		<th colspan="2">Relay States</th>
	</tr>
<? 
} 

/* relays */
for ( $i=0 ; $i<4 ; $i++ ) {
	$colLabel=$l["r" . $i . "L"];

	if ( ""==$colLabel )
		continue;


	if ( 1 == $r["relay" . $i] ) 
		$state='On';
	else
		$state='Off';

?>
	<tr>
		<th><? echo $colLabel; ?>:</th>
		<td><? echo $state; ?></td>
	</tr>
<?
}


$counterLabel=$l["c0L"];
$counterUnits=$l["c0U"];
if ( "" != $counterLabel || "" != $counterUnits ) {
?>
	<tr>
		<th colspan="2">Event Counter</th>
	</tr>
	<tr>
		<th><? echo $counterLabel; ?>:</th>
		<td><? printf("%d %s",$r["pulseCount"],$counterUnits); ?></td>
	</tr>
<?
}
?>
</table>

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

<h4>Last <span id="titleChartHours"><? echo $chartHours; ?></span> hours</h4>

<div id="placeholder" style="width: 100%; height: 450px; font-size: 14px;line-height: 1em;overflow: visible; overflow-x: hidden;"></div>

<div style="display: table;">

	<div style="display: table-cell; vertical-align: middle;">
		<table style="font-weight: bold; border: none; ">
			<tr>
				<td style="width: 25px;"><div style="background-color: red; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></div></td><td style="font-size: .75em;"><? echo $l["t" . 0 . "L"]?></td>
			</tr>
			<tr>
				<td style="width: 25px"><div style="background-color: blue; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></td><td style="font-size: .75em;"><? echo $l["t" . 1 . "L"]?></td>
			</tr>
			<tr>
				<td style="width: 25px"><div style="background-color: yellow; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></td><td style="font-size: .75em;"><? echo $l["t" . 2 . "L"] ?></td>
			</tr>
			<tr>
				<td style="width: 25px"><div style="background-color: green; width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></td><td style="font-size: .75em;"><? echo $l["t" . 3 . "L"] ?></td>
	<?
	 if ( ""!=$l["r0L"] ) { 
	?>
			</tr>
			<tr>
				<td style="width: 25px"><div style="background-color: rgba(128, 0, 128, 0.5); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></td><td style="font-size: .75em;"><? echo $l["r" . 0 . "L"]?></td>
	<? }
	 if ( ""!=$l["r1L"] ) { ?>
			</tr>
			<tr>
				<td style="width: 25px"><div style="background-color: rgba(178, 178, 178, 0.5); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></td><td style="font-size: .75em;"><? echo $l["r" . 1 . "L"]?></td>
	<? } 
	 if ( ""!=$l["r2L"] && false ) { ?>
			</tr>
			<tr>
				<td style="width: 25px"><div style="background-color: rgba(0, 153, 0, 0.5); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></td><td style="font-size: .75em;"><? echo $l["r" . 2 . "L"]?></td>
	<? } 
	if ( ""!=$l["r1L"] && ""!=$l["r0L"] ) { ?>
			</tr>
			<tr>
				<td style="width: 25px"><div style="background-color: rgba(121, 89, 121, 0.75); width:10px;height:10px; border: solid;margin-left:auto;margin-right:auto;"></td><td style="font-size: .75em;"><? echo $l["r" . 0 . "L"]." and ". $l["r" . 1 . "L"]?></td>
	<? } 
	?>
			</tr>
		</table>
	</div>


	<div style="display: table-cell; vertical-align: middle; text-align: left; ">
		<table style="">
			<tr>	
				<th colspan="2">Chart Settings</th>
			</tr>
			<tr>
			
					<th>Hours:</th><td> <input type="number" min="1" max="168" value="<? echo $chartHours; ?>" id="hours" style="width: 50px;">
					<button class="controlButton" onclick="updateHours();">Update hours</button></td>
			
			</tr>	
			<tr>
				<th>Y Scale Mode:</th>
					<td><button class="controlButton" onclick="yScaleSettings()"><span id="yscale"><? printf($yScaleMode); ?></span></button><br>
			</tr>
			<tr class="yScaleSettings" style="<? if ( "auto" == $yScaleMode ) echo "display: none;"; ?>">	
				<th>Y min:</th><td><input  type="number"  value="<? printf($yMin); ?>" id="yScaleMin" style="width: 50px;"><span id="degUnit">&deg;<? echo $deg; ?><span></td>
			</tr>
			<tr class="yScaleSettings" style="<? if ( "auto" == $yScaleMode ) echo "display: none;"; ?>">		
				<th>Y max:</th><td><input  type="number"  value="<? printf($yMax); ?>" id="yScaleMax" style="width: 50px;"><span id="degUnit1">&deg;<? echo $deg; ?><span></td>
			</tr>
			<tr class="yScaleSettings" style="<? if ( "auto" == $yScaleMode ) echo "display: none;"; ?>">
				<th colspan="2"><button style="margin-right:10px;" onclick="switchDegUnit()">Switch to <span id="degUnit2">&deg;<? echo $oDeg; ?><span></button><button onclick="setYMinMaxButton()">Apply</button></th>
			</tr>
		</table>
	</div>

	

</div>

<?
require $_SERVER["DOCUMENT_ROOT"] . "/world_foot.php";
?>
