<?php

namespace Rox\Member\Controller;

use Rox\Member\Repository\MemberRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class CommentController
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    public function __construct(EngineInterface $engine, MemberRepositoryInterface $memberRepository)
    {
        $this->engine = $engine;
        $this->memberRepository = $memberRepository;
    }

    public function index(Request $request)
    {
        $username = $request->attributes->get('username');

        $member = $this->memberRepository->getByUsername($username);

        $content = $this->engine->render('@member/comments.html.twig', [
            'member' => $member,
        ]);

        return new Response($content);
    }
}
