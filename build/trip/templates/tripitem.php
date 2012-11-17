<?php

$words = new MOD_words();
// processing to display date ranges of trip correctly
// TODO MAYBE: add date range to DB table for each trip or similar solution
if (isset($trip_data[$trip->trip_id])) {
    $trip->count = count($trip_data[$trip->trip_id]);
    $first = 0;
    $last = 0;
    foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
        $now = $blog->blog_start;
        if (!$first || $now <= $first)
            $first = $blog->blog_start;
        if (!$last || $now > $last)
            $last = $blog->blog_start;
    }
    if ($first != $last)
        if (date("Y", strtotime($first)) == date("Y", strtotime($last)))
            $daterange = date("M d", strtotime($first)).' - '.date("M d Y", strtotime($last));
        else $daterange = date("M d Y", strtotime($first)).' - '.date("M d Y", strtotime($last));
    else $daterange = $first;
}
if (!isset($daterange)){
    $daterange = '';
}
?>

<h2 class="trip_title"><a href="trip/<?=$trip->trip_id; ?>"><?=$trip->trip_name; ?></a></h2>
<div class="trip_author">
        <span class="trip_daterange"><?=$daterange?></span>
        <a href="trip/<?=$trip->trip_id; ?>"><img src="styles/css/minimal/images/iconsfam/map.png" alt="Trip Map &amp; Details" /> </a>
         &mdash;
        <?=$words->get('by');?> <a href="user/<?=$trip->handle; ?>"><?=$trip->handle; ?></a>
<?php
// show flags for a trip that is about a specific country !?
if ($trip->fk_countrycode) {
    echo ' <a href="country/'.$trip->fk_countrycode.'"><img src="images/icons/flags/'.strtolower($trip->fk_countrycode).'.png" alt="" /></a>';
}
?>
        <a href="blog/<?=$trip->handle; ?>" title="Read blog by <?=$trip->handle; ?>"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/<?=$trip->handle; ?>" title="Show trips by <?=$trip->handle; ?>"><img src="images/icons/world.gif" alt="" /></a>

</div> <!-- trip_author -->

<div class="floatbox">
<?php
if (!isset($trip_data[$trip->trip_id])) {
    if (isset($trip->trip_descr) && $trip->trip_descr) {
        echo '<p>'.$trip->trip_descr.'</p>';
    }
    if (isset($trip->trip_text) && $trip->trip_text) {
        echo '<p>'.$trip->trip_text.'</p>';
    }
} else {

?>
<!-- Subtemplate: 2 columns 50/50 size -->
<div class="subcolumns" style="width: 500px">
  <div class="c50l">
    <div class="subcl">
<?php
    if (isset($trip->trip_descr) && $trip->trip_descr) {
        echo '<p>'.$trip->trip_descr.'</p>';
    }
    if (isset($trip->trip_text) && $trip->trip_text) {
        echo '<p>'.$trip->trip_text.'</p>';
    }
?>
<!-- End of contents for left subtemplate -->
    </div> <!-- subcl -->
  </div> <!-- c50l -->

  <div class="c50r">
    <div class="subcr">
      <!-- Contents for right subtemplate -->
<?php
    echo '<ul>';
    $counter = 0;
    foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {

        echo '<li style="line-height: 2.2em; border-bottom: 1px solid #e5e5e5"><span>';
        echo '<a href="blog/'.$trip->handle.'/'.$blogid.'" style="color: #333">';
        switch (++$counter) {
            case 1:
            case $trip->count:
                $flag = 'flag_yellow.png';
                break;
            default:
                $flag = 'bullet_go.png';
                break;
        }
        if ($blog->name) {
            echo '<img src="styles/css/minimal/images/iconsfam/'.$flag.'" alt="flag" /> ';
            echo '<b>'.$blog->name.'</b>';
        } elseif ($blog->blog_title) {
            echo '<b><i>'.$blog->blog_title.'</i></b>';
        }
        echo '</a>';
        echo '</span>';

        if ($blog->blog_start) {
            echo ', ';
            echo date("M d Y", strtotime($blog->blog_start));
        }
        echo '</li>';

    }
    echo '</ul>';
?>
<!-- End of contents for right subtemplate -->
    </div> <!-- subcr -->
  </div> <!-- c50r -->
</div> <!-- subcolumns -->
<?
}
?>

</div>
