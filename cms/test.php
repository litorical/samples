<?php
/*
require_once('config.php');
require_once('framework/code/c-logic.php');
require_once('framework/code/c-templates.php');

$img = new imgm();

$db = new sqli('test');
$tmp4 = new tmp4('test2');
//$w = $img->findwidth(11, 'framework/resources/fonts/arial.ttf', 'test');
//$h = $img->findheight(11, 'framework/resources/fonts/arial.ttf', 'test');

$tmp4->fetch('editor_base');

$im = $img->createImage(21, 'This is only a test!', 'arial', 'test.png');

$si = new serverinfo();

echo $si->gettime(1297963239, true);


?>

<img src="test.png" />*/

function highlight_html($string, $decode = TRUE){
    $tag = '#0000ff';
    $att = '#ff0000';
    $val = '#8000ff';
    $com = '#34803a';
    $find = array(
        '~(\s[a-z].*?=)~',                    // Highlight the attributes
        '~(&lt;\!--.*?--&gt;)~s',            // Hightlight comments
        '~(&quot;[a-zA-Z0-9\/].*?&quot;)~',    // Highlight the values
        '~(&lt;[a-z].*?&gt;)~',                // Highlight the beginning of the opening tag
        '~(&lt;/[a-z].*?&gt;)~',            // Highlight the closing tag
        '~(&amp;.*?;)~',                    // Stylize HTML entities
    );
    $replace = array(
        '<span style="color:'.$att.';">$1</span>',
        '<span style="color:'.$com.';">$1</span>',
        '<span style="color:'.$val.';">$1</span>',
        '<span style="color:'.$tag.';">$1</span>',
        '<span style="color:'.$tag.';">$1</span>',
        '<span style="font-style:italic;">$1</span>',
    );
    if($decode)
        $string = htmlentities($string);
    return '<pre>'.preg_replace($find, $replace, $string).'</pre>';
}

echo highlight_html('
<!-- This is an
HTML comment -->
<a href="home.html" style="color:blue;">Home</a>
<p>Go &amp; here.</p>
<!-- This is an HTML comment -->
<form action="/login.php" method="post">
    <input type="text" value="User Name" />
</form>
');
?>