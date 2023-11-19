<?php
use App\Utilities\ForumUtilities;

$words = new MOD_words();

?>
<input type="hidden" id="read.more" value="<?php echo $words->get('forum.read.more'); ?>">
<input type="hidden" id="show.less" value="<?php echo $words->get('forum.read.less'); ?>">
<input type="hidden" id="keyword" name="keyword" value="<?php echo htmlspecialchars($this->search_terms) ?>">
<div class="row no-gutters">
        <div class="col-12">
            <h3><?= $words->get('GroupsSearchDiscussionsGroup', htmlspecialchars($this->search_terms, ENT_QUOTES)); ?></h3>
        </div>
<?php $this->pager->render();
$loggedInMember = $this->member;

$styles = array('l-search-post--dark', '');

$cnt = 0;
foreach ($this->search_result as $post) {
?>

<div class="l-search-post <?php echo $styles[$cnt % 2]; ?> u-w-full">
    <!-- left column -->
    <div class="c-search-user_info">
        <a id="post<?php echo $post->id; ?>" style="position: relative; top:-50px;"></a>
        <div class="d-flex flex-row text-break">
            <img class="profileimg avatar-48 mr-1" width="48" height="48" src="/members/avatar/<?php echo($post->Username); ?>/48">
            <div class="small">
                <a href="members/<?php echo $post->Username; ?>"><?php echo $post->Username; ?></a>
                <?php
                if (isset($post->city) && isset($post->country)) {
                    echo '<br>' . $post->city . '<br>' .$post->country;
                }
                ?>
            </div>
        </div>
    </div>
    <div class="c-search-post_info">
        <div class="d-flex flex-column flex-sm-row small">
            <?php
            echo '<div class="mr-1"><span><i class="fa fa-comment fa-w-16 mr-1" title="' . $words->getFormatted('posted'); ?>"></i><?php echo date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($post->created, $this->getSession())) . '</span></div>';
            echo $words->flushBuffer() . '<div class="mr-1"><i class="fa fa-eye fa-w-16 mr-1" title="' . $words->getFormatted("forum_label_visibility") . '"></i>' . $words->getFormatted("forum_edit_vis_" . $post->ThreadVisibility) . '</div>';
            ?>
        </div>
    </div>
    <div class="c-search-permalink">
    <div class="c-search-permalink">
        <?php
        if (isset($post->IdGroup) && $post->IdGroup != 0) {
            echo '<small><a href="group/' .$post->IdGroup . '/forum/s' . $post->IdThread . '/#post' . $post->id . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small>';
        } else {
            echo '<small><a href="forums/s' . $post->IdTthread . '/#post' . $post->id . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small>';
        }
        ?>
    </div>
    <div class="c-search-thread_info js-highlight"><div class="u-flex u-justify-end"><small><?php
            echo $words->get('forum.thread');
        if (isset($post->IdGroup) && $post->IdGroup != 0) {
            echo '<a href="group/' .$post->IdGroup . '/forum/s' . $post->IdThread . '/#post' . $post->id . '">' . $post->title . '</a>';
        } else {
            echo '<a href="forums/s' . $post->IdTthread . '/#post' . $post->id . '">' . $post->title . '</a>';
        }
        ?></small></div></div>

    <div class="c-search-content js-highlight js-read-more">
         <?php echo $post->message; ?>
    </div>
</div>
<?php $cnt++;
} ?>
</div>
<?php
$this->pager->render();
