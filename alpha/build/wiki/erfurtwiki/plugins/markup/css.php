<?php

/*
   This plugin provides for CSS support in WikiPages. To add a style
   (color, background, font, etc.) simply use the "@@" to initiate
   a CSS definition:

        @@cssparagraph  ... here comes the text
        that is formatted according to the style
        class ".cssparagraph" of our stylesheet

   In the above example the style is applied to the whole paragraph (every
   piece of text, that follows the @@). But you can also assign styles to
   just some parts of the text or even intermix and overlap multiple style
   definitions. To do so, you must however close a begun style allocation:

        @@parastyle  ... some text following
        ... but @@color:red; this part@@ is
        coloured!
        And @@subdef1 ...here... @@more3 ... @@
        a piece@@ of nested CSS-stuff.

   In this example (looks a bit weird) the last two definitions are nested!
   Note also, that you cannot only assign CSS class names to a paragraph or
   piece of text, but also direct format it using all possible CSS
   definitions - but beware that there cannot be any whitespace in the CSS
   instruction that you apply using this syntax.

   This plugin uses regular expressions, but does not slow down the
   rendering process much more than any other plugin!

   See also the 'markup_css_singleat' plugin, which allows to use just
   a single @ instead of two, like with javadoc. Both can be used
   alternative or in conjunction.
*/


define("EWIKI_CSS_BLOCK", "div");
define("EWIKI_CSS_INLINE", "span");
define("EWIKI_CSS_CLASSPREP", "");	# "wiki-" for example
define("EWIKI_CSS_LOWER", 0);		# classnames to lowercase
define("EWIKI_CSS_FIX", 0);		# allow only correct classnames

$ewiki_plugins["format_source"][] = "ewiki_format_css";



function ewiki_format_css(&$src) {

   #-- wikisource is splitted into paragraphs and later reconcatenated
   #   (which will collapse multiple linebreaks, and thus may break some
   #   things in <pre> and eventually others - the impact would be little!)
   if (strpos($src, "@@") !== false) {

      $src = explode("\n\n", $src);

      foreach ($src as $i=>$para) {
         if (strpos($para, "@@") !== false) {
            ewiki_format_css_para($src[$i]);
         }
      }

      $src = implode("\n\n", $src);
   }

}



function ewiki_format_css_para(&$para) {

   #-- opened html container elememts
   $stack = array();

   #-- how class names may look
   $s_defs = EWIKI_CSS_FIX
        ? '[-\dA-Za-z]*|[^\s]+:[^\s]+'	// disallow wrong class names
        : '[^\s]*';			// allows nearly anything

   #-- find every @@ occourence
   while (preg_match('/^(.*?)@@('.$s_defs.')(.*)$/s', $para, $uu)) {

      #-- closing @@
      if (!strlen($uu[2])) {

         if ($stack) {
            $repl = "</" . array_pop($stack) . ">";
         }
         else {
            $repl = "@&#x40;";
         }
         $para = $uu[1] . $repl . $uu[3];

      }

      #-- opening @@...
      else {

         #-- html container element
         $span = (trim( $uu[1]) ? EWIKI_CSS_INLINE : EWIKI_CSS_BLOCK);
         $stack[] = $span;

         #-- style= or class= definition?
         $is_class = !strpos($uu[2], ":");

         #-- class names into lowercase?
         if ($is_class && EWIKI_CSS_LOWER) {
            $uu[2] = strtolower($uu[2]);
         }

         #-- output
         $para = $uu[1] . "<$span " . ($is_class ? "class" : "style")
               . '="' . ($is_class ? EWIKI_CSS_CLASSPREP : "") . $uu[2] . '">'
               . $uu[3];
      }

   }

   #-- close still opened html container elements
   while ($span = array_pop($stack)) {
      $para .= "</$span>";
   }

   #<off># return($para);
         # we're now pass-by-reference
}


?>