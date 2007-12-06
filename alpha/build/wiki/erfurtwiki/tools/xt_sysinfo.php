<html>
<head>
 <title>PHP System Information</title>
 <link rel="stylesheet" type="text/css" href="t_config.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>compatibility check</h1>
<p>
 This page only provides compatibility informations about ewiki and your
 PHP setup/version. Check it on new installations to be aware of problems
 and workarounds beforehand.
</p>
<table border="0" summary="info sections">
<tr>
  <th>info</th>
  <th>value</th>
  <th>help/workaround</th>
</tr>
<?php
   $tests = array(
      "PHP version" => array(
         PHP_VERSION,
         PHP_VERSION<"4.3",
         "ewiki requires version 4.3 or later, else a few features may not work due to lack of language functionality"
      ),
      "safe mode" => array(
         ini_get("safe_mode"),
         ini_get("safe_mode"),
         "The so called 'Safe Mode' cripples the PHP language from a few features, and makes running it on a Unix/Linux server senseless. If your provider has Perl or Python enabled on the server, this setting is clearly dumb or only meant to squeeze additional cash from you (before you get full PHP support). 90% of ewiki plugins will run anyhow. Don't ask for support in the other cases.",
      ),
   );
   foreach ($tests as $title=>$dat) {
      if ($dat[1]) {
         $text = $dat[2];
         $class = "error";
      }
      else {
         $text = "-";
         $class = "ok";
      }
      if (is_bool($dat[0]) || !strlen($dat[0])) {
         $dat[0] = $dat[0] ? "ON" : "OFF";
      }
      echo "<tr>\n"
         . " <td>$title</td>\n"
         . " <td class=\"$class\">$dat[0]</td>\n"
         . " <td>$text</td>\n"
         . "</tr>\n";
   }
?>
</table>
<br><br>
</body>
</html>
