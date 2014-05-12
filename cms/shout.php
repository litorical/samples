<?php

// This file is for writing new blogs.
// This is the old fashioned way. I.E for when we want more control
// over what we're doing and maybe even preview it.

//define("MN_VERS", "0.5.0");

include('./header.php');
error_reporting(E_ALL ^ E_NOTICE);

// open the shoutbox database!
$shouts = new sqli('shouts');

/*
$shouts->query("DROP TABLE shouts");
$shouts->query("CREATE TABLE shouts (sid INTEGER PRIMARY KEY, name VARCHAR(64), email VARCHAR(128), shout VARCHAR(256), ip INT(15), time TIMEDATE)");
$shouts->query(sprintf("INSERT INTO shouts VALUES (NULL, 'test', 'test@test.com', 'This is only a test.', '127.0.0.1', '%s')", time()));
 *
 */

if ($shouts->error) {
    echo $shouts->error;
} else {

    // now just show all shouts!
    $shout = $shouts->query("SELECT * FROM shouts");
    //
    while ($shouty = $shout->fetch()) {
        print_r($shouty);
    }
}

?>
