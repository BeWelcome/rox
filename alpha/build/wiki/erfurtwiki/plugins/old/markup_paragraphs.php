<?php

/*

  this plugin makes ewiki mimic the (more text paragraph oriented)
  markup style of newer PhpWiki versions
  * text style triggers (bold or italic) work over multiple lines
  * some markup (lists) can be continued after line breaks

*/



$ewiki_plugins["format_source"][] = "ewiki_format_text_in_paragraphs";


function ewiki_format_text_in_paragraphs(&$wiki_source) {

   $wiki_source = preg_replace('/(?<!\n)\n([^-!*#\t;: \n])/', ' $1', $wiki_source);

}



?>