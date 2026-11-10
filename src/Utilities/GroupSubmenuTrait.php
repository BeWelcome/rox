<?php

namespace App\Utilities;

use App\Entity\Group;
use App\Entity\Member;
use App\Security\GroupVoter;

/**
 * The menu of a group's pages: the tabs (overview, discussions, rules, wiki, members; the agora has no wiki and no
 * members tab; the forum moderators' reports last), the entries of the head's "…" menu (menu: true) and join, the
 * head's button for those not in it.
 */
trait GroupSubmenuTrait
{
    /**
     * @return array<string, array{key: string, url: string, icon: string, menu?: bool, danger?: bool, count?: string}>
     */
    private function getSubmenuItems(?Member $member, Group $group): array
    {
        $groupId = $group->getId();
        $agora = $group->isBeWelcome();
        $url = fn (string $route): string => $this->generateUrl($route, ['group_id' => $groupId]);

        $items = [
            'overview' => ['key' => 'GroupOverview', 'url' => $url('group_start'), 'icon' => 'house'],
            'forum' => ['key' => 'GroupDiscussions', 'url' => $url('group_forum'), 'icon' => 'comments', 'count' => 'threads'],
            'rules' => ['key' => 'group.rules', 'url' => $url('group_rules'), 'icon' => 'scale-balanced'],
        ];
        if (!$agora) {
            $items['wiki'] = ['key' => 'GroupWiki', 'url' => $this->generateUrl('group_wiki_page', ['id' => $groupId]), 'icon' => 'book'];
            $items['members'] = ['key' => 'GroupMembers', 'url' => $url('group_members'), 'icon' => 'user-group', 'count' => 'members'];
        }

        // the forum moderators' tab, the last one: the reports about posts of the group
        if ($this->isGranted(Member::ROLE_ADMIN_FORUMMODERATOR) || $this->isGranted(Member::ROLE_ADMIN_ADMIN)) {
            $items['reports'] = ['key' => 'forum.reports.tab', 'url' => $url('group_reports'), 'icon' => 'flag', 'count' => 'reports'];
        }

        $isMember = null !== $member && $group->isMember($member);
        if ($isMember) {
            $items['membersettings'] = ['key' => 'group.menu.settings', 'url' => $url('group_membersettings'), 'icon' => 'bell', 'menu' => true];
            if (!$agora) {
                $items['relatedgroupsettings'] = ['key' => 'GroupRelatedGroups', 'url' => $url('relatedgroup_log'), 'icon' => 'link', 'menu' => true];
            }
        } else {
            $items['join'] = ['key' => 'group.join', 'url' => $this->generateUrl('join_group', ['groupId' => $groupId]), 'icon' => 'user-plus'];
        }

        if ($this->isGranted(GroupVoter::GROUP_ADMINISTER, $group)) {
            $items['memberadministration'] = ['key' => 'GroupsAdministrateMembers', 'url' => $url('group_memberadministration'), 'icon' => 'users-gear', 'menu' => true];
            $items['groupsettings'] = ['key' => 'group.menu.edit', 'url' => $url('group_groupsettings'), 'icon' => 'pen', 'menu' => true];
        }
        if ($isMember) {
            $items['leave'] = ['key' => 'groupsleavethegroup', 'url' => $url('group_leave'), 'icon' => 'right-from-bracket', 'menu' => true, 'danger' => true];
        }
        if (!$agora && $this->isGranted(GroupVoter::GROUP_ADMINISTER, $group)) {
            $items['delete'] = ['key' => 'groupsdeletegroup', 'url' => $url('group_delete'), 'icon' => 'trash', 'menu' => true, 'danger' => true];
        }

        return $items;
    }
}
