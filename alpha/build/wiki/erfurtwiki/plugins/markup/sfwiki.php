<?php

 # this plugins tries to emulate some of the markup found in »sfWiki«
 # - fancy links with 'title-->href'
 # - <nolink>WikiLink</nolink>
 # - %Wiki links%
 # - {http://include}

 # it is rather slow due to the use of regexs - nevertheless faster
 # than sfWiki I think ;->


 $ewiki_plugins["format_source"][] = "ewiki_format_source_emulate_sfwiki";


function ewiki_format_source_emulate_sfwiki (&$source) {

   $source = preg_replace('/\{([a-z0-9]+:\/\/[^}\s]+)\}/',
                          '<?plugin include $1 ?>', $source);

   $source = preg_replace('/&lt;nolink&lt;([^&]+)&lt;/nolink&gt;/',
                          '!$1', $source);

   $source = preg_replace('/%([^%]+)%/', '[$1]', $source);

   $source = preg_replace('/([^\s]+)--&gt;([^\s]+)/',
                          '[$1|$2]', $source);

}


?>