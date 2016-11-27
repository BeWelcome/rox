<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    /**
     * @Route("/members/{username}", name="member", requirements={"username" = "(?i:[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9])"}))
     * @param Request $request
     * @return Response
     */
    public function view(Request $request)
    {
        $username = $request->attributes->get('username');

        $member = $this->getDoctrine()->getRepository(Member::class)->findBy('username', $username);

        $content = $this->render(':profile/view.html.twig', [
            'member' => $member,
        ]);

        return new Response($content);
    }

    /**
     * @Route("/editmyprofile", name="editmyprofile")
     * @return Response
     */
    public function edit()
    {
        $member = $this->getUser();

        $content = $this->render(':profile/edit.html.twig', [
            'member' => $member
        ]);

        return new Response($content);
    }
}
