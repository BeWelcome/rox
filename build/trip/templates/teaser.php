<?php
$User = APP_User::login();
$words = new MOD_words();
$search = '';
if (isset($_GET['s']) && $_GET['s'])
    $search = $_GET['s'];
?>

<div id="teaser" class="clearfix">
    <h1 style="width: 200px; float:left;"><a href="trip"><?php echo $words->getFormatted('tripsTitle'); ?></a></h1>
    <div id="searchteaser" style="width: 40%; float: left;">
    <form method="get" action="trip/search">
    <div class="trip_author" style="padding: 10px 10px 8px 10px"><a href="trip/search"><?php echo $words->getFormatted('TripsSearch'); ?> </a>
        <input type="text" style="font-size: 12px" name="s" value="<?=$search?>">
    </div>
    </form>
    </div>
</div>
