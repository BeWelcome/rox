<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AvatarController
 * @package AppBundle\Controller
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class AvatarController extends Controller
{
    const OFFSET = 172800;

    /**
     * @Route("/members/avatar/{username}/{size}", name="avatar",
     *     requirements={"username" : "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])",
     *          "size" : "\d+" }))
     */
    public function showAvatarAction($username, $size = 50)
    {
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
                $suffix = '';
        }

        $member = $this->getDoctrine()->getRepository(Member::class)->findOneBy(['username' => $username]);
        if (!$member) {
            $filename = 'bundles/app/images/empty_avatar' . $suffix . '.png';
            return new BinaryFileResponse($filename);
        }

        $isBrowseable = $member->isBrowseable();
        if (!$isBrowseable) {
            $filename = 'bundles/app/images/empty_avatar' . $suffix . '.png';
            return new BinaryFileResponse($filename);
        }

        $filename = '../data/user/avatars/' . $member->getId() . $suffix;
        if (file_exists($filename)) {
            return new BinaryFileResponse($filename);
        }

        $filename = 'bundles/app/images/empty_avatar' . $suffix . '.png';
        return new BinaryFileResponse($filename);
    }
}
