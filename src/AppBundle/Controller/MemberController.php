<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
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

    /**
     * @Route("/members/{username}/comment/{commentId}/report", name="report_comment",
     *     requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     *
     * @ParamConverter("member", class="AppBundle\Entity\Member", options={"mapping": {"username": "username"}})
     * @ParamConverter("comment", class="AppBundle\Entity\Comment", options={"mapping": {"commentId": "id"}})
     *
     * @param Member  $member
     * @param Comment $comment
     *
     * @return Response
     */
    public function viewAction(Member $member, Comment $comment)
    {
        if ($comment->getToMember()->getId() !== $member->getId() && $comment->getFromMember()->getId() !== $member->getId()) {
            throw new AccessDeniedException('Hau ab!');
        }

        return $this->render(':member:report.comment.html.twig', [
            'comment' => $comment,
            'member' => $member,
        ]);
    }
}
