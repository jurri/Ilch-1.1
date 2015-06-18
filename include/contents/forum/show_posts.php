<?php 
#   Copyright by: FeTTsack
#   Support: gcc

#   Forenmod by Malte Wiatrowski alias "IRvD"  - Vorlage von Benjamin Rau & matthias-schlich.de

defined ('main') or die ( 'no direct access' );

# check ob ein fehler aufgetreten ist.
check_forum_failure($forum_failure);

# toipc als gelesen markieren
$_SESSION['forumSEE'][$fid][$tid] = time();

$title = $allgAr['title'].' :: Forum :: '.$aktTopicRow['name'].' :: Beitr&auml;ge zeigen';
$hmenu  = $extented_forum_menu.'<a class="smalfont" href="index.php?forum">Forum</a><b> &raquo; </b>'.aktForumCats($aktForumRow['kat']).'<b> &raquo; </b><a class="smalfont" href="index.php?forum-showtopics-'.$fid.'">'.$aktForumRow['name'].'</a><b> &raquo; </b>';
$hmenu .= $aktTopicRow['name'].$extented_forum_menu_sufix;
$design = new design ( $title , $hmenu, 1);
$design->header();


# Topic Hits werden eins hochgesetzt.
db_query('UPDATE `prefix_topics` SET hit = hit + 1 WHERE id = "'.$tid.'"');

$erg = db_query("SELECT fid FROM `prefix_posts` WHERE tid = ".$tid);
$row = db_fetch_assoc($erg);
$newth = '<a href="index.php?forum-newtopic-'.$row['fid'].'"><img src="include/images/forum/newth.png" border="0"></a>';

# mehrere seiten fals gefordert         
$limit = $allgAr['Fpanz'];  // Limit 
$page = ($menu->getA(3) == 'p' ? $menu->getE(3) : 1 );
$MPL = db_make_sites ($page , "WHERE tid = ".$tid , $limit , 'index.php?forum-showposts-'.$tid , 'posts' );
$anfang = ($page - 1) * $limit;

$antworten = '';
if (($aktTopicRow['stat'] == 1 AND $forum_rights['reply'] == TRUE) OR ($_SESSION['authright'] <= '-7' OR $forum_rights['mods'] == TRUE)) {
  $antworten = '<a href="index.php?forum-newpost-'.$tid.'"><img src="include/images/forum/antw.png" border="0"></a>';
}


$class = 'Cmite';

$tpl = new tpl ( 'forum/showpost' );
$ar = array (
  'SITELINK' => $MPL,
  'tid' => $tid,
        'ANTWORTEN' => $antworten,
        'TOPICNAME' => $aktTopicRow['name'],
                'HMENU' => $hmenu,
                'NEWTH' => $newth
);
$tpl->set_ar_out($ar,0);
$i = $anfang +1;
$ges_ar = array ('wurstegal', 'maennlich', 'weiblich');

                ##################################
                #Forenmod by Malte Wiatrowski  - Vorlage von Benjamin Rau & matthias-schlich.de
                #Posts

