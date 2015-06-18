<?php
#   Copyright by: FeTTsack
#   Support: www.fettsack.de.vc


##letzter besuchter User
/*
function last_user($uid){
 $lb = mysql_fetch_object(mysql_query("SELECT last_bes, last_user_time FROM prefix_user WHERE id = ".$uid));
 $lba = explode('#',$lb->last_bes);
 $lbt = explode('#',$lb->last_user_time);
 $name = '';
 
foreach ($lba as $k => $v) {
     if ($v < 1) { continue; }
  $besname = @mysql_result($sql = mysql_query("SELECT name FROM prefix_user WHERE id = ".$v),0,0);
  $besstaat = @mysql_result($sql,0,1); 
  $time = date("d.m.Y \u\m H:i",$lbt[$k]);
  $name .= "<div><a href=\"index.php?user-details-$v\" target=\"_self\" title=\"besucht am $time Uhr\">$besname</a></div>";
 }
 return($name);
} 
*/

## alter vom Geburtsdatum rausfinden
function getage($gebdtm){
	if($gebdtm !== "0000-00-00"){
		$gebdatum = date('d.m.Y',strtotime($gebdtm));
	    $tag   = date('d',strtotime($gebdtm));
	    $monat = date('m',strtotime($gebdtm));
	    $jahr  = date('Y',strtotime($gebdtm));
		$jetzt = mktime(0,0,0,date("m"),date("d"),date("Y"));
	    $geburtstag = mktime(0,0,0,$monat,$tag,$jahr);
	    $alter   = intval(($jetzt - $geburtstag) / (3600 * 24 * 365));
	} else {
		$gebdatum = "Kein Datum angegeben";
		$alter = "n/a";
	}
	return($alter);
}
 
 ##spezialrangausgeben
function spezrang ($uid) {
    if ( empty($uid) ) {
      $rRang = 'Gast';
    } else {
      $rRang = @db_result(db_query("SELECT bez FROM prefix_user LEFT JOIN prefix_ranks ON prefix_ranks.id = prefix_user.spezrank WHERE prefix_user.id = ".$uid),0);
    }

  return ($rRang);
}

##geschlecht mit bild darstellen
function getgender ($name,$genderdb) {
	if($genderdb==1){
	$gender='<img src="include/images/forum/male-symbol.png" width="28px" height="28px" alt="m&auml;nnlich" border="0">&nbsp;'.$name;
	} elseif($genderdb==2){
	$gender='<img src="include/images/forum/female-symbol.png" width="28px" height="28px" alt="weiblich" border="0">&nbsp;'.$name;
	} else {
	$gender='<img src="include/images/forum/Unentschlossen.png" width="16px" height="16px" alt="Unentschlossen" border="0">&nbsp;'.$name;
	}
return ($gender);
}


//facebook like button ...
function get_like_button($url) {
    global $allgAr;
    if ($allgAr['fb_active'] == 0) {
        return('');
    } else {
        $fb_send = 'true';
        $fb_width = $allgAr['fb_width'];
        $fb_faces = 'true';
        if ($allgAr['fb_send'] == 0) {
            $fb_send = 'false';
        }
        if ($allgAr['fb_faces'] == 0) {
            $fb_faces = 'false';
        }
        $like_button = '<div class="fb-like" data-href="http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/index.php?'.$url.'" data-send="'.$fb_send.'" data-width="'.$fb_width.'" data-show-faces="'.$fb_faces.'"></div>';
        return($like_button);
    }
}
?>