<?php

/*
   This plugin provides translation of the currently shown page via the
   Google, Altavista/Babelfish or InterTran online services. The services
   support different source and destination languages. The bottleneck of
   this plugin however is the detection of the language, the current page is
   in (only detectes English, Spanish, French, German and Italian, Portuguese
   very poorly). So you shouldn't forget to correct EWIKI_DEFAULT_LANG for
   your Wiki setup.
*/


#-- order of this array marks the preference
$language_service = array(
   "google" => array("http://translate.google.com/translate_p?hl=en&ie=ISO-8859-1&prev=/language_tools", "langpair", "u"),
   "babelfish" => array("http://babelfish.altavista.com/babelfish/urlload?tt=url", "lp", "url"),
   "intertran" => array("http://intertran.tranexp.com.com/Translate/index.shtml&type=url", array("from", "to"), "url"),
);
$language_detect = array(
   "en" => "the to of is and this for be if you with an or it on not as that by will are see also can from use only file your at note may no which all false when about has have new one true any else more should without into first these own but must there do other",
   ".en" => "ing ght y",
   "de" => "die der und mit das sie den ist wird von eine im ein zu des auf dem werden oder aus wie datei es als nicht nen bei auch wenn einer einen an sind zur sich einem daß dass diese um zum nur gibt eines kann nach beim dann also dieser durch haben hat dabei alle jedoch noch aber dazu er diesem soll keine",
   ".de" => "en ag",
   "*de" =>  "isch cht wert ung ä ö ü ß",
   "es" => "de en la el para que un una no los con por es se del como su las puede al página si ud texto ser sobre lo gina ginas esta entre más debe hay dos son este sus est nueva esto desde versi sin tiene pero dise cual uno sea nuevo mismo nea usar caso as pues ha eso all mucho bien dar estas estar tema tan desee aún nz os hace otros ya muy uno tulo nuevos deber poco le esa tal menos cap buena qu muchos hacer trav modo vaya tres lea nada vez vea ar met ve mi il út así está ese deja bajo sino muchas an qui está eso tema",
   "fr" => "de la les le des pour est et l un en vous une sur dans du que par il qui es pas avec ne ce ou tre sont plus au qu me comme donn acc on pr cette peut tout si mais re se votre cela bien je sans autres aux vos cr ces ont fa fait puis cas avez ment ils temps autre sous mes fen donc ainsi tous alors apr te doit son chez deux tant res leur devez soit tat moins sa nom non ci faut peu avant avoir elle ses mot tes vue ceci elles bas ois ma er tait voulez ceux ex quoi",
   "*fr" => "è",
   "it" => "gli di nel ed per i della sono dell e con pi",
   ".it" => "zion zione zioni zie zia ierie i",
   "pt" => "do seu seus",
   ".pt" => "ê",
);
$language_comb = array(
   "google" => array(
      "en|es", "en|fr", "en|de", "en|it", "en|pt",
      "es|en", "fr|en", "de|en", "it|en", "pt|en",
      "fr|de", "de|fr",
   ),
   "babelfish" => array(
      "en|zh", "en|fr", "en|de", "en|it", "en|ja", "en|ko", "en|pt", "en|es",
      "zh|en", "fr|en", "de|en", "it|en", "ja|en", "ko|en", "pt|en", "es|en",
      "ru|en", "fr|de", "de|fr",
   ),
   "intertran" => array(
      "*|*",
   ),
);
$language_names = array(  // for intertran
   "ch" => "chi",   "en" => "eng",   "fr" => "fre",   "it" => "ita",
   "jp" => "jpn",   "nl" => "dut",   "po" => "pol",   "pt" => "poe",
   "ru" => "rus",   "de" => "ger",   "ed" => "spa",   "lt" => "ltt",
);
$ewiki_t["C"]["en"] = "English";
$ewiki_t["C"]["de"] = "German";
$ewiki_t["C"]["fr"] = "French";
$ewiki_t["C"]["es"] = "Spanish";
$ewiki_t["C"]["it"] = "Italian";
$ewiki_t["C"]["pt"] = "Portuguese";



$ewiki_plugins["handler"][] = "ewiki_add_action_link_for_translation";



function ewiki_add_action_link_for_translation($id, &$data, $action) {

   global $language_comb, $language_service, $language_names, $ewiki_t,
          $ewiki_config;

   $o = "";
   $url = "";

   if ($data["version"]) {
      $lang_src = ewiki_guess_lang($data["content"]);

      #-- check if page is already in desired language
      if ($ewiki_t["languages"][0] == $lang_src) {
      }
      else {

         foreach ($ewiki_t["languages"] as $lang_dest) {

            $url = "";
            $comb = "$lang_src|$lang_dest";

            foreach ($language_service as $SERVICE=>$params) {

               if (in_array($comb, $language_comb[$SERVICE]) || ($SERVICE=="intertran")) {
                  if ($SERVICE == "babelfish") {
                     $lp = "&" . $params[1] . "=" . strtr($comb, "|", "_");
                  }
                  elseif ($SERVICE == "google") {
                     $lp = "&" . $params[1] . "=" . $comb;
                  }
                  else {
                     $from = $language_names[strtok($comb, "|")];
                     $to = $language_names[strtok("|")];
                     if (!$from || !$to) { 
                        continue;
                     }
                     $lp = "&" . $params[1][0] . "=" . $from
                         . "&" . $params[1][1] . "=" . $to;
                  }
                  $url = $params[0] . $lp
                       . "&" . $params[2] . "="
                       . urlencode( ewiki_script($action, $id, "", 0, 0,
                                                 ewiki_script_url())     );
                  break;
               }
            }

            #-- add translation link to page
            if ($url) {
               $ewiki_config["action_links"]["view"][$url] = "TranslateInto" . $ewiki_t["C"][$lang_dest];
               /*---
                  $o = "<br /><a class=\"tool-button\" href=\""
                  . $url . "\">"
                  . "TranslateInto" . $ewiki_t["C"][$lang_dest]
                  . "</a>\n";
               ---*/
               break;
            }

         }
      }
   }
/*---
   return($o);
---*/
}



function ewiki_guess_lang(&$data) {

   global $language_detect;

   #-- prepare
   $detect = array(
      "en"=>0,
      "de"=>0,
   );

   #-- separate words in text page
   $text = strtr(
      "  $data ",
      "\t\n\r\f_<>\$,.;!()[]{}/",
      "                        "
   );

   #-- search for words in text
   foreach ($language_detect as $lang => $word_str) {
      foreach (explode(" ", $word_str) as $word) {
         switch ($lang[0]) {
            case ".":
               $word = "$word ";
            case "*":
               $lang = substr($lang, 1);
               break;
            default:
               $word = " $word ";
         }
         $l = -1;
         while ($l = strpos($text, $word, $l+1)) {
            $detect[$lang]++;
         }
      }
   }

   #-- get entry with most counts
   $lang = EWIKI_DEFAULT_LANG;
   arsort($detect);
   $keys = array_keys($detect);
   if (array_shift($detect) >= 5) {
      $lang = array_shift($keys);
   }

   return($lang);
}



?>