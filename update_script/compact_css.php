#!/usr/bin/php
<?php
/**
 * Script compacts CSS files.
 * 
 * usage: php compact_css.php /folder/to/traverse/into
 *
 * @license	LGPL
 * @package	+CMS <http://cms.naczasie.pl>
 */
$total = 0;
if (!function_exists('fnmatch')) {
function fnmatch($pattern, $string) {
   for ($op = 0, $npattern = '', $n = 0, $l = strlen($pattern); $n < $l; $n++) {
       switch ($c = $pattern[$n]) {
           case '\\':
               $npattern .= '\\' . @$pattern[++$n];
           break;
           case '.': case '+': case '^': case '$': case '(': case ')': case '{': case '}': case '=': case '!': case '<': case '>': case '|':
               $npattern .= '\\' . $c;
           break;
           case '?': case '*':
               $npattern .= '.' . $c;
           break;
           case '[': case ']': default:
               $npattern .= $c;
               if ($c == '[') {
                   $op++;
               } else if ($c == ']') {
                   if ($op == 0) return false;
                   $op--;
               }
           break;
       }
   }

   if ($op != 0) return false;

   return preg_match('/' . $npattern . '/i', $string);
}
}


function traverse($dirname, $function_name, $pattern) {
    $dir = dir($dirname);
    while ($entry = $dir->read()) {
        if ($entry{0} == '.') 
            continue; // skip .*
        if (preg_match('/gif$|jpg$|jpeg$|zip$|gif$|bin$|png$|gz$|tar$|exe$|com$|ico$|bmp$|so$|dll$/', $entry))
            continue;

        $full_path = $dirname . '/' . $entry;  
        if (is_dir($full_path))
            traverse($full_path, $function_name, $pattern);
        else {
            if (fnmatch($pattern, $entry)) {
                $function_name($full_path);
            }
        }
    }
}

function filewrite($f,$s) {
	$file = fopen($f, 'w');
	fwrite($file, $s);
	fclose($file);
}

function remove_white_chars($str) {
	$str = str_replace("\r",'',$str);

	$lines = explode("\n",$str);

	for($i=0;$i<count($lines);$i++) {
		$line = $lines[$i];

		$line = preg_replace("/\/\/(.*)/u","",$line);
		$line = trim($line);
		$line = str_replace("\t",' ',$line);
		$line = preg_replace('/ {2,}/', ' ', $line);
		$line = str_replace(': ',':',$line);
		$line = str_replace('; ',';',$line);
		$line = str_replace("\n",'',$line);
		
		$lines[$i] = $line;
	}
	$str = implode('',$lines);
	$str = preg_replace("/\/\*(.*?)\*\//u","",$str);
	return $str;
}

function clean_css($filename) {
	global $total;
	echo $filename;
	if(is_writable($filename)) {
		$content = file_get_contents($filename);
		$start = strlen($content);
	/* Clear CSS */
		$content=remove_white_chars($content);
		$content=str_replace('}',"}\r\n",$content);
		$end = strlen($content);
		filewrite($filename,$content);
		$save = (($start-$end)/$start);
		$total = $total + $save;
		echo ': '.$save.': Total: '.$total;
	} else {
		echo ': cant write!';
	}
	echo "\n\r";
}
if ($argc == 2) {
	if(is_dir($argv[1])) {
		echo 'CSS in folder: '.$argv[1]."\n";
		traverse($argv[1], "clean_css", '*.css');
	} else {
		echo 'Folder: '.$argv[1]." not readable!\n";
	}
}
?>