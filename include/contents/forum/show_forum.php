<?php 
#   Copyright by: Manuel Staechele
#   Support: www.ilch.de

#	Forenmod by Malte Wiatrowski alias "IRvD"  - Vorlage von Benjamin Rau & matthias-schlich.de
# mod by FeTTsack

defined ('main') or die ( 'no direct access' );

$title = $allgAr['title'].' :: Forum';
$hmenu = $extented_forum_menu.'Forum'.$extented_forum_menu_sufix;
$design = new design ( $title , $hmenu, 1);
$design->header();

if ($menu->get(1) == 'markallasread') {
  user_markallasread ();
}


$category_array = array();
$forum_array = array();

$q = "SELECT
  a.id, a.cid, a.name, a.besch,
  a.topics, a.posts, b.name as topic,
  c.id as pid, c.tid, b.rep, c.erst, c.time,
  a.cid, k.name as cname, a.erwrecht
FROM prefix_forums a
  LEFT JOIN prefix_forumcats k ON k.id = a.cid
  LEFT JOIN prefix_posts c ON a.last_post_id = c.id
  LEFT JOIN prefix_topics b ON c.tid = b.id
  LEFT JOIN prefix_groupusers vg ON vg.uid = ".$_SESSION['authid']." AND vg.gid = a.view
  LEFT JOIN prefix_groupusers rg ON rg.uid = ".$_SESSION['authid']." AND rg.gid = a.reply
  LEFT JOIN prefix_groupusers sg ON sg.uid = ".$_SESSION['authid']." AND sg.gid = a.start
	
WHERE ((".$_SESSION['authright']." <= a.view AND a.view < 1) 
   OR (".$_SESSION['authright']." <= a.reply AND a.reply < 1)
   OR (".$_SESSION['authright']." <= a.start AND a.start < 1)
	 OR vg.fid IS NOT NULL
	 OR rg.fid IS NOT NULL
	 OR sg.fid IS NOT NULL
	 OR -9 = ".$_SESSION['authright'].")
	 AND k.cid = 0
	 AND a.erwrecht = 0
ORDER BY k.pos, a.pos";
$erg1 = db_query($q);

