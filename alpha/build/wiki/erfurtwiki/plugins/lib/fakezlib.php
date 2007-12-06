<?php
/*
   You need to load this, if you want to use db/fast_files with very
   old PHP versions, such without zlib extension.
*/


 #-- fake zlib
 if (!function_exists("gzopen")) {

    function gzopen($fp, $mode) {
       $mode = preg_replace('/[^carwb+]/', '', $mode);
       return(fopen($fp, $mode));
    }

    function gzread($fp, $len) {
       return(fread($fp, $len));
    }

    function gzwrite($fp, $string) {
       return(fwrite($fp, $string));
    }

    function gzseek($fp, $arg2) {
       return(fseek($fp, $arg2));
    }

    function gzclose($fp) {
       return(fclose($fp));
    }

 }


?>