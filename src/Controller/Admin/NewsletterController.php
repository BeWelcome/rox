<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Model\TranslationModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends AbstractController
{
    /**
     * Work around to have newsletter directly translated without the need to save it in the
     * translation module.
     *
     * @Route("/admin/massmail/create/finish", name="admin_massmail_finish")
     */
    public function updateTranslations(Request $request, TranslationModel $translationModel): RedirectResponse
    {
        if (!$this->isGranted(Member::ROLE_ADMIN_MASSMAIL)) {
            throw $this->createAccessDeniedException('You need to have Massmail right to access this.');
        }

        $translationModel->refreshTranslationsCacheForLocale('en');

        return $this->redirectToRoute('admin_massmail');
    }
}
