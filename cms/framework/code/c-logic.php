<?php

/*
LOGIC CODE

This is code that does basic math and other logical matters.

*/

// error reporting should only show notices when doing some extreme debugging!
if (defined('DEBUG'))
	error_reporting(E_ALL);
else
	error_reporting(E_ALL ^ E_NOTICE);
	
	
/***************************************************

IMAGE MANIPULATION

****************************************************/

class imgm {
	private		$text;
	private		$fontwidth;
	private		$lines;
	
	//
	// randomtext
	//
	public function randomtext($len=6) {
		$chars	= 'abcdefghjkmnpqrstuvwxyz234568';		// don't use letters or numbers that look alike
		
		srand((double)microtime() * 1000000);
		$text = '';
		$lines = '';
		
		// don't allow a length of less than 6
		if ( $len < 6)
			$len = 6;
			
		for ($i = 0; $i < $len; $i++) {
			$num 	= rand() % strlen($chars);
			$tmp	= substr($chars, $num, 1);
			$text .= $tmp;
		}
		return $text;
	}

        //
        // saveText
        //
        public function saveText($text, $cookiename='imagevar') {
            // We need to save the text so we can compare it later.
            //$this->text = $text;
            // 06.25.2011: Using encrypted cookies now.

            // If the cookie already exists we need to unset it!
            if (isset($_COOKIE[$cookiename])) {
                unset($_COOKIE[$cookiename]);
            }

            // Now we set the text and the cookie!
            $newtext = md5(strtolower($text));
            //$this->text = $newtext;
            setCookie($cookiename, $newtext, time() + 3600);        // set the cookie and let it expire in an hour.
        }

        //
        // compare
        //
        public function compare($string, $cookiename='imagevar') {
            // We compare $string to our text generated in randomtext().
            // If it matches then return true, otherwise it doesn't match so return false.
            /*if (strtolower($string) == $this->text)
                return true;
            return false;*/
            // 06.25.2011: Using encrypted cookies now.
            if (!isset($_COOKIE[$cookiename])) {
                // cookie isn't set, can't compare to nothing now can we?
                return false;
            } else {
                if (md5(strtolower($string)) == $_COOKIE[$cookiename]) {
                    unset($_COOKIE[$cookiename]);
                    return true;
                }
                return false;
            }
        }
	
	//
	// findwidth
	//
	private function findwidth($size, $font, $text) {
		// find the bounding box of the current font
		// i.e. the dimensions of the font
		$box = ImageTTFbbox($size, 0, $font, 'A');
		// box is setup as follows:
		// box[0] = lower left corner (x)
		// box[1] = lower left corner (y)
		// box[2] = lower right corner (x)
		// box[3] = lower right corner (y)
		// box[4] = upper right corner (x)
		// box[5] = upper right corner (y)
		// box[6] = upper left corner (x)
		// box[7] = upper left corner (y)
		
		// box[0] should always be the same as box[6]
		// just as box[2] should always be the same as box[4]
		// box[1] should always be the same as box[3]
		// box[5] should be the same as box[7]
		return $box[2] - $box[0];		
	}
	
	//
	// findheight
	//
	private function findheight($size, $font, $text) {
		// find the bounding box of the current font
		// i.e. the dimensions of the font
		$box = ImageTTFbbox($size, 0, $font, 'A');
		// box is setup as follows:
		// box[0] = lower left corner (x)
		// box[1] = lower left corner (y)
		// box[2] = lower right corner (x)
		// box[3] = lower right corner (y)
		// box[4] = upper right corner (x)
		// box[5] = upper right corner (y)
		// box[6] = upper left corner (x)
		// box[7] = upper left corner (y)
		
		// box[0] should always be the same as box[6]
		// just as box[2] should always be the same as box[4]
		// box[1] should always be the same as box[3]
		// box[5] should be the same as box[7]
		return $box[1] - $box[5];
	}
	
