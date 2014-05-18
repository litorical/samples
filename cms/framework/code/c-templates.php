<?php
/*
 * V5.0
 * Version 4 was, well, useless. Let's combine v4 with v3 shall we?
 * DUMPING V4.0...
Templates V4.0
This version uses SQLite...and HTML.

VERSION HISTORY:
V1.0 - Used HTML files for templates. Opened a new file for each template. Rather boring IMO.
V2.0 - Used a large dumpfile. 

The templating system was renamed to Blempt for this iteration. It was also reset to v1.0
V1.0 - Used an XML file to store all the templates. What a terrible idea.
V2.0 - Improved the rendering, allowed for caching. Faster, more efficient.

Dropped the name for the templating as it's no longer a main feature.
Something that should have been done from the very beginning...
It still needs versioning to prevent mismatched features.
V3.0 - Smarter, faster, sexier.
V3.1 - Same as 3.0 but much cleaner code and more efficient. Now includes a hash file for editing.
V4.0 - reworked entirely.

Template naming is number based consisting of 4-5 numbers.
First number = The type of template it is (i.e. Major, minor)
Second number = Area that the template belongs to.
Third number = Secondary area that the template belongs to.
Fourth-Fifth = The template number.

^^ This has been changed...but for legacy purposes still exists in the code.
Now we make use of proper template naming.

EG.
1000 denotes the body skeleton of the entire page.
1101 denotes the first template in the header section. This contains a fair amount of page code.
2101 dentoes the first minor template in the header section. This could be something as small as a link.
21011 denotes the eleventh minor template in the header section.
1201 denotes the first template in the footer section.
*/
error_reporting(E_ALL ^ E_NOTICE);

//define('SQLITE',                        true);          // use SQLITE?
define('TMP_DEBUG',			true);		// set debugging for easy editing, aka html data
define('TMP_VERS',			'5.0');		// don't allow legacy templates to be used.

if (defined('SQLITE')) {
    // For debugging purposes this is set wrong.
    if (defined('EDITOR')) {
            define('TMP_DATA', 		'shared/templates/');
    } else {
            define('TMP_DATA',			'framework/shared/templates/');
    }
}

class sqli {
	public				$error		= '';
	public				$db		= '';
        public                          $query_string   = '';
        public                          $dbType         = 0;


        //
	// logerror
	//
	private function logerror() {
		global $cfg;

                // sanity check - need to send this error somewhere
                if (strlen($cfg['errorlog'] < 5)) {
                    $cfg['errorlog'] = 'error.log';
                }

                // also check for time format!
                if (strlen($cfg['time4'] < 3)) {
                    $cfg['time4'] = 'm.d.Y h:i:s A';
                }

		//if (isset($this->error)) {
                if (strlen($this->error > 3)) {
                    $this->error .= "\n";		// append a new line to every error logged.
                    $debug = debug_backtrace();
                    $this->error = sprintf("[%s]::[%s:%s](%s::%s): %s\r\n", date($cfg['time4'], time()), $debug[1]['file'], $debug[1]['line'], $debug[1]['class'], $debug[1]['function'], $this->error);

                    // log the error!
                    error_log($this->error, 3, $cfg['errorlog']);
                    unset($this->error);
		}
	}

        public function throwError($error, $type=E_ERROR) {
            // need to have an error to throw an error right?
            if (strlen($error) <= 4) {
                return;
            }

            if ($type == E_NOTICE) {
                $this->error = sprintf("[NOTICE] %s", $error);
                $this->logerror();
            } elseif ($type == E_WARNING) {
                $this->error = sprintf("[WARNING] %s", $error);
                $this->logerror();
            } else {
                $this->error = sprintf("[ERROR] %s", $error);
                $this->logerror();
                die();
            }
        }
			
	
	//
	// rmd5
	//
	private function rmd5($string) {
		return md5(strrev($string));
	}
	
	//
	// test_database
	//
	public function test_database() {
		// need to see if this database exists.
		if (isset($this->db)) {
			return true;
                } else {
                    $this->error = 'Testing of database failed!';
                    $this->throwError(E_WARNING);
                    return false;
                }
	}
	