$erg = db_query("SELECT geschlecht, prefix_posts.id,txt,time,erstid,erst,sig,avatar,posts FROM `prefix_posts` LEFT JOIN prefix_user ON prefix_posts.erstid = prefix_user.id WHERE tid = ".$tid." ORDER BY time LIMIT ".$anfang.",".$limit);
while($row = db_fetch_assoc($erg)) {
  
        $class = ( $class == 'Cnorm' ? 'Cmite' : 'Cnorm' );
        
      
$monatsnamen = array( '','Januar','Februar','März', 'April','Mai','Juni', 'Juli','August','September', 'Oktober','November','Dezember');
$month_current = $monatsnamen [gmdate('n')];

        # define some vars.
		$row['danke'] = '';
		$row['THX'] = '';
        $row['sig'] = ( empty($row['sig']) ? '' : '<br /><hr style="width: 80%;" align="left">'.bbcode($row['sig']) );
        $row['TID'] = $tid;
        $row['class'] = $class;
      $row['date'] = date('d', $row['time']).'. '.$monatsnamen [gmdate('n', $row['time'])].' '.date('Y - H:i', $row['time']);        
      $row['delete'] = '';
        $row['change'] = '';
      $row['erst'] = forum_farbname($row['erst']);

        if (file_exists($row['avatar'])) { $row['avatar'] = '<br /><img src="'.$row['avatar'].'" alt="User Pic" border="0" style="max-width:130px; max-height:180px;" /><br />'; }
              elseif ($allgAr['forum_default_avatar']) { $row['avatar'] = '<br /><img src="include/images/avatars/'.$ges_ar[$row['geschlecht']].'.jpg" alt="User Pic" border="0" /><br />'; }
               else { $row['avatar'] = ''; }
        $row['rang2']   = userrang ($row['posts'],$row['erstid']);
        $row['txt']    = (isset($_GET['such']) ? markword(bbcode ($row['txt']),$_GET['such']) : bbcode ($row['txt']) );
        $row['i']      = $i;

                  if ( $row['erstid'] != '' ) {
                  $user_row = db_fetch_assoc(db_query("SELECT posts,regist FROM prefix_user WHERE id = ".$row['erstid']));

                $posts = $row['posts'];
                $row['posts']  = '<font style="font-weight:bold">Beiträge:</font> '.$row['posts'].'<br />';
				
                if ( $posts == '' ) { $row['posts'] = '<br>Nicht registriert';}

#Ranking Balken
   #Grafiken definieren
   $rank_0 = '<img src="include/images/forum/rank/rank0.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>5</b> Posts</span><br>';
   $rank_1 = '<img src="include/images/forum/rank/rank1.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>10</b> Posts</span><br>';
   $rank_2 = '<img src="include/images/forum/rank/rank2.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>50</b> Posts</span><br>';
   $rank_3 = '<img src="include/images/forum/rank/rank3.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>100</b> Posts</span><br>';
   $rank_4 = '<img src="include/images/forum/rank/rank4.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>150</b> Posts</span><br>';
   $rank_5 = '<img src="include/images/forum/rank/rank5.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>200</b> Posts</span><br>';
   $rank_6 = '<img src="include/images/forum/rank/rank6.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>250</b> Posts</span><br>';
   $rank_7 = '<img src="include/images/forum/rank/rank7.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>300</b> Posts</span><br>';
   $rank_8 = '<img src="include/images/forum/rank/rank8.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>400</b> Posts</span><br>';
   $rank_9 = '<img src="include/images/forum/rank/rank9.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>450</b> Posts</span><br>';
   $rank_10 = '<img src="include/images/forum/rank/complete.png" alt="" border="0"><br><span class="smalfont">Next Level bei <b>500</b> Posts</span><br>';

   #Bezugnehmen auf das Ranking des users
   if ( $row['posts'] != '' ) {
   $row['rang'] = $rank_name.'<br>';
   if ($posts <= '4') { $row['rang'] .= $rank_0; }
   elseif ($posts <= '9') { $row['rang'] .= $rank_1;}
   elseif ($posts <= '49') { $row['rang'] .= $rank_2; }
   elseif ($posts <= '99') { $row['rang'] .= $rank_3; }
   elseif ($posts <= '149') { $row['rang'] .= $rank_4; }
   elseif ($posts <= '199') { $row['rang'] .= $rank_5; }
   elseif ($posts <= '249') { $row['rang'] .= $rank_6; }
   elseif ($posts <= '299') { $row['rang'] .= $rank_7; }
   elseif ($posts <= '399') { $row['rang'] .= $rank_8; }
   elseif ($posts <= '449') { $row['rang'] .= $rank_9; }
   elseif ($posts <= '499') { $row['rang'] .= $rank_10; }
            
        }
        else {$row['rang'] .= ""; }
        }

                #User Details
                if ( $posts != '' ) {
                $abf1 = 'SELECT * FROM prefix_user where id = '.$row["erstid"];
                $erg1 = db_query($abf1);
                $user = db_fetch_object($erg1);
            $zeit = date('d.m.Y',$user->regist);                
            $llogin = date('d.m.Y - H:i',$user->llogin);
                $ort = $user->wohnort;
                $www = $user->homepage;
                $email = $user->opt_mail;
                $pm = $user->opt_pm;
                $land = $user->staat; 
                #Flagge
                 if ($land != '')
                 {$row['land'] = '<img src="include/images/flags/'.$land.'" >';}
                 else{$row['land'] = '';}
                #Dabei seit
                $row['details'] = "<span class=\"info\">Dabei seit:</span> ".$zeit."<br>";
                #Wohnort
                if ($ort != ''){$row['details'] .= "<span class=\"info\">Wohnort:</span> ".$ort."<br>";}
                #Homepage
                if ($www != ''){$row['www'] = '<a href="'.$www.'" target="_blank"><img src="include/images/forum/www.png" border="0" alt="Website des Users besuchen"></a>';}
                else{$row['www'] = '';}
                #Letzter Login
                $row['details'] .= "<span class=\"info\">Letzter Login:<br></span> ".$llogin."<br>";
                #PM
                if ($pm == '1'){
                $row['pm'] = "<a href='?forum-privmsg-new=0&empfid=".$row['erstid']."'><img src='include/images/forum/pm.png' border='0' alt='Private Nachricht an den User senden'></a>";
                }else{$row['pm'] = '';}
                #email
                if ($email == '1'){
                $row['email'] = "<a href='?user-mail-".$row['erstid']."'><img src='include/images/forum/email.png' border='0' alt='E-Mail an den User senden'></a>";
                }else{$row['email'] = '';}
                }
                else {
                $row['details'] = "";
                $row['land'] = '';
                $row['pm'] = '';
                $row['email'] = '';
                $row['www'] = '';                
                }
                
                if ( $posts != '' ) {


                #User Online o Offline
                
                $abf1 = "SELECT * FROM prefix_online where uid = ".$row['erstid'];
                $erg1 = db_query($abf1);
                $status = db_fetch_object($erg1);
                if ($status->uid == $row['erstid']) {
                $row['online'] = '&nbsp;<img src="include/images/forum/uonline.png" border="0">'; }
                else {
                $row['online'] = '&nbsp;<img src="include/images/forum/uoffline.png" border="0">'; }
                  }
                  else {$row['online'] = '';}


                #
                #Edit Ende
                ##################################
  $row['page']   = $page;
  
               if ( $row['posts'] != 0 ) {
               $row['erst'] = '<a href="index.php?user-details-'.$row['erstid'].'"><b>'.forum_farbname($row['erst']).'</b></a>';        } 
        elseif ( $row['erstid'] == 0 ) {
        $row['rang'] = 'gel&ouml;schter User';
        }
  
        if ($forum_rights['mods'] == TRUE AND $i>1) {
          $row['delete'] = '<a class="forum" href="index.php?forum-delpost-'.$tid.'-'.$row['id'].'"><img src="include/images/forum/showpost/delete.png" width="16" height="16" alt="delete" align="absmiddle" title="löschen"/> l&ouml;schen</a>';
        }
        if ( $forum_rights['reply'] == TRUE AND loggedin() ) {
          $row['change'] = '&nbsp;<a class="forum" href="index.php?forum-editpost-'.$tid.'-'.$row['id'].'"><img src="include/images/forum/showpost/change.png" width="16" height="16" alt="change" align="absmiddle" title="ändern"/> &auml;ndern</a>';
        }
        $row['posts']  = ($row['posts']?'<br />'.$row['posts']:'').'<br />';
		
		// Danke-Link anzeigen oder ausblenden falls user == ersteller oder Gast
		if ($row['erstid'] == $_SESSION['authid'] or $_SESSION['authid'] == 0) {
			$row['THX'] = '';
		} else {
			# Zufallszahl generieren um Missbrauch vorzubeugen
			if (!isset($_SESSION['thx_rand']) OR empty($_SESSION['thx_rand'][$row['id']])) {
			$_SESSION['thx_rand'][$row['id']] = rand(000,999);
			}
			$row['THX'] = '<a href="index.php?danke-'.$row['id'].'-'.$_SESSION['thx_rand'][$row['id']].'-'.$tid.'-'.$row['erstid'].'-'.$_SESSION['authid'].'-'.$_SESSION['authname'].'"><img src="http://www.kizuna-la.org/wp-content/uploads/2013/11/Special-Thanks-Donors-Icon.jpg" width="16" height="16" alt="thx" align="absmiddle" title="danke"/> bedanken</a>&nbsp;';
		}
		
		// Ausgeben der Danke-Liste im Post
		$thxcount = db_fetch_assoc(db_query("SELECT COUNT(id) thxcount FROM `prefix_danke` WHERE pid = ".$row['id'].""));
		if ($thxcount['thxcount'] >= 1) {
			$row['danke'] .= '<hr /><strong>F&uuml;r diesen Post bedankten sich '.$thxcount['thxcount'].' User :</strong><br />';
			$thx_qry = db_query("SELECT bedankername,bedankerid FROM `prefix_danke` WHERE pid = ".$row['id']."");
			while ($thx_row = db_fetch_assoc($thx_qry)) {
				$row['danke'] .= '<a href="index.php?user-details-'.$thx_row['bedankerid'].'"><img src="http://www.kizuna-la.org/wp-content/uploads/2013/11/Special-Thanks-Donors-Icon.jpg" width="16" height="16"/>'.$thx_row['bedankername'].'</a> ';
			}
		} 
		
		$row['txt'] =  FE_Vote2HTML($row['id'], $row['txt']);
		//$row['txt'] = bbcode ($row['txt']);
		//$row['txt'] = preg_replace("/\[b\](.*)\[\/b\]/Usi", "<b>\\1</b>", $row['txt']);
		// bbcode ($row['txt'])
        $tpl->set_ar_out($row,1);
  
  $i++;
}

