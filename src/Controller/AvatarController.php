<?php

namespace App\Controller;

use App\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AvatarController.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class AvatarController extends AbstractController
{
    const OFFSET = 172800;

    /**
     * @Route("/members/avatar/{username}/{size}", name="avatar",
     *     requirements={"username" : "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])",
     *          "size" : "\d+|original" }))
     *
     * @param mixed $username
     * @param mixed $size
     *
     * @return BinaryFileResponse
     */
    public function showAvatar($username, $size = 50)
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
            case 'original':
                $suffix = '_' . $size;
                break;
            default:
                $suffix = '';
        }

        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $filename = 'images/empty_avatar' . $suffix . '.png';

            $response = new BinaryFileResponse($filename);
            $response->setSharedMaxAge(600);

            return $response;
        }

        $member = $this->getDoctrine()->getRepository(Member::class)->findOneBy(['username' => $username]);
        if (!$member) {
            $filename = 'images/empty_avatar' . $suffix . '.png';

            $response = new BinaryFileResponse($filename);
            $response->setSharedMaxAge(600);

            return $response;
        }

        $isBrowseable = $member->isBrowseable();
        if (!$isBrowseable) {
            $filename = 'images/empty_avatar' . $suffix . '.png';

            $response = new BinaryFileResponse($filename);
            $response->setSharedMaxAge(600);

            return $response;
        }

        $filename = '../data/user/avatars/' . $member->getId() . $suffix;
        if (file_exists($filename)) {
            $response = new BinaryFileResponse($filename);
            $response->setSharedMaxAge(600);

            return $response;
        }

        $filename = 'images/empty_avatar' . $suffix . '.png';

        $response = new BinaryFileResponse($filename);
        $response->setSharedMaxAge(600);

        return $response;
    }
}
