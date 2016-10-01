<?php

namespace Rox\Member\Controller;

use Rox\Member\Model\Member;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class AvatarController
{
    const OFFSET = 48 * 60 * 60;

    public function showAvatarAction(Request $request, $username)
    {
        $size = $request->query->get('size');

        switch ($size) {
            case '30':
                $suffix = '_30_30';
                break;
            case '50':
                $suffix = '_xs';
                break;
            case '75':
                $suffix = '_75_75';
                break;
            case '150':
                $suffix = '_150';
                break;
            case '200':
                $suffix = '_200';
                break;
            case '500':
                $suffix = '_500';
                break;
            default:
                $suffix = '_original';
        }

        $memberModel = new Member();
        $member = $memberModel->getByUsername($username);
        $isBrowseable = $member->isBrowseable();

        $filename = 'htdocs/images/misc/empty_avatar' . $suffix . '.png';
        if ($isBrowseable && file_exists('data/user/avatars/' . $member->id . $suffix)) {
            $filename = 'data/user/avatars/' . $member->id . $suffix;
        }

        return new BinaryFileResponse($filename);
    }
}
