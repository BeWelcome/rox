<?php

/*
   This mpi allows for polls/surveys, whose results are stored into
   named datapages.

      !! What would you say?
      <?plugin Survey data=PollOneData
         answer1="I vote for this!"
         second="another option"
         3="no meaning on that issue."
      ?>

   Except for the data= setting, the named parameters are free-form and
   only associate the text to an internal (named hash) entry.

   If you later want to make a poll read-only, you could use:
   <?plugin-show Survey data="data/var/poll1.dat" ... ?>  (this is just
   a pagename, that __looks__ like a filename).
*/


define("EWIKI_UP_SURVEY", "_voting4");


$ewiki_plugins["mpi"]["survey"] = "ewiki_mpi_survey";
$ewiki_plugins["view_stat"][] = "ewiki_show_survey";

function ewiki_mpi_survey($action, &$args, &$iii, &$s)
{
   global $ewiki_id, $ewiki_plugins;
   $o = "";

   #-- load data page
   if (! ($df = $args["data"])) {
      return;
   }
   unset($args["data"]);
   unset($args["_"]);
   unset($args[""]);
   $data = ewiki_db::GET($df);
   if (!$data["version"]) {
      $data = ewiki_new_data($df, EWIKI_DB_F_BINARY);
      $data["version"]--;
   }
   if ($data["flags"] != EWIKI_DB_F_BINARY) {
      return;
   }
   $survey = unserialize($data["content"]);

   #-- operation
   $vote = @$_REQUEST[EWIKI_UP_SURVEY];
   if ($vote == "$") {
      $action = "show";
   }

   if ($action=="html")
   {
      #-- show entries
      if (!$vote) {
         $o = "\n"
            . '<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" enctype="multipart/form-data">'
            . '<input type="hidden" name="id" value="'.htmlentities($ewiki_id).'">'
            . "\n";
         foreach ($args as $name=>$text) {
            if (!$name || !$text || ($name=="data")) { continue; }
            $o .= '<input type="radio" name="'.EWIKI_UP_SURVEY.'" value="'
                . htmlentities($name) . '"> ' . $text . "<br />\n";
         }
         $o .= '<input type="submit" value="vote">';
         $o .= "\n</form>\n<br /><br />\n";
         $o .= '<a href="'.ewiki_script("",$ewiki_id,array(EWIKI_UP_SURVEY=>"$")).'">show results</a><br />';
      }

      #-- store an entry
      if ($vote) {
         $survey[$vote]++;
         $data["content"] = serialize($survey);
         $data["version"]++;
         $data["lastmodified"] = time();
         $data["author"] = ewiki_author();
         ewiki_db::WRITE($data);

         #-- show it
         $action = "show";
      }
   }

   if ($action=="show")
   {
      $o .= $ewiki_plugins["view_stat"][0]($survey, $args);
   }
   return($o);
}


function ewiki_show_survey($count, $text) {

   $o = "";
   $char = "*";    // char for result bars (<img> alt text or <div> content)
   $clen = 60;     // max len of above
   $px = 6;        // pixel size of each $char, for CSS
   $colors = array(
      "#ff8888", "#88ee88", "#9999ff", "#ffbb33", "#eeee66",
      "#dd99dd", "#555555", "#dddddd",
//      "poll1.png", "poll2.png", "poll3.png", "poll4.png", "poll5.png",
   );

   $all = 0;
   $max = 0;
   foreach ($text as $name=>$uu) {
      $all += $count[$name];
      $max = max($max, $count[$name]);
   }

   $i = 0;
   if ($max && $all) {
      $o .= "\n";
      foreach ($text as $name=>$title) {

         #-- calc
         $n = 0 + $count[$name];                 // number of votes
         $n_percent = ((int) (1000*$n/$all))/10;
         $bar_chars = (int)($clen * ($n/$max));  // num of chars in bar
         $bar_width = $n_c * $px;                // pixel width of bar
         $bar_str = "[" . str_repeat($char, $bar_chars) . "]";

         #-- visualization
         $color = $colors[$i];
         if ($color{0} == "#") {
            $bar = "<div style=\"color:$color; background:$color; width:$bar_width; height: 5px; border: 1px solid #333333;\">" . $bar_str . "</div>\n";
         }
         else {
            $bar = "<div><img src=\"$color\" height=\"6\" width=\"$bar_width\" alt=\"$bar_str\" /></div>";
         }
         $i += 1;
         $i %= count($colors);

         #-- print
         $o .= "$title<br />\n";
         $o .= "<b>$n</b> votes, $n_percent%<br />\n";
         $o .= $bar;
         $o .= "<br />\n\n";

      }
   }

   return($o);
}


?>