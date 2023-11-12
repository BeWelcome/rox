<?php
use App\Utilities\ForumUtilities;

?>
<input type="hidden" id="keyword" name="keyword" value="<?php echo htmlspecialchars($this->search_terms) ?>">
<div class="row no-gutters">
        <div class="col-12">
            <h3><?= $words->get('GroupsSearchDiscussionsGroup', htmlspecialchars($this->search_terms, ENT_QUOTES)); ?></h3>
        </div>
<?php $this->pager->render();
$loggedInMember = $this->member;

$words = new MOD_words();
$styles = array('l-forum-single-post--dark', '');

$cnt = 0;
foreach ($this->search_result as $post) {
?>

<div class="l-forum-single-post <?php echo $styles[$cnt % 2]; ?>">
    <!-- left column -->
    <div class="c-single-post-user_info">
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
    <div class="c-single-post-post_info">
        <div class="d-flex flex-column flex-sm-row small">
            <?php
            echo '<div class="mr-1"><span><i class="fa fa-comment fa-w-16 mr-1" title="' . $words->getFormatted('posted'); ?>"></i><?php echo date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($post->created, $this->getSession())) . '</span></div>';
            echo $words->flushBuffer() . '<div class="mr-1"><i class="fa fa-eye fa-w-16 mr-1" title="' . $words->getFormatted("forum_label_visibility") . '"></i>' . $words->getFormatted("forum_edit_vis_" . $post->ThreadVisibility) . '</div>';
            ?>
        </div>
    </div>
    <div class="c-single-post-report"></div>
    <div class="c-single-post-permalink">
        <?php
        if (isset($post->IdGroup) && $post->IdGroup != 0) {
            echo '<small><a href="group/' .$post->IdGroup . '/forum/s' . $post->IdThread . '/#post' . $post->id . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small>';
        } else {
            echo '<small><a href="forums/s' . $post->IdTthread . '/#post' . $post->id . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small>';
        }
        ?>
    </div>

    <div class="c-single-post-content js-highlight">
         <?php echo $post->message; ?>
    </div>
    <div class="c-single-post-edit"></div>
    <div class="c-single-post-moderate"><?php
        if ($loggedInMember->HasRight("ForumModerator")) {
            $TheReports = $this->_model->GetReports($post->postid);
            $max = count($TheReports);
            foreach ($TheReports as $report) {
                echo "<small class='text-muted'>{$report->Status} report from ", $report->Username, "</small><br>";
                echo "<small class='text-muted'><a href='forums/reporttomod/", $report->IdPost, "/" . $report->IdReporter . "'>view report</a></small><br>";
            }
            echo '<span></span>';
        } ?></div>
    <div class="c-single-post-admin_edit"><?php
        if (($loggedInMember->HasRight("ForumModerator", "Edit")) || ($loggedInMember->HasRight("ForumModerator", "All"))) {
            echo '<div class="d-flex justify-content-end text-nowrap">';
            echo '<a href="forums/modfulleditpost/' . $post->postid . '" class="btn btn-sm btn-outline-primary ml-1"><i class="fa fa-edit" title="adminedit"></i> Admin Edit</a>';
            echo '</div>';
        } ?></div>
</div>
<?php $cnt++;
} ?>
</div>
<?php
$this->pager->render();
