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
        return $this->redirectToRoute('faqs_overview');
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
     * @Route("/about/thepeople", name="about_people")
     *
     * @return Response
     */
    public function showAboutThePeople()
    {
        return $this->render('about/thepeople.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_people',
            ],
        ]);
    }

    /**
     * @Route("/about/getactive", name="getactive")
     * @Route("/volunteer", name="volunteer")
     *
     * @return Response
     */
    public function showAboutGetActive()
    {
        return $this->render('about/getactive.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'getactive',
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
        return $this->render('about/about.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about_theidea',
            ],
        ]);
    }

    /**
     * @Route("/bod", name="about_bod")
     *
     * @return RedirectResponse
     */
    public function showAboutBod()
    {
        return $this->redirect('https://www.bevolunteer.org/about-bevolunteer/board-of-directors/');
    }
    
    /**
     * @Route("bv", name="about_bv")
     *
     * @return RedirectResponse
     */
    public function showAboutBv()
    {
        return $this->redirect('https://www.bevolunteer.org/');
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
     * @Route("/about/stats", name="stats")
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
     * @Route("/impressum", name="imprint")
     *
     * @return Response
     */
    public function showImpressum()
    {
        return $this->render('about/impressum.html.twig', [
            'submenu' => [
                'items' => $this->getSubMenuItems(),
                'active' => 'about',
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
                'url' => $this->generateUrl('about_theidea'),
            ],
            'about_people' => [
                'key' => 'About_ThePeople',
                'url' => $this->generateUrl('about_people'),
            ],
            'getactive' => [
                'key' => 'About_GetActive',
                'url' => $this->generateUrl('getactive'),
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
            'statistics' => [
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
