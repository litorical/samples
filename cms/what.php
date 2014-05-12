<?php

error_reporting(E_ALL ^ E_NOTICE);
// open "Library.xml"
$file = 'Library.xml';

$bkey = false;
$bstring = false;
$bartist = false;

$artists = array();
$i = 1;

function xStart($parser, $name, $attrs) {
	global $bkey, $bstring;
	// string is an artist name...
	if ($bkey == true) {
		if (strtoupper($name) == 'STRING')
			$bstring = true;
		else
			$bstring = false;
	}
	
	if (strtoupper($name) == 'KEY') {
		$bkey = true;
	} else
		$bkey = false;
	
}

function xEnd($parser, $name) {
}

function dataHandler($parser, $data) {
	global $bkey, $bstring, $bartist, $artists, $i;

	if ($bstring == true && $bartist == true) {
		$bstring = false;
		$bartist = false;
		$bkey = false;
		
		if (array_search($data, $artists) == false || array_search($data, $artists) == NULL ) {
			// in case some bad English is used in tagging (i.e. To instead of to)
                    // also remove certain characters from the title to prevent things such as a dash in a name where a space should be.
			if (strtoupper($data) != strtoupper($artists[$i])) {
				$i++;
				$artists[$i] = $data;
			}
		}
	}
	
	else if ($bkey == true && $bartist == false) {
		if (strtoupper($data) == 'ARTIST')
			$bartist = true;
	}
}

$parser = xml_parser_create();
xml_set_character_data_handler($parser, 'dataHandler');
xml_set_element_handler($parser, 'xStart', 'xEnd');

ini_set("upload_max_filesize", "100M");

if (isset($_FILES['library'])) {
	//$fp = fopen($file, 'r');
	$fp = fopen($_FILES['library']['tmp_name'], 'r');
	if ($fp != NULL) {
		while ($data = fread($fp, 4096)) {
			if (!xml_parse($parser, $data, feof($fp))) {
				xml_parser_free($parser);
				die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($parser)), xml_get_current_line_number($parser)));
			}
		}
	}	

	foreach ($artists as $key => $value) {
		printf("%s, ", $value);
	}
} else {

	?>
<html>
	<head><title>what.cd Notification Updater</title></head>
	<body>
		<form action="what.php" enctype="multipart/form-data" method="POST">
			iTunes Library: <input name="library" type="file" />
			<input type="submit" value="Parse Library" />
		</form>
	</body>
</html>
<?php
}

?>