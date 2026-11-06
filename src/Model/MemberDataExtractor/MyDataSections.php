<?php

namespace App\Model\MemberDataExtractor;

/**
 * The sections of the personal data export, as the sidebar of every page and the overview list them: by group, each
 * with its page (name.html), its texts (mydata.<key>.headline and .abstract) and its icon. The extractors report how
 * many items each section has (ExtractorInterface::extract).
 */
final class MyDataSections
{
    /**
     * @var array<string, array<string, array{key: string, icon: string}>> sections by group (mydata.group.<name>)
     */
    private const array GROUPS = [
        'you' => [
            'profile' => ['key' => 'profile', 'icon' => 'fa-user'],
            'gallery' => ['key' => 'gallery', 'icon' => 'fa-images'],
            'trips' => ['key' => 'trips', 'icon' => 'fa-route'],
            'relations' => ['key' => 'friends', 'icon' => 'fa-user-group'],
            'comments' => ['key' => 'comments', 'icon' => 'fa-comment-dots'],
        ],
        'conversations' => [
            'messages' => ['key' => 'messages', 'icon' => 'fa-envelope'],
            'requests' => ['key' => 'requests', 'icon' => 'fa-bed'],
            'invitations' => ['key' => 'invitations', 'icon' => 'fa-handshake'],
        ],
        'community' => [
            'groups' => ['key' => 'groups', 'icon' => 'fa-users'],
            'posts' => ['key' => 'posts', 'icon' => 'fa-comments'],
            'subscriptions' => ['key' => 'subscriptions.threads', 'icon' => 'fa-bell'],
            'activities' => ['key' => 'activities', 'icon' => 'fa-calendar-days'],
            'polls' => ['key' => 'polls', 'icon' => 'fa-square-poll-vertical'],
            'communitynews' => ['key' => 'communitynews', 'icon' => 'fa-newspaper'],
            'communitynews_comments' => ['key' => 'communitynewscomments', 'icon' => 'fa-comment'],
            'newsletters' => ['key' => 'newsletters', 'icon' => 'fa-envelope-open-text'],
            'broadcasts' => ['key' => 'broadcasts', 'icon' => 'fa-inbox'],
        ],
        'account' => [
            'donations' => ['key' => 'donations', 'icon' => 'fa-hand-holding-heart'],
            'rights' => ['key' => 'rights', 'icon' => 'fa-shield-halved'],
            'privileges' => ['key' => 'privileges', 'icon' => 'fa-key'],
            'translations' => ['key' => 'translations', 'icon' => 'fa-language'],
            'logs' => ['key' => 'logs', 'icon' => 'fa-list'],
            'browser_push_subscriptions' => ['key' => 'browser.push.subscriptions', 'icon' => 'fa-bell'],
            'browser_push_notifications' => ['key' => 'browser.push.notifications', 'icon' => 'fa-message'],
            'browser_push_notification_deliveries' => ['key' => 'browser.push.deliveries', 'icon' => 'fa-paper-plane'],
        ],
    ];

    /**
     * The groups with their sections and how many items each has; sections the extractors didn't report count 0.
     *
     * @param array<string, int> $counts by section
     *
     * @return array<string, list<array{name: string, key: string, icon: string, count: int}>>
     */
    public static function withCounts(array $counts): array
    {
        $groups = [];
        foreach (self::GROUPS as $group => $sections) {
            foreach ($sections as $name => $section) {
                $groups[$group][] = $section + ['name' => $name, 'count' => $counts[$name] ?? 0];
            }
        }

        return $groups;
    }

    /**
     * @return list<string> every section, in the sidebar's order
     */
    public static function names(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::GROUPS)));
    }

    public static function find(string $name): ?array
    {
        foreach (self::GROUPS as $group => $sections) {
            if (isset($sections[$name])) {
                return $sections[$name] + ['name' => $name, 'group' => $group];
            }
        }

        return null;
    }
}
