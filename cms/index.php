<?php

//define("MN_VERS", "0.5.0");
//$global['mn_vers'] = '0.1.0';

include('./header.php');
error_reporting(E_ALL ^ E_NOTICE);

//$temp->fetch('page_base');

// setup comments!
//$content->db->query("CREATE TABLE comments (comment TEXT, created DATE, modified DATE, ip VARCHAR(16), parentid INT(5), uid INT(5), bid INT(5))", SQLITE_ASSOC, $content->error);
//$content->db->query(sprintf("INSERT INTO comments VALUES ('This is an example comment. It was generated using the software.', '%s', '%s', '127.0.0.1' ,'0', '1', '1')", time(), time()), SQLITE_ASSOC, $content->error);
//$content->db->query(sprintf("INSERT INTO comments VALUES ('This is an example comment reply. It was generated using the software.', '%s', '%s', '127.0.0.1' ,'1', '1', '1')", time(), time()), SQLITE_ASSOC, $content->error);
//$content->db->query(sprintf("INSERT INTO users VALUES(NULL, 'w00t', '%s', '127.0.0.1', '%s', '%s', '5')", md5('atyt8451'), time(), time()));
//$content->db->query("CREATE TABLE user_blogs (uid INT(5), blogs TEXT)", SQLITE_ASSOC, $content->error);
//echo $content->db->error;

// see if we're viewing a single blog post.
if ($si->vars['bid']) {
	$blog = $content->fetch(sprintf("SELECT * FROM blogs WHERE bid='%s'", $si->vars['bid']));
	$user = $content->fetch(sprintf("SELECT username FROM users WHERE uid='%s'", $blog['uid']));
	
	// find the number of comments
	$comments = $content->numRows(sprintf("SELECT rowid FROM comments WHERE bid='%s'", $si->vars['bid']));
		
	// setup the variables to be output
	// i.e. these are used for replacements
	$global['blogtitle'] = $blog['title'];
	$global['blogtime'] = $si->gettime($blog['time'], $cfg['showfulltime']);		// "long ago" code
	$global['blogtext'] = $blog['blog'];
	$global['blogid'] = $blog['bid'];
	$global['bloguid'] = $blog['uid'];
	$global['blogauthor'] = $user['username'];
	$global['blogrealtime'] = date($cfg['time4'], $blog['time'] + $cfg['offset']);
	$global['blogimg'] = sprintf('%s/images/icons/noicon.png', $cfg['workingdir']);
	$global['comments'] = $comments;
		
	// show the blog
	$temp->fetch('page_blog');
	
	// this is for comments...
	$temp->fetch('page_comments');
        unset($accessed);
	$result = $content->query(sprintf("SELECT rowid, comment, uid, created FROM comments WHERE bid='%s'", $si->vars['bid']));
	while ($comment = $result->fetch()) {
            print_r($comment);
		// we've already seen this comment!
		// aka it's a reply :D
		if (!isset($accessed[$comment['rowid']])) {
			// set accessed
			$accessed[$comment['rowid']] = true;
			
			// now find all replies...
			$result2 = $content->query(sprintf("SELECT rowid, comment, uid, created FROM comments WHERE bid='%s' AND parentid='%s'", $si->vars['bid'], $comment['rowid']));
			while ($reply = $result2->fetch()) {
				$user = $content->fetch(sprintf("SELECT username FROM users WHERE uid='%s'", $reply['uid']));
				
				$global['rowid'] = $reply['rowid'];
				$global['commentuid'] = $reply['uid'];
				$global['commentauthor'] = $user['username'];
				$global['commenttext'] = $reply['comment'];
				$global['commenttime'] = $si->gettime($reply['created'], $cfg['showfulltime']);
				$global['commentrtime'] = date($cfg['time4'], $reply['created'] + $cfg['offset'] );
				$global['replies'] = '';
				
				$replies .= $temp->fetch('page_reply', true);
				$accessed[$reply['rowid']] = true;
			}
			$global['replies'] = $replies;
			
			
			$user = $content->fetch(sprintf("SELECT username FROM users WHERE uid='%s'", $comment['uid']));
			
			$global['rowid'] = $comment['rowid'];
			$global['commentuid'] = $comment['uid'];
			$global['commentauthor'] = $user['username'];
			$global['commenttext'] = $comment['comment'];
			$global['commenttime'] = $si->gettime($comment['created'], $cfg['showfulltime']);
			$global['commentrtime'] = date($cfg['time4'], $comment['created'] + $cfg['offset'] );
			
			
			$temp->fetch('page_comment');
		}
	}
        unset($accessed);


// load the blogs
} else if ($si->vars['start']) {
	// don't allow naughty naughty editing!
	$naught = $si->vars['start'] / $cfg['maxblogs'];
	if ( (int)$naught == $naught) {
		// this is a good starting point.
		// now see if this blog actually exists
		$result = $content->query(sprintf("SELECT bid FROM blogs WHERE bid='%s'", $si->vars['start']));
		if ( $result->numRows() > 0 ) {
			// this blog exists, fetch it!
			$blog = $content->query(sprintf("SELECT * FROM blogs ORDER BY bid DESC LIMIT %s, %s", $si->vars['start'], (int)$si->vars['start'] + $cfg['maxblogs']));
		} else {
			// let us know that we're over-reaching.
			$temp->extradata .= sprintf("Invalid data provided. %s yields no results.", $si->vars['start']);
		}
	} else {
		// let us know that we can't do this!
		$temp->extradata .= sprintf("Invalid data provided. %s is out of range.", $si->vars['start']);
	}
}
	
// check if blog is set
if (!is_object($blog) && !is_array($blog)) {
	$blog = $content->query(sprintf("SELECT * FROM blogs ORDER BY bid DESC LIMIT %s", $cfg['maxblogs']));
}

//$content->db->query("INSERT INTO blogs VALUES (NULL, '1', 'Example Blog #2', 'This is another example of a blog post. <br />\nIt was also generated by the software for more testing purposes.', '" .time() ."', '1')", SQLITE_BOTH, $content->error);
	
	
// can't do this if we're already viewing an entry!
if (!$si->vars['bid']) {
	while ($blogs = $blog->fetch()) {
		$user = $content->fetch(sprintf("SELECT username FROM users WHERE uid='%s'", $blogs['uid']));
		
		// find the number of comments
		$comments = $content->numRows(sprintf("SELECT rowid FROM comments WHERE bid='%s'", $blogs['bid']));
		
		// setup the variables to be output
		// i.e. these are used for replacements
		$global['blogtitle'] = $blogs['title'];
		$global['blogtime'] = $si->gettime($blogs['time'], $cfg['showfulltime']);		// "long ago" code
		$global['blogtext'] = $blogs['blog'];
		$global['blogid'] = $blogs['bid'];
		$global['bloguid'] = $blogs['uid'];
		$global['blogauthor'] = $user['username'];
		$global['blogrealtime'] = date($cfg['time4'], $blogs['time'] + $cfg['offset']);
		$global['blogimg'] = sprintf('%s/images/icons/noicon.png', $cfg['workingdir']);
		$global['comments'] = $comments;
		
		// show the blog
		$temp->fetch('page_blog');
	}
}
?>