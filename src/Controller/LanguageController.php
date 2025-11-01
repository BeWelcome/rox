<?php

namespace App\Controller;

use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LanguageController extends AbstractController
{
    #[Route('/mothertongues', name: 'mothertongues')]
    public function spokenLanguages(EntityManagerInterface $entityManager): JsonResponse
    {
        $languageRepository = $entityManager->getRepository(Language::class);
        $languages = $languageRepository->findAll();

        $motherTongues = [];
        foreach ($languages as $language) {
            if ($language->getIsSpokenlanguage() || $language->getIsSignlanguage()) {
                $motherTongues[] = [
                    'id' => $language->getShortCode(),
                    'label' => $language->getName(),
                ];
            }
        }

        $jsonPayload = json_encode(['list' => $motherTongues]);

        return new JsonResponse($jsonPayload);
    }
}
