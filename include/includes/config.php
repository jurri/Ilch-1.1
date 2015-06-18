<?php
define ( 'DBHOST', 'localhost' );   # sql host
define ( 'DBUSER', 'root');  		# sql user
define ( 'DBPASS', '');  			# sql pass
define ( 'DBDATE', 'ilch1');  		# sql datenbank
define ( 'DBPREF', 'ic1_'); 		# sql prefix

//-- oracle verbindung
define('DBPASSOCI', '');  	# oracle pass
define('DBUSEROCI', '');  	# oracle schema
define('DBSERVEROCI', ''); 	# oracle server


/***********************************************
## eine weiter unterteilung für Oracle Server
## damit wenn gewollt man von mehreren Servern gleichzeitig abfragen starten kann
## dafür unentlich viele Cases anlegen mit dem namen des schemata
#### ACHTUNG:
#### Sollte man nicht auf die Hauptverbindung zugreifen
#### so ändert sich der Befehl von:
#### oci_query("Query") ->in-> oci_schema_query("Schema", "Query")
*/
function oci_schema($erg){
	$server = "";
	switch($erg){
		case "schema_name":
			$user = "";
			$pwd = "";
		break;
		
		default:
			echo "Bitte dem Admin bescheid geben!!!<br/>die Verbindung zum Server scheint nicht zu funktionieren.";
	}
	
	$conn = oci_connect($user, $pwd, $server);
	
	if (!$conn) { //ausgabe des Fehlers
		$e = oci_error();
		echo "connection zum Schema: ".$erg." ging schief<br/>";
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}
	return($conn);
}
?>