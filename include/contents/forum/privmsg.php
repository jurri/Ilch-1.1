<?php
// Copyright by: Manuel Staechele
// Support: www.ilch.de
# modded by FeTTsack

defined ('main') or die ('no direct access');

//Limit wie viele Nachrichten pro Seite angezeigt werden
$limit = 60;
//Farbe für Multipageanzeige bei Archiv (css)
$color = '#9DBDD4';

function getSDmon($time){
	$m = date('n', $time);
	$s = $m == 3 ? 8 : 3;
	return substr(getDmon($m),0,$s);
}

$title = $allgAr['title'] . ' :: Forum :: Private Nachrichten';
$hmenu = $extented_forum_menu . '<a class="smalfont" href="index.php?forum">Forum</a><b> &raquo; </b><a class="smalfont" href="index.php?forum-privmsg">Private Nachrichten</a>' . $extented_forum_menu_sufix;
$design = new design ($title , $hmenu, 1);
$design->header();

if ($allgAr['Fpmf'] != 1) {
    echo 'Private Nachrichten wurden von dem Administrator komplet gesperrt';
    echo '<br><a href="javascript:history.back(-1)">zurück</a>';
    $design->footer(1);
} elseif (!loggedin()) {
    echo '<br>Gäste dürfen keine Privaten Nachrichten Verschicken!';
    $tpl = new tpl ('user/login');
    $tpl->set_out('WDLINK', 'index.php', 0);
    $design->footer(1);
} elseif (db_result(db_query("SELECT opt_pm FROM prefix_user WHERE id = " . $_SESSION['authid']), 0) == 0) {
    echo 'Im <a href="index.php?user-profil">Profil</a> einstellen das du die PrivMsg Funktion nutzen m&ouml;chtest';
    $design->footer(1);
}

$uum = $menu->get(2);
if ($uum == 'delete' and isset($_POST['toArchiv'])) {
	$uum = 'toArchiv';
}

switch ($uum) {
    case 'new' :
        // neue pm schreiben und eintragen
		$show_formular = true;
        $txt = '';
        $bet = '';

        if (isset($_POST['sub'])) {
            $txt = escape($_POST['txt'], 'textarea');
            $bet = escape($_POST['bet'], 'string');
			$name = escape($_POST['name'], 'string');
        	$names = explode(',', $name);
        	$count = count($names);
        	if ($count > 1) {
        		//mehrere Empfänger
				$namessql = array();
        		for($i = 0; $i < $count; $i++){
        			$name = trim($names[$i]);
					$names[$i] = $name;
        			$namessql[] = "'{$name}'";
        		}
        		$userids = array();
        		$qry = db_query("SELECT id, name FROM prefix_user WHERE name IN (".implode(', ', $namessql).")");
        		while ($r = db_fetch_assoc($qry)){
        			$userids[] = $r['id'];
        			$usersfound[] = $r['name'];
        		}
        		$countids = count($userids);
        		if ($countids) {
        			if ($count == $countids) {
        				$wdtext = '';
        				$wdtime = 5;
					} else {
						$notfound = array_diff($names, $usersfound);
						$wdtext = 'Folgende Namen konnten nicht gefunden werden: '.implode(', ', $notfound);
						$wdtime = 10;
					}
					if (isset($_POST['opt_empf']) AND $_POST['opt_empf'] == 1) {
						$txt .= '\n\n---------- INFO ----------\nNachricht wurde versendet an '.implode(', ',$usersfound).'.';
					}
					sendpm($_SESSION['authid'], $userids, $bet, $txt);
        			$wdtext .= "{$countids} PMs erfolgreich verschickt.<br />";
        			wd('index.php?forum-privmsg', $wdtext, $wdtime);
        			$show_formular = false;
				} else {
					echo 'Keinen der Empf&auml;nger gefunden.<br />';
				}
        	} elseif (1 == db_result(db_query("SELECT count(*) FROM prefix_user WHERE name = BINARY '" . $name . "'"), 0)) {
				$unqid .= 1;
				$eid = db_result(db_query("SELECT id FROM prefix_user WHERE name = BINARY '" . $name . "'"), 0);
				sendpm($_SESSION['authid'], $eid, $bet, $txt);
				wd('index.php?forum-privmsg', 'Die Nachricht wurde erfolgreich gesendet');
				$show_formular = false;
            } else {
                echo 'Dieser Empf&auml;nger konnte nicht gefunden werden';
            }
        }

        if ($show_formular === true) {
            $name = '';
            $empfid = 0;
            if (isset($_REQUEST['empfid'])) {
                $empfids = explode(',', $_REQUEST['empfid']);
				$count = count($empfids);
				if ($count) {
					for($i = 0; $i < $count; $i++){
						$int = intval($empfids[$i]);
						if ($int > 0) {
							$empfids[$i] = $int;
						}
					}
				}
				$qry = db_query("SELECT name FROM prefix_user WHERE id IN (".implode(', ', $empfids).")");
            	$names = array();
            	while ($r = db_fetch_assoc($qry)){
            		$names[] = $r['name'];
            	}
            	$name = implode(', ', $names);
			}
            $ar = array (
                'name' => $name,
                'SMILIES' => getsmilies(),
                'TXT' => $txt,
                'BET' => $bet,
                );

            if (isset($_REQUEST['text'])) {
                $ar['TXT'] = unescape(escape($_REQUEST['text'], 'textarea'));
            }
            if (isset($_REQUEST['anhang'])) {
                $x = explode("\n", unescape(escape(urldecode($_REQUEST['anhang']), 'textarea')));
                $n = '';
                for ($i = 0; $i <= count($x); $i++) {
                    if (empty($x[$i])) {
                        continue;
                    }
                    $n .= '> ' . $x[$i] . "\n";
                }
                $ar['TXT'] .= "\n\n" . $n;
            }
            if (isset($_POST['bet'])) {
                $ar['BET'] = unescape(escape($_REQUEST['bet'], 'string'));
            }
            if (isset($_POST['re']) AND strpos ($ar['BET'], 're') === false AND strpos ($ar['BET'], 'Re') === false AND strpos ($ar['BET'], 'RE') === false) {
                $ar['BET'] = 'Re(1): ' . $ar['BET'];
            } elseif (isset($_POST['re'])) {
                $x = preg_replace("/re\((\d+)\):.*/i", "\\1", trim($ar['BET']));
                if (is_numeric($x)) {
                    $x = $x + 1;
                    $ar['BET'] = preg_replace("/(re)\(\d+\):(.*)/i", "\\1(" . $x . "):\\2", $ar['BET']);
                }
            }

            $tpl = new tpl ('forum/pm/new');
            $tpl->set_ar_out($ar, 0);
		}
        break;

	case 'edit' :
		//message bearbeiten
		$pmid = escape($menu->get(3), 'integer');
		//zugehörige uniqueid
		$erg = db_query('SELECT groupid FROM `prefix_pm` WHERE id = "'.$pmid.'" ');
		$r = db_fetch_assoc($erg);
		$gid = $r['groupid'];
		
		if (isset($_POST['sub'])) {
			$txt = escape($_POST['txt'], 'textarea');
			$bet = escape($_POST['bet'], 'string');
			
			$pmid = escape($_POST['pmids'], 'string');
			$pmids = explode(',', $pmid);
			$countids = count($pmids);
			
			db_query("UPDATE prefix_pm SET titel = '".$bet."', txt = '".$txt."' WHERE id IN (".implode(', ', $pmids).")");
        	
        	if ($countids > 1) {
				$wdtext .= "{$countids} PMs erfolgreich geändert.<br />";
			} else {
				$wdtext .= "1 PM erfolgreich geändert.<br />";
			}
			$wdtime = 5;
        	wd('index.php?forum-privmsg-showsend', $wdtext, $wdtime);
        	$show_formular = false;
			
			$design->footer(1);
		}
		
		//alle Empfänger mit gleicher uniqueid ermitteln
        $res = db_query('SELECT id, eid FROM `prefix_pm` WHERE groupid = "'.$gid.'" AND gelesen = 0');
        while ($r = db_fetch_assoc($res)) {
			$pmids[] = $r['id'];
			$nameids[] = $r['eid'];
		}
		$pmids = implode(', ', $pmids);
		
		$res2 = db_query("SELECT id, name FROM prefix_user WHERE id IN (".implode(', ', $nameids).")");
		while ($r2 = db_fetch_assoc($res2)) {
			$usernames[] = $r2['name'];
		}
		$empf = implode(', ', $usernames);
		
		$erg = db_query('SELECT titel, txt FROM `prefix_pm` WHERE groupid = "'.$gid.'" ');
		$row = db_fetch_assoc($erg);
		//$row['txt'] = bbcode($row['txt']);
		$txt = $row['txt'];
		$bet = $row['titel'];
		
		$ar = array (
			'name' => $empf,
			'SMILIES' => getsmilies(),
            'TXT' => $txt,
            'BET' => $bet,
			);
		
		$tpl = new tpl ('forum/pm/edit');
		$tpl->set('pmids', $pmids);
		$tpl->set_ar_out($ar,0);
		break;

    case 'showmsg' :
        // message anzeigen lassen
        $pid = escape($menu->get(3), 'integer');
		$soeid = ($menu->get(4) == 's' ? 'eid' : 'sid');
        $erg = db_query("SELECT a.gelesen, a.eid, a.sid, a.id, b.name, a.titel, a.time, a.txt, a.archiv, a.groupid, a.anz_in_group FROM `prefix_pm` a LEFT JOIN prefix_user b ON a." . $soeid . " = b.id WHERE a.id = " . $pid); //neu -> a.uniqueid
        $row = db_fetch_assoc($erg);
		//neu//
		$gid = $row['groupid'];
		$anz = $row['anz_in_group'];
		
		$res = db_result(db_query("SELECT COUNT(*) FROM prefix_pm WHERE groupid = ".$gid." AND gelesen = 0 AND status = 0 AND archiv = 0"),0);
		
		if ($anz == $res) {
			$read = 0;
		} else {
			$read = 1;
		}
		//ende neu//
		if (($row['sid'] != $_SESSION['authid'] AND $menu->get(4) == 's')
                OR ($row['eid'] != $_SESSION['authid'] AND $menu->get(4) != 's')) {
            $design->footer(1);
        }
        if ($row['gelesen'] == 0 AND $menu->get(4) != 's') {
            db_query("UPDATE `prefix_pm` SET gelesen = 1 WHERE id = " . $pid);
        }
        $row['time'] = date('d. ',$row['time']).getDmon(date(n, $row['time'])).date(' y - H:i \U\h\r', $row['time']);
        $row['anhang'] = urlencode($row['txt']);
        $row['txt'] = bbcode(unescape($row['txt']));
		if ($menu->get(4) == 's') {
            $tpl = new tpl ('forum/pm/show_mess_send');
        } else {
            $tpl = new tpl ('forum/pm/show_mess');
        }

		if ($soeid == 'sid') {
			$row['archiv'] = ($row['archiv'] + 1) % 2;
		} else {
			if ($row['archiv'] == 0) {
				$row['archiv'] = $row['archiv'] + 1;
			} else {
				$row['archiv'] = $row['archiv'] % 2;
			}
		}
		
		if ($menu->get(5) == 'arch') {
			$row['archiv'] = 0;
		}
		$tpl->set('read',$read);

		
        $tpl->set_ar_out($row, 0);
        break;
    case 'delete' :
		// löschen von nachrichten
		if ($menu->get(3) != '' AND $menu->get(4) == '') {
            $_POST['delids'][] = $menu->get(3);
        }elseif ($menu->get(3) != '' AND $menu->get(4) == 's') {
            $_POST['delsids'][] = $menu->get(3);
        }
        if (empty($_POST['delids']) AND empty($_POST['delsids'])) {
            echo 'Es wurde keine Nachricht zum l&ouml;schen gew&auml;hlt <br /><br />';
            echo '<a href="javascript:history.back(-1)"><b>&laquo;</b> zur&uuml;ck</a>';
        } else {
            if ((empty($_POST['delids']) AND empty($_POST['delsids'])) OR empty($_POST['sub'])) {
                $delids = (empty($_POST['delids'])?$_POST['delsids']:$_POST['delids']);
                $s = (empty($_POST['delids'])?'':'s');
                echo '<form action="index.php?forum-privmsg-delete" method="POST">';
                $i = 0;
                if (!is_array($delids)) {
                    $delids = array ($delids);
                }
                foreach ($delids as $a) {
                    $i++;
                    echo '<input type="hidden" name="del' . $s . 'ids[]" value="' . $a . '">';
                }
                echo '<br>Wollen Sie ';
                echo ($i > 1 ? 'die (' . $i . ') Nachrichten ' : 'die Nachricht ');
                echo 'wirklich löschen ?<br><br><input type="submit" value=" Ja " name="sub"> &nbsp; &nbsp; <input type="button" value="Nein" onclick="document.location.href =\'?forum-privmsg\'"></form>';
            } else {
                $delids = (empty($_POST['delids'])?$_POST['delsids']:$_POST['delids']);
                $s = (empty($_POST['delids'])?'':'s');
                if ($s == 's') {
                	$soeid = 'sid';
                	$stat1 = 1;
                	$arch = 'IF(archiv>=2,archiv-2,archiv)';
				} else {
					$soeid = 'eid';
					$stat1 = -1;
					$arch = 'IF(archiv%2=1,archiv-1,archiv)';
				}
				$stat2 = $stat1 * - 1;
                $i = 0;
                if (!is_array($delids)) {
                    $delids = Array ($delids);
                }
                foreach ($delids as $a) {
                    if (is_numeric($a) AND $a != 0) {
                        db_query("DELETE FROM `prefix_pm` WHERE id = " . $a . " AND " . $soeid . " = " . $_SESSION['authid'] . " AND status = " . $stat1);
                        db_query("UPDATE prefix_pm SET status = " . $stat2 . ", archiv = {$arch} WHERE id = " . $a . " AND " . $soeid . " = " . $_SESSION['authid']);
                        $i++;
                    }
                }
                echo 'Es wurd';
                echo ($i > 1 ? 'en (' . $i . ') Nachrichten ' : 'e eine Nachricht ');
                echo <<<HTML
erfolgreich gelöscht <br /><br /><a href="index.php?forum-privmsg">zum Posteingang</a>
<br /><a href="index.php?forum-privmsg-showsend">zum Postausgang</a>
<br /><a href="index.php?forum-privmsg-archiv">zum Archiv</a>
HTML;
            }
        }
        break;
	case 'toArchiv' :
		// archivieren von nachrichten
		if ($menu->get(3) != '' AND $menu->get(4) == '') {
			$_POST['delids'][] = $menu->get(3);
		}elseif ($menu->get(3) != '' AND $menu->get(4) == 's') {
			$_POST['delsids'][] = $menu->get(3);
		}
		if (empty($_POST['delids']) AND empty($_POST['delsids'])) {
			echo 'Es wurde keine Nachricht zum Archivieren gew&auml;hlt <br /><br />';
			echo '<a href="javascript:history.back(-1)"><b>&laquo;</b> zur&uuml;ck</a>';
		} else {
			$delids = (empty($_POST['delids'])?$_POST['delsids']:$_POST['delids']);
			$s = (empty($_POST['delids'])?'':'s');
			$i = 0;
			if (!is_array($delids)) {
				$delids = Array ($delids);
			}
			foreach ($delids as $j => $a) {
				if (intval($a) > 0) {
					$delids[$j] = intval($a);
				} else {
					unset($delids[$j-$i]);
					$i++;
				}
			}
			$delids = implode(', ', $delids);
			if ($s == 's') {
				$qry = "UPDATE prefix_pm SET archiv = archiv + 1 WHERE id IN ({$delids}) AND eid = {$_SESSION['authid']} AND (archiv % 2) = 0";
			} else {
				$qry = "UPDATE prefix_pm SET archiv = archiv + 2 WHERE id IN ({$delids}) AND sid = {$_SESSION['authid']} AND archiv < 2";
			}
			db_query($qry);
			echo 'Es wurd';
			echo ($i > 1 ? 'en (' . $i . ') Nachrichten ' : 'e eine Nachricht ');
			echo <<<HTML
erfolgreich ins Archiv verschoben. <br /><br /><a href="index.php?forum-privmsg">zum Posteingang</a>
<br /><a href="index.php?forum-privmsg-showsend">zum Postausgang</a>
<br /><a href="index.php?forum-privmsg-archiv">zum Archiv</a>
HTML;
		}
		break;
	case 'showsend' :
        $tpl = new tpl ('forum/pm/showsend');
        $tpl->out(0);
        $class = 'Cmite';

		$page = ( $menu->getA(3) == 'p' ? $menu->getE(3) : 1 );
		$MPL = db_make_sites ($page , "WHERE sid = '{$_SESSION['authid']}}' AND status >= 0 AND archiv < 2" , $limit , '?forum-privmsg-showsend' , 'pm' );
		$anfang = ($page - 1) * $limit;
		
		$abf = "SELECT a.titel, b.name as empf, a.id, a.`time`, a.gelesen, a.groupid, a.anz_in_group FROM `prefix_pm` a left join prefix_user b ON a.eid = b.id WHERE a.sid = " . $_SESSION['authid'] . " AND a.status >= 0 AND a.archiv < 2 ORDER BY time DESC LIMIT $anfang, $limit";
        $erg = db_query($abf);
		while ($row = db_fetch_assoc($erg)) {
            $class = ($class == 'Cmite' ? 'Cnorm' : 'Cmite');
            $row['class'] = $class;
            $row['time'] = date('d. ',$row['time']).getSDmon($row['time']).date(' y - H:i \U\h\r', $row['time']);
        	$row['titel'] = (trim($row['titel']) == '' ? ' -- kein Nachrichtentitel -- ' : $row['titel']);
			//neu//
			$pmid = $row['id'];
			$gid = $row['groupid'];
			$anz = $row['anz_in_group'];
			$res = db_result(db_query("SELECT COUNT(*) FROM prefix_pm WHERE groupid = ".$gid." AND gelesen = 0 AND status = 0 AND archiv = 0"),0);
			if ($anz == $res) {
				$row['NEW'] = $row['gelesen'] == 0 ? '<img src="include/images/icons/red-mail-icon.png" alt="ungelesen" title="Nachricht wurde noch nicht gelesen" style="cursor:help;"/> <a href="index.php?forum-privmsg-edit-'.$pmid.'"><img src="include/images/icons/edit.gif" name="edit" title="Nachricht bearbeiten" />' : '<img src="include/images/icons/green-mail-icon.png" alt="ungelesen" title="Nachricht wurde gelesen" style="cursor:help;"/>';
			} else {
				$row['NEW'] = $row['gelesen'] == 0 ? '<img src="include/images/icons/red-mail-icon.png" alt="ungelesen" title="Nachricht wurde noch nicht gelesen" style="cursor:help;"/> <a href="index.php?forum-privmsg-edit-'.$pmid.'"><img src="include/images/icons/edit.gif" name="edit" title="Nachricht bearbeiten" />' : '<img src="include/images/icons/green-mail-icon.png" alt="ungelesen" title="Nachricht wurde gelesen" style="cursor:help;"/>';
			}
            $tpl->set_ar_out($row, 1);
        }
        $tpl->set_out('MPL', $MPL, 2);
        break;
    case 'archiv':
		$tpl = new tpl ('forum/pm/archiv');
		$tpl->out(0);
		$tpl->set('color', $color);
		$class = 'Cmite';

		$epage = ( $menu->getA(3) == 'e' ? $menu->getE(3) : 1 );
		$eMPL = db_make_sites ($epage , "WHERE eid = '{$_SESSION['authid']}}' AND status <= 0 AND archiv % 2 = 1" , $limit , '?forum-privmsg-archiv' , 'pm' );
		$eMPL = str_replace('archiv-p', 'archiv-e', $eMPL);
		$tpl->set('eMPL', $eMPL);
		$eanfang = ($epage - 1) * $limit;

		$spage = ( $menu->getA(4) == 's' ? $menu->getE(4) : 1 );
		$sMPL = db_make_sites ($spage , "WHERE sid = '{$_SESSION['authid']}}' AND status >= 0 AND archiv >= 2" , $limit , '?forum-privmsg-archiv-e'.$epage , 'pm' );
		$sMPL = str_replace('archiv-e'.$epage.'-p', 'archiv-e'.$epage.'-s', $sMPL);
		$tpl->set('sMPL', $sMPL);
		$sanfang = ($spage - 1) * $limit;


		//empf
		$abf = "SELECT a.titel as BET, a.gelesen as NEW, b.name as ABS, a.id as ID, a.`time` FROM `prefix_pm` a left join prefix_user b ON a.sid = b.id WHERE a.eid = " . $_SESSION['authid'] . " AND a.status <= 0 AND a.archiv % 2 = 1 ORDER BY time DESC LIMIT $eanfang, $limit";
		$erg = db_query($abf);
		if (db_num_rows($erg)) {
			$tpl->out(1);
			while ($row = db_fetch_assoc($erg)) {
				$class = ($class == 'Cmite' ? 'Cnorm' : 'Cmite');
				$row['BET'] = (trim($row['BET']) == '' ? ' -- kein Nachrichtentitel -- ' : $row['BET']);
				$row['CLASS'] = $class;
				$row['time'] = date('d. ',$row['time']).getSDmon($row['time']).date(' y - H:i \U\h\r', $row['time']);
				$row['NEW'] = ($row['NEW'] == 0 ? '<span style="color: red; text-decoration: blink; font-weight: bold; font-style: italic;">neu</span>' : '');
				$tpl->set_ar_out($row, 2);
			}
			$tpl->out(3);
		}
    	//ges
		$abf = "SELECT a.titel, b.name as empf, a.id, a.`time`, a.gelesen FROM `prefix_pm` a left join prefix_user b ON a.eid = b.id WHERE a.sid = " . $_SESSION['authid'] . " AND a.status >= 0 AND a.archiv >= 2 ORDER BY time DESC LIMIT $sanfang, $limit"; //neu -> a.uniqueid//
		$erg = db_query($abf);
		if (db_num_rows($erg)) {
			$tpl->out(4);
			while ($row = db_fetch_assoc($erg)) {
				$class = ($class == 'Cmite' ? 'Cnorm' : 'Cmite');
				$row['titel'] = (trim($row['titel']) == '' ? ' -- kein Nachrichtentitel -- ' : $row['titel']);
				$row['class'] = $class;
				$row['time'] = date('d. ',$row['time']).getSDmon($row['time']).date(' y - H:i \U\h\r', $row['time']);
				$row['NEW'] = $row['gelesen'] == 0 ? '<img src="include/images/icons/ungelesen.gif" alt="ungelesen" title="Nachricht wurde noch nicht gelesen" style="cursor:help;"/>' : '';
				
				$tpl->set_ar_out($row, 5);
			}
			$tpl->out(6);
		}
		break;
    default :
        // message übersicht.
        $tpl = new tpl ('forum/pm/show');
        $tpl->out(0);

		$page = ( $menu->getA(2) == 'p' ? $menu->getE(2) : 1 );
		$MPL = db_make_sites ($page , "WHERE eid = '{$_SESSION['authid']}}' AND status <= 0 AND archiv % 2 = 0" , $limit , '?forum-privmsg' , 'pm' );
		$anfang = ($page - 1) * $limit;

        $class = 'Cmite';
        $abf = "SELECT a.titel as BET, a.gelesen as NEW, b.name as ABS, a.id as ID, a.`time` FROM `prefix_pm` a left join prefix_user b ON a.sid = b.id WHERE a.eid = " . $_SESSION['authid'] . " AND a.status <= 0 AND a.archiv % 2 = 0 ORDER BY time DESC LIMIT $anfang, $limit";
        $erg = db_query($abf);
        while ($row = db_fetch_assoc($erg)) {
            $class = ($class == 'Cmite' ? 'Cnorm' : 'Cmite');
            $row['NEW'] = ($row['NEW'] == 0 ? '<span style="color: red; text-decoration: blink; font-weight: bold; font-style: italic;">neu</span>' : '');
            $row['CLASS'] = $class;
            $row['BET'] = (trim($row['BET']) == '' ? ' -- kein Nachrichtentitel -- ' : $row['BET']);
            $row['time'] = date('d. ',$row['time']).getSDmon($row['time']).date(' y - H:i \U\h\r', $row['time']);
            $tpl->set_ar_out($row, 1);
        }
        $tpl->set_out('MPL', $MPL, 2);
        break;
}
$design->footer();

?>