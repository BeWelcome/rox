<?php

namespace App\Controller;

use App\Model\SafetyModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SafetyController extends AbstractController
{
    /**
     * @Route("/safety", name="safety")
     *
     * @return Response
     */
    public function showSafetyMain()
    {
        return $this->render('safety/safetymain.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'saftey',
            ],
        ]);
    }

    /**
     * @Route("/safety/basics", name="safety_basics")
     *
     * @return Response
     */
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
     * @Route("/safety/whattodo", name="safety_what_to_do")
     *
     * @return Response
     */
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
     * @Route("/safety/tips", name="safety_tips")
     *
     * @return Response
     */
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
     * @Route("/safety/faq", name="safety_faq")
     *
     * @return Response
     */
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
     * @Route("/safety/team", name="safety_team")
     *
     * @return Response
     */
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
     * @Route("/feedback?IdCategory=2", name="safety_contact")
     *
     * @return Response
     */
    public function showSafetyContact()
    {
        return $this->redirectToRoute('feedback', ['IdCategory' => 2]);
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
