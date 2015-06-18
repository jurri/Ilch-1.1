<?php
#   Copyright by FeTTsack
#   Support www.buw.de


defined ('main') or die ( 'no direct access' );

function oci_db(){
	define('oci_conn', @oci_connect(DBUSEROCI, DBPASSOCI, DBSERVEROCI));
	
	if (!oci_conn) { //ausgabe des Fehlers
		$e = oci_error();
		echo "Connection zum Schema: ".DBUSEROCI." ging schief<br/>";
		echo "Bitte überprüfen sie ihre Oracle daten !!!<br/>";
		trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}
	return(oci_conn);
}

function oci_query($erg){
	$execute = oci_parse(oci_db(), $erg);
	oci_execute($execute);
	oci_close(oci_conn);
	return($execute);
}

function oci_shema_query($sch, $query){
	$execute = oci_parse(oci_schema($sch), $query);
	oci_execute($execute);
	oci_close(oci_conn);
	return($execute);
}

function oci_datetime($erg){
	$exec = "to_date('$erg', 'dd.mm.yyyy hh24:mi:ss')";
	return($exec);
}

function oci_date($erg){
	$exec = "to_date('$erg', 'dd.mm.yyyy')";
	return($exec);
}

function oci_time($erg){
	$exec = "to_date('$erg', 'hh24:mi:ss')";
	return($exec);
}
?>