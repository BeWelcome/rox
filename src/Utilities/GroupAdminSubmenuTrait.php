<?php

namespace App\Utilities;

/**
 * The tabs of the group admin team's pages (approval, archival, unarchival, rename, categories, logs).
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
}
