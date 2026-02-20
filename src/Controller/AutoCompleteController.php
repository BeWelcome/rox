<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AutoCompleteController extends AbstractController
{
    #[Route(path: '/member/autocomplete', name: 'members_autocomplete')]
    public function autoCompleteAction(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $names = [];
        $callback = trim(strip_tags((string) $request->query->get('callback')));
        $term = trim(strip_tags((string) $request->query->get('term')));

        /** @var MemberRepository $memberRepository */
        $memberRepository = $entityManager->getRepository(Member::class);
        $entities = $memberRepository->loadMembersByUsernamePart($term);

        foreach ($entities as $entity) {
            $names[] = [
                'id' => $entity['username'],
                'label' => $entity['username'],
                'value' => $entity['username'],
            ];
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }

    #[Route(path: '/member/autocomplete/start', name: 'members_autocomplete_starts_with', format: 'json')]
    public function autoCompleteStartsWith(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $names = [];
        $callback = trim(strip_tags((string) $request->query->get('callback')));
        $term = trim(strip_tags((string) $request->query->get('term')));

        /** @var MemberRepository $memberRepository */
        $memberRepository = $entityManager->getRepository(Member::class);
        $entities = $memberRepository->findByProfileInfoStartsWith($term);

        foreach ($entities as $entity) {
            $names[] = [
                'id' => $entity->getUsername(),
                'label' => $entity->getUsername(),
                'value' => $entity->getUsername(),
            ];
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $this->json(['items' => $names]);
    }
}
