<?php

//
// image verification
//
class image {
	var	$text;	// for debugging
    var $fontwidth;
	
	//
	// randomtext
	//
	function randomtext($len=6) {
		$chars = 'abcdefghjkmnpqrstuvwxyz23456789';
		
		srand((double)microtime() * 1000000);
		$i = 0;
		$text = '';

		// don't allow a length of less than 6.
        if ($len < 6)
            $len = 6;

		while($i < $len) {
			$num = rand() % strlen($chars);
			$tmp = substr($chars, $num, 1);
			$text .= $tmp;
			$i++;
		}
		return $text;
	}
	
	//
	// findwidth
	//
	function findwidth($size, $font, $text) {
		$box = ImageTTFbbox($size, 0, $font, 'A');
		
		$minx = min(array($box[0], $box[2], $box[4], $box[6]));
		$maxx = max(array($box[0], $box[2], $box[4], $box[6]));
		
		return $maxx - $minx;
	}
	
	//
	// findheight
	//
	function findheight($size, $font, $text) {
		$box = ImageTTFbbox($size, 0, $font, 'A');
		
		$miny = min(array($box[1], $box[3], $box[5], $box[7]));
		$maxy = max(array($box[1], $box[3], $box[5], $box[7]));
		
		return $maxy - $miny;
	}
	
	//
	// setfont
	//
	function setfont($font, $ext='ttf') {
		// No font is set so use a default.
		// Or it could be an invalid font.
		// Add an exception for Arial.
		if (strlen($font) < 6 && $font != strtolower('arial')) {
			$font = 'ITCBLKAD';
		}
			
		// Add the path.
		$font = sprintf("./framework/local/fonts/%s.%s", $font, $ext);
		
		// Try to open the file, if it doesn't exist then use the default again.
		$fp = fopen($font, 'r');
		if (!$fp) {
			$font = sprintf("./framework/local/fonts/ITCBLKAD.%s", $ext);
			fclose($fp);
		
			// Try to open the default font, if it doesn't exist then something went wrong.
			$fp = fopen($font, 'r');
			if (!$fp) {
				die("Could not open default font.");
			}
		}
		fclose($fp);
		
		// Should only get here if we found the font.
		if ($ext == strtolower('gdf'))
			return imageloadfont($font);
		else
			return $font;
		
	}
	
	//
	// colourtext
	//
	/*function colourtext($text) {
		// Cycle through the colours.
		for ($i = 0; $i < 4; $i++) {
			$count = substr_count($text, sprintf("^%s", $i));
			// lower numbers have priority.
			return explode(sprintf("^%s", $i), $text);
		}
		// no colours were found.
		return $text;
	}*/
	
	
	//
	// createimage
	//
	function createImage($size=40, $font='', $filename='') {
		// Make sure that GD is enabled on the server.
		if (!function_exists('ImageCreate')) {
			die("CreateImage error: GD library not found on server!");
		}

		// set our font
		$font = $this->setfont($font);

		// Only allow random text.
		$this->text = $this->randomtext();
		
		// If it failed then throw an error.
		if (strlen($this->text) < 2) {
			$this->text = "Error!";
		}

		// Set the image size.
		$size2 = $size / 1.5;
		$w = strlen($this->text) * $size2 + 2 * $size2;
		$h = ($size + $size / 1.5);


  		// Now create the image.
  		$im  =  ImageCreate($w, $h);

  		// Colourize the image.
  	    $fill = ImageColorAllocate($im, 241, 241, 241);
  	    $light = ImageColorAllocate($im, 255, 255, 255);
  	    $corners = ImageColorAllocate($im, 153, 153, 102);
  	    $dark = ImageColorAllocate($im, 51, 51 , 0);
        $color = ImageColorAllocate($im, 0, 0, 0);

        $x = ($w * 2 - ((strlen($this->text) + 2) * $size)) / 4;
        $y = $size + ($size / 4);

		// Are we saving the image? If not display it.
		if ($filename == '')
			header("Content-Type: image/png");

		ImageTTFText($im, $size, 0, $x, $y, $color, $font, $this->text);

		ImageLine($im, 0, 0, $w - 1, 0, $light);
		ImageLine($im, 0, 0, 0, $h - 2, $light);
		ImageLine($im, $w - 1, 0, $w-1, $h, $dark);
		ImageLine($im, 0, $h - 1, $w - 1, $h - 1, $dark);
		ImageSetPixel($im, 0 , $h - 1, $corners);
		ImageSetPixel($im, $w - 1, 0, $corners);

        // Check to see if we're saving the image or displaying it.
        if ($filename)
        	ImagePNG($im, $filename);
        else
			ImagePNG($im);
	}