$tpl->set_ar_out( array ( 'SITELINK' => $MPL, 'ANTWORTEN' => $antworten ) , 2 );
// anfang qpost
if (loggedin()) {

$dppk_time = time();
$time = time();
if (!isset($_SESSION['klicktime'])) { $_SESSION['klicktime'] = 0; }

$topic = '';
$txt   = '';
$xnn   = '';

if (isset($_POST['txt_qp'])) {
  $txt = trim(escape($_POST['txt_qp'], 'textarea'));
}


$tpl = new tpl ('forum/qpost');
   $ar = array (
     'txt_qp'    => escape_for_fields(unescape($txt)),
     'tid'    => $tid,

   );

   $tpl->set_ar_out($ar,1);

if (($_SESSION['klicktime'] + 150) > $dppk_time OR empty($txt) OR !empty($_POST['priview']) OR (empty($_POST['Gname']) AND !loggedin())) {



}
else
{
# save qpost
  $_SESSION['klicktime'] = $dppk_time;

  $design = new design ( $title , $hmenu, 1);
  $design->header();

  if (loggedin()) {
    $uid = $_SESSION['authid'];
                $erst = escape($_SESSION['authname'],'string');
          db_query("UPDATE `prefix_user` set posts = posts+1 WHERE id = ".$uid);
  } else  {
          $erst = $xnn;
                $uid = 0;
  }
  db_query ("INSERT INTO `prefix_posts` (tid,fid,erst,erstid,time,txt) VALUES ( ".$tid.", ".$fid.", '".$erst."', ".$uid.", ".$time.", '".$txt."')");
  $pid = db_last_id();

        db_query("UPDATE `prefix_topics` SET last_post_id = ".$pid.", rep = rep + 1 WHERE id = ".$tid);
        db_query("UPDATE `prefix_forums` SET posts = posts + 1, last_post_id = ".$pid." WHERE id = ".$fid );
        $page = ceil ( ($aktTopicRow['rep']+1)  / $allgAr['Fpanz'] );
          # topic als gelesen markieren
  $_SESSION['forumSEE'][$fid][$tid] = time();

        wd ( array (
          $lang['backtotopic'] => 'index.php?forum-showposts-'.$tid.'-p'.$page.'#'.$pid,
                $lang['backtotopicoverview'] => 'index.php?forum-showtopics-'.$fid
        ) , $lang['createpostsuccessful'] , 3 );
}
}

