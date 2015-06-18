<?php
# Erstellt von Mairu

defined ('main') or die ( 'no direct access' );

# Wandelt den Votecode aus dem Forum in HTML um
//FE_Vote2HTML(int Postid, string Posttext,[int View])   View: 0 = Normal; 1 = Preview; 2 = Ergebnis
function FE_Vote2HTML(){
	if(func_num_args() == 2){
		$post_id = func_get_arg(0);
		$txt = func_get_arg(1);
		$preview = 0;
	}elseif(func_num_args() == 3){
		$post_id = func_get_arg(0);
		$txt = func_get_arg(1);
		$preview = func_get_arg(2);
		if($preview == TRUE AND $preview < 2){
			$preview = 1;
		}
	}else{
		return(FALSE);
	}
	
	$vote_start = strpos($txt, '[vote]');
	$vote_end = strpos($txt, '[/vote]');
	$vote_count = substr_count($txt, '[vote]');
  
	if($vote_start === false OR $vote_end === false OR $vote_start > $vote_end){
		return($txt);
	}
	
	
	switch($preview){
		case 0:
			$erg = db_query('SELECT * FROM prefix_posts_poll WHERE post_id = '.$post_id);
			//if(db_num_rows($erg) >= 1){
			while($row = db_fetch_assoc($erg)){
				//$row = db_fetch_assoc($erg);
				$voters = explode('#',$row['voters']);
				$results = explode('#',$row['results']);
				$gesamt = array_sum($results);
				if($_SESSION['authid'] == 0){
					$voter = $_SERVER['REMOTE_ADDR'];
				}else{
					$voter = $_SESSION['authid'];
				}
				$vstart = $txt;
				$vote_txt = substr($txt, $vote_start + 6, $vote_end - $vote_start - 6);
				$vote_vtxt = substr($txt, $vote_start, $vote_end - $vote_start + 7 );
				$vstart = str_replace($vote_vtxt, '', $txt);
				$sm[0] = '/\[question\]/';
				$sm[1] = '/\[\/question\]/';
				$sm[2] = '/\[option=(\d+)\]/';
				
				
		  
				if(in_array($voter, $voters)){
					#User hat schon abgestimmt -> Ergebnis anzeigen
					$rm[0] = '<tr><td colspan="3" style="font-size:13px; font-weight:bold;">';
					$rm[1] = '';
					$rm[2] = '</td></tr><tr><td width="100">[\1]</td><td  width="20">%\1%</td><td>';
					  
					$vote_txt = '<table>'.$vote_txt;
					$vote_txt .= '</td></tr><tr><td>Votes: '.$gesamt.'</td></tr></table>';
					$vote_txt = preg_replace($sm,$rm,$vote_txt);
				
					foreach ($results as $key => $result){
						$vote_txt = str_replace('['.($key).']','<table height="15"><tr><td bgcolor="#3300cc" width="'.round($result/$gesamt*100,0).'"></td></tr></table>',$vote_txt);
						$vote_txt = str_replace('%'.($key).'%',$result,$vote_txt);    
					}     
				}else{#User hat noch nicht abgestimmt -> Abstimmungsformular
					$rm[0] = '<font style="font-size:13px; font-weight:bold;">';
					$rm[1] = '</font><br />';
					$rm[2] = '<input type="radio" name="vote" value="\1">';
					   
					$vote_txt = '<form method="POST" action="index.php?forum-vote-'.$post_id.'"><p>'.$vote_txt;
					$vote_txt .= '<input type="button" name="showerg" value="Ergebnis anzeigen" onclick="window.open (\'index.php?forum-vote-'.$post_id.'-showerg\', \'finduser\', \'status=no,scrollbars=yes,height=200,width=350\');"> <input type="submit" value="Abstimmen"></p>';
					$vote_txt .= '<input type="hidden" name="h_pk" value="'.$row['pp_pk'].'"/></form>';
					$vote_txt = preg_replace($sm,$rm,$vote_txt);
				}    
				
				if($vote_count > 1){
					$txt = str_replace($vote_vtxt, '', $txt);
					$vote_start = strpos($txt, '[vote]');
					$vote_end = strpos($txt, '[/vote]');
				}
				
				$txtoutput .= substr($txt, 0, $vote_start).$vote_txt.substr($txt, $vote_end + 7);
				//$txtoutput .= $vote_vtxt;
				
				 
		  //}
			}
			
			return($txtoutput);

		break;
		
		case 1:
			$vote_txt = substr($txt, $vote_start + 6, $vote_end - $vote_start - 6);
			$sm[0] = '/\[question\]/';
			$sm[1] = '/\[\/question\]/';
			$sm[2] = '/\[option=(\d+)\]/';
		  
			$rm[0] = '<font style="font-size:13px; font-weight:bold;">';
			$rm[1] = '</font><br />';
			$rm[2] = '<input type="radio" name="vote" value="\1">';
			   
			$vote_txt = '<form><p>'.$vote_txt;
			$vote_txt .= '</p></form>';
			$vote_txt = preg_replace($sm,$rm,$vote_txt);
		  
			$txt = substr($txt, 0, $vote_start).$vote_txt.substr($txt, $vote_end + 7);
			return($txt); 
		break;
	  
		case 2:
			#Ergebnis anzeigen
			$erg = db_query('SELECT * FROM prefix_posts_poll WHERE post_id = '.$post_id);
			if(db_num_rows($erg) == 1){
				$row = db_fetch_assoc($erg);
				$results = explode('#',$row['results']);
				$gesamt = array_sum($results);
					   
				$vote_txt = substr($txt, $vote_start + 6, $vote_end - $vote_start - 6);
				$sm[0] = '/\[question\]/';
				$sm[1] = '/\[\/question\]/';
				$sm[2] = '/\[option=(\d+)\]/';
				
				$rm[0] = '<tr><td colspan="3" style="font-size:13px; font-weight:bold;">';
				$rm[1] = '';
				$rm[2] = '</td></tr><tr><td width="100">[\1]</td><td  width="20">%\1%</td><td>';
				  
				$vote_txt = '<table>'.$vote_txt;
				$vote_txt .= '</td></tr><tr><td>Votes: '.$gesamt.'</td></tr></table>';
				$vote_txt = preg_replace($sm,$rm,$vote_txt);
			
				foreach ($results as $key => $result){
					$vote_txt = str_replace('['.($key).']','<table height="15"><tr><td bgcolor="#3300cc" width="'.($gesamt > 0 ? round($result/$gesamt*100,0) : 0).'"></td></tr></table>',$vote_txt);
					$vote_txt = str_replace('%'.($key).'%',$result,$vote_txt);    
				}
				return ($vote_txt);
			}
		break;
	}
}

