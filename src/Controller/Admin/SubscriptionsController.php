<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\Admin\ForumNotificationsType;
use App\Repository\MemberRepository;
use App\Service\MemberForumNotifications;
use App\Utilities\TranslatedFlashTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Volunteers turn all forum notifications of a member on or off, e.g. when the member's mails bounce.
 */
#[IsGranted(Member::ROLE_ADMIN_MANAGESUBSCRIPTIONS)]
class SubscriptionsController extends AbstractController
{
    use TranslatedFlashTrait;

    public function __construct(
        private readonly MemberRepository $memberRepository,
        private readonly MemberForumNotifications $notifications,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/admin/subscriptions', name: 'admin_subscriptions', methods: ['GET', 'POST'])]
    public function manage(Request $request): Response
    {
        $form = $this->createForm(ForumNotificationsType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $username = (string) $form->get('username')->getData();
            $member = $this->memberRepository->findOneBy(['username' => $username]);
            if (null === $member) {
                $form->get('username')->addError(new FormError($this->translator->trans('admin.subscriptions.unknown')));
            } else {
                $enable = $form->get('enable');
                $enabled = $enable instanceof SubmitButton && $enable->isClicked();
                $this->notifications->switch($member, $enabled);
                $this->addTranslatedFlash(
                    'notice',
                    $enabled ? 'admin.subscriptions.enabled' : 'admin.subscriptions.disabled',
                    ['%username%' => $member->getUsername()],
                );

                return $this->redirectToRoute('admin_subscriptions');
            }
        }

        return $this->render('admin/subscriptions/manage.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
