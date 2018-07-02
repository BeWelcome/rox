<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Word;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationController extends Controller
{
    /**
     * @Route("/admin/translations", name="translations")
     *
     * @return Response
     */
    public function listTranslationsAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 20);
        $locale = $this->get('session')->get('locale');
        $translations = $this->getDoctrine()
            ->getRepository(Word::class)
            ->paginateTranslations($locale, $page, $limit);
        return $this->render(':admin:translations/list.html.twig', [
            'translations' => $translations,
            'is_translation_interface' => true,
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/edit", name="translation_edit")
     *
     * @param mixed $locale
     * @param mixed $code
     *
     * @return Response
     */
    public function editTranslationAction($locale, $code)
    {
        return $this->render(':admin:translations/list.html.twig', [
            'is_translation_interface' => true,
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/add", name="translation_create")
     *
     * Creates an English index and the matching translation (if locale != 'en')
     *
     * @param mixed $locale
     * @param mixed $code
     *
     * @return Response
     */
    public function createTranslationAction($locale, $code)
    {
        return $this->render(':admin:translations/list.html.twig', [
            'is_translation_interface' => true,
        ]);
    }

    /**
     * @Route("/admin/translations/{locale}/{code}/add", name="translation_add")
     *
     * Adds a missing translation for an existing english index
     *
     * @param mixed $locale
     * @param mixed $code
     *
     * @return Response
     */
    public function addTranslationAction($locale, $code)
    {
        return $this->render(':admin:translations/list.html.twig', [
            'is_translation_interface' => true,
        ]);
    }
}
