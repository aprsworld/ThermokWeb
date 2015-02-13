<?

header("Cache-Control: no-cache");
header("Content-Type: text/plain");
require_once $_SERVER['DOCUMENT_ROOT'] . '/world_config.php';


$station_id=cleanStationID($_REQUEST["station_id"]);

$db = _open_mysql('worldData');

$sql=sprintf("SELECT * FROM deviceInfo WHERE serialNumber='%s'",$station_id);
$query=mysql_query($sql,$db);
$deviceInfo=mysql_fetch_array($query,MYSQL_ASSOC);

$sql=sprintf("SELECT *, (UNIX_TIMESTAMP()-UNIX_TIMESTAMP(packet_date)) AS ageSeconds, DATE_ADD(packet_date,INTERVAL %d HOUR) AS packet_date_local FROM thermok4_%s ORDER BY packet_date DESC LIMIT 1",$deviceInfo['timeZoneOffsetHours'],$station_id);

//echo $sql;

$query=mysql_query($sql,$db);

$r=mysql_fetch_array($query,MYSQL_ASSOC);

$r["timeZone"]=$deviceInfo['timeZone'];

$r["localTime"]=sprintf("%s (%s)",$r["packet_date_local"],$r["timeZone"]);

echo json_encode($r);

?>
