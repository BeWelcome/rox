<?php

namespace App\Controller;

use App\Form\FeedbackFormType;
use App\Model\AboutModel;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AboutBaseController extends AbstractController
{
    /**
     * @return array
     */
    protected function getSubMenuItems()
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
