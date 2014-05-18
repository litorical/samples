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

// first thing...we logout!
//if ($_GET['logout'] == true) {
if ($si->vars['logout'] == true) {
    if (sizeof($_SESSION) > 0) {
        foreach ($_SESSION as $key => $value) {
            //if (isset($_SESSION[$key])) {
            //    unset($_SESSION[$key]);
            //}
            $si->destroy($_SESSION[$key]);
        }
    }
    // also check si.
    if (sizeof($si->sess) > 0) {
        foreach ($si->sess as $key => $value) {
            //if (isset($si->sess[$key])) {
            //    unset($si->sess[$key]);
            //}
            $si->destroy($si->sess[$key]);
        }
    }
    echo 'LOGOK!';
    exit();
}

$users = new sqli('content');

// open the user database and try to find a match.
$user = $users->query("SELECT * FROM users");

while ($userdat = $users->fetch($user, false)) {
    if ( $si->vars['username'] == $userdat['username'] && md5($si->vars['password']) == $userdat['password']) {
        //$_SESSION[$sessname] = $userdat['uid'];
        //$si->sess[$sessname] = $userdat['uid'];
        $si->set($sessname, $userdat['uid']);
        echo "OK!";
    } else {
        echo "EPIC FAIL!";
    }
}
	
?>