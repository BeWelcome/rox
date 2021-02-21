<div class="row no-gutters">
        <div class="col-12">
            <h3><?= $words->get('GroupsSearchDiscussionsGroup', htmlspecialchars($this->search_terms, ENT_QUOTES)); ?></h3>
        </div>
    <div class="col-12">
        <?php $this->pager->render(); ?>
        <table class="table table-striped table-hover" style="table-layout: fixed;">
            <tbody>
        <?php
        $words = new MOD_words();
        $layoutbits = new MOD_layoutbits();
        foreach ($this->search_result as $thread) {

            $url = "/forums/s" . $thread->IdThread;

            $max = $thread->replies + 1;
            $maxPage = ceil($max / 200);

            $last_url = $url . ($maxPage != 1 ? '/page'.$maxPage : '') . '/#post' . $thread->last_postid;

                ?>
                <tr>
                    <td class="text-truncate"><?php
                        if ($thread->ThreadDeleted=='Deleted') {
                            echo "[Deleted]" ;
                        }
                        if ($thread->ThreadVisibility=="ModeratorOnly") {
                            echo "[ModOnly]" ;
                        }
                        ?>
                        <a href="<?php echo $url; ?>">
                            <?php
                            echo $words->fTrad($thread->IdTitle);
                            ?></a>
                        <div class="w-100">
                    <span class="small grey"><?php echo $words->getSilent('by');?> <a href="members/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a>
                    <?php
                        echo $words->getFormatted('in') . ' <a href="group/' . $thread->IdGroup . '/" title="' . $words->getSilent('Group') . ': ' . $thread->GroupName . '">' . $thread->GroupName . '</a></span>';
                    ?>
                        <?php echo '<span class="small grey pull-right" title="' . date($words->getSilent('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time, $this->getSession())) . '"><a href="' . $last_url . '" class="grey">' . $layoutbits->ago($thread->last_create_time) . '<i class="fa fa-caret-right ml-1" title="' . $words->getBuffered('to_last') . '"></i></a></span>'; ?>
                        <?php echo $words->flushBuffer(); ?>
                        </div>
                    </td>
                </tr>
        <?php
        }
        ?>
        </tbody>
        </table>

        <?php $this->pager->render(); ?>
    </div>
</div>
