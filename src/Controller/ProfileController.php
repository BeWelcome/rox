<?php

namespace App\Controller;

use _PHPStan_9a6ded56a\Symfony\Component\Finder\Exception\AccessDeniedException;
use App\Entity\Member;
use App\Form\ProfileStatusFormType;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/members/{username}/new", name="members_profile_new")
     *
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     */
    public function show(Member $member, ProfileSubmenu $profileSubmenu): Response
    {
        if (!$member->isBrowseable()) {
            throw new AccessDeniedException();
        }

        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($loggedInMember === $member) {
            return $this->showOwnProfile($member, $profileSubmenu);
        }

        return $this->render('profile/show.html.twig', [
            'own' => false,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember),
            'member' => $member,
        ]);
    }

    public function showOwnProfile(Member $member, ProfileSubmenu $profileSubmenu): Response
    {
        return $this->render('profile/show.html.twig', [
            'own' => true,
            'submenu' => $profileSubmenu->getSubmenu($member, $member),
            'member' => $member,
        ]);
    }

    /**
     * @Route("/members/status/set", name="profile_set_status",
     *     methods={"POST"}
     * )
     */
    public function setMemberStatus(Request $request, EntityManagerInterface $entityManager): Response
    {
        $statusForm = $this->createForm(ProfileStatusFormType::class);
        $statusForm->handleRequest($request);

        if ($statusForm->isSubmitted() && $statusForm->isValid())
        {
            $data = $statusForm->getData();
            $memberId = $data['member'];
            $status = $data['status'];
            $memberRepository = $entityManager->getRepository(Member::class);
            $member = $memberRepository->find($memberId);
            if (null !== $member) {
                $member->setStatus($status);
                $entityManager->persist($member);
                $entityManager->flush();
            }
        }

        return new RedirectResponse($request->headers->get('referer'));
    }
}
