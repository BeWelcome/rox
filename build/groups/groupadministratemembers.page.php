<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.
*/

    /**
     * @author Fake51
     */

    /**
     * This page allows to create a new group
     *
     * @package Apps
     * @package Groups
     */
class GroupMemberAdministrationPage extends GroupsBasePage
{
    private function javascript_escape($str) {
        $new_str = '';

        $str_len = strlen($str);
        for($i = 0; $i < $str_len; $i++) {
            $new_str .= '\\x' . dechex(ord(substr($str, $i, 1)));
        }

        return $new_str;
    }

    protected function teaserContent()
    {
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        // &gt; or &raquo; ?
        ?>
        <div>
            <h1><a href="groups"><?= $words->get('Groups');?></a> &raquo; <a href=""><?= $words->get('GroupsAdministrateMembers');?></a></h1>
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
        $need_approval = $this->group->getMembers('WantToBeIn');
        $invited = $this->group->getMembers('Invited');

?>
    <div class="col-12">
        <h3><?= $words->get('GroupsAdministrateMembers'); ?></h3>
    </div>
        <div class="col-12">
            <?php $this->pager_widget->render(); ?>
        </div>

        <div class="col-12">
            <h4><?= $words->get('GroupsCurrentMembers');?></h4>
        </div>
    </div>

        <div class="row">
            <div class="col-8 col-md-3">
                <?= $words->get('Username');?>
            </div>
            <div class="col-4 col-md-3">
                <?= $words->get('Action');?>
            </div>
            <div class="col-8 col-md-3 d-none d-md-block">
                <?= $words->get('Username');?>
            </div>
            <div class="col-4 col-md-3 d-none d-md-block">
                <?= $words->get('Action');?>
            </div>

            <?php
            $purifier = MOD_htmlpure::getBasicHtmlPurifier();
            $count = 0;
            foreach ($this->pager_widget->getActiveSubset($this->group->getMembers('In', $this->pager_widget->getActiveStart(), $this->pager_widget->getActiveLength())) as $member)
            {
                ?>
            <div class="col-8 col-md-3">
                <?=MOD_layoutbits::linkWithPicture($member->Username) ?>
                <a href="members/<?= $member->Username; ?>" class="username"><?=$member->Username ?></a>
            </div>

            <div class="col-4 col-md-3 pt-2">
                <?php
                $groupid = $this->group->getPKValue();
                $memberid = $member->getPKValue();
                $BWAdmin = $this->isBWAdmin;
                if ($this->member->getPKValue() == $memberid  && !$BWAdmin) {
                    echo "<a class='resignAdmin' href='groups/{$groupid}/resignAdmin'>{$words->getSilent('resignAsAdmin')}</a>";
                } elseif ($this->member->getPKValue() == $memberid  && $BWAdmin) {
                    echo "SuperAdminPower!";
                }
                else {
                    if ($this->group->isGroupOwner($member) && !$BWAdmin) {
                        echo "<i class='fa fa-user-cog mr-1 mt-3' title='{$words->getSilent('MemberIsAdmin')}'></i>";
                    } elseif ($this->group->isGroupOwner($member) && $BWAdmin) {
                        echo "<i class='fa fa-user-cog' title='{$words->getSilent('MemberIsAdmin')}'></i>";
                        echo "<a class='kick btn btn-sm btn-warning' href='groups/{$groupid}/kickmember/{$memberid}'><i class='fa fa-user-times' title='{$words->getSilent('GroupsKickMember')}'></i></a>";
                        echo "<a class='ban btn btn-sm btn-danger' href='groups/{$groupid}/banmember/{$memberid}'><i class='fa fa-user-slash' title='{$words->getSilent('GroupsBanMember')}'></i></a>";
                    } else {
                        echo "<a class='addAdmin btn btn-sm btn-info' href='groups/{$groupid}/addAdmin/{$memberid}'><i class='fa fa-user-cog' title='{$words->getSilent('GroupsAddAdmin')}'></i></a>";
                        echo "<a class='kick btn btn-sm btn-warning' href='groups/{$groupid}/kickmember/{$memberid}'><i class='fa fa-user-times' title='{$words->getSilent('GroupsKickMember')}'></i></a>";
                        echo "<a class='ban btn btn-sm btn-danger' href='groups/{$groupid}/banmember/{$memberid}'><i class='fa fa-user-slash' title='{$words->getSilent('GroupsBanMember')}'></i></a>";
                    }
                } ?>
            </div>
                <?php
                $count++;
            }
            ?>
            <div class="col-12">
                <?
                $this->pager_widget->render();
                ?>
            </div>

            <script type='text/javascript'>
                var memberban = $('current_members').getElementsBySelector('a.ban');
                var memberkick = $('current_members').getElementsBySelector('a.kick');
                var memberasadmin = $('current_members').getElementsBySelector('a.addAdmin');
                var resignasadmin = $('current_members').getElementsBySelector('a.resignAdmin');
                memberban.each(function(elem){
                    elem.observe('click', function(e){
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberBan'));?>'))
                        {
                            Event.stop(e);
                        }
                    })
                });
                memberkick.each(function(elem){
                    elem.observe('click', function(e){
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberKick'));?>'))
                        {
                            Event.stop(e);
                        }
                    })
                });
                memberasadmin.each(function(elem){
                    elem.observe('click', function(e){
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberAsAdmin'));?>'))
                        {
                            Event.stop(e);
                        }
                    })
                });
                resignasadmin.each(function(elem){
                    elem.observe('click', function(e){
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmResignAsAdmin'));?>'))
                        {
                            Event.stop(e);
                        }
                    })
                });
            </script>
            <?=$words->flushBuffer()?>


