<?php

namespace App\Controller;

use App\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AvatarController.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class AvatarController extends AbstractController
{
    private const EXPIRY = 60 * 60 * 24 * 365; // One year

    /**
     * @Route("/members/avatar/{username}/{size}", name="avatar",
     *     requirements={"username" : "(?i:[a-z][a-z0-9-._ ]{1,30}[a-z0-9])",
     *          "size" : "\d+|original" },
     *     _defaults={"size": "50"})
     *
     * @param mixed $username
     * @param mixed $size
     *
     * @return BinaryFileResponse|RedirectResponse
     */
    public function showAvatar($username, $size)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->emptyAvatar($size);
        }

        $member = $this->getDoctrine()->getRepository(Member::class)->findOneBy(['username' => $username]);
        if (!$member) {
            return $this->emptyAvatar($size);
        }

        $isBrowseable = $member->isBrowseable();
        if (!$isBrowseable) {
            return $this->emptyAvatar($size);
        }

        $filename = '../data/user/avatars/' . $member->getId() . $this->getSuffix($size);
        if (file_exists($filename)) {
            return $this->createCacheableResponse($filename);
        }

        return $this->emptyAvatar($size);
    }

    private function getSuffix($size)
    {
        $suffix = '';
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
        }

        return $suffix;
    }

    /**
     * @param mixed $size
     *
     * @return BinaryFileResponse
     */
    private function emptyAvatar($size)
    {
        $filename = 'images/empty_avatar' . $this->getSuffix($size) . '.png';

        return $this->createCacheableResponse($filename, self::EXPIRY);
    }

    private function createCacheableResponse(string $filename, $expiry = 86400)
    {
        $response = new BinaryFileResponse($filename);
        $response->setSharedMaxAge($expiry);

        return $response;
    }
}
