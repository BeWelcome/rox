<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PoliciesController extends AbstractController
{
    /**
     * @Route("/terms/{locale}", name="terms_of_use",
     *     _defaults={"locale":"en"})
     * @param string $locale
     * @return Response
     */
    public function showTermsOfUse(string $locale)
    {
        switch($locale) {
            case 'en':
            case 'fr':
                // Show English or French version depending on locale (no translations at the moment)!
                break;
            default:
                $locale = 'en';
        }
        return $this->render('policies/terms.'.$locale.'.html.twig');
    }

    /**
     * @Route("/privacy/{locale}", name="privacy_policy",
     *     _defaults={"locale":"en"})
     *
     * @param string $locale
     * @return Response
     */
    public function showPrivacyPolicy(string $locale)
    {
        switch($locale) {
            case 'en':
            case 'fr':
                // Show English or French version depending on locale (no translations at the moment)!
                break;
            default:
                $locale = 'en';
        }
        return $this->render('policies/privacy.'.$locale.'.html.twig');
    }

    /**
     * @Route("/datarights/{locale}", name="data_rights",
     *     defaults={"locale":"en"})
     * @param string $locale
     * @return Response
     */
    public function showDataRights(string $locale)
    {
        switch($locale) {
            case 'en':
            case 'fr':
                // Show English or French version depending on locale (no translations at the moment)!
                break;
            default:
                $locale = 'en';
        }
        return $this->render('policies/datarights.'.$locale.'.html.twig');
    }
}