<?php if ($this->group->Type != 'Public') :?>
            <div class="col-12">
                <h4><?= $words->get('GroupsProspectiveMembers');?></h4>
            </div>

            <div class="col-8 col-md-3">
                <?= $words->get('Username');?>
            </div>
            <div class="col-4 col-md-3">
                <?= $words->get('Action');?>
            </div>
            <div class="col-8 col-md-3 d-none d-md-block">
                <?= $words->get('Username');?>
            </div>
            <div class="col-4 col-md-3 d-none d-md-block">
                <?= $words->get('Action');?>
            </div>

                <?php foreach ($need_approval as $member) : ?>
            <div class="col-8 col-md-3">
                <?=MOD_layoutbits::linkWithPicture($member->Username) ?>
                <a href="members/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
            </div>
            <div class="col-4 col-md-3">
                       <?= (($this->member->getPKValue() == $member->getPKValue()) ? '' :
                                 "<a class='accept btn btn-sm btn-success' href='groups/{$this->group->getPKValue()}/acceptmember/{$member->getPKValue()}'><i class='fa fa-user-check' title='".$words->get('GroupsAcceptMember')."'></i></a><br>
                                  <a class='kick btn btn-sm btn-warning' href='groups/{$this->group->getPKValue()}/declinemember/{$member->getPKValue()}'><i class='fa fa-user-times' title='".$words->get('GroupsDeclineMember')."'></i></a><br>
                                  <a class='ban btn btn-sm btn-danger' href='groups/{$this->group->getPKValue()}/banmember/{$member->getPKValue()}'><i class='fa fa-user-slash' title='".$words->get('GroupsBanMember')."'></i></a>");?>
            </div>
                <?php endforeach; ?>

<?php endif ;?>
            <div class="col-12">
                <h4><?= $words->get('GroupsInvitedMembers');?></h4>
            </div>

            <div class="col-12">
                <?= $words->get('Username');?>
            </div>

<?php if ($invited) : ?>
    <?php foreach ($invited as $member) : ?>
                    <div class="col-12 col-md-6">
                        <?=MOD_layoutbits::linkWithPicture($member->Username) ?></td>
                        <a href="members/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                    </div>
    <?php endforeach; ?>
<?php endif; ?>
            <div class="col-12">
                <h4><?= $words->get('GroupsInviteMember') ?></h4>
            </div>

            <div id="search_result" class="col-12" style="display: none;"></div>

            <div class="col-12">
                <form method='get' action='groups/<?= $this->group->getPKValue(); ?>/invitemembers/search' id='invite_form'>
                    <input type='text' value='<?= $words->getSilent('GroupsEnterUsername');?>' name='username' id='search_username'/><input type='submit' value='<?= $words->getSilent('Search');?>' id='search_username_submit'/>
                </form>
                <?=$words->flushBuffer()?>
            </div>

            <script type='text/javascript'>
                var possiblemembers = $('possible_members');
                if (possiblemembers) {
                    var newmemberban = $('possible_members').getElementsBySelector('a.ban');
                    var newmemberkick = $('possible_members').getElementsBySelector('a.kick');
                    newmemberban.each(function (elem) {
                        elem.observe('click', function (e) {
                            if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberBan'));?>')) {
                                Event.stop(e);
                            }
                        })
                    });
                    newmemberkick.each(function (elem) {
                        elem.observe('click', function (e) {
                            if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberDecline'));?>')) {
                                Event.stop(e);
                            }
                        })
                    });
                }

                var search_handler = {
                    display_result: function(member_object){
                        var search_div = $('search_result');
                        search_div.innerHTML = '';
                        search_div.style.border = '1px solid black';
                        search_div.style.display = 'block';
                        search_div.style.backgroundColor = '#ffffff';
                        var counter = 0;
                        for (var m in member_object)
                        {
                            var a = document.createElement('a');
                            a.href = '';
                            a.id = 'invite_member_' + member_object[m];
                            a.title = '<?= $this->javascript_escape($words->get('GroupsClickToSendInvite'));?>' + m;
                            a.appendChild(document.createTextNode('<?= $this->javascript_escape($words->getSilent('GroupsInvite'));?>' + m));
                            $(a).observe('click',function(e){
                                e = e || window.event;
                                search_handler.add_invite(e);
                                Event.stop(e);
                            });
                            search_div.appendChild(a);
                            search_div.appendChild(document.createElement('br'));
                            counter++;
                        }
                        if (counter == 0)
                        {
                            search_div.appendChild(document.createTextNode('<?= $this->javascript_escape($words->getSilent('GroupsCouldNotFindMembers'));?>'));
                        }
                    },
                    add_invite: function(e){
                        var it = e.target || e.srcElement;
                        var id = it.id.substr(14);
                        var ajax = new Ajax.Request('groups/<?= $this->group->getPKValue(); ?>/invitememberajax/' + id, {
                            method: 'get',
                            onSuccess: function(transport){
                                if (transport.responseText == 'success')
                                {
                                    search_handler.add_invite_callback(it);
                                }
                                else
                                {
                                    alert('<?= $this->javascript_escape($words->getSilent('GroupsCouldNotInvite'));?>');
                                }
                            },
                            onFailure: function(transport){
                                alert('<?= $this->javascript_escape($words->getSilent('GroupsInviteFailedTechError'));?>');
                            }
                        });
                    },
                    add_invite_callback: function(it){
                        var invited = it.firstChild.data.substr(7);
                        var tr = document.createElement('tr');
                        var td = document.createElement('td');
                        td.appendChild(document.createTextNode(invited + '<?= $this->javascript_escape($words->getSilent('GroupsHasBeenInvited'));?>'));
                        td.setAttribute('colspan', 2);
                        tr.appendChild(td);
                        $('invited_members').tBodies[0].appendChild(tr);
                        $(it).remove();
                    }

                };
                $('search_username').observe('focus', function(e){
                    if ($('search_username').value == '<?= $this->javascript_escape($words->getSilent('GroupsEnterUsername'));?>')
                    {
                        $('search_username').value = '';
                    }
                });
                $('invite_form').observe('submit', function(e){
                    e = e || window.event;
                    var ajax = new Ajax.Request('groups/<?= $this->group->getPKValue(); ?>/membersearchajax/' + $('search_username').value, {
                        method: 'get',
                        onSuccess: function(transport){
                            var result = ((transport.responseText != '[]') ? transport.responseText.evalJSON() : {});
                            search_handler.display_result(result);
                        },
                        onFailure: function(transport){
                            alert('<?= $this->javascript_escape($words->getSilent('GroupsInviteFailedTechError'));?>');
                        }
                    });
                    Event.stop(e)
                });
            </script>
            <?=$words->flushBuffer()?>

    <?php
    }
}
?>
