<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AutoCompleteController extends AbstractController
{
    /**
     * @Route("/member/autocomplete", name="members_autocomplete")
     *
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $names = [];
        $callback = trim(strip_tags($request->get('callback')));
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        $entities = $memberRepository->loadMembersByUsernamePart($term);

        foreach ($entities as $entity) {
            $names[] = [
                'id' => $entity['username'],
                'label' => $entity['username'],
                'value' => $entity['username'],
            ];
        }

        $response = new JsonResponse();
        $response->setCallback($callback);
        $response->setData($names);

        return $response;
    }

    /**
     * @Route("/member/autocomplete/start", name="members_autocomplete_starts_with")
     *
     * @return JsonResponse
     */
    public function autoCompleteStartsWith(Request $request)
    {
        $names = [];
        $callback = trim(strip_tags($request->get('callback')));
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        $entities = $memberRepository->findByProfileInfoStartsWith($term);

        foreach ($entities as $entity) {
            $names[] = [
                'id' => $entity->getUsername(),
                'label' => $entity->getUsername(),
                'value' => $entity->getUsername(),
            ];
        }

        $response = new JsonResponse();
        $response->setCallback($callback);
        $response->setData($names);

        return $response;
    }
}
