<?php

namespace AppBundle\Doctrine\Filter;

use AppBundle\Entity\Language;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Annotations\Reader;

class Configurator
{
    protected $em;
    protected $requestStack;
    protected $reader;

    public function __construct(ObjectManager $em, RequestStack $requestStack, Reader $reader)
    {
        $this->em              = $em;
        $this->requestStack    = $requestStack;
        $this->reader          = $reader;
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