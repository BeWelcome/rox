<div id="teaser" class="floatbox">
<div class="float_left">
<h1><a href="activities"><?php echo $words->get('Activities'); ?></a>
<?php
if (isset($this->event)) {
    echo htmlspecialchars(MOD_layoutbits::truncate($this->event->name, 20), ENT_QUOTES);
}
?>
</h1>
</div>
<div class="float_right">
<form action="activities/search" id="activities-search-box">
<label for="activities-search"><?php echo $words->get('ActivitiesFind'); ?></label><br />
<input type="text" name="activities-search" size="15" />
<input type="submit" name="activities-submit" value="Search" />
</form>
</div>
</div>