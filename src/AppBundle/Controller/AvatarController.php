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

        $member = $this->getDoctrine()->getRepository(Member::class)->findBy('username', $username);
        $isBrowseable = false;
        if ($member) {
            $isBrowseable = $member->isBrowseable();
        }

        $filename = 'htdocs/images/misc/empty_avatar' . $suffix . '.png';
        if ($isBrowseable && file_exists('data/user/avatars/' . $member->id . $suffix)) {
            $filename = 'data/user/avatars/' . $member->id . $suffix;
        }

        return new BinaryFileResponse($filename);
    }
}
