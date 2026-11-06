<?php

namespace App\Utilities;

use App\Entity\Group;

/**
 * The tabs of the group admin team's pages (approval, archival, unarchival, rename, categories, logs). Needs the
 * controller's entityManager for the number of groups waiting for approval.
 */
trait GroupAdminSubmenuTrait
{
    /**
     * @return array<string, array{key: string, url: string, icon: string}>
     */
    private function getSubMenuItems(): array
    {
        return [
            'approval' => ['key' => 'admin.groups.approval', 'url' => $this->generateUrl('admin_groups_approval'), 'icon' => 'check'],
            'archival' => ['key' => 'admin.groups.archival', 'url' => $this->generateUrl('admin_groups_archival'), 'icon' => 'box-archive'],
            'unarchival' => ['key' => 'admin.groups.unarchival', 'url' => $this->generateUrl('admin_groups_unarchival'), 'icon' => 'box-open'],
            'rename' => ['key' => 'admin.groups.rename', 'url' => $this->generateUrl('admin_groups_rename'), 'icon' => 'pen'],
            'categories' => ['key' => 'admin.groups.categories', 'url' => $this->generateUrl('admin_groups_categories'), 'icon' => 'folder'],
            'logs' => ['key' => 'admin.groups.logs', 'url' => $this->generateUrl('admin_groups_logs'), 'icon' => 'list'],
        ];
    }

    /**
     * The tabs with the active one and their counts; the approval tab always shows how many groups wait.
     *
     * @param array<string, int> $counts
     *
     * @return array{items: array<string, array{key: string, url: string, icon: string}>, active: string, counts: array<string, int>}
     */
    private function getGroupAdminSubmenu(string $active, array $counts = []): array
    {
        $waiting = $this->entityManager->getRepository(Group::class)->count(['approved' => [Group::NOT_APPROVED, Group::IN_DISCUSSION]]);

        return ['items' => $this->getSubMenuItems(), 'active' => $active, 'counts' => $counts + ['approval' => $waiting]];
    }
}
