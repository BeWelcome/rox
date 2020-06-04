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
class GroupMemberAdministrationPage extends GroupsSubPage
{
    private function javascript_escape($str)
    {
        $new_str = '';

        $str_len = strlen($str);
        for ($i = 0; $i < $str_len; $i++) {
            $new_str .= '\\x' . dechex(ord(substr($str, $i, 1)));
        }

        return $new_str;
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
    <div class="row">
        <div class="col-12">
            <?php $this->pager_widget->render(); ?>
        </div>

        <div class="col-12">
            <h4><?= $words->get('GroupsCurrentMembers'); ?></h4>
        </div>
        <div id="current_members" class="col-12">
            <div class="row align-items-center">
            <div class="col-8 col-md-3">
                <?= $words->get('Username'); ?>
            </div>
            <div class="col-4 col-md-3">
                <?= $words->get('Action'); ?>
            </div>
            <div class="col-8 col-md-3 d-none d-md-block">
                <?= $words->get('Username'); ?>
            </div>
            <div class="col-4 col-md-3 d-none d-md-block">
                <?= $words->get('Action'); ?>
            </div>

            <?php
            $purifier = MOD_htmlpure::getBasicHtmlPurifier();
            $count = 0;
            foreach ($this->pager_widget->getActiveSubset($this->group->getMembers('In', $this->pager_widget->getActiveStart(), $this->pager_widget->getActiveLength())) as $member) {
                ?>
                <div class="col-8 col-md-3 pt-2">
                    <?= MOD_layoutbits::linkWithPicture($member->Username) ?>
                    <a href="members/<?= $member->Username; ?>" class="username"><?= $member->Username ?></a>
                </div>

                <div class="col-4 col-md-3 pt-2">
                    <?php
                    $groupid = $this->group->getPKValue();
                    $memberid = $member->getPKValue();
                    $BWAdmin = $this->isBWAdmin;
                    if ($this->member->getPKValue() == $memberid && !$BWAdmin) {
                        echo "<a class='resignAdmin' href='group/{$groupid}/resignAdmin'>{$words->getSilent('resignAsAdmin')}</a>";
                    } elseif ($this->member->getPKValue() == $memberid && $BWAdmin) {
                        echo "SuperAdminPower!";
                    } else {
                        $isGroupOwner = $this->group->isGroupOwner($member);
                        if ( $BWAdmin) {
                            echo "<i class='fa fa-user-cog mr-1 mt-3' title='{$words->getSilent('MemberIsAdmin')}'></i>";
                        } elseif ($isGroupOwner) {
                            echo "<div class='btn-group'><span class='btn btn-sm'><i class='fa fa-user-cog' title='{$words->getSilent('MemberIsAdmin')}'></i></span>";
                            echo "<a class='kick btn btn-sm btn-warning' href='group/{$groupid}/kickmember/{$memberid}'><i class='fa fa-user-times' title='{$words->getSilent('GroupsKickMember')}'></i></a>";
                            echo "<a class='ban btn btn-sm btn-danger' href='group/{$groupid}/banmember/{$memberid}'><i class='fa fa-user-slash' title='{$words->getSilent('GroupsBanMember')}'></i></a></div>";
                        } else {
                            echo "<div class='btn-group'><a class='addAdmin btn btn-sm btn-info' href='group/{$groupid}/addAdmin/{$memberid}'><i class='fa fa-user-cog' title='{$words->getSilent('GroupsAddAdmin')}'></i></a>";
                            echo "<a class='kick btn btn-sm btn-warning' href='group/{$groupid}/kickmember/{$memberid}'><i class='fa fa-user-times' title='{$words->getSilent('GroupsKickMember')}'></i></a>";
                            echo "<a class='ban btn btn-sm btn-danger' href='group/{$groupid}/banmember/{$memberid}'><i class='fa fa-user-slash' title='{$words->getSilent('GroupsBanMember')}'></i></a></div>";
                        }
                    } ?>
                </div>
                <?php
                $count++;
            }
            ?>
            </div>
            <div class="col-12">
                <?php
                $this->pager_widget->render();
                ?>
            </div>

            <script type='text/javascript'>
                $(function () {
                    $('#current_members a.ban').click(function (e) {
                        // code goes here
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberBan'));?>')) {
                            e.preventDefault();
                        }
                    });
                    $('#current_members a.kick').click(function (e) {
                        // code goes here
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberKick'));?>')) {
                            e.preventDefault();
                        }
                    });
                    $('#current_members a.addAdmin').click(function (e) {
                        // code goes here
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberAsAdmin'));?>')) {
                            e.preventDefault();
                        }
                    });
                    $('#current_members a.resignAdmin').click(function (e) {
                        // code goes here
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmResignAsAdmin'));?>')) {
                            Event.stop(e);
                        }
                    });
                });
            </script>
            <?= $words->flushBuffer() ?>
        </div>
        <?php if ($this->group->Type != 'Public') : ?>
        <?php if (!empty($need_approval)) : ?>
        <div id="possible_members" class="col-12">
        <div class="row align-items-center">
            <div class="col-12">
                <h4><?= $words->get('GroupsProspectiveMembers'); ?></h4>
            </div>

            <div class="col-8 col-md-3 pt-2">
                <?= $words->get('Username'); ?>
            </div>
            <div class="col-4 col-md-3">
                <?= $words->get('Action'); ?>
            </div>
            <div class="col-8 col-md-3 d-none d-md-block pt-2">
                <?= $words->get('Username'); ?>
            </div>
            <div class="col-4 col-md-3 d-none d-md-block">
                <?= $words->get('Action'); ?>
            </div>

            <?php foreach ($need_approval as $member) : ?>
                <div class="col-8 col-md-3 pt-2">
                    <?= MOD_layoutbits::linkWithPicture($member->Username) ?>
                    <a href="members/<?= $member->Username ?>" class="username"><?= $member->Username ?></a>
                </div>
                <div class="col-4 col-md-3 pt-2">
                    <?= (($this->member->getPKValue() == $member->getPKValue()) ? '' :
                        "<div class='btn-group'><a class='accept btn btn-sm btn-success' href='group/{$this->group->getPKValue()}/acceptmember/{$member->getPKValue()}'><i class='fa fa-user-check' title='" . $words->get('GroupsAcceptMember') . "'></i></a>
                                  <a class='kick btn btn-sm btn-warning' href='group/{$this->group->getPKValue()}/declinemember/{$member->getPKValue()}'><i class='fa fa-user-times' title='" . $words->get('GroupsDeclineMember') . "'></i></a>
                                  <a class='ban btn btn-sm btn-danger' href='group/{$this->group->getPKValue()}/banmember/{$member->getPKValue()}'><i class='fa fa-user-slash' title='" . $words->get('GroupsBanMember') . "'></i></a></div>"); ?>
                </div>
            <?php endforeach;
            ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
        <div class="col-12">
            <div class="row">
                <div class="col-12">
                    <h4><?= $words->get('GroupsInviteMembers') ?></h4>
                </div>

                <div class="col-12"><div class="container"><div id="search_result" class="row d-none"></div></div></div>

                <div class="col-12">
                    <form method='get' class="form-inline" autocomplete="off" action='group/<?= $this->group->getPKValue(); ?>/invitemembers/search'
                          id='invite_form'>
                        <input type='text' placeholder='<?= $words->getSilent('GroupsEnterUsername'); ?>'
                               name='username' id='search_username' class="form-control mr-2"/>
                        <input type='submit' value='<?= $words->getSilent('Search'); ?>'
                               class="btn btn-primary" id='search_username_submit'/>
                    </form>
                    <?= $words->flushBuffer() ?>
                </div>

                <?= $words->flushBuffer() ?>
            </div>
        </div>
        <div id="invited_members" class="col-12">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4><?= $words->get('GroupsInvitedMembers'); ?></h4>
                </div>

                <div class="col-8 col-md-3">
                    <?= $words->get('Username'); ?>
                </div>
                <div class="col-4 col-md-3">
                    <?= $words->get('Action'); ?>
                </div>
                <div class="col-8 col-md-3 d-none d-md-block">
                    <?= $words->get('Username'); ?>
                </div>
                <div class="col-4 col-md-3 d-none d-md-block">
                    <?= $words->get('Action'); ?>
                </div>

                <?php if ($invited) : ?>
                    <?php foreach ($invited as $member) : ?>
                        <div class="col-8 col-md-3">
                            <?= MOD_layoutbits::linkWithPicture($member->Username) ?>
                            <a href="members/<?= $member->Username ?>" class="username"><?= $member->Username ?></a>
                        </div>
                        <div class="col-4 col-md-3">
                            <div class='btn-group'><a class='withdraw btn btn-sm btn-warning' href='group/<?= $groupid ?>/withdraw/<?= $member->id ?>'><i class='fa fa-user-slash' title='<?= $words->getSilent('GroupsWithdrawInvitation')?>'></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <script type='text/javascript'>
            $(function () {
                var possiblemembers = $('#possible_members');
                if (possiblemembers.length) {
                    $('#possible_members a.ban').click(function (e) {
                        // code goes here
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberBan'));?>')) {
                            Event.stop(e);
                        }
                    });
                    $('#possible_members a.kick').click(function (e) {
                        // code goes here
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmMemberKick'));?>')) {
                            Event.stop(e);
                        }
                    });
                }
                var invitedmembers = $('#invited_members');
                if (invitedmembers.length) {
                    $('#invited_members a.withdraw').click(function (e) {
                        // code goes here
                        if (!confirm('<?= $this->javascript_escape($words->getSilent('GroupsConfirmWithdrawInvite'));?>')) {
                            Event.stop(e);
                        }
                    });
                }
            });
            var search_handler = {
                display_result: function(member_object){
                    var search_div = $('#search_result');
                    search_div.empty();
                    search_div.css('border', '1px solid black');
                    var counter = 0;
                    for (var m in member_object)
                    {
                        search_div.append("<div class='col-12 col-sm-6 col-md-4 col-lg-3'>" +
                            "<a href='' class='btn btn-sm btn-primary' data-username='" + m + "' " +
                            "id='invite_member_"+ member_object[m] + "' " +
                            "title='<?= $this->javascript_escape($words->get('GroupsClickToSendInvite'));?>" + m +
                            "'><?= $this->javascript_escape($words->getSilent('GroupsInvite'));?>" + m + "</a></div>");
                        counter++;
                    }
                    if (counter === 0)
                    {
                        search_div.append(
                            "<p><?= $this->javascript_escape($words->getSilent('GroupsCouldNotFindMembers'));?></p>"
                        );
                    }
                    $('a[id^="invite_member_"]').click( search_handler.add_invite );
                    search_div.toggleClass('d-none');
                },
                add_invite: function(e){
                    e.preventDefault();
                    var it = e.target;
                    var id = it.id.substr(14);
                    $.ajax({
                        url: '/group/<?= $this->group->getPKValue(); ?>/invite/' + id,
                        dataType: 'json',
                        type: 'post',
                        success: function(data){
                            if (data.success === true)
                            {
                                search_handler.add_invite_callback(it);
                            }
                            else
                            {
                                alert('<?= $this->javascript_escape($words->getSilent('GroupsCouldNotInvite'));?>');
                            }
                        },
                        error: function(transport){
                            alert('<?= $this->javascript_escape($words->getSilent('GroupsInviteFailedTechError'));?>');
                        }
                    });
                },
                add_invite_callback: function(it){
                    alert('<?= $this->javascript_escape($words->getSilent('GroupsInvitedMember'));?>' +
                        $(it).data("username"));
                    $(it).parent().remove();
                }
            };
            $('#invite_form').submit( function(e){
                e = e || window.event;
                $.ajax({
                    type: 'get',
                    dataType: 'json',
                    url: '/group/<?= $this->group->getPKValue(); ?>/membersearchajax/' + $('#search_username').val(),
                    success: function (data) {
                        search_handler.display_result(data);
                    },
                    error: function(data){
                        alert('<?= $this->javascript_escape($words->getSilent('GroupsInviteFailedTechError'));?>');
                    }
                });
                e.preventDefault();
            });
        </script>
    </div>
<?php
    }
}

