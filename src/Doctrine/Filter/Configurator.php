<?php

namespace App\Doctrine\Filter;

use App\Entity\Language;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;

class Configurator
{
    protected $em;
    protected $requestStack;
    protected $reader;

    public function __construct(ObjectManager $em, RequestStack $requestStack, Reader $reader)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->reader = $reader;
    }

    public function onKernelRequest()
    {
        $language = $this->getLanguage();

        $filter = $this->em->getFilters()->enable('language_filter');
        $filter->setParameter('language', $language->getId());
        $filter->setAnnotationReader($this->reader);
    }

    private function getLanguage()
    {
        // get Language for request locale
        $languageRepository = $this->em->getRepository(Language::class);
        $language = $languageRepository->findOneBy(['shortcode' => $this->requestStack->getCurrentRequest()->getSession()->get('locale', 'en')]);

        return $language;
    }
}