$tpl = new tpl ( 'forum/showpost' );
// end qpost
if (loggedin()) {
  if ($menu->get(3) == 'topicalert') {
    if (1 == db_result(db_query("SELECT COUNT(*) FROM prefix_topic_alerts WHERE uid = ".$_SESSION['authid']." AND tid = ".$tid),0)) {
      db_query("DELETE FROM prefix_topic_alerts WHERE uid = ".$_SESSION['authid']." AND tid = ".$tid);
    } else {
      db_query("INSERT INTO prefix_topic_alerts (tid,uid) VALUES (".$tid.", ".$_SESSION['authid'].")");
    }
  }
  
  echo 'Optionen:';
  if (1 == db_result(db_query("SELECT COUNT(*) FROM prefix_topic_alerts WHERE uid = ".$_SESSION['authid']." AND tid = ".$tid),0)) {
    echo '<br />- <a href="index.php?forum-showposts-'.$tid.'-topicalert">'.$lang['nomailonreply'].'</a><br />';
  } else {
    echo '<br />- <a href="index.php?forum-showposts-'.$tid.'-topicalert">'.$lang['mailonreply'].'</a><br />';
  }
}

if ( $forum_rights['mods'] == TRUE ) {
  $tpl->set ( 'status', ($aktTopicRow['stat'] == 1 ? $lang['close'] : $lang['open'] ) );
        $tpl->set ( 'festnorm', ($aktTopicRow['art'] == 0 ? $lang['fixedtopic'] : $lang['normaltopic'] ) );
        $tpl->set('tid',$tid);
        $tpl->out(3);
}
$design->footer();
?>