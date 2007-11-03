<?php
/*

  code by Hans B Pufal <hansp@aconit.org>

  allows html arguments inside the
  |{border=1} wiki | table |
  |         markup |       |

*/
 


 $ewiki_plugins["format_table"][] = "ewiki_format_complex_tables";


 function ewiki_format_complex_tables(&$o, &$line, &$post, $table=0) {

	 if (!$table)
	 {
	    if ((@$line[0] == '{') && ($e = strpos ($line, '}', 2)))
	    {
	       $o .= "<table " . trim (substr ($line, 1, $e - 2)) . ">\n";
	       $line = "|" . substr ($line, $e + 1);
	    }
	    else
	       $o .= "<table>\n";
	    $table = 1;
	 }

	 $line = trim ($line, "|");
	 $telements = explode ("|", $line);

	 $line = "";
	 foreach ($telements as $td)
	 {
	    if (empty($line))
	    {
	       if ((strlen($td) > 1) && (@$td[0] == '{') && ($e = strpos ($td, '}', 1)))
	       {
		  $line = "<tr " . trim (substr ($td, 1, $e - 1)) . ">";
		  $td = substr ($td, $e + 1);
	       }
	       else
		  $line = "<tr>";
	    }

	    if ((strlen($td) > 1) && (@$td[0] == '{') && ($e = strpos ($td, '}', 1)))
	       $line .= "<td " . trim (substr ($td, 1, $e - 1)) . ">" . trim(substr ($td, $e + 1)) . "</td>";
	    else
	       $line .= "<td>" . trim ($td) . "</td>";
	 }
	 $line .= "</tr>";

 }


?>