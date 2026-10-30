<?php

namespace App\Utilities;

use App\Entity\Group;
use App\Entity\Member;
use App\Security\GroupVoter;

/**
 * The submenu of a group's pages (overview, forum, wiki, members, ...).
 */
trait GroupSubmenuTrait
{
    /**
     * @return array<string, array{key: string, url: string}>
     */
    private function getSubmenuItems(?Member $member, Group $group): array
    {
        $groupId = $group->getId();
        $submenuItems = [
            'overview' => [
                'key' => 'GroupOverview',
                'url' => $this->generateUrl('group_start', ['group_id' => $groupId]),
            ],
            'forum' => [
                'key' => 'GroupDiscussions',
                'url' => $this->generateUrl('group_forum', ['group_id' => $groupId]),
            ],
            'wiki' => [
                'key' => 'GroupWiki',
                'url' => $this->generateUrl('group_wiki_page', ['id' => $groupId]),
            ],
            'members' => [
                'key' => 'GroupMembers',
                'url' => $this->generateUrl('group_members', ['group_id' => $groupId]),
            ],
        ];
        if (null !== $member && $group->isMember($member)) {
            $submenuItems['membersettings'] = [
                'key' => 'GroupMembersettings',
                'url' => $this->generateUrl('group_membersettings', ['group_id' => $groupId]),
            ];
            $submenuItems['relatedgroupsettings'] = [
                'key' => 'GroupRelatedGroups',
                'url' => $this->generateUrl('relatedgroup_log', ['group_id' => $groupId]),
            ];
        } else {
            $submenuItems['join'] = [
                'key' => 'group.join',
                'url' => $this->generateUrl('join_group', ['groupId' => $groupId]),
            ];
        }

        if ($this->isGranted(GroupVoter::GROUP_ADMINISTER, $group)) {
            $submenuItems['memberadministration'] = [
                'key' => 'GroupsAdministrateMembers',
                'url' => $this->generateUrl('group_memberadministration', ['group_id' => $groupId]),
            ];
            $submenuItems['groupsettings'] = [
                'key' => 'GroupsAdministrateGroup',
                'url' => $this->generateUrl('group_groupsettings', ['group_id' => $groupId]),
            ];
        }

        return $submenuItems;
    }
}
