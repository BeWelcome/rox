<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    /**
     * @Route("/about", name="about")
     *
     * @return Response
     */
    public function showAbout()
    {
        return $this->render('about/about.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about',
            ],
        ]);
    }

    /**
     * @Route("/about/faq", name="about_faq")
     *
     * @return Response
     */
    public function showAboutFAQ()
    {
        return $this->redirectToRoute('about_faq');
    }

    /**
     * @Route("/about/feedback", name="contactus")
     *
     * @return Response
     */
    public function showAboutFeedback()
    {
        return $this->render('about/feedback.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_feedback',
            ],
        ]);
    }

    /**
     * @Route("/about/thepeople", name="about_thepeople")
     *
     * @return Response
     */
    public function showAboutThePeople()
    {
        return $this->render('about/thepeople.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_thepeople',
            ],
        ]);
    }

    /**
     * @Route("/about/getactive", name="about_getactive")
     *
     * @return Response
     */
    public function showAboutGetActive()
    {
        return $this->render('about/getactive.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_getactive',
            ],
        ]);
    }

    /**
     * @Route("/about", name="about_theidea")
     *
     * @return Response
     */
    public function showAboutTheIdea()
    {
        return $this->redirectToRoute('about', [
        'submenu' => [
            'items' => $this->getSubMenuItems(),
            'active' => 'about',
            'active' => 'about_theidea',
        ],
    ]);
    }

    /**
     * @Route("https://www.bevolunteer.org/about-bevolunteer/board-of-directors/", name="about_bod")
     *
     * @return RedirectResponse
     */
    public function showAboutBod()
    {
        return $this->redirect('https://www.bevolunteer.org/about-bevolunteer/board-of-directors/');
    }
    
    /**
     * @Route("http://www.bevolunteer.org/", name="about_bv")
     *
     * @return RedirectResponse
     */
    public function showAboutBv()
    {
        return $this->redirect('http://www.bevolunteer.org/');
    }
    
    /**
     * @Route("/about/commentguidelines", name="profilecomments")
     *
     * @return Response
     */
    public function showAboutCommentGuidelines()
    {
        return $this->render('about/commentsguidelines.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_commentguidelines',
            ],
        ]);
    }

    /**
     * @Route("/about/statistics", name="about_statistics")
     *
     * @return Response
     */
    public function showAboutStatistics()
    {
        $statistics = [
            'members' => [
                'headline' => 'members',
                'route' => 'stats_members',
            ],
            'sent_messages' => [
                'headline' => 'sent_messages',
                'route' => 'stats_messages_sent',
            ],
            'read_messages' => [
                'headline' => 'read_messages',
                'route' => 'stats_messages_read',
            ],
            'sent_requests' => [
                'headline' => 'sent_requests',
                'route' => 'stats_requests_sent',
            ],
            'accepted_requests' => [
                'headline' => 'accepted_requests',
                'route' => 'stats_requests_accepted',
            ],
        ];
        return $this->render('about/statistics.html.twig', [
            'statistics' => $statistics,
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'statistics',
            ],
        ]);
    }

    /**
     * @Route("/about/credits", name="about_credits")
     *
     * @return Response
     */
    public function showAboutCredits()
    {
        return $this->render('about/credits.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_credits',
            ],
        ]);
    }

    /**
     * @return array
     */
    private function getSubMenuItems()
    {
        return [
            'about' => [
                'key' => 'AboutUsSubmenu',
                'url' => $this->generateUrl('about'),
            ],
            'about_faq' => [
                'key' => 'Faq',
                'url' => $this->generateUrl('about_faq'),
            ],
            'about_feedback' => [
                'key' => 'ContactUs',
                'url' => $this->generateUrl('contactus'),
            ],
            'separator' => [
                'key' => 'About_AtAGlance',
                'url' => '',
            ],
            'about_theidea' => [
                'key' => 'About_TheIdea',
                'url' => $this->generateUrl('about'),
            ],
            'about_thepeople' => [
                'key' => 'About_ThePeople',
                'url' => $this->generateUrl('about_thepeople'),
            ],
            'about_getactive' => [
                'key' => 'About_GetActive',
                'url' => $this->generateUrl('about_getactive'),
            ],
            'separator2' => [
                'key' => 'MoreInfo',
                'url' => '',
            ],
            'about_press' => [
                'key' => 'PressInfoPage',
                'url' => $this->generateUrl('wiki_page', ['pageTitle' => 'press information']),
            ],
            'about_bod' => [
                'key' => 'BoardOfDirectorsPage',
                'url' => $this->generateUrl('about_bod'),
            ],
            'about_bv' => [
                'key' => 'BeVolunteerBlogs',
                'url' => $this->generateUrl('about_bv'),
            ],
            'about_terms' => [
                'key' => 'TermsPage',
                'url' => $this->generateUrl('terms_of_use'),
            ],
            'about_privacy' => [
                'key' => 'PrivacyPage',
                'url' => $this->generateUrl('privacy_policy'),
            ],
            'about_commentguidelines' => [
                'key' => 'CommentGuidelinesPage',
                'url' => $this->generateUrl('profilecomments'),
            ],
            'about_statistics' => [
                'key' => 'StatsPage',
                'url' => $this->generateUrl('about_statistics'),
            ],
            'about_credits' => [
                'key' => 'credits.title',
                'url' => $this->generateUrl('about_credits'),
            ],
        ];
    }
}
