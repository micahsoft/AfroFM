<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$total_users = $dbase->count(TBL_USERS, 'id');
$active_users = $dbase->count(TBL_USERS_ACTIVE, 'id');
$banned_users = $dbase->count(TBL_USERS_BANNED, 'id');	
$admin_users = $dbase->count(TBL_USERS, 'id', 'userlevel = 9');

$total_playlists = $dbase->count(TBL_PLAYLISTS, 'id');	
$total_genres = $dbase->count(TBL_GENRES, 'id', 'parent_id IS NULL');
$total_subgenres = $dbase->count(TBL_GENRES, 'id', 'parent_id IS NOT NULL');
$hidden_genres = $dbase->count(TBL_GENRES, 'id', 'hidden = 1');
$featured_genres = $dbase->count(TBL_GENRES, 'id', 'featured = 1');



$tpl->assign('total_users', $total_users);
$tpl->assign('active_users', $active_users);
$tpl->assign('banned_users', $banned_users);
$tpl->assign('admin_users', $admin_users);
$tpl->assign('total_playlists', $total_playlists);
$tpl->assign('total_genres', $total_genres);
$tpl->assign('total_subgenres', $total_subgenres);
$tpl->assign('hidden_genres', $hidden_genres);
$tpl->assign('featured_genres', $featured_genres);
	
?>