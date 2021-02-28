<?php

$User = $this->_model->getLoggedInMember();

$words = new MOD_words();
if (isset($keyword)) {
    echo '<input type="hidden" id="keyword" name="keyword" value="' . htmlentities($keyword) . '">';
}
?>
<div class="row">
<div class="col-8"><h3>
<?php echo $words->flushBuffer();

	$number = $boards->getTotalThreads();
	if ($number == 0) {
		echo $words->getFormatted("Found0Threads");
	} else if ($number == 1) {
		echo $words->getFormatted("Found1Threads");
	} else {
		echo $words->getFormatted("FoundXThreads", $number);
	}
	?>
    </h3>
</div>

<?php
if ($User && empty($noForumNewTopicButton)) {
?>
	<div class="col-4 mb-1">
        <a class="btn btn-primary float-right" href="group/<?php if ($this->_model->IdGroup) {
            echo $this->_model->IdGroup;
        } else {
            echo $this->uri;
        } ?>/new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a><?php echo $words->flushBuffer(); ?></div>
<?php
} // end if $User


	if ($threads = $boards->getThreads()) {
		require 'boardthreads.php';
	}

?>
</div>
<?php
