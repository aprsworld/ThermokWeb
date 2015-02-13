<?

/* The script contained within this area only applies to Thermok chart */

$chartHours = 24;
if ( isset($_COOKIE["chartHours"]) ) {
	$chartHours = $_COOKIE["chartHours"];
}

$yScaleMode="Auto";
$yMin=-10;
$yMax=100;


if ( isset( $_COOKIE["yScaleMode"] ) ) $yScaleMode = $_COOKIE["yScaleMode"];

if ( isset( $_COOKIE["yMin"] ) ) $yMin = $_COOKIE["yMin"];

if ( isset( $_COOKIE["yMax"] ) ) $yMax = $_COOKIE["yMax"];

if ( "Auto" == $yScaleMode ) {
	$yScaleModeButton = "Switch to Manual";	
} else {
	$yScaleModeButton = "Switch to Auto";
}

?>
$(document).ready(function(){

	<? if ( $_COOKIE["yScaleMode"] == "Auto" ) { ?>
	disableManual();
	<? } ?>

	<? if ( $_COOKIE["collapseSettings"] == "true" ) { ?>
		$(".collapse").hide();
	<? } ?>

	$(document).scrollTop( $("#currentTable").offset().top );
	chartTick();
});

var plot;

function noTimerLoad(){
	var url="jsonNonView.php?station_id=<? echo $station_id; ?>";	
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
				$('#t0').html(parseFloat(data.t0* 9 / 5 + 32).toFixed(2)+" &deg;F");
				$('#t1').html(parseFloat(data.t1* 9 / 5 + 32).toFixed(2)+" &deg;F");
				$('#t2').html(parseFloat(data.t2* 9 / 5 + 32).toFixed(2)+" &deg;F");
				$('#t3').html(parseFloat(data.t3* 9 / 5 + 32).toFixed(2)+" &deg;F");

			}
		});
}

function switchDegUnit(){
	//console.log($("#degUnit").html());
/*	if( -1 != $("#degUnit").html().indexOf("C") ){
		$("#degUnit").html("&deg;F");
		$("#degUnit1").html("&deg;F");
		$("#degUnit2").html("&deg;C");
	} else {
		$("#degUnit").html("&deg;C");
		$("#degUnit1").html("&deg;C");
		$("#degUnit2").html("&deg;F");
	}
*/	
	if ( "C" == $("input[name=tempRadio]:checked").val() ) {
		setCookie('deg',"C",365);
		$("#degUnit").html("&deg;C");
		$("#degUnit1").html("&deg;C");
	} else {
		setCookie('deg',"F",365);
		$("#degUnit").html("&deg;F");
		$("#degUnit1").html("&deg;F");
	}
		
	noTimerLoad();
	

	

}

function disableManual(){

		$(".manualScale").css("background-color","grey");
		$(".manualScale").css("color","darkgrey");

		$("#buttonScale").prop('disabled', true);
		$("yScaleMin").prop('disabled', true);
		$("yScaleMax").prop('disabled', true);

}

function setYMinMaxButton(){
	if( -1 != $("input[name=tempRadio]:checked").val().indexOf("C") ){
		setYMinMax($("#yScaleMin").val(),$("#yScaleMax").val());
		//
	} else {
		setYMinMax((($("#yScaleMin").val() -32) * 5/9) ,(($("#yScaleMax").val() -32) * 5/9));
		//setCookie('deg',"F",365);
	}

	setCookie('yMin',$("#yScaleMin").val(),365);
	setCookie('yMax',$("#yScaleMax").val(),365);

}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

function setYMinMax(a, b) {
	
	plot.getOptions().yaxes[0].min = a;
	plot.getOptions().yaxes[0].max = b;
	plot.setupGrid();
	plot.draw();

}
var miny, maxy;
function yScaleSettings(){
	//console.log($("input[name=scaleRadio]:checked").val());
	if ( "Manual" == $.trim($("input[name=scaleRadio]:checked").val())) {
		//$("#yscale").html("Switch to Auto");
		//$(".yScaleSettings").show();
		setCookie('yScaleMode',"Manual",365);
		setYMinMaxButton();
		//$("#mode").html("Manual");

		$("#buttonScale").prop('disabled', false);
		$("yScaleMin").prop('disabled', false);
		$("yScaleMax").prop('disabled', false);

		$(".manualScale").css("background-color","#0055a5");
		$(".manualScale").css("color","white");
		
		$("#tdwhite1").css("background-color","white");
		$("#tdwhite1").css("color","black");	
		$("#tdwhite2").css("background-color","white");
		$("#tdwhite2").css("color","black");


	} else {
		//$("#yscale").html("Switch to Manual");
		//$(".yScaleSettings").hide();

		disableManual();
		setCookie('yScaleMode',"Auto",365);
		setYMinMax(miny,maxy);
		//$("#mode").html("Auto");		
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
		miny = data.minY;
		maxy = data.maxY;
		var ext = obToAr(data[0]);
		var ce = obToAr(data[1]);
		var topSh = obToAr(data[2]);
		var botSh = obToAr(data[3]);
		var relayA = obToAr(data[4]);
		var relayB = obToAr(data[5]);
		var relayC = obToAr(data[6]);
		for ( var i = 0 ; i < relayA.length ; i++ ) {
			if ( relayA[i][1] != 0 ) {
				relayA[i].push(-1000);
			} else {
				relayA[i].push(0);
			}
		}
		for ( var i = 0 ; i < relayB.length ; i++ ) {
			if ( relayB[i][1] != 0 ) {
				relayB[i].push(-1000);
			} else {
				relayB[i].push(0);
			}
		}
		for ( var i = 0 ; i < relayC.length ; i++ ) {
			if ( relayC[i][1] != 0 ) {
				relayC[i].push(-1000);
			} else {
				relayC[i].push(0);
			}
		}
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
				if ( "Auto" == $yScaleMode ) {
				?>
				min: data.minY,
				max: data.maxY 
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

<?

$refSec = 60;

if (isset($_COOKIE["refSec"]))
	$refSec = $_COOKIE["refSec"];


?>

var timerChart;
var refSec = <? echo $refSec; ?>;
function chartSetRefresh(){
	
	refSec = $( "#chartSetRefresh" ).val();
	setCookie("refSec",refSec,365);
	

}

function chartTick(){
	console.log("updateChart");
	plotGraph(<? echo $chartHours; ?>);
	timerChart = setTimeout(chartTick,refSec*1000);
	chartAge=0;
}



function goWideScreen(bool){
	if(bool == "false"){
		setCookie("wideScreen","false",365);
	} else {
		setCookie("wideScreen","true",365);
	}
	window.location.replace("?station_id=<? echo $station_id; ?>");

}

function collapseSettings(){
	
	if ( $(".collapse").is(":visible") ) {
		$(".collapse").hide();
		setCookie("collapseSettings", true, 365);	
	} else {
		$(".collapse").show();
		setCookie("collapseSettings", false, 365);	
	}
}




