<?php

namespace Rox\Member\Controller;

use Rox\Core\Controller\AbstractController;
use Rox\Member\Repository\MemberRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MemberController extends AbstractController
{
    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    public function __construct(MemberRepositoryInterface $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    public function view(Request $request)
    {
        $username = $request->attributes->get('username');

        if (!$username) {
            throw new NotFoundHttpException();
        }

        $member = $this->memberRepository->getByUsername($username);

        $content = $this->engine->render('@member/profile/view.html.twig', [
            'member' => $member,
        ]);

        return new Response($content);
    }

    public function edit()
    {
        $content = $this->engine->render('@member/profile/edit.html.twig', [

        ]);

        return new Response($content);
    }
}
