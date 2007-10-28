<?php


 # this plugin does spell checking via a real "ispell" (based upon
 # the orig. spellcheck plugin)
 # Andy Fundinger (@burgiss.com)



 $ewiki_spellcheck_language = "en";
 $ewiki_plugins["edit_preview"][0] = "ewiki_page_edit_preview_spellcheck";




 function ewiki_page_edit_preview_spellcheck($data) {

    $html .= ewiki_page_edit_preview($data);
	
    ewiki_spellcheck_init($GLOBALS["ewiki_spellcheck_language"]);

    $regex = '(<.+?>)|([\w]{2,256})';  //Word characters only to prevent shell relevent characters
    preg_match_all("/".$regex."/", $html, $words);
    $words = $words[2];
    $replacements = ewiki_spellcheck_list($words);

    $html = preg_replace("/$regex/e", ' ( empty($replacements["$2"]) ? "$1$2" : "$1".$replacements["$2"] ) ', $html);

	
    return($html);

 }



 function ewiki_spellcheck_init($lang="en") {
    global $spell_bin;
    $spell_bin="ispell -a -S -C ";
 }



 function ewiki_spellcheck_list($ws) {

    global $spell_bin;

    #-- every word once only
    $words = array();
    foreach (array_unique($ws) as $word) {
       if (!empty($word)) {
          $words[] = $word;
       }
    }

    #-- via ispell binary
    if ($spell_bin) {

       #-- pipe word list through ispell
       $r = implode(" ", $words);

       $results = explode("\n", $r=`echo $r | $spell_bin `);
       $results = array_slice($results, 1);

    }

    #-- build replacement html hash from results
    $r = array();

      foreach ($results as $currline) {

		switch ($currline[0]) {
             case "-":
             case "+":
             case "*":
		//unset($repl);
                //$repl = "{".$word."}";
                break;

             default:
			 	//set word to the first word in the line form is * <WORD> ## ### ....
			 	preg_match('/. (.*?) .*/',$currline,$temp);
				$word= $temp[1];
                $repl = '<s title="'. htmlentities($currline) .'" style="color:#ff5555;" class="wrong">'.$word.'</s>';
		        $r[$word] = $repl;

          }
       }
     
 
    return($r);
 }


?>