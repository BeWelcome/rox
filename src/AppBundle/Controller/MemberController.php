<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    /**
     * @Route("/member/autocomplete", name="members_autocomplete")
     *
     * @param Request $request
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $names = [];
        $callback = trim(strip_tags($request->get('callback')));
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

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

    //    /**
//     * @Route("/members/{username}", name="member", requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
//     *
//     * @return Response
//     */
//    public function viewAction(Member $member)
//    {
//        $content = $this->render(':profile:view.html.twig', [
//            'member' => $member,
//        ]);
//
//        return new Response($content);
//    }
//
//    /**
//     * @Route("/editmyprofile", name="editmyprofile")
//     *
//     * @return Response
//     */
//    public function edit()
//    {
//        $member = $this->getUser();
//
//        $content = $this->render(':profile:edit.html.twig', [
//            'member' => $member,
//        ]);
//
//        return new Response($content);
//    }
//
//    /**
//     * @Route("/member/edit", name="member/edit")
//     * @Route("/member/comment", name="member/comment")
//     * @Route("/member/change_password", name="member/change_password")
//     * @Route("/message/compose", name="message/compose")
//     */
//    public function dummyAction()
//    {
//        return new Response();
//    }
}
