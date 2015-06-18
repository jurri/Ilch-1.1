<?php
defined ('main') or die ( 'no direct access' );

if ($menu->get(3) == 'showerg') {
  $pid = $menu->get(2);
  $txt = @db_result(db_query('SELECT `txt` FROM `prefix_posts` WHERE id ='.$pid));
  echo FE_Vote2HTML($pid,bbcode($txt),2);
}else {
	$title = $allgAr['title'].' :: Forum :: Abstimmen';
	$hmenu  = 'Forum :: Abstimmen';
	$design = new design ( $title , $hmenu );
	$design->header();

	$pid = $menu->get(2);
	FE_Vote($pid,$_POST['vote'],$_POST['h_pk']);

	$tid = @db_result(db_query('SELECT tid FROM `prefix_posts` WHERE id ='.$pid));
	$posts = @db_result(db_query('SELECT rep FROM `prefix_topics` WHERE id = '.$tid)) + 1;
	$page = ceil ( $posts  / $allgAr['Fpanz'] );
	wd('index.php?forum-showposts-'.$tid.'-p'.$page.'#'.$pid,'Vote erfolgreich abgegeben');
	$design->footer();
}
?>
