<?php

define('AJAX', true);
error_reporting(E_ERROR);

// then include all of our important information.
require_once('../config.php');
require_once('../framework/code/c-logic.php');
require_once('../framework/code/c-templates.php');

// setup the session data.
// setup the session variables and start the session.
session_start();
session_name($cfg['sessname']);
$sessname = session_name();
$sess = session_id();

//$si = new session();
$si  = new serverinfo();

// initiate other databases
// this uses the template class because it already has the database functions setup
// no point in reinventing the wheel.
$content = new sqli('content');
if ($content->test_database() == false) {
	echo $content->error;
	//die();
}

// TODO: user needs to be logged in and have permission to write to this blog!

if (strlen($si->vars['title']) > 8 && strlen($si->vars['blogpost']) > 24) {
    // save the blog :)
    $content->db->query(sprintf("INSERT INTO blogs VALUES (NULL, '%s', '%s', '%s', '%s', '1')", $si->sess[$sessname], sqlite_escape_string($si->vars['title']), sqlite_escape_string($si->vars['blogpost']), time() ), SQLITE_ASSOC, $content->error);
    echo $content->error;
} else {
    echo "STRLEN";
}
?>
