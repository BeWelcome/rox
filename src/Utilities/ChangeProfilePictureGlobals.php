<?php

namespace App\Utilities;

use App\Entity\Member;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangeProfilePictureGlobals
{
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function getGlobalsJsAsJson(Member $member, Member $loggedInMember): string
    {
        $globalsJs = json_encode([
            'baseUrl' => $this->urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'texts' => [
                'profile.change.avatar' => $this->translator->trans('profile.change.avatar'),
                'profile.change.avatar.success' => $this->translator->trans('profile.change.avatar.success'),
                'profile.change.avatar.fail' => $this->translator->trans('profile.change.avatar.fail'),
                'profile.change.avatar.fail.file.too.big' => $this->translator->trans(
                    'profile.change.avatar.fail.file.too.big'
                ),
                'profile.picture.title' => $this->translator->trans('profile.picture.title', [
                    'username' => $member->getUsername()
                ]),
                'uploading' => $this->translator->trans('uploading'),
            ],
            'config' => [
                'isMyself' => ($member === $loggedInMember),
                'avatarUseLightbox' => ($member !== $loggedInMember),
                'avatarUrl' => $this->urlGenerator->generate('avatar', [
                    'username' => $member->getUsername(), 'size' => 48
                ]),
                'username' => $member->getUsername(),
            ]
        ]);

        return $globalsJs;
    }
}
