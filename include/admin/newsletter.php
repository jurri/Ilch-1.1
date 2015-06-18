<?php
// Copyright by: Manuel
// Support: www.ilch.de
# modded by FeTTsack
defined ('main') or die ('no direct access');
defined ('admin') or die ('only admin access');

$design = new design ('Admins Area', 'Admins Area', 2);
$design->header();

if(isset($_POST['SEND']) and chk_antispam('newsletter', true)){
    $mailopm = $_POST['r_mail_pm'];
    $zahler = 0;
	
	//-- Prüft und ermittelt an wen alles die Mail oder PM geschickt werden soll.
	if($_POST['ch_one_user'] == 1){
		$user = $_POST['s_user'];
		$q = "SELECT DISTINCT u.email, name as uname, id as uid FROM prefix_user u, prefix_newsletter n WHERE id = $user AND n.email = u.email";
	}elseif($_POST['ch_all_recht'] == '1'){
		$q = "SELECT DISTINCT u.email, name as uname, id as uid FROM prefix_user u, prefix_newsletter n WHERE n.email = u.email";
	}elseif($_POST['ch_all_gruppe'] == '1' AND $_POST['ch_all_recht'] == ''){
		$q = "SELECT DISTINCT u.email, u.name as uname, u.id as uid FROM ic1_user u, ic1_groups g, ic1_groupusers gu, prefix_newsletter n WHERE u.id = gu.uid AND gu.gid = g.id AND n.email = u.email";
	}elseif($_POST['ch_all_gruppe'] == '' AND $_POST['ch_all_recht'] == ''){
		$grecht = '';
		for($i=0; $i<10; $i++){
			if(isset($_POST['grecht_'.$i])){
				if($grecht != ''){
					$grecht .= ',-'.$i;
				}else{
					$grecht .= '-'.$i;
				}
			}
		}
		$q = "SELECT DISTINCT u.email, name as uname, id as uid FROM prefix_user u, prefix_newsletter n WHERE recht IN ( $grecht ) AND n.email = u.email";
		
		if($grecht == ''){
			$groups = '';
			$sql = db_query("SELECT id FROM prefix_groups");
			while($r = db_fetch_assoc($sql)){
				if(isset($_POST['groups_'.$r['id']])){
					if($groups != ''){
						$groups .= ','.$r['id'];
					}else{
						$groups .= $r['id'];
					}
				}
			}
			$q = "SELECT DISTINCT u.email, u.name as uname, u.id as uid FROM ic1_user u, ic1_groups g, ic1_groupusers gu, prefix_newsletter n WHERE u.id = gu.uid AND n.email = u.email AND gu.gid IN ( $groups )";
		}
	}
	$erg = db_query($q);
	
	
    if(db_num_rows($erg) > 0){	
	
		$zahl = db_fetch_assoc(db_query("SELECT t.auto_increment as zahl FROM information_schema.`TABLES` t where t.table_name = 'ic1_newsletter_send'"));
		$zahl = $zahl['zahl'];
	
		//-- Mail oder PM wird versändet 
        if($mailopm == 'mail'){
        	$emails = array('bbc', $allgAr['adminMail']);
			while($row = db_fetch_object($erg)){
				if(preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', $row->email) == 1){
					$emails = $row->email;
					$zahler++;
					icmail($emails, $_POST['bet'], $_POST['txt'], '', isset($_POST['html']));
					db_query("INSERT INTO prefix_newsletter2user (nlu_fknl, nlu_fkuid) VALUES ($zahl, ".$row->uid.")");
  				}
        	}
        }elseif($mailopm == 'pm'){
        	$uids = array();
			while($row = db_fetch_object($erg)){
            	$uids[] = $row->uid;
        		$zahler++;
				db_query("INSERT INTO prefix_newsletter2user (nlu_fknl, nlu_fkuid) VALUES ($zahl, ".$row->uid.")");
        	}
			sendpm($_SESSION['authid'], $uids, 'gfa-Newsletter: '.escape($_POST['bet'], 'string'), escape($_POST['txt'], 'string'), -1);
		}

		db_query("INSERT INTO prefix_newsletter_send (`nls_strtext`, `nls_strbetreff`, `nls_art`, `nls_anzahl`)
					VALUES ('".$_POST['txt']."', '".$_POST['bet']."', '$mailopm', $zahler)");
			
		//-- für die Schreibweise was verschickt wurde.
        if($mailopm == 'mail'){
            $eMailorPmsg = 'eMail(s)';
        }elseif($mailopm == 'pm'){
            $eMailorPmsg = 'Private Nachrichte(n)';
        }

        wd('admin.php?newsletter', 'Es wurde(n) '.$zahler.' '.$eMailorPmsg.' verschickt.', 5);
    }else{
        wd('admin.php?newsletter', 'F&uuml;r diese Auswahl konnte nichts gefunden werden.', 5);
    }
}else{
    $tpl = new tpl('newsletter', 1);
	
	//-- Spamfilter
	$ar['antispam'] = get_antispam('newsletter', 0, true);	
	
	if($_GET['newsletter'] != ''){
		if(strpos($_GET['newsletter'], ';') == 0){
			$nlid = $_GET['newsletter'];
			$erg_send = db_query("SELECT u.* FROM prefix_newsletter2user, prefix_user u WHERE nlu_fknl = $nlid AND nlu_fkuid = u.id");
			$erg_news = db_fetch_assoc(db_query("SELECT * FROM prefix_newsletter_send WHERE nls_pk = $nlid"));
			$ar['archiv'] = '<tr class="Cdark"><td valign="top">'.$erg_news['nls_dtmcreate'].'</td><td valign="top">'.$erg_news['nls_strbetreff'].'</td><td valign="top">'.$erg_news['nls_strtext'].'</td><td valign="top">'.$erg_news['nls_art'].'</td><td valign="top">'.$erg_news['nls_anzahl'].'</td></tr>';
		
			$ar['aruser'] = '';
			while($row = db_fetch_assoc($erg_send)){
				$ar['aruser'] .= '<tr class="Cdark"><td valign="top">'.$row['name'].'</td><td valign="top">'.$row['email'].'</td>';
				$erg_nl = db_query("SELECT * FROM prefix_newsletter WHERE email = '".$row['email']."'");
				if(db_num_rows($erg_nl) >= 1){
					$ar['aruser'] .= '<td valign="top"><font color="#00ff00">aktiv</font></td></tr>';
				}else{
					$ar['aruser'] .= '<td valign="top"><font color="#ff0000">deaktiviert</font></td></tr>';
				}	
			}
		
			$tpl->set_ar_out($ar, 2);
		}
	}else{
	
		if(isset($_POST['sub_archiv'])){
			$ar['archiv'] = '';
			$erg_archiv = db_query("SELECT * FROM prefix_newsletter_send ORDER BY nls_dtmcreate DESC");
			while($row = db_fetch_assoc($erg_archiv)){
				$ar['archiv'] .= '<tr class="Cdark"><td valign="top"><a href="admin.php?newsletter='.$row['nls_pk'].'" title="Um zu sehen an wen du diese Mail geschickt hast.">'.$row['nls_dtmcreate'].'</a></td><td valign="top">'.$row['nls_strbetreff'].'</td><td valign="top">'.$row['nls_strtext'].'</td><td valign="top">'.$row['nls_art'].'</td><td valign="top">'.$row['nls_anzahl'].'</td></tr>';
			}
			$tpl->set_ar_out($ar, 1);
		}else{
			//-- Checkbox Grundrechte
			$ar['grecht'] = '';
			$qry = db_query('SELECT ABS(id) as id, name FROM prefix_grundrechte ORDER BY id');	
			while($r = db_fetch_assoc($qry)){
				$ar['grecht'] .= '<span style="white-space: nowrap; margin-right: 5px;"><input type="checkbox" id="grecht_'.$r['id'].'" name="grecht_'.$r['id'].'"/><label for="grecht_'.$r['id'].'">'.$r['name']."</label></span>\n";
			}
			
			//-- Checkbox Groups
			$ar['groups'] = '';
			$qry = db_query('SELECT id, name FROM prefix_groups ORDER BY id');
			while($r = db_fetch_assoc($qry)){
				$ar['groups'] .= '<span style="white-space: nowrap; margin-right: 5px;"><input type="checkbox" id="groups_'.$r['id'].'" name="groups_'.$r['id'].'"/><label for="groups_'.$r['id'].'">'.$r['name']."</label></span>\n";
			}
			
			//-- Dropdown User
			$erg = db_query("SELECT * FROM prefix_user");
			$ar['auser'] = '<select name="s_user"/><option selected="selected" disabled="disabled">Bitte erst den Empf&auml;nger ausw&auml;hlen</option>';
			while($row = db_fetch_assoc($erg)){
				$ar['auser'] .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
			}
			$ar['auser'] .= '</select>';
			
			$erg_nl = db_query("SELECT u.* FROM prefix_newsletter n, prefix_user u WHERE n.email = u.email");
			while($row = db_fetch_assoc($erg_nl)){
				$ar['nlaktivuser'] .= '<tr><td>'.$row['name'].'</td><td>'.$row['email'].'</td></tr>';
			}
			
			
			$tpl->set_ar_out($ar, 0);
		}
	}
}

$design->footer();
?>