	//
	// constructor
	//
	public function __construct($db='default', $dbt=1) {
		global $cfg;
		// the dbname should be set here.
                // ajax requires a different path...yet the same!
                if ($dbt == 2) {        // SQLITE = DBTYPE 2
                    if (defined('AJAX')) {
                           $dbname = sprintf("../%s/databases/%s.db", $cfg['workingdir'], $db);
                    } else {
                        $dbname = sprintf("%s/databases/%s.db", $cfg['workingdir'], $db);
                    }

                    // create a new database instance.
                    //if ( ($db = new SQLiteDatabase($dbname, 0666, $this->error) ) == true ) {
                    if ( ($db = new SQLite3($dbname ) ) == true )  {
                        $this->db = $db;
                    } else {
                        $this->throwError($this->error, E_ERROR);
                    }
                } else {        // MySQL = DBTYPE 1
                    // a little hacky but whatever...
                    if (defined('TMP_DEBUG') && $db == 'default') {
                        return;
                    }
                    
                    // connect to a MySQL database instead
                    if ( ($db= new mysqli($cfg['sqlhost'], $cfg['sqluser'], $cfg['sqlpass'], $db) ) == true ) {
                        $this->db = $db;
                    } else {
                        $this->throwError($this->error, E_ERROR);
                    }
                }
		
		unset($db);
	}
	
	//
	// __call
	//
	public function __call($name, $arguments) {
		$this->logerror();
	}
	
	//
	// __callStatic
	//
	public static function __callStatic($name, $arguments) {
		$this->logerror();
	}
	
	//
	// query
	//
	public function query($query, $ret=true) {
            // AJAX doesn't play nice
            if (defined('AJAX')) {
                $query = $this->db->query($query);
                return $query;
            } else {
                $this->query_string = $this->db->query($query);                
                if ($ret == true) {
                    return $this->query_string;
                }
            }
	}
        
        //
        // setQuery
        //
        public function setQuery($query) {
            $this->query_string = $query;
        }
	
	//
	// numrows
	//
	public function numRows($query, $doquery=true) {
                if ($this->dbType == 2) {
                    if ($doquery == true) {
                        $query = str_replace(" * ", " COUNT(*) ", $query);
                        $this->query_string = $this->query($query);
                    }
                    return $this->fetchArray($this->query_string);
                } else {
                    return $query->num_rows;
                }
	}
	
	//
	// fetch
	//
	public function fetch($query, $doquery=true) {
            // we're not actually using the data that's being passed here...
            // except with ajax
            // Everything else is using data that gets stored in the object
            // THIS NEEDS TO BE FIXED!!
            if ($doquery == true) {
            /* @var $fetch type */
                $this->query_string = $this->query($query);
            }

        if ($this->dbType == 2) {
		return $this->query_string->fetchArray();
            } else {
                if (defined('AJAX')) {
                    return $query->fetch_array();
                } else {
                    return $this->query_string->fetch_array();
                }
            }
	}
}

class tmp4 extends sqli {
	public				$tmp_data		= array();
	public				$extradata		= '';	
	
	//
	// deconstructor
	//
	public function __destruct() {
		// print all of the templates!
		if (isset($this->tmp_data)) {
			foreach ($this->tmp_data as $key => $value) {
				// display the template!
				echo stripslashes($value);
			}
		}
		echo $this->extradata;
	}
	
	//
	// setup
	//
	public function setup() {
		global $cfg;
		// see if the data already exists.
		if (parent::numRows("SELECT tmpid FROM templates") >= 1) {
			// if there's rows then it exists so we exit.
			// unless we're debugging then just drop the table.
			if (defined('TMP_DEBUG') && TMP_DEBUG != false) {
				echo "Data already exists...dropping tables and trying again.";
				parent::query("DROP TABLE templates");
				parent::query("DROP TABLE replacements");
			} else {
				echo "Data already exists...";
				return;
			}
			
			// create the tables.
			parent::query("CREATE TABLE templates (tid INTEGER PRIMARY KEY, templatename VARCHAR(64), data TEXT, author VARCHAR(32), modified DATE, created DATE, tver VARCHAR(32))");
			parent::query("CREATE TABLE replacements (rid INTEGER PRIMARY KEY, repdat VARCHAR(32), repstr VARCHAR(32))");
			
			// load the editor base for sample data.
			$fp = fopen(sprintf("%s/databases/debug/editor_base.html", $cfg['workingdir']), 'r');
			$contents = fread($fp, filesize(sprintf("%s/databases/debug/editor_base.html", $cfg['workingdir'])));
			fclose($fp);
			
			// now create sample rows.
			parent::query(sprintf("INSERT INTO templates VALUES(NULL, 'Editor_base', '%s', '%s', '%s', '%s', '%s')", sqlite_escape_string($contents), 'Software', time(), time(), TMP_VERS));
			parent::query("INSERT INTO replacements VALUES (NULL, '\$title', 'title')");
			
			// check for errors
			$this->logerror();
		}
	}
	
	//
	// fetch
	//
	public function fetch_temp($template, $save=false) {
		global $cfg;
		
		// When debugging we just get HTML files (They're a lot easier to edit).
		if (defined('TMP_DEBUG') /*&& TMP_DEBUG != false*/) {       // why would this be set to false?
			$fp = fopen(sprintf("%s/databases/debug/%s.html", $cfg['workingdir'], $template), 'r');
			$contents = stripslashes(stream_get_contents($fp));
			fclose($fp);
			
			// see if it actually exists.
			if (strlen($contents) < 2) {
				//$this->error = sprintf("Specified template %s does not exist.", $template);
				$this->throwError(sprintf("specified template %s does not exist.", $template), E_WARNING);
			}
			
			// BEFORE PASSING IT ON
			// We need to parse the replacements.
			global $global;
			foreach($global as $key => $value) {
				$contents = str_replace("\$$key", $value, $contents);
			}
			
			// check for language replacements too.
			global $lang;
			foreach ($lang as $key => $value) {
				$contents = str_replace("lang[$key]", $value, $contents);
			}
			
			// and also...smilies!
                        // ummm...later...
				
			
			// return the template
			if ($save == true) {
				return $contents;
                        } else {
				$this->tmp_data[$template] .= $contents;
                        }
			return true;
		} else {
			// otherwise pull them from a database
			// start with a name, if it's not found then try for templateid
			$temp = parent::query(sprintf("SELECT * FROM templates WHERE templatename='%s'", $template));
			
			// Now check if we actually have anything.
			if ($temp->numRows() >= 1) {
				// We found it!
				$temp = $temp->fetch_temp();
			} else {
				// ok...we didn't find it, try the templateid now.
				$temp = parent::query(sprintf("SELECT * FROM templates WHERE tid='%s'", $template));
				
				if ($temp->numRows() >= 1) {
					// We found it!
					$temp = $temp->fetch_temp();
				} else {
					// The template wasn't found :(
					$this->error = sprintf("Specified template %s does not exist.", $template);
					$this->logerror();
					
					return false;
				}
			}
				
			// check the version.
			// This is important so we don't allow templates from previous versions to be used.
			// Since we've made drastic changes to the system.
			if ($temp['tver'] != TMP_VERS) {
				//$this->error = sprintf("Mismatched template found. Template version %s found, expecting %s.", $temp['tver'], TMP_VERS);
				$this->ThrowError(sprintf("Mismatched template found. Template version %s found, expecting %s.", $temp['tver'], TMP_VERS), E_WARNING);
				return false;
			}
				
			// BEFORE PASSING IT ON
			// We need to parse the replacements.
			global $global;
			foreach($global as $key => $value) {
				$temp['data'] = str_replace("$$key", $value, $temp['data']);
			}
			
			// check for language replacements too.
			global $lang;
			foreach ($lang as $key => $value) {
				$temp['data'] = str_replace("lang[$key]", $value, $temp['data']);
			}
			
			// and also...smilies!
				
			// return the data instead
			if ($save == true) {
				return $temp['data'];
			} else {
				$this->tmp_data[$template] .= $temp['data'];
				return;
			}
		}
	}			
}
