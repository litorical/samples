<?php

include('./header.php');
error_reporting(E_ALL ^ E_NOTICE);

// all user's are set to private for now :P
$notfriend = true;

// get user information
$user = $content->fetch(sprintf("SELECT * FROM USERS WHERE uid='%s'", $si->vars['uid']));
$global['username'] = $user['username'];
$global['uid'] = $user['uid'];

if ($notfriend) {
	// need to add this user as a friend to see their profile.
	$temp->fetch('users_addfriend');
}

?>