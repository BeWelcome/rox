<?php

/*
   This plugin allows you to use the toy programming language known
   as "Brainfuck", which is turing complete with only 8 commands:

   > increment pointer into data array
   < decrement the data pointer
   + increment the byte at the pointer
   - decrement the data byte
   . output the byte at the pointer
   , input a byte, store it into the data array
   [ starts a code block, only run if current data byte is zero
   ] loop back to the opening bracket

   You simply write:
   <?plugin BrainFuck
      ++>++++++++++[-<+++++++>]<.+>+++++++[-<++++>]<.++>++
      +++[-<+>]<..+++.------>+++++++++[-<-------->]<.
   ?>
   Or
   <?plugin-input BrainFuck ....code.... ?>

   You must first create a symlink in the mpi/ main directory to
   here, else this can't be used (disabled because it could slow
   down wiki sites).
*/


$ewiki_plugins["mpi"]["brainfuck"] = "ewiki_mpi_brainfuck";
function ewiki_mpi_brainfuck($action, $args, &$iii, &$s)
{
   global $ewiki_id;

   if ($action == "input") {
      return('<form action="'.ewiki_script("", $ewiki_id).'">'
            .'<input name="i" type="text"> '
            .'<input type="hidden" name="id" value="'.htmlentities($ewiki_id).'">'
            .'<input type="submit"></form>'
      );
   }
   else {
      $input = "";
      foreach ($_REQUEST as $i=>$str) {
         if (strlen($i)==1) {
            $input .= $str;
         }
      }
      return(bf_exec($args["_"], $input));
   }
}


function bf_exec(&$code, $in)
{
   $out = "";
   $data[0] = 0;
   $p = 0;
   $in_len = strlen($in);
   $in_p = 0;
   $code_len = strlen($code);
   $pc = 0;

   while ($pc < $code_len) {
      switch ($c=$code{$pc}) {

         case ">":      // increment data pointer
           $p++;
           if ($p >= count($data)) {
              $data[$p] = 0;
           }
           break;

         case "<":      // decrement data pointer
           if ($p) {
              $p--;
           }
           break;

         case "+":      // inc data byte
           $data[$p]++;
           break;

         case "-":      // dec data byte
           $data[$p]--;
           break;

         case ".":
            $out .= chr($data[$p]);
            break;

         case ",":
            if ($in_p < $in_len) {
               $data[$p] = ord($in[$in_p++]);
            }
            else {
               $data[$p] = 0;
            }
            break;

         case "[":      // skip loop, if data byte is zero
           if ($data[$p] == 0) {
              $s = 1;
              while ($s) {
                 $c = $code[++$pc];
                 if ($c == "[") {
                    $s++;
                 }
                 elseif ($c == "]") {
                    $s--;
                 }
              }
           }
           break;

         case "]":      // repeat
              $s = 1;
              while ($s) {
                 $c = $code[--$pc];
                 if ($c == "[") {
                    $s--;
                 }
                 elseif ($c == "]") {
                    $s++;
                 }
              }             
              $pc--;
           break;

         case "\n":
         case "\r":
         case "\f":
         case "\t":
         case " ":
            break;

         default:
            $out .= " (PARSING ERROR '$c' at position #$pc)";
            return($out);
            break;
      }

      $pc++;
   }

   return($out);
}


?>