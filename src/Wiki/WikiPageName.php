<?php

namespace App\Wiki;

/**
 * Page names of the wiki: a title's spaces become underscores (Hosting tips → Hosting_tips); a group's page is
 * Group_ and the group's name without spaces (Group Hiking in the Alps → Group_HikingintheAlps).
 */
final class WikiPageName
{
    public const string FRONT_PAGE = 'WikiFrontPage';
    private const string GROUP_PREFIX = 'Group_';

    public static function fromTitle(string $title): string
    {
        $title = trim($title);
        if (str_starts_with($title, 'Group ')) {
            $title = self::GROUP_PREFIX . substr($title, 6);
        }
        if (str_starts_with($title, self::GROUP_PREFIX)) {
            return (string) preg_replace('~\s+~', '', $title);
        }

        return (string) preg_replace('~\s+~', '_', $title);
    }

    public static function forGroup(string $groupName): string
    {
        return self::fromTitle(self::GROUP_PREFIX . $groupName);
    }

    public static function isGroupPage(string $pageName): bool
    {
        return str_starts_with($pageName, self::GROUP_PREFIX);
    }

    /** the group's name without spaces, as the page name has it */
    public static function groupKey(string $pageName): string
    {
        return substr($pageName, \strlen(self::GROUP_PREFIX));
    }

    /** how a page name reads: Hosting_tips → Hosting tips */
    public static function toTitle(string $pageName): string
    {
        return str_replace('_', ' ', $pageName);
    }
}
