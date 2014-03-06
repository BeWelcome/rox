<?php

/**
 * This page allows to create a new group
 *
 */
class NotifyAdminSettingsPage extends NotifyBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?>
        <div id="teaser" class="page-teaser clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups">Groups</a> &raquo; <a href="">Admininstrate groupmembers</a></h1>
        </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'admin';
    }

    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();

        $members = $this->group->getMembers();
        $newmembers = $this->group->getMembers('WantToBeIn');

?>
    <div id="groups">
    <div class="subcolumns">
        <h3><?= $words->get('GroupsAdministrateMembers'); ?></h3>
        <div class="c50l">
            <div class="subcl">
        <h4>Current Members</h4>
        <table id='current_members'>
            <tr>
              <th colspan="2">Username</th>
              <th>Action</th>
            </tr>
        <?php foreach ($members as $member) : ?>
            <tr>
                <td><?=MOD_layoutbits::linkWithPicture($member->Username) ?></td>
                <td><a href="#" class="username"><?=$member->Username ?></a></td>
                <td><?= (($this->member->getPKValue() == $member->getPKValue()) ? '' : "<a class='ban' href='groups/{$this->group->getPKValue()}/banmember/{$member->getPKValue()}'>Ban?</a> / <a class='kick' href='groups/{$this->group->getPKValue()}/kickmember/{$member->getPKValue()}'>Kick?</a>");?></td>
            </tr>
        <?php endforeach; ?>
        </table>
        <script type='text/javascript'>
        var memberlinks = $('current_members').getElementsByTagName('a');
        for (var i=0; i<memberlinks.length; i++)
        {
            $(memberlinks[i]).observe('click', function(e){
                if (!confirm('Are you sure you want to ban this member?'))
                {
                    Event.stop(e);
                }
            });
        }
        </script>
            </div> <!-- subcl -->
        </div> <!-- c62l -->

<?php if ($this->group->Type != 'Public') :?>
        <div class="c50r">
            <div class="subcl">
        <h4>Prospective Members</h4>
        <table id='possible_members'>
            <tr>
              <th colspan="2">Username</th>
              <th>Action</th>
            </tr>
        <?php foreach ($newmembers as $member) : ?>
            <tr>
                <td><?=MOD_layoutbits::linkWithPicture($member->Username) ?></td>
                <td><a href="#" class="username"><?=$member->Username ?></a></td>
                <td><?= (($this->member->getPKValue() == $member->getPKValue()) ? '' : "<a class='accept' href='groups/{$this->group->getPKValue()}/acceptmember/{$member->getPKValue()}'>Accept?</a>");?></td>
            </tr>
        <?php endforeach; ?>
        </table>

            </div> <!-- subcl -->
        </div> <!-- c50r -->
<?php endif ;?>
    </div> <!-- subcolums -->

    </div>
    <?php
    }


}

?>
