<?
/* This function takes a MySQL column/JSON key and creates a row in the table  */
function simpleDataRow($data,$title=""){
	//add the javascript
	global $pScript,$pRows;

	$pScript.=sprintf("$('#%s').html(data.%s);\n",$data,$data);
	//add a row table
	if($title==""){	
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$data,$data);
	}else{
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$title,$data);
	}

}
/* Thermok relays */
function relayDataRow($data,$title=""){
	//add the javascript
	global $pScript,$pRows;


	$pScript.=sprintf("if(data.%s == 0 ) {
		$('#%s').html('Off');
	} else {
		$('#%s').html('On');
	}\n",$data,$data,$data);
	//add a row table
	if($title==""){	
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$data,$data);
	}else{
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$title,$data);
	}

}
/* takes key and creates a sparkline 24 hour chart out of it */
function sparkLineDataRow($data,$title="",$label=""){
	//add the javascript
	global $pScript,$pRows, $station_id;

	$pScript.=sprintf("$('#%sSpark').html(data.%s+\" %s<br /><img src='spark_single.php?station_id=%s&parameter=%s' />\");\n",$data,$data,$label,$station_id,$data);
	//add a row table
	if($title==""){	
		$pRows.=sprintf("<tr><th>%s</th><td id='%sSpark'></td></tr>",$data,$data);
	}else{
		$pRows.=sprintf("<tr><th>%s</th><td id='%sSpark'></td></tr>",$title,$data);
	}

}
function sparkLineRow($data,$title=""){
	//add the javascript
	global $pScript,$pRows, $station_id;

	$pScript.=sprintf("$('#%sSpark').html(\"<img src='spark_single.php?station_id=%s&parameter=%s' />\");\n",$data,$station_id,$data);
	//add a row table
	if($title==""){	
		$pRows.=sprintf("<tr><th>%s</th><td id='%sSpark'></td></tr>",$data,$data);
	}else{
		$pRows.=sprintf("<tr><th>%s</th><td id='%sSpark'></td></tr>",$title,$data);
	}

}

/*  */
function toFixedDataRow($data,$title="",$fixed=2,$unit=""){
	//add the javascript
	global $pScript,$pRows;

	$pScript.=sprintf("$('#%s').html(parseFloat(data.%s).toFixed(%d)+\"%s\");\n",$data,$data,$fixed,$unit);
	//add a row table
	if($title==""){	
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$data,$data);
	}else{
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$title,$data);
	}

}

/* row that will count up */
function ageRow(){
	global $pScript,$pRows;
	$pRows.=sprintf("<tr><th>Age in Seconds:</th><td id='pageAge'></td></tr>",$title,$data);
}

/* for temperatures */
function toFixedTemperatureDataRow($data,$title="",$fixed=2,$unit=""){
	//add the javascript
	global $pScript,$pRows;

	$pScript.=sprintf("$('#%s').html(parseFloat(data.%s).toFixed(%d)+\"%s\");\n",$data,$data,$fixed,$unit);
	//add a row table
	if($title==""){	
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$data,$data);
	}else{
		$pRows.=sprintf("<tr><th>%s</th><td id='%s'></td></tr>",$title,$data);
	}

}

function anemometerSparkDataRow($anemometerNumber,$unit="MS"){
	global $pScript,$pRows, $station_id;
	
	$html=sprintf("parseFloat(data.windAverage%s%d).toFixed(1)+\" m/s average<br />\"+parseFloat(data.windSpeed%s%d).toFixed(1)+\" m/s gusting to \"+parseFloat(data.windGust%s%d).toFixed(1)+\" m/s<br /><img src='spark_single.php?station_id=%s&parameter=windAverage%s%d' alt=''>\"",$unit,$anemometerNumber,$unit,$anemometerNumber,$unit,$anemometerNumber,$station_id,$unit,$anemometerNumber);
	
	$pScript.=sprintf("$('#anemometer%s').html(%s);\n",$anemometerNumber,$html);
	//add a row table
	
	$pRows.=sprintf("<tr><th>Anemometer %d</th><td id='anemometer%s'></td></tr>",$anemometerNumber,$anemometerNumber);
	
}

function headline($headline){

	global $pRows;

	$pRows.=sprintf("<tr><th colspan=\"2\" >%s</th></tr>",$headline);
}

?>
