<?php

namespace App\Model\About;

/**
 * The teams of Get active in their order: the icon, the group of the filter (tech, comm, org) and the translation of
 * their tags (links split at |). Their title and text are getactive{team}title and getactive{team}text. The member to
 * contact is a TeamCoordinator; a team without one has no contact.
 */
final class VolunteerTeams
{
    public const string GROUP_TECH = 'tech';
    public const string GROUP_COMMUNITY = 'comm';
    public const string GROUP_ORGANISATION = 'org';

    /** @var array<string, array{icon: string, group: string, tags: string}> */
    public const array TEAMS = [
        'dev' => ['icon' => 'fas fa-code', 'group' => self::GROUP_TECH, 'tags' => 'helpbewelcomedevtags'],
        'testing' => ['icon' => 'fas fa-flask', 'group' => self::GROUP_TECH, 'tags' => 'helpbewelcometestingtags'],
        'sysadmin' => ['icon' => 'fas fa-server', 'group' => self::GROUP_TECH, 'tags' => 'helpsysadmintags'],
        'design' => ['icon' => 'fas fa-paintbrush', 'group' => self::GROUP_TECH, 'tags' => 'helpbewelcomedesigntags'],
        'support' => ['icon' => 'far fa-circle-question', 'group' => self::GROUP_ORGANISATION, 'tags' => 'helpbewelcomesupporttags'],
        'translate' => ['icon' => 'fas fa-language', 'group' => self::GROUP_COMMUNITY, 'tags' => 'helpbewelcometranslatetags'],
        'pr' => ['icon' => 'fas fa-bullhorn', 'group' => self::GROUP_COMMUNITY, 'tags' => 'helpbewelcomeprtags'],
        'nmbw' => ['icon' => 'far fa-handshake', 'group' => self::GROUP_COMMUNITY, 'tags' => 'helpbewelcomenmbwtags'],
        'mod' => ['icon' => 'fas fa-user-shield', 'group' => self::GROUP_COMMUNITY, 'tags' => 'helpbewelcomemodtags'],
        'local' => ['icon' => 'fas fa-location-dot', 'group' => self::GROUP_COMMUNITY, 'tags' => 'helpbewelcomelocaltags'],
        'donation' => ['icon' => 'fas fa-euro-sign', 'group' => self::GROUP_ORGANISATION, 'tags' => 'helpbewelcomedonationtags'],
        'bv' => ['icon' => 'fas fa-heart', 'group' => self::GROUP_ORGANISATION, 'tags' => 'helpbewelcomebvtags'],
    ];

    /** The groups of the filter, in its order */
    public const array GROUPS = [self::GROUP_TECH, self::GROUP_COMMUNITY, self::GROUP_ORGANISATION];
}
