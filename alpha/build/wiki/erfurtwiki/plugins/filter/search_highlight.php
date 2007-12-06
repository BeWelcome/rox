<?php

/*
   CSS-highlights the terms used as search patterns. This is done
   by evaluating the REFERRER and using the QUERY_STRINGs "q="
   parameter (which is used by Google and ewikis` PowerSearch).

   Using this plugin costs you nearly nothing (not slower), because
   there most often isn't a "?q=" from a search engine in the referer
   url.

   Modified 20040811 by Jochen Schuh
     
   Missing feature:
     Different search words are now marked different so they could 
     (but must not) styled individually.
     
   CSS styling examples:
   - I prefer to highlight only in the contents of the page, not in the
     menu or the list of modules or the footer. This is done with:
          em {
            font-style:normal;
          }
   - To style all of the searchwords the same you can use this rule. Using
     only this rule would be enough to style all searchwords. But also if
     you want to style individually such a styling rule is heavily
     recommended as fallback to style a possible 10. or 42. searchword:
          .text-body em.highlight {
            border-top: solid;
            border-bottom: solid;
            border-width: 1px;
            padding-top: 1px;
            padding-bottom: 1px;
            border-color: #000000;
            background-color: #EEEEEE;
          }
   - The first few searchwords _can_ easily styled different with:
          .text-body em.searchword-0 {
            border-color: #0066FF;
            background-color: #E8F4FF;
          }
          .text-body em.searchword-1 {
            border-color: #FF6600;
            background-color: #FFF4E8;
          }
*/


$ewiki_plugins["page_final"][] = "ewiki_search_highlight";


function ewiki_search_highlight(&$o, &$id, &$data, &$action) {

   $ref = $_SERVER["HTTP_REFERER"];
   if (strpos($ref, "q=") || strpos($ref, "search=")) {

      #-- get ?q=...
      $uu = $ref;
      $uu = substr($uu, strpos($uu, "?"));
      parse_str($uu, $q);
      if (($q = $q["q"]) || ($q = $q["search"])) {

         #-- get words out of it
         $q = preg_replace('/[^-_\d'.EWIKI_CHARS_L.EWIKI_CHARS_U.']+/', " ", $q);
         $q = array_unique(explode(" ", $q));

         #-- walk through words
         foreach ($q as $key => $word) {

            if (empty($word)) {
               continue;
            }

            while ($l = strpos(strtolower($o), strtolower($word), $l)) {

               #-- check for html-tags
               $t0 = strpos($o, "<", $l);
               $t1 = strpos($o, ">", $l);
               $found = substr($o, $l, strlen($word));
               if ((!$t0) || ($t0 < $t1)) {

                  $repl = '<em class="highlight marker searchword-' . $key . '">' . $found . '</em>';
                  $o = substr($o, 0, $l)
                     . $repl
                     . substr($o, $l + strlen($word));

                  $l += strlen($repl);
               }

               $l++;   // advance strpos
            }

         } // foreach(word)

      }

   } // if(q)

} // func

?>