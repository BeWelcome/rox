<input type="hidden" id="keyword" name="keyword" value="<?php echo htmlspecialchars($this->search_terms) ?>">
<div class="row no-gutters">
        <div class="col-12">
            <h3><?= $words->get('GroupsSearchDiscussionsGroup', htmlspecialchars($this->search_terms, ENT_QUOTES)); ?></h3>
        </div>
        <?php
        if (!empty($this->search_errors)) {
            echo '<div class="col-12"><div class="alert alert-info">' . $words->get('GroupsSearchDiscussionsNoResults') . '</div></div>';
        } else {
            $purifierModule = new MOD_htmlpure();
            $purifier = $purifierModule->getBasicHtmlPurifier();

        $this->pager->render(); ?>
        <?php
            $words = new MOD_words();
            $layoutbits = new MOD_layoutbits();
            $i = 0;
            foreach ($this->search_result as $post) {
                if ($i % 2 === 0) {
                    $style="background-color: rgba(0, 0, 0, 0.05)";
                } else {
                    $style="";
                }

                $url = "/group/" . $this->group->id . "/forum/s" . $post->IdThread . "/#post" . $post->id;

                    ?>
        <div class="col-12 m-1 p-1" style="<?= $style ?>">
                                <?php echo $words->get('Thread'); ?> <a href="<?php echo "/group/" . $this->group->id . "/forum/s" . $post->IdThread; ?>"><?= $post->title ?></a><br>
                                    <?php
                                    echo $purifier->purify(MOD_layoutbits::truncate_words($post->message, 70));
                                    ?>
                                <div class="w-100">
                            <span><?php echo $words->getSilent('by');?> <a href="members/<?php echo $post->Username; ?>"><?php echo $post->Username; ?></a>
                                <?php echo '<span class="pull-right" title="' . date($words->getSilent('DateHHMMShortFormat'), ServerToLocalDateTime($post->created, $this->getSession())) . '"><a href="' . $url . '" class="grey">' . $layoutbits->ago($post->created) . '<i class="fa fa-caret-right ml-1" title="' . $words->getBuffered('to_last') . '"></i></a></span>'; ?>
                                </div>
        </div>
            <?php
                $i++;
            }
        }
        ?>

        <?php $this->pager->render(); ?>
</div>
