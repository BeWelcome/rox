<?php

 # This plugin is a small modification of the original diff_gnu.php plugin.
 # It allows use with safemode enabled. 
 # However it requires the directory where diff is located to be in the php ini
 # This way you can still control what programs are executed by PHP and the programs 
 # can have root as owner.
 # Of course its not smart to put the normal bin in your php settings, cause that
 # would effectively disable safemode, just copying the diff utility works best.
 
 # Example phpsettings entry:
 #   safe_mode_exec_dir = /usr/local/bin/ewiki
 
 # With safemode disabled it should work without any special settings.
 
 # Original plugin comment:
 
 # This diff plugin utilizes the external GNU diff program to show up
 # differences between two versions of a WikiPage.
 # The diff utility is commonly not part of the graphical OS simulators
 # from Redmond, but you could install the Cygwin environment to make
 # it available.



 $ewiki_plugins["action"]["diff"] = "ewiki_page_gnu_diff_safemode";
 $ewiki_config["action_links"]["info"]["diff"] = "diff";



 function ewiki_page_gnu_diff_safemode($id, &$data, $action) {

    #-- different operation modes of GNU diff:
   $OPTIONS = " -B -u -U 50 ";
#   $OPTIONS = " -B ";
#   $OPTIONS = " -c ";
#   $OPTIONS = " --side-by-side ";

    #-- fetch old wiki source
    if (($old_ver = ($new_ver = $data["version"]) - 1) > 0)
    $data0 = ewiki_db::GET($id, $old_ver);

    $o = ewiki_make_title($id, "Differences between version $new_ver and $old_ver of »{$id}«");

    #-- create temporary files from wikipages
    $file0 = tempnam(EWIKI_TMP, "ewiki.diff.gnu.");
    $f = fopen($file0, "w");
    fwrite($f, $data0["content"]);
    fclose($f);
    $file1 = tempnam(EWIKI_TMP, "ewiki.diff.gnu.");
    $f = fopen($file1, "w");
    fwrite($f, $data["content"]);
    fclose($f);

    #-- parse thru GNU diff util
    $fn = addslashes($id);
    $OPTIONS .= " --label='$fn (version $old_ver)' --label='$fn (version $new_ver)' ";
    //following lines replace the original 
    //$diff = shell_exec("diff $OPTIONS $file0 $file1");
    $dfd = popen("diff $OPTIONS $file0 $file1", "r");
    $diff = "";
    while (!feof($dfd)) {
    	$diff .= fread($dfd, 5000);
    }
    pclose($dfd);
    //end of moderation

    #-- remove temporary files
    unlink($file0);
    unlink($file1);

    #-- encolor diff output
    foreach (explode("\n", $diff) as $dl) {

       $str = substr($dl, 1);

       switch (substr($dl, 0, 1)) {
          case "<":
          case "-":
             $o .= "<b>-</b><font color=\"#990000\"> <tt>$str</tt></font><br />\n";
             break;
          case ">":
          case "+":
             $o .= "<b>+</b><font color=\"#009900\"> <tt>$str</tt></font><br />\n";
             break;
          case "*":
          case "-":
             break;
          default:
             $o .= "<small><tt>$dl</tt></small><br />";
       }

    }

    return($o);
 }


?>
