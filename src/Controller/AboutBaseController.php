<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AboutBaseController extends AbstractController
{
    /**
     * The menu of the About pages (about/layout.html.twig): the pages by group, the groups split by separators with
     * their heading. External pages (Board of directors, BeVolunteer) are marked.
     *
     * @return array<string, array{key: string, url: string, external?: bool}>
     */
    protected function getSubMenuItems(string $locale): array
    {
        return [
            'about' => ['key' => 'AboutUsSubmenu', 'url' => $this->generateUrl('about')],
            'about_faq' => ['key' => 'Faq', 'url' => $this->generateUrl('faqs_overview')],
            'about_feedback' => ['key' => 'ContactUs', 'url' => $this->generateUrl('contactus')],
            'separator' => ['key' => 'About_AtAGlance', 'url' => ''],
            'about_people' => ['key' => 'About_ThePeople', 'url' => $this->generateUrl('about_people')],
            'getactive' => ['key' => 'About_GetActive', 'url' => $this->generateUrl('getactive')],
            'separator2' => ['key' => 'MoreInfo', 'url' => ''],
            'about_press' => ['key' => 'PressInfoPage', 'url' => $this->generateUrl('about_press')],
            'about_bod' => ['key' => 'BoardOfDirectorsPage', 'url' => $this->generateUrl('about_bod'), 'external' => true],
            'about_bv' => ['key' => 'BeVolunteerBlogs', 'url' => $this->generateUrl('about_bv'), 'external' => true],
            'about_policies' => ['key' => 'about.policies', 'url' => $this->generateUrl('terms_of_use', ['locale' => $locale])],
            'about_commentguidelines' => ['key' => 'CommentGuidelinesPage', 'url' => $this->generateUrl('profilecomments')],
            'statistics' => ['key' => 'StatsPage', 'url' => $this->generateUrl('about_statistics')],
            'about_credits' => ['key' => 'credits.title', 'url' => $this->generateUrl('about_credits')],
            'imprint' => ['key' => 'impressum', 'url' => $this->generateUrl('imprint')],
        ];
    }
}
