<?php

namespace Rox\Member\Controller;

use Rox\Member\Model\Member;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Templating\EngineInterface;

class MemberController
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(EngineInterface $engine, SessionInterface $session)
    {
        $this->engine = $engine;
        $this->session = $session;
    }

    public function view(Request $request)
    {
        if (!$this->session->get('IdMember')) {
            return new RedirectResponse('/');
        }

        $username = $request->attributes->get('username');

        if (!$username) {
            throw new NotFoundHttpException();
        }

        $model = new Member();

        $member = $model->getByUsername($username);

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
