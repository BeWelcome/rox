<?php

/*
   This plugin adds the use of @CLASSNAME to create a <div class=CLASSNAME> 
   surronding the current line. It works faster than the markup_css plugin
   and its syntax is probably well known to JavaDoc users. (You do not need
   to have the markup_css loaded to use this one.)

   Andy Fundinger
*/


$ewiki_plugins["format_line"][] = "ewiki_format_line_css_div";

function ewiki_format_line_css_div (&$o, &$line, &$post) {

	$atregex = "/^@(\w+) (.*)/";
	if (preg_match( $atregex, $line, $regs)){
		$o .=  "<div class=\"$regs[1]\"> ";
		$post = "</div>" . $post;
		$line = $regs[2];  //Remove @CLASSNAME from this line
	}
}

?>

