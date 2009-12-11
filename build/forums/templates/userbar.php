<?php
if ($this->_model->GetTopMode()==Forums::CV_TOPMODE_LASTPOSTS) {
?>

<h3><?php echo $this->words->getFormatted('ForumBrowseCategories'); ?></h3>
<select name="board" id="forumsboarddropdown" onchange="window.location.href=this.value;">
    <option value=""><?php echo $this->words->getFormatted('ForumChooseCategory'); ?></option>
<?php
    foreach ($topboards as $topboard) {
        $url = 'forums/t'. $topboard->tagid.'-'.$topboard->tag;
        ?>
            <option value="<?php echo $url; ?>"><?php echo $topboard->tag; ?></option>
        <?php
        /*if ($board->hasSubBoards()) {
            foreach ($board as $b) {
                echo '<a href="'.$uri.$b->getBoardLink().'">'.$b->getBoardName().'</a>';
                echo '<br />';
            }
        }*/
    }
?>
</select>
<?php
}
?>

<h3><?php echo $this->words->getFormatted('Actions'); ?></h3>
<ul class="linklist">
<?php 
//	        echo "<li class=\"icon fam_commentadd\"><a href=\"forums/new\"" ;
    $request = PRequest::get()->request;
    $uri = implode('/', $request);
    $uri = rtrim($uri, '/').'/';
?>
    <li class="icon fam_commentadd">
        <a href="<?php echo $uri,'new'; ?>"><?php echo $this->words->get('ForumNewTopic'); ?></a>
    </li>
	<?php
	if ($this->_model->GetTopMode()==Forums::CV_TOPMODE_CATEGORY) {
		echo '<li><a href="forums/lastposts">Last Posts</a></li>' ;
	}
	if ($this->_model->GetTopMode()==Forums::CV_TOPMODE_LASTPOSTS) {
		echo '<li><a href="forums/category"> by categories</a></li>' ;
	}
	?>

    <li><a href="forums/rules"><?php echo $this->words->get('ForumRulesShort'); ?></a></li>
    <li><a href="http://www.bewelcome.org/wiki/Howto_Forum"><?php echo $this->words->get('ForumLinkToDoc'); ?></a></li>
<?php  if (isset($_SESSION["IdMember"])) {
			echo "<li><a href=\"forums/subscriptions\">",$this->words->get('forum_YourSubscription'),"</a></li>"; 
			if ($this->BW_Right->HasRight("ForumModerator")) {
				echo '<li><a href="forums/reporttomod/AllMyReport">All reports for me</a></li>' ;
				echo '<li><a href="forums/reporttomod/MyReportActive">Pending reports for me ('.$this->_model->countReportList($_SESSION["IdMember"],"('Open','OnDiscussion')").')</a></li>' ;
				echo '<li><a href="forums/reporttomod/AllActiveReports">All pending reports ('.$this->_model->countReportList(0,"('Open','OnDiscussion')").')</a></li>' ;
			}
		}
		?>
</ul>