# Überprüft ob der übergebene Text eine richtige Vote enthält und
# gibt die Anzahl der Antwortmöglichkeiten oder 0 zurück
function FE_ValidVote($txt){
	$vote_start = strpos($txt, '[vote]');
	$vote_end = strpos($txt, '[/vote]');
	
  
	if($vote_start === false OR $vote_end === false OR $vote_start > $vote_end){
		return(0);
	}
	$vote_txt = substr($txt, $vote_start + 6, $vote_end - $vote_start - 6);
	$q1 = strpos($vote_txt, '[question]');
	$q2 = strpos($vote_txt, '[/question]');
	if($q1 === false OR $q2 === false OR $q1 > $q2){
		$valid = FALSE;
	}else{
		$valid = TRUE;
	}
	return(substr_count($txt, '[option='));
	
	$i = 1;
	while(strpos($vote_txt, '[option='.$i.']')){
		$opt[$i] = strpos($vote_txt, '[option='.$i.']');
		if($i>1){
			for($j=1;$j<$i;$j++){
				if($opt[$i] < $opt[$j]){
					$valid = FALSE; #Überprüft ob die Reihenfolge richtig ist 
				}
			}
		}
		
		if(strpos($vote_txt, '[option='.$i.']',$opt[$i]+1)){
			$valid = FALSE; #Überprüft ob eine Option doppelt da ist
		}
		$i++;   
	}
	if($valid AND $i>2){
		return($i-1);
	}
	return(0);
}

# Trägt Vote ein, wenn der Eintrag in der Datenbank existiert
function FE_Vote($post_id,$option,$pppk){
	$erg = db_query('SELECT * FROM `prefix_posts_poll` WHERE post_id = '.$post_id.' AND pp_pk = '.$pppk);
	if(db_num_rows($erg) == 1){
		$row = db_fetch_assoc($erg);
		$voters = explode('#',$row['voters']);
		$results = explode('#',$row['results']);
		if($_SESSION['authid'] == 0){
			$voter = $_SERVER['REMOTE_ADDR'];
		}else{
			$voter = $_SESSION['authid'];
		}
		$voters[] = $voter;
		if(array_key_exists($option, $results)){
			$results[$option]++;
		}else{
			return(FALSE);
		}
		$voters = implode('#', $voters);
		$results = implode('#', $results);
		db_query('UPDATE `prefix_posts_poll` SET voters = "'.$voters.'", results = "'.$results.'" WHERE post_id = '.$post_id.' AND pp_pk = '.$pppk);
	}
}

#Erstellt einen Tabelleneintrag für eine Umfrage oder editiert/löscht eine Vorhandene, nur löschen oder hinzufügen möglich
function FE_CreateVote($post_id,$txt){
	$options = FE_ValidVote($txt); 
	$vote_count = substr_count($txt, '[vote]');	
	$erg = db_query('SELECT * FROM `prefix_posts_poll` WHERE post_id = '.$post_id);
	if(db_num_rows($erg) >= 1){
		while($row = db_fetch_assoc($erg)){
			if($options == 0){ //Lösche den Datenbankeintrag, wenn kein Vote mehr in dem Post enthalten ist
				db_query('DELETE FROM `prefix_posts_poll` WHERE post_id = '.$post_id);
				return(TRUE);
			}else{
				//$row = db_fetch_assoc($erg);
				$results = explode('#',$row['results']);
				$resanz = count($results)-1;
				if($resanz == $options){
					return(TRUE); //Ändere nichts, wenn sich am Vote nichts geändert hat (Anzahl der Optionen)
				}elseif($resanz > $options){
					//Bei Verringerung der Optionen Anzahl der Results löschen
					$results = array_slice($results,0,$options+1); 
				}else{
					for($i = $resanz; $i <= $options; $i++){
						//Füge noch leere Results ein
						$results[$i]=0;  
					}
				}
				$results = implode('#', $results);
				db_query('UPDATE `prefix_posts_poll` SET results = "'.$results.'" WHERE post_id = '.$post_id);
				return(TRUE);
			}
		}
    }else{
		//Erstellt Eintrag, wenn noch keiner vorhanden ist
		while($vote_count > 0){
			$vote_start = strpos($txt, '[vote]');
			$vote_end = strpos($txt, '[/vote]');
			$vote_vtxt = substr($txt, $vote_start, $vote_end - $vote_start + 7 );
			$options = FE_ValidVote($vote_vtxt); 
			if($options == 0){
				return(FALSE);
			}
			//for($i=1; $i <= $options; $i++){    
			//	$results[$i]=0;
			//}
			$results = '';
			$sopt = $options;
			while($options > 0){
				//$results[$options]=0;
				$results .= '#0';
				$options--;
			}
			//$results = '#'.implode('#', $results);
			db_query('INSERT INTO `prefix_posts_poll` (post_id,voters,results) VALUES ('.$post_id.', "", "'.$results.'")');
			$vote_count--;
			$txt = str_replace($vote_vtxt, '', $txt);
		}
		return(TRUE); 
	}
}
?>
