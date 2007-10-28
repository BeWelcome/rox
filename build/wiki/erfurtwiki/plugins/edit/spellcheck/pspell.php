<?php

 # this plugin adds spell checking to the preview function
 #
 # you'll need a working "aspell/ispell" installed on your system,
 # or the PHP internal pspell extension
 # if you have a real "ispell", you probably should choose the other
 # spellcheck plugin, which is dedicated to that variant



 $ewiki_spellcheck_language = "en";
 $ewiki_plugins["edit_preview"][0] = "ewiki_page_edit_preview_spellcheck";

 # these will be overwritten if you load this plugin before the core script
 $ewiki_t["en"]["PREVIEW"] = "SpellCheck";
 $ewiki_t["de"]["PREVIEW"] = "RechtschreibPrüfung";




 function ewiki_page_edit_preview_spellcheck($data) {


    $html .= ewiki_page_edit_preview($data);

    ewiki_spellcheck_init($GLOBALS["ewiki_spellcheck_language"]);

    $regex = '(<.+?>|&\w;)|([\w'.EWIKI_CHARS_L.EWIKI_CHARS_U.']{2,256})';
    preg_match_all("/$regex/", $html, $words);
    $words = $words[2];
    $replacements = ewiki_spellcheck_list($words);

    $html = preg_replace("/$regex/e", ' "$1" . ( empty($replacements["$2"]) ? "$2" : $replacements["$2"] ) ', $html);

    return($html);

 }



 function ewiki_spellcheck_init($lang="en") {
    global $spell_bin, $pspell_h;
    $pspell_h = 0;
    $spell_bin = 0;

    if (function_exists("pspell_new")) {
       $pspell_h = pspell_new($lang, "", "", "iso8859-1", PSPELL_FAST|PSPELL_RUN_TOGETHER);
    }
    else {
       ($spell_bin = trim(`which aspell`)) and ($spell_bin .= " -a -B --lang=$lang ")
       or
       ($spell_bin = trim(`which ispell`)) and ($spell_bin .= " -a -B ");
    }
 }



 function ewiki_spellcheck_list($ws) {

    global $spell_bin, $pspell_h;

    #-- every word once only
    $words = array();
    foreach (array_unique($ws) as $word) {
       if (!empty($word)) {
          $words[] = $word;
       }
    }
#print_r($words);

    #-- PHP internal pspell
    if ($pspell_h) {

       #-- build ispell like check list
       $results = array();
       foreach ($words as $w) {
          if (pspell_check($pspell_h, $w)) {
             $results[$word] = "*";
          }
          else {
             $results[$word] = "& " . implode(", ", pspell_suggest($pspell_h, $w));
          }
       }

    }

    #-- external ispell binary
    elseif ($spell_bin) {

       #-- pipe word list through ispell
       $r = implode(" ", $words);
       $results = explode("\n", $r=`echo $r | $spell_bin`);
       $results = array_slice($results, 1);

    }
#print_r($results);

    #-- build replacement html hash from results
    $r = array();
    foreach ($words as $n=>$word) {
       if ($repl = $results[$n])
       {
          switch ($repl[0]) {
             case "-":
             case "+":
             case "*":
                $repl = $word;
                break;

             default:
                $repl = '<s title="'. htmlentities($repl) .'" style="color:#ff5555;" class="wrong">'.$word.'</s>';
          }
       }
       else {
          $repl = $word;
       }
       $r[$word] = $repl;
    }
#print_r($r);

    return($r);
 }



?>