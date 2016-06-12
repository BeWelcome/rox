<?php

namespace Rox\Member\Controller;

use Rox\Member\Model\Member;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AvatarController
{
    public function __invoke(Request $request, $username)
    {
        // tmp
        return new RedirectResponse('https://www.fillmurray.com/50/50');

        $size = $request->getQueryString();

        if ($size === '50_50') {
            $size = 'xs';
        }

        if ($size[0] !== '_') {
            $size = '_' . $size;
        }

        $memberModel = new Member();

        $member = $memberModel->getByUsername($username);

        ob_start();

        $model = new \MembersModel();

        // TODO this function hits the database again to get user by id. It also exit()
        $model->showAvatar($member->id, $size);

        return new Response(ob_end_clean());
    }
}