	//
	// setfont
	//
	private function setfont($font, $ext='ttf') {
		global $cfg;
		// NO font is set so use a default.
		// Or it could be an invalid font.
		// Add an exception for Arial.
		if (strlen($font) <= 6 && $font != strtolower('arial')) {
			$font = 'ITCBLKAD';
		}
		
		// Only allow 3 letter extensions
		if (strlen($ext) != 3) {
			$ext = 'ttf';
		}
		
		// add the path
		$font = sprintf("%s/fonts/%s.%s", $cfg['workingdir'], $font, $ext);
		
		// Try to open the file, if it doesn't exist then use the default again.
		$fp = fopen($font, 'r');
		if (!$fp) {
			$font = sprintf("%s/fonts/ITCBLKAD.ttf", $cfg['workingdir']);
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
	public function colourtext($text, $colours='4') {
		// Cycle through our colours.
		for ($i = 0; $i < $colours; $i++) {
			$count = substr_count($text, sprintf("^%s", $i));
			
			// lower numbers have priority
			return explode(sprintf("^%s", $i), $text);
		}
		
		// no colours were found.
		return $text;
	}
	
	//
	// createimage
	//
	public function createImage($size=40, $text='', $font='', $filename='') {
		// Make sure that GD is enabled on the server.
		if (!function_exists('ImageCreate')) {
			die("CreateImage error: GD library not found on server!");
		}
		
		// set our font
		$font = $this->setfont($font);
		
		// See if our text is long enough...that's what she said ;)
		if ( strlen($text) <= 2)
			$this->text = $this->randomtext();
		else
			$this->text = $text;
			
		// if it failed then throw an error
		if (strlen($this->text) <= 2 ) {
			$this->text = "Error!";
		}
			
		// find if there's a new line in the text.
		// if so then we need to set our height accordingly.
		$nlc = substr_count($this->text, "\n");
		$nlc++;
		
		// To prevent our width from getting too large we need to find the largest line.
		if ( $nlc > 1 ) {
			$largest = 0;
			$newvars = explode("\n", $this->text);
			foreach ($newvars as $key => $value) {
				if (strlen($value) > $largest)
					$largest = strlen($value);
			}
			//unset($newvars);
		} else {
			$largest = strlen($this->text);
		}
		
		// set the image size.
		$box = ImageTTFbbox($size, 0, $font, $this->text);
		$w = $box[4] + $size;
		$h = $box[1] + $size + ($size / 2);
		
		// now create the image.
		$im = ImageCreate($w, $h);
		
		// Colourize the image.
		$fill = ImageColorAllocate($im, 241, 241, 241);
		$light = ImageColorAllocate($im, 255, 255, 255);
		$corners = ImageColorAllocate($im, 153, 153, 102);
		$dark = ImageColorAllocate($im, 51, 51, 0);
		$color = ImageColorAllocate($im, 0, 0, 0);
		$grey = ImageColorAllocate($im, 128, 128, 128);
		
		// offsets for the text.
		// *TRY* to centre the text!
		if ($nlc > 1) {
			// offset the text a bit.
			$y = $size / 4;
			
			foreach ($newvars as $key => $value) {
				$x = ($w * 2 - ((strlen($value) + 2) * $size)) / 4;
				$y += $size + ($size / 3);
			}
		} else {
			$x = ($fw * strlen($this->text) + 2) / 66;
			$y = $size + ($size / 4);
		}
		
		// Are we saving the image? IF not just display it.
		if (strlen($filename) < 1) {
			header("Content-type: image/png");
		}
		
		ImageTTFText($im, $size, 0, $x, $y, $grey, $font, $this->text);
		ImageTTFText($im, $size, 0, $x+1, $y+1, $color, $font, $this->text);
		
		ImageLine($im, 0, 0, $w - 1, 0, $light);
		ImageLine($im, 0, 0, 0, $h - 2, $light);
		ImageLine($im, $w - 1, 0, $w - 1, $h, $dark);
		ImageLine($im, 0, $h - 1, $w - 1, $h - 1, $dark);
		ImageSetPixel($im, 0, $h - 1, $corners);
		ImageSetPixel($im, $w - 1, 0, $corners);
		
		// check to see if we're saving the image or displaying it.
		if (strlen($filename) > 0) {
			ImagePNG($im, $filename);
		} else {
			ImagePNG($im);
		}
	}
};





/***************************************************

SERVER DATA

****************************************************/
class serverinfo {
	public		$vars;
	public		$server;
        public          $sess;

        public function __construct() {

            session_start();
		
		// We want nice vars.
		// see if there's any _POST vars.
		if (count($_POST) < 1) {
			$this->vars = $_GET;		// We only have $_GET vars.
                        unset($_POST);
		} elseif (count($_GET) < 1) {
			$this->vars = $_POST;
                        unset($_GET);
		} else {
			// Otherwise we want both.
			$this->vars = array_merge( (array)$_POST, (array)$_GET);
			extract( $this->vars, EXTR_SKIP );	// Only one of each pair.
                        unset($_POST);
                        unset($_GET);
		}
		
		// Do the same thing for server vars.
		if (count($_SERVER) < 1) {
			$this->server = $_ENV;
                        unset($_ENV);
		} elseif (count($_ENV) < 1) {
			$this->server = $_SERVER;
                        unset($_SERVER);
		} else {
			$this->server = array_merge( (array)$_ENV, (array)$_SERVER);
			extract( $this->server, EXTR_SKIP );
                        unset($_ENV);
                        unset($_SERVER);
		}

                // and do it again for sessions for sanity purposes.
                $this->sess = $_SESSION;    // 08.08.2011: Why are we doing this?
                //unset($_SESSION);     // 06.04.2011: Why would you do that?
	}

        //
        // save
        // prepare a string
        //
        public function save($string) {
            // Can't parse an empty string
            if ( empty($string) )
                return;

            // Needs to be a string.
            // No point in parsing something that doesn't need to be parsed.
            if (is_string($string)) {
                // TODO: Stuff here...
            }
        }

        //
        // bytes
        // convert bytes to something more readable
        //
        public function bytes($bytes, $format=true) {
            // 1kb = 1024 bytes
            // 1mb = 1,048,576 bytes
            // 1gb = 1,073,741,824 bytes
            // 1tb = 1,099,511,627,776 bytes

            if ($bytes >= 1099511627776) {
                $bytes /= 1099511627776;        // tb
                if ($format == true)        // make it more readable
                    $bytes = sprintf("%s tb", number_format($bytes, '2', '.', ','));
            } else if ($bytes >= 1073741824) {
                $bytes /= 1073741824;     // gb
                if ($format == true)        // make it more readable
                    $bytes = sprintf("%s gb", number_format($bytes, '2', '.', ','));
            } else if ($bytes >= 1048576) {
                $bytes /= 1048576;      // mb
                if ($format == true)        // make it more readable
                    $bytes = sprintf("%s mb", number_format($bytes, '2', '.', ','));
            } else if ($bytes >= 1024) {
                $bytes /= 1024;         // kb
                if ($format == true)        // make it more readable
                    $bytes = sprintf("%s kb", number_format($bytes, '2', '.', ','));
            }
            return $bytes;

        }
	
	//
	// how long ago?
	//
	public function gettime($time, $showall) {
		global $lang;
		$time2 = time();
		$delta = $time2 - $time;
		
		// a year has passed!
		if ($delta > 29030400) {
			$years = floor($delta / 29030400);
			$delta -= $years * 29030400;
			if ($showall == true)
				$ret .= sprintf("%s %s, ", $years, $years > 1 ? $lang['years'] : $lang['year']);
		}
		
		// a month has passed!
		if ($delta > 2419200) {
			$months = floor($delta / 2419200);
			$delta -= $months * 2419200;
			if ($showall == true)
				$ret .= sprintf("%s %s, ", $months, $months > 1 ? $lang['months'] : $lang['month']);
		}
		
		// a week has passed!
		if ($delta > 604800) {
			$weeks = floor($delta / 604800);
			$delta -= $weeks * 604800;
			if ($showall == true)
				$ret .= sprintf("%s %s, ", $weeks, $weeks > 1 ? $lang['weeks'] : $lang['week']);
		}
		
		//a day has passed!
		if ($delta > 86400) {
			$days = floor($delta / 86400);
			$delta -= $days * 86400;
			if ($showall == true)
				$ret .= sprintf("%s %s, ", $days, $days > 1 ? $lang['days'] : $lang['day']);
		}
		
		// an hour has passed!
		if ($delta > 3600) {
			$hours = floor($delta / 3600);
			$delta -= $hours * 3600;
			if ($showall == true)
				$ret .= sprintf("%s %s, ", $hours, $hours > 1 ? $lang['hours'] : $lang['hour']);
		}
		
		// a minute has passed!
		if ($delta > 60) {
			$minutes = floor($delta / 60);
			$delta -= $minutes * 60;
			if ($showall == true)
				$ret .= sprintf("%s %s, ", $minutes, $minutes > 1 ? $lang['minutes'] : $lang['minute']);
		}
		
		if ($showall == true) {
			return sprintf("%s %s %s %s", $ret, $delta, $delta > 1 ? $lang['seconds'] : $lang['second'], $lang['ago']);
		} else {
			if ($years > 0) {
				return sprintf("%s %s, %s %s %s", $years, $years > 1 ? $lang['years'] : $lang['year'], $months, $months > 1 ? $lang['months'] : $lang['month'], $lang['ago']);
			} else if ($months > 0) {
				return sprintf("%s %s, %s %s %s", $months, $months > 1 ? $lang['months'] : $lang['month'], $weeks, $weeks > 1 ? $lang['weeks'] : $lang['week'], $lang['ago']);
			} else if ($weeks > 0) {
				return sprintf("%s %s, %s %s %s", $weeks, $weeks > 1 ? $lang['weeks'] : $lang['week'], $days, $days > 1 ? $lang['days'] : $lang['day'], $lang['ago']);
			} else if ($days > 0) {
				return sprintf("%s %s, %s %s %s", $days, $days > 1 ? $lang['days'] : $lang['day'], $hours, $hours > 1 ? $lang['hours'] : $lang['hour'], $lang['ago']);
			} else if ($hours > 0) {
				return sprintf("%s %s, %s %s %s", $hours, $hours > 1 ? $lang['hours'] : $lang['hour'], $minutes, $minutes > 1 ? $lang['minutes'] : $lang['minute'], $lang['ago']);
			} else if ($minutes > 0) {
				return sprintf("%s %s, %s %s %s", $minutes, $minutes > 1 ? $lang['minutes'] : $lang['minute'], $delta, $delta > 1 ? $lang['seconds'] : $lang['second'], $lang['ago']);
			} else {
				return sprintf("%s %s %s", $delta, $delta > 1 ? $lang['seconds'] : $lang['second'], $lang['ago']);
			}
		}
	}


    public function exists($session) {
        if (isset($this->sess[$session]) || isset($_SESSION[$session])) {
            return true;
        } else {
            return false;
        }
    }

    public function set($key, $value) {
        if (isset($this->sess[$key]) || isset($_SESSION[$key])) {
            return;
        } else {
            $_SESSION[$key] = $value;
            $this->sess[$key] = $value;
        }
    }

    public function destroy($session) {
        if (isset($this->sess[$session]) || isset($_SESSION[$session])) {
            unset($this->sess[$session]);
            unset($_SESSION[$session]);     // 05.29.2011: Keep this here as a sanity check.
        }
    }
};


// 05.29.2011: combined the two classes...it makes more sense this way!
/*class session extends serverinfo {
    public      $sess;

    public function __construct() {
        session_start();

        $this->sess = $_SESSION;
        unset($_SESSION);
    }

    public function exists($session) {
        if (isset($this->sess[$session]) || isset($_SESSION[$session])) {
            return true;
        } else {
            return false;
        }
    }

    public function set($key, $value) {
        if (isset($this->sess[$key]) || isset($_SESSION[$key])) {
            return;
        } else {
            $_SESSION[$key] = $value;
            $this->sess[$key] = $value;
        }
    }

    public function destroy($session) {
        if (isset($this->sess[$session]) || isset($_SESSION[$session])) {
            unset($this->sess[$session]);
            unset($_SESSION[$session]);
        }
    }
};*/
