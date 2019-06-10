<?php
    if (strlen($group_data->Picture) > 0) {
    $img_link = "group/thumbimg/{$group_data->getPKValue()}";
    } else {
    $img_link = "images/icons/group.png";
    }
    ?>
<div class="col-12 col-md-6 col-lg-4 order-3 pt-2">
    <div class="media">
        <a href="group/<?= $group_data->getPKValue() ?>">
            <img class="groupimg framed mr-1" alt="<?= htmlspecialchars($group_data->Name, ENT_QUOTES) ?>"
                 src="<?= $img_link; ?>">
        </a>
        <div class="media-body">
            <h5><a href="group/<?= $group_data->getPKValue() ?>"><?= htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a></h5>
                <div>
                    <span><i class="fa fa-users mr-1"
                       title="<?= $words->getSilent('group.member.count'); ?>"></i><?= $group_data->getMemberCount(); ?></span>
                    <span class="ml-3"><i class="fa fa-user-plus mr-1" title="<? echo $words->get('GroupsNewMembers'); ?>"></i><?php echo count($group_data->getNewMembers()); ?></span>
                </div>
                <?php if ($group_data !== 0) { ?>
                    <?php
                        if ($group_data->latestPost) {
                            $interval = date_diff(date_create(date('d F Y')), date_create(date('d F Y', ServerToLocalDateTime($group_data->latestPost, $this->getSession()))));
                            ?>
                            <div><i class="far fa-comment mr-1" title="<? echo $words->get('GroupsLastPost'); ?>"></i><span class="text-nowrap"><?=date($words->getBuffered('d F Y'), ServerToLocalDateTime($group_data->latestPost, $this->getSession())); ?></span></div>
                            <?
                        } else {
                            echo $words->get('GroupsNoPostYet');
                        }
                        ?>
                <?php } ?>
        </div>
    </div>
</div>
