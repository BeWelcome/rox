<?php
$User = APP_User::login();

$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
<div id="title">
  <h1><?php echo $words->getFormatted('ForumTitle'); ?><br />
<?php
    // CategoryTitle in teaser
    echo '<span class="small">';
    echo '<a href="forums">'.$words->getFormatted('ForumOverviewLink').'</a>';    
    $title = $boards->getBoardName();
    if ($title != 'Forums') {echo ' > <a href="forums/', $title ,'">', $title,'</a>';}
    echo '</span>';
?>
  </h1>
</div>

</div>