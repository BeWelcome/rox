<?php ?>

<div>
        <h3>Search Groups</h3>
        <form>
        <input><input type="submit" value="Find"><br />
        </form>
        <?php
      /*   one button is enough
            if (APP_user::isBWLoggedIn('NeedMore,Pending')) {
                ?>
                <h3>Create new groups</h3>
                <div><span class="button"><a href="groups/new">New group</a></span></div>
                <?php
            }
      */
        if (!empty($my_groups)) {
            ?><h3>My Groups</h3><?php
            foreach($this->getModel()->getMyGroups() as $group_data) {
                ?><div>
                <a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a>
                </div><?php
            }
        }
        ?>
        </div>

        <?php if (APP_user::isBWLoggedIn('NeedMore,Pending')) { ?>
        <div style="float:right"><span class="button"><a href="groups/new">New group</a></span></div>
        <?php } ?>
        <h3>Group List</h3>
        <?php
        foreach($this->getModel()->getGroups() as $group_data) {
            ?><div>
            <a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a>
            </div><?php
        }
        ?>
        </div>