$xcid = 0;
$x = 0;
$y=0;
echo '<link rel="stylesheet" type="text/css" href="include/includes/css/forum/userfarben.css" media="screen">';
echo "<style type=\"text/css\">
dt {
	font-weight:bold;
	background: #eceff3;
	background: -moz-linear-gradient(top, #eceff3 0%, #c1cad5 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #eceff3), color-stop(100%, #c1cad5));
	background: -webkit-linear-gradient(top, #eceff3 0%, #c1cad5 100%);
	background: -o-linear-gradient(top, #eceff3 0%, #c1cad5 100%);
	background: -ms-linear-gradient(top, #eceff3 0%, #c1cad5 100%);
	background: linear-gradient(top, #eceff3 0%, #c1cad5 100%);
 filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#eceff3', endColorstr='#c1cad5', GradientType=0 );
	float:left;
	width:100%;
	padding:10px 10px 9px 10px;
	margin-top:15px;
	margin-bottom:0px;
	border-top:1px solid white;
	border-left:1px solid white;
	border-right:1px solid white;
	cursor:pointer;
	-webkit-box-shadow:0 1px 1px rgba(0,0,0,1);
	-moz-box-shadow:0 1px 1px rgba(0,0,0,1);
	-ms-box-shadow:0 1px 1px rgba(0,0,0,1);
	-o-box-shadow:0 1px 1px rgba(0,0,0,1);
	box-shadow:0 1px 1px rgba(0,0,0,1);
	-webkit-border-radius:5px;
	-moz-border-radius:5px;
	-ms-border-radius:5px;
	-o-border-radius:5px;
	border-radius:5px;
}

dt:hover {
	color:black;
	background: #eceff3;
	background: -moz-linear-gradient(top, #fff 0%, #c1cad5 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #fff), color-stop(100%, #c1cad5));
	background: -webkit-linear-gradient(top, #fff 0%, #c1cad5 100%);
	background: -o-linear-gradient(top, #fff 0%, #c1cad5 100%);
	background: -ms-linear-gradient(top, #fff 0%, #c1cad5 100%);
	background: linear-gradient(top, #fff 0%, #c1cad5 100%);
 filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fff', endColorstr='#c1cad5', GradientType=0 );
}

dd {
	display:none;
	background:#c7cfd8;
	margin:0 0 15px 0;
	float:left;
	width:100%;
	padding:10px 10px 15px 10px;
	border-left:1px solid white;
	border-right:1px solid white;
	-webkit-box-shadow:0 1px 1px rgba(0,0,0,1);
	-moz-box-shadow:0 1px 1px rgba(0,0,0,1);
	-ms-box-shadow:0 1px 1px rgba(0,0,0,1);
	-o-box-shadow:0 1px 1px rgba(0,0,0,1);
	box-shadow:0 1px 1px rgba(0,0,0,1);
	-webkit-border-radius:0 0 5px 5px;
}

#button {
	float:right;
	display:block;
	height:18px;
	width:17px;
	text-indent:-9999px;
}

.closed { background:url(include/images/forum/button.png) left no-repeat; }

.open { background:url(include/images/forum/button.png) right no-repeat; }
</style>";

echo '<table width="100%" cellpadding="4" cellspacing="1" border="0">
		<td width="58%" colspan="2" align="center" valign="middle"class="Chead">Foren&uuml;bersicht</td>
		<td width="6%" align="center" valign="middle" class="Chead">Posts</td>
		<td width="6%" align="center" valign="middle" class="Chead">Topics</td>
		<td width="25%" align="center" valign="middle" class="Chead">Last Post</td>
</table>';

echo '<div id="container">
	<dl>';
$katid = 0;
while ($r = db_fetch_assoc($erg1) ) {

	$x++;
	$y++;
	
    $r['topicl'] = $r['topic'];
	$r['topic']  = html_enc_substr($r['topic'],0,30);
	$r['ORD']    = forum_get_ordner($r['time'], $r['id']);
	$r['mods']   = getmods($r['id']);
	$r['datum']  = date('d.m.y - H:i', $r['time']);
	$r['page']   = ceil ( ($r['rep']+1)  / $allgAr['Fpanz'] );
	$r['erst'] = forum_farbname($r['erst']);
	//$tpl->set_ar ($r);
	$arr[$x] = $r['cname'];

	$zahl = db_fetch_assoc(db_query("SELECT count(*) anzahl FROM prefix_forums WHERE cid = ".$r['cid']." AND view >= ".$_SESSION['authright']." AND erwrecht = 0"));
	
	$erg_recht = db_query(
		"SELECT * 
		   FROM ic1_forumrecht,
			    ic1_groupusers g,
			    ic1_forums f 
		  WHERE recht_bearbeiten = 1 
		    and g.uid = 1
		    and g.gid = recht_fkrecht
		    and f.cid = ".$r['cid']."
			and recht_fkforum = f.id"
	);
	if(db_num_rows($erg_recht) > 0){
		$erwr_forum = '';
		while($rowerw = db_fetch_assoc($erg_recht)){
			if($katid != $rowerw['cid']){
				$katid = $rowerw['cid'];
				$rowerw['ORD'] = forum_get_ordner($rowerw['time'], $rowerw['id']);
				$erwr_forum = '
					<tr class="Cnorm" cellspacing="1">
						<td width="5%" align="center" valign="middle" class="Cdark"><img alt="" src="include/images/forum/'.$rowerw['ORD'].'.png" border="0"></td>
						<td width="53%" class="Cnorm"><a href="index.php?forum-showtopics-'.$rowerw['id'].'">'.$rowerw['name'].'</a><br /><span class="smalfont">'.$rowerw['besch'].'</span></td>   
						<td align="center" class="Cdark" width="6%"><img src="include/images/forum/beitrag.png" border="0" /><span class="smalfont">'.$rowerw['posts'].'</span></td>
						<td align="center" class="Cdark" width="6%"><img src="include/images/forum/themen.png" border="0" /><span class="smalfont">'.$rowerw['topics'].'</span></td>
						<td class="Cnorm" width="25%"><img src="include/images/forum/post.png" border="0" width="14" height="14"> <a class="smalfont" title="'.$rowerw['topicl'].'" href="index.php?forum-showposts-'.$rowerw['tip'].'-p'.$rowerw['page'].'#'.$rowerw['pid'].'">'.$rowerw['topics'].'</a>
							<br /><span class="smalfont">von: '.$rowerw['erst'].'</span></td>
					</tr>';
			}
		}
	}else{
		$erwr_forum = '';
	}
	
  
	if($arr[$x] == $arr[$x-1]){
		$r['tab'] = "";
	}else{
		$r['tab'] = '<tr class="Cdark">
		<td colspan="5" height="20"><strong><dt>'.$r['cname'].'<a href="#" id="button" class="closed">Details</a><dt></strong></td>
	</tr>';
	$r['tab'] = '<dt>'.$r['cname'].'<a href="index.php?forum-showcat-'.$r['cid'].'" id="button" class="closed">Details</a></dt><dd><table width="98%" cellpadding="4" cellspacing="1" border="0">';
	}
	
	$r['tab'] .= $erwr_forum;
	
	$r['tab'] .= '<tr class="Cnorm" cellspacing="1">
		<td width="5%" align="center" valign="middle" class="Cdark"><img alt="" src="include/images/forum/'.$r['ORD'].'.png" border="0"></td>
		<td width="53%" class="Cnorm"><a href="index.php?forum-showtopics-'.$r['id'].'">'.$r['name'].'</a>
                  			<br />
								<span class="smalfont">'.$r['besch'].'</span></td>   
		<td align="center" class="Cdark" width="6%"><img src="include/images/forum/beitrag.png" border="0" /><span class="smalfont">'.$r['posts'].'</span></td>
		<td align="center" class="Cdark" width="6%"><img src="include/images/forum/themen.png" border="0" /><span class="smalfont">'.$r['topics'].'</span></td>
		<td class="Cnorm" width="25%"><img src="include/images/forum/post.png" border="0" width="14" height="14"> <a class="smalfont" title="'.$r['topicl'].'" href="index.php?forum-showposts-'.$r['tip'].'-p'.$r['page'].'#'.$r['pid'].'">'.$r['topics'].'</a>
                   			 <br />
                          <span class="smalfont">von: '.$r['erst'].'</span></td>
	</tr>';
	if($y == $zahl['anzahl']){
		$y=0;
		$r['tab'] .= '</table></dd>';
		//$katid = 0;
	}

  /*if ($r['cid'] <> $xcid) {
    $tpl->out(1);
    //Unterkategorien
    $sql = db_query("SELECT DISTINCT a.name as cname, a.id as cid FROM `prefix_forumcats` a LEFT JOIN `prefix_forums` b ON a.id = b.cid WHERE a.cid = {$r['cid']} AND a.id = b.cid ORDER BY a.pos, a.name");
    while ($ucat = db_fetch_assoc($sql)) {
      $tpl->set_ar_out($ucat,2);
    }
    //Unterkategorien - Ende
    $xcid = $r['cid'];
  }*/
  echo $r['tab'];
}
echo '</dl>
</div><script type="text/javascript">

$(document).ready(function(){
	$("dt").click(function(){
		$(this).next("dd").slideToggle("fast"); 
		$(this).children("a").toggleClass("closed open"); 
	});
});

</script>';

$string = <<<end_of_quote
<br /><br />
<table width="100%" cellpadding="0" cellspacing="0" border="0" >
	<tr>
		<td width="50%" valign="top"><a href="index.php?forum-markallasread">alles als gelesen markieren</a></td>
		<td width="50%" style="text-align: right" valign="top">
			<a href="index.php?search-augt">neue Themen seit dem letzten Besuch</a>
			<br />
			<a href="index.php?search-aubt">unbeantwortete Themen</a>
			<br />
			<a href="index.php?search-aeit">eigene Beitr&auml;ge</a></td>
	</tr>
</table>
end_of_quote;
echo $string;

# statistic #
$ges_online_user = ges_online();
function user_online_today_liste(){
    $OnListe = '';
  $dif = mktime(0,0,0,date('m'),date('d'),date('Y'));
    $erg = db_query("SELECT a.id, a.name, a.llogin, b.bez, a.spezrank FROM `prefix_user` a LEFT JOIN prefix_ranks b ON b.id = a.spezrank  WHERE a.llogin > '". $dif."' ORDER BY recht");
    while($row = db_fetch_object($erg)) {
      if ( $row->spezrank <> 0 ) {
      $OnListe .= '<a class="'.$row->bez.'" title="'.$row->bez.'" href="index.php?user-details-'.$row->id.'">'.forum_farbname ($row->name).'</a>, ';
    } else {
      $OnListe .= '<a href="index.php?user-details-'.$row->id.'">'.forum_farbname ($row->name).'</a>, ';
      }
  }

    $OnListe = substr($OnListe,0,strlen($OnListe) - 3);
  return ($OnListe);
      }
$stats_array = array (
  'privmsgpopup' => check_for_pm_popup (),
  'topics' => db_result(db_query("SELECT COUNT(ID) FROM `prefix_topics`"),0),
  'posts' => db_result(db_query("SELECT COUNT(ID) FROM `prefix_posts`"),0),
  'users' => db_result(db_query("SELECT COUNT(ID) FROM `prefix_user`"),0),
  'istsind' => ( $ges_online_user > 1 ? 'sind' : 'ist' ),
  'gesonline' => $ges_online_user,
  'gastonline' => ges_gast_online(),
  'useronline' => ges_user_online(),
  'userliste' => user_online_liste(),
  'userliste_today' => user_online_today_liste(),
    'moda' => $moda
);

echo '     
<br /><br />
<table width="100%" border="0" cellspacing="1" cellpadding="0" class="border">
  <tr>
<td class="Chead" colspan="2" height="25" align="left"><div style="padding-left:10px;"><b>Es waren Online heute: </b></div></td>
  </tr>
  <tr>
    <td height="40" align="center" valign="middle" class="Cdark"><img src="include/images/forum/legende/user.png" width="36" height="36" border="0"></td>
    <td height="40" align="left" valign="middle" class="Cnorm"><span style="padding-left:9px;">'.user_online_today_liste().'</span></td>
  </tr>
  <tr>
<td class="Chead" colspan="2" height="25" align="left"><div style="padding-left:10px;"><b>Statistiken</b></div></td>
  </tr>
  <tr>
    <td height="40" align="center" valign="middle" class="Cdark"><img src="include/images/forum/legende/statistik.png" width="36" height="36" border="0"></td>
    <td height="40" align="left" valign="middle" class="Cnorm"><p style="padding-left:9px;"><span style="color:#FF0000">'.db_result(db_query("SELECT COUNT(ID) FROM `prefix_user`"),0).'</span> Mitglieder haben <span style="color:#FF0000">'.db_result(db_query("SELECT COUNT(ID) FROM `prefix_posts`"),0).'</span> Beitr&auml;ge in <span style="color:#FF0000">'.db_result(db_query("SELECT COUNT(ID) FROM `prefix_topics`"),0).'</span> Themen geschrieben</p></td>
  </tr>
  <tr>
    <td colspan="2" align="left" class="Chead" height="25"><div style="padding-left:10px;"><b>Foren - Legende</b></div></td>
  </tr>
  <tr class="Cnorm">
  <td width="8%" height="40" align="center" valign="middle" class="Cdark"><img src="include/images/forum/legende/legend.png" width="36" height="36" border="0"></td>
    <td width="92%" height="40" align="left" valign="middle">
	<div style="padding-left:9px;">
	<span class="Recht-9" style="font-size:10px;">Admin</span> , 
	<span class="Recht-8" style="font-size:10px;">CoAdmin</span> , 
	<span class="Recht-7" style="font-size:10px;">SiteAdmin</span> , 
	<span class="Recht-6" style="font-size:10px;">Leader</span> ,
	<span class="Recht-5" style="font-size:10px;">CoLeader</span> , 
	<span class="Recht-4" style="font-size:10px;">Member</span> , 
	<span class="Recht-3" style="font-size:10px;">Trialmember</span> , 
	<span class="Recht-2" style="font-size:10px;">SuperUser</span> , 
	<span class="Recht-1" style="font-size:10px;">User</span> , 
	</div></td>
  </tr>
  <tr class="Cnorm">
    <td class="Cdark" colspan="2" align="center" height="40"><img src="include/images/forum/ntop.png" alt="neue Beitr&auml;ge" border="0" align="absmiddle" /> &nbsp;neue Beitr&auml;ge
      &nbsp;&nbsp;<img src="include/images/forum/top.png" alt="keine neuen Beitr&auml;ge" border="0" align="absmiddle" /> &nbsp; keine neuen Beitr&auml;ge
      &nbsp;&nbsp;<img src="include/images/forum/ctop.png" alt="Thema geschlossen" border="0" align="absmiddle" /> &nbsp; Thema geschlossen &nbsp;&nbsp;<img src="include/images/forum/htop.png" alt="brisantes Thema" border="0" align="absmiddle" /> &nbsp; brisantes Thema</td>
  </tr>
</table>';



$design->footer();
?>
