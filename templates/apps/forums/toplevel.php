<?php
$User = APP_User::login();

$i18n = new MOD_i18n('apps/forums/board.php');
$boardText = $i18n->getText('boardText');

?>

<!--<h2><?php echo $boardText['title']; ?></h2>


<div id="forums_introduction"><?php echo $boardText['intro']; ?></div>-->

<div id="forum">
  <div class="row">
    <h3><?php echo $boardText['browse']; ?></h3>
    <div class="l" style="width: 25%;">
      <h4><?php echo $boardText['browse_category']; ?></h4>
      <ul>
<?php
	foreach ($top_tags as $tagid => $tag) {
		echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($tag->tag).'">'.$tag->tag.'</a><br />
			<span class="forums_tag_description">'.$tag->tag_description.'</span>
		</li>';
	}

?>
      </ul>
    </div> <!-- l -->


    <div class="l" style="width: 25%;">
      <h4><?php echo $boardText['browse_continent']; ?></h4>
      <ul class="floatbox">
        <li><a href="forums/kAF-Africa">Africa</a></li>
        <li><a href="forums/kAN-Antarctica">Antarctica</a></li>
        <li><a href="forums/kAS-Asia">Asia</a></li>
        <li><a href="forums/kEU-Europe">Europe</a></li>
        <li><a href="forums/kNA-North America">North America</a></li>
        <li><a href="forums/kSA-South Amercia">South Amercia</a></li>
        <li><a href="forums/kOC-Oceania">Oceania</a></li>
      </ul>
    </div> <!-- l -->

    <div class="l floatbox tags" style="width: 45%;">
      <h4><?php echo $boardText['browse_tag']; ?></h4>
      <?php
//      	$taglist = '';
//      	foreach ($all_tags as $tagid => $tag) {
//			if 
//      		$taglist .=  '<a href="forums/t'.$tagid.'-'.rawurlencode($tag).'">'.$tag.'</a>&nbsp;:: ';
//      	}
//      	$taglist = rtrim($taglist, ': ');
//      	echo $taglist;
      
// New Tag Cloud
	  
	echo '<div id="tagcloud">';
	$maximum = $all_tags_maximum;
    $taglist = '';
    foreach ($all_tags as $tagid => $tag) {

	$percent = floor(($tag->counter / $maximum) * 100);

	if ($percent <20)
	{
	$class = 'tag_smallest';
	} elseif ($percent>= 20 and $percent <40) {
	$class = 'tag_small';
	} elseif ($percent>= 40 and $percent <60) {
	$class = 'tag_medium';
	} elseif ($percent>= 60 and $percent <80) {
	$class = 'tag_large';
	} else {
	$class = 'tag_largest';
	}
		
   		$taglist .=  '<a href="forums/t'.$tag->tagid.'-'.rawurlencode($tag->tag).'" class="'.$class.'">'.$tag->tag.'</a>&nbsp;:: ';

	}
   	$taglist = rtrim($taglist, ': ');
    echo $taglist;


	echo '</div>';
?>
    </div> <!-- l floatbox tags -->
  </div> <!-- row -->
  
<br style="clear: both;" />
<?php
	$uri = 'forums/';
	if ($threads = $boards->getThreads()) {
?>
  <div class="row">
    <div class="r">
      <span class="button"><a href="forums/new">New Topic</a></span>
    </div> <!-- r -->
    <h3><?php printf($boardText['newest'], $boards->getTotalThreads()); ?></h3>
  </div><!--  row -->
<?php
		require TEMPLATE_DIR.'apps/forums/boardthreads.php';
?>

</div> <!-- Forum-->
<?php
	}
?>