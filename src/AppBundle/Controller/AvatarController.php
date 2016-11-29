<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

class AvatarController extends Controller
{
    const OFFSET = 48 * 60 * 60;

    /**
     * @Route("/members/avatar/{username}", name="avatar", requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     */
    public function showAvatarAction(Request $request, $username)
    {
        $size = $request->query->get('size');

        switch ($size) {
            case '30':
            case '75':
                $suffix = '_' . $size . '_' . $size;
                break;
            case '50':
                $suffix = '_xs';
                break;
            case '150':
            case '200':
            case '500':
                $suffix = '_' . $size;
            default:
                $suffix = '_original';
        }

        $member = $this->getDoctrine()->getRepository(Member::class)->findBy('username', $username);
        $isBrowseable = false;
        if (!$member) {
            $filename = 'htdocs/images/misc/empty_avatar' . $suffix . '.png';
            return new BinaryFileResponse($filename);
        }

        $isBrowseable = $member->isBrowseable();
        if (!$isBrowseable) {
            $filename = 'htdocs/images/misc/empty_avatar' . $suffix . '.png';
            return new BinaryFileResponse($filename);
        }

        $filename = 'data/user/avatars/' . $member->id . $suffix;
        return new BinaryFileResponse($filename);
    }
}
