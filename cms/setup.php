<?php

require_once('config.php');
require_once('framework/code/c-logic.php');
require_once('framework/code/c-templates.php');

// setup directories.
$text = imgm::randomtext(8);
$text = md5(strrev($text));

// adminpath can't exist
if (!isset($cfg['adminpath'])) {
	if (!is_dir($text)) {
		echo "Yay!";
		
		$umask = umask(0);
		mkdir($text, 0777);
		umask($umask);
		unset($umask);
		
		// now try to update config!
		$fp = fopen('./config.php', 'a');
		fwrite($fp, sprintf("\n\$cfg['adminpath'] = '%s';", $text));
		fclose($fp);
	}
}