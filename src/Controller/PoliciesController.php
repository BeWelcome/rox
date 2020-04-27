<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PoliciesController extends AbstractController
{
    /**
     * @Route("/terms/{locale}/new", name="terms_of_use",
     *     requirements={"locale":"en|fr"})
     * @param string $locale
     * @return Response
     */
    public function showTermsOfUse(string $locale)
    {
        return $this->render('policies/terms.'.$locale.'.html.twig');
    }

    /**
     * @Route("/privacy/{locale}", name="privacy_policy",
     *     requirements={"locale":"en|fr"})
     * @param string $locale
     * @return Response
     */
    public function showPrivacyPolicy(string $locale)
    {
        return $this->render('policies/privacy.'.$locale.'.html.twig');
    }

    /**
     * @Route("/datarights/{locale}", name="data_rights",
     *     requirements={"locale":"en|fr"},
     *     defaults={"locale":"en"})
     * @param string $locale
     * @return Response
     */
    public function showDataRights(string $locale)
    {
        return $this->render('policies/datarights.'.$locale.'.html.twig');
    }
}