	//
	// createimagefromtext
	//
	function createImageFromText($size=40, $text='', $font='', $filename='') {
		// Make sure that GD is enabled on the server.
		if (!function_exists('ImageCreate')) {
			die("CreateImage error: GD library not found on server!");
		}

        // We have text so pass it.
        if ($text)
            $this->text = $text;
            
		// If it failed then throw an error.
		if (strlen($this->text) < 2) {
			$this->text = "Error!";
		}

		// set our font
		$font = $this->setfont($font);
		//$tmpf = imageloadfont("./framework/local/fonts/anonymous.gdf");
		$width = $this->findwidth($size, $font, $this->text);
		$height = $this->findheight($size, $font, $this->text);

		// Set the random text.
		// Erm...no. Actually allow for text to be set outside.
		if (strlen($this->text) <= 1)
           	$this->text = $this->randomtext();

       	// Find if there's a new line in the text.
       	// If so then we need to set our height accordingly.
		$nlc = substr_count($this->text, "\n");
		$nlc++;

		// To prevent our width from getting too large we need to find the largest line.
		if ($nlc > 1) {
			$largest = 0;	// zero out the largest.
       		$newvars = explode("\n", $this->text);
       		foreach ($newvars as $key => $value) {
				if (strlen($key) > $largest)
					$largest = strlen($value);
			}
			unset($newvars);
		} else {
			$largest = strlen($this->text);
		}

		// Set the image size.
		$size2 = $size / 1.5;
		//$w = $largest * $size2 + 2 * $size2;
		$w = $largest * ($width - 4);
		//$h = ($size + $size / 1.5) * $nlc;
		$h = ($height * $nlc) * ceil($height / 10);


		// Now create the image.
		$im  =  ImageCreate($w, $h);

		// Colourize the image.
		$fill = ImageColorAllocate($im, 241, 241, 241);
		$light = ImageColorAllocate($im, 255, 255, 255);
		$corners = ImageColorAllocate($im, 153, 153, 102);
  	    $dark = ImageColorAllocate($im, 51, 51 , 0);
        $color = ImageColorAllocate($im, 0, 0, 0);

        // Offsets for the text.
        // *TRY* to centre the text.
        if ($nlc > 1) {
           	// Offset the text a bit.
           	$y = $size / 4;
               	foreach ($newvars as $key => $value) {
                   	$x = ($w * 2 - ((strlen($value) + 2) * $size)) / 4;
                   	$y += $size + ($size / 3);

                   	// Are we saving the image? If not display it.
                   	if ($filename == '')
               	    	header("Content-Type: image/png");

                   	// See if we want to change the text colour.
               		if (substr_count($value, "^1")) {
                   		$values = explode("^1", $value);
                   		$x2 = $x + (strlen($values[0]) + 0.7) * ($size / 2);
                   		$prevcolor = $color;
                   		$color = ImageColorAllocate($im, 255, 0, 0);
                   		ImageTTFText($im, $size, 0, $x, $y, $prevcolor, $font, $values[0]);
                  		//ImageTTFText($im, $size, 0, $x2, $y, $color, $font, $values[1]);
                  		//ImageString($im, $font, $x, $y, $values[0], $color);
                   		$value = $values[1];
                   	}
                   	if (substr_count($value, "^0")) {
                   		$values = explode("^0", $value);
                   		$prevcolor = $color;
                   		$color = ImageColorAllocate($im, 0, 0, 0);
                   		ImageTTFText($im, $size, 0, $x2, $y, $prevcolor, $font, $values[0]);
                   		//$x2 = $x2 + (strlen($values[0]) + 1) * ($size / 2);
                   		ImageTTFText($im, $size, 0, $x2 + (strlen($values[0])+0.3) * ($size / 2), $y, $color, $font, $values[1]);
                  	} else {
                      	ImageTTFText($im, $size, 0, $x, $y, $color, $font, $value);
                   	}
               	}
        } else {
       		$x = ($w * 2 - ($largest + 2) * $size) / 4;
//         	$x = 20;    // We were unsuccessful...so far.
       		$y = $size + ($size / 4);

          	// Are we saving the image? If not display it.
           	if ($filename == '')
       	    	header("Content-Type: image/png");

           	ImageTTFText($im, $size, 0, $x, $y, $color, $font, $this->text);
       	}

	    ImageLine($im, 0, 0, $w - 1, 0, $light);
	    ImageLine($im, 0, 0, 0, $h - 2, $light);
	    ImageLine($im, $w - 1, 0, $w-1, $h, $dark);
	    ImageLine($im, 0, $h - 1, $w - 1, $h - 1, $dark);
	    ImageSetPixel($im, 0 , $h - 1, $corners);
	    ImageSetPixel($im, $w - 1, 0, $corners);

        // Check to see if we're saving the image or displaying it.
        if ($filename)
			ImagePNG($im, $filename);
		else
			ImagePNG($im);
	}
};