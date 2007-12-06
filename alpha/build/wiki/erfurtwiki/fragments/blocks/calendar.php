<?php

/*
   This snippet invokes the calendar (month view) output function, if
   existing entries belonging to the current site are detected. This only
   works if ewiki_page() was called before.
*/

if (function_exists("calendar") && calendar_exists()) {
   echo calendar();
}

?>