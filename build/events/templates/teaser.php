<?php 
    if (is_object($this->event)) {
        echo '<div id="teaser" class="clearfix">';
        echo '<h1><a href="events">Events</a>';
        echo ' &raquo; <a href="events/' . $this->event->id . '">' . htmlspecialchars(MOD_layoutbits::truncate($this->event->name, 20), ENT_QUOTES) .'</a>';
        echo '</h1>';
        echo '</div>';
    }
    else
    {
        echo '<div id="teaser" class="clearfix">';
        echo '<div style="width: 40em; float: left"><h1><a href="events">Events</a></h1></div>';
        echo '<div class="float_right">';
        echo '<form action="events/search" id="events-search-box">';
        echo '<label for="events-search">Find an event</label><br />';
        echo '<input type="text" name="events-search" size="15" />';
        echo '<input type="submit" name="events-submit" value="Search" />';
        echo '</form>';
        echo '</div>';
    }
?>