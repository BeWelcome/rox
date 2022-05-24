<?php

namespace App\Utilities;

use App\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class MembersSubmenu
{
    public function getSubmenuItems(Member $member, Router $routing): array
    {
        $member = $this->getUser();
        $submenuItems = [
            'comment' => [
                'key' => 'add_comment',
                'url' => $routing->generate('add_comment'),
            ],
        ];

        return $submenuItems;
    }
}
