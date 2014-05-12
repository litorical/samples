<?php

#define('PHP_VER',	'5.0.0');

//printf("Inital memory imprint: %s.<br />", memory_get_usage());
$mem = memory_get_usage();

// call the footer function even if there's an error somewhere.
register_shutdown_function('footer');


// The server needs to be running at least PHP 5.0.0? Maybe newer?
if (phpversion() < PHP_VER) {
	trigger_error(sprintf("PHP version %s found, expecting version %s or newer", phpversion(), PHP_VER), E_USER_ERROR);
}

// use GZIP to compress output
if (defined('GZIP')) {
	ob_start();
	ob_implicit_flush(0);
}

$global['mn_vers'] = '0.2.0';

// now call the required language
// but for now, we only have English support.
// 2.16.2011 - move this to a database as well.
include('./framework/resources/lang-english.php');

// then include all of our important information.
require_once('./config.php');
require_once('./framework/code/c-logic.php');
require_once('./framework/code/c-templates.php');

// setup the session variables and start the session.
session_start();
session_name($cfg['sessname']);
$sessname = session_name();
$sess = session_id();

// setup some global vars.
global $global;
$global['title'] = '3bird Framework';

$si = new serverinfo();
//$si = new session();

$temp = new tmp4('default');
//if ($temp->test_database() == false) {
$temp->test_database();
if ( isset($temp->error) ) {
	echo $temp->error;
}

function footer() {
    global $mem;
    // memory usage.
    // good for debugging!
    //$memused = number_format(memory_get_usage() - $mem, 0, '.', ',');
    //$mempeak = number_format(memory_get_peak_usage() - $mem, 0, '.', ',');
    $memused = memory_get_usage() - $mem;
    $mempeak = memory_get_peak_usage() - $mem;
    $memused = serverinfo::bytes($memused);
    $mempeak = serverinfo::bytes($mempeak);
    printf("Memory Usage: %s (Peak: %s).", $memused, $mempeak);
    unset($memused);
    unset($mempeak);
}

// initiate other databases
// this uses the template class because it already has the database functions setup
// no point in reinventing the wheel.
$content = new sqli('content');
if ($content->test_database() == false) {
	echo $content->error;
	//die();
}

// We need to set up some basic image variables
$global['imgw'] = 64;
$global['yesimg'] = sprintf('%s/images/icons/yes.png', $cfg['workingdir']);
$global['noimg'] = sprintf('%s/images/icons/no.png', $cfg['workingdir']);
$global['homeimg'] = sprintf('%s/images/icons/home.png', $cfg['workingdir']);
$global['infoimg'] = sprintf('%s/images/icons/info.png', $cfg['workingdir']);
$global['friendimg'] = sprintf('%s/images/icons/friend.png', $cfg['workingdir']);
$global['saveimg'] = sprintf('%s/images/icons/save.png', $cfg['workingdir']);
if ( isset($si->sess[$sessname])) {
	$global['newblogimg'] = sprintf('%s/images/icons/newblog.png', $cfg['workingdir']);
	$global['logimg'] = sprintf('%s/images/icons/logout.png', $cfg['workingdir']);
        $global['settingsimg'] = sprintf('%s/images/icons/settings.png', $cfg['workingdir']);
	$global['loglink'] = 'javascript:logout()';
        $global['username'] = $content->fetch(sprintf("SELECT username FROM users WHERE uid='%s'", $si->sess[$sessname]));
        $global['username'] = $global['username']['username'];
} else {
	$global['newblogimg'] = sprintf('%s/images/icons/lock.png', $cfg['workingdir']);
	$global['logimg'] = sprintf('%s/images/icons/login.png', $cfg['workingdir']);
        $global['settingsimg'] = sprintf('%s/images/icons/register.png', $cfg['workingdir']);
	$global['loglink'] = 'javascript:login()';
        $global['username'] = 'Guest';
}

$temp->fetch('header_base');

?>