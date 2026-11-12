<?php

namespace App\Controller;

use App\Entity\FeedbackCategory;
use App\Model\About\VolunteerTeams;
use App\Repository\TeamCoordinatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AboutController extends AboutBaseController
{
    /**
     * @return Response
     */
    #[Route(path: '/about', name: 'about')]
    public function showAbout(Request $request)
    {
        return $this->render('about/about.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/about/thepeople', name: 'about_people')]
    public function showAboutThePeople(Request $request)
    {
        return $this->render('about/thepeople.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about_people',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/about/getactive', name: 'getactive')]
    #[Route(path: '/volunteer', name: 'volunteer')]
    public function showAboutGetActive(Request $request, TeamCoordinatorRepository $coordinators)
    {
        return $this->render('about/getactive.html.twig', [
            'volunteer_teams' => VolunteerTeams::TEAMS,
            'groups' => VolunteerTeams::GROUPS,
            'coordinators' => $coordinators->byTeam(),
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'getactive',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/press-information', name: 'about_press')]
    #[Route(path: '/media', name: 'media')]
    public function showAboutPressInfo(Request $request, EntityManagerInterface $entityManager)
    {
        // "Write to the press team" opens the contact form on Media
        $media = $entityManager->getRepository(FeedbackCategory::class)->findOneBy(['name' => 'media']);

        return $this->render('about/pressinfo.html.twig', [
            'media_category' => $media?->getId(),
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about_press',
            ],
        ]);
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/bod', name: 'about_bod')]
    public function showAboutBod()
    {
        return $this->redirect('https://www.bevolunteer.org/about-bevolunteer/board-of-directors/');
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/bv', name: 'about_bv')]
    public function showAboutBv()
    {
        return $this->redirect('https://www.bevolunteer.org/');
    }

    /**
     * @return Response
     */
    #[Route(path: '/about/commentguidelines', name: 'profilecomments')]
    public function showAboutCommentGuidelines(Request $request)
    {
        return $this->render('about/commentsguidelines.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about_commentguidelines',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/about/credits', name: 'about_credits')]
    public function showAboutCredits(Request $request)
    {
        return $this->render('about/credits.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'about_credits',
            ],
        ]);
    }

    /**
     * @return Response
     */
    #[Route(path: '/impressum', name: 'imprint')]
    public function showImpressum(Request $request)
    {
        return $this->render('about/impressum.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems($request->getLocale()),
                'active' => 'imprint',
            ],
        ]);
    }
}
