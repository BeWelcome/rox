<?php

namespace App\Controller;

use App\Model\SafetyModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The safety pages (templates/safety/): an overview, the basics of trust, what to do in an uncomfortable or an
 * emergency situation, tips, a FAQ and the Safety Team. Reporting a problem goes to the feedback form's safety
 * category.
 */
class SafetyController extends AbstractController
{
    #[Route(path: '/safety', name: 'safety')]
    public function showSafetyMain(): Response
    {
        return $this->render('safety/safetymain.html.twig');
    }

    #[Route(path: '/safety/basics', name: 'safety_basics')]
    public function showSafetyBasics(): Response
    {
        return $this->render('safety/safetybasics.html.twig');
    }

    #[Route(path: '/safety/whattodo', name: 'safety_what_to_do')]
    public function showSafetyWhatToDo(): Response
    {
        return $this->render('safety/safetywhattodo.html.twig');
    }

    #[Route(path: '/safety/tips', name: 'safety_tips')]
    public function showSafetyTips(): Response
    {
        return $this->render('safety/safetytips.html.twig');
    }

    #[Route(path: '/safety/faq', name: 'safety_faq')]
    public function showSafetyFAQ(): Response
    {
        return $this->render('safety/safetyfaq.html.twig');
    }

    #[Route(path: '/safety/team', name: 'safety_team')]
    public function showSafetyTeam(SafetyModel $safetyModel): Response
    {
        return $this->render('safety/safetyteam.html.twig', [
            'team_members' => $safetyModel->getSafetyTeamMembers(),
        ]);
    }

    #[Route(path: '/feedback?IdCategory=2', name: 'safety_contact')]
    public function showSafetyContact(): RedirectResponse
    {
        return $this->redirectToRoute('feedback', ['IdCategory' => 2]);
    }

    #[Route(path: '/feedback?IdCategory=2&no', name: 'safety_contact_no_modal')]
    public function showSafetyContactNoModal(): RedirectResponse
    {
        return $this->redirectToRoute('feedback', ['IdCategory' => 2, 'no' => 1]);
    }
}
