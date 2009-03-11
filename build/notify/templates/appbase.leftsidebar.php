<h3>Last visited groups</h3>
        <ul>
        <?php
        $last_visited = $this->getModel()->getLastVisited();
        foreach ($last_visited as $group) {
            if ($group) {
                ?>
                <li><a href="groups/<?=$group->getData()->id ?>"><?=$group->getData()->Name ?></a></li>
                <?php
            }
        }
        ?>
        </ul>
        <h3>My groups</h3>
            <ul>
        <?php
        $my_groups = $this->getModel()->getMyGroups();
        foreach ($my_groups as $group_data) {
            if ($group_data) {
                ?>
                <li><a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a></li>
                <?php
            }
        }
        ?>
            </ul>
