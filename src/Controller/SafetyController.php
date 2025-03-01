<?php

namespace App\Controller;

use App\Model\SafetyModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SafetyController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/safety', name: 'safety')]
    public function showSafetyMain()
    {
        return $this->render('safety/safetymain.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'safety',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/safety/basics', name: 'safety_basics')]
    public function showSafetyBasics()
    {
        return $this->render('safety/safetybasics.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'safety_basics',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/safety/whattodo', name: 'safety_what_to_do')]
    public function showSafetyWhatToDo()
    {
        return $this->render('safety/safetywhattodo.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'safety_what_to_do',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/safety/tips', name: 'safety_tips')]
    public function showSafetyTips()
    {
        return $this->render('safety/safetytips.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'safety_tips',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/safety/faq', name: 'safety_faq')]
    public function showSafetyFAQ()
    {
        return $this->render('safety/safetyfaq.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'safety_faq',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/safety/team', name: 'safety_team')]
    public function showSafetyTeam(SafetyModel $safetyModel)
    {
        $teamMembers = $safetyModel->getSafetyTeamMembers();

        return $this->render('safety/safetyteam.html.twig', [
            'team_members' => $teamMembers,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'safety_team',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/feedback?IdCategory=2', name: 'safety_contact')]
    public function showSafetyContact()
    {
        return $this->redirectToRoute('feedback', ['IdCategory' => 2]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/feedback?IdCategory=2&no', name: 'safety_contact_no_modal')]
    public function showSafetyContactNoModal()
    {
        return $this->redirectToRoute('feedback', ['IdCategory' => 2, 'no' => 1]);
    }

    /**
     * @return array
     */
    private function getSubMenuItems()
    {
        return [
            'safety' => [
                'key' => 'SafetyMain',
                'url' => $this->generateUrl('safety'),
            ],
            'safety_basics' => [
                'key' => 'SafetyBasics',
                'url' => $this->generateUrl('safety_basics'),
            ],
            'safety_what_to_do' => [
                'key' => 'SafetyWhatToDo',
                'url' => $this->generateUrl('safety_what_to_do'),
            ],
            'safety_tips' => [
                'key' => 'SafetyTips',
                'url' => $this->generateUrl('safety_tips'),
            ],
            'safety_faq' => [
                'key' => 'SafetyFAQ',
                'url' => $this->generateUrl('safety_faq'),
            ],
            'safety_team' => [
                'key' => 'SafetyTeam',
                'url' => $this->generateUrl('safety_team'),
            ],
            'safety_contact' => [
                'key' => 'SafetyContact',
                'url' => $this->generateUrl('safety_contact'),
            ],
        ];
    }
}
