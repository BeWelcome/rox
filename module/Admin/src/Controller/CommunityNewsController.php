<?php

namespace Rox\Admin\Controller;

use Rox\CommunityNews\Repository\CommunityNewsRepositoryInterface;
use Rox\Core\Controller\AbstractController;
use Rox\Member\Repository\MemberRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityNewsController extends AbstractController
{
    /**
     * @var CommunityNewsRepositoryInterface
     */
    protected $communityNewsRepository;

    /**
     * @var MemberRepositoryInterface
     */
    protected $memberRepository;

    public function __construct(
        CommunityNewsRepositoryInterface $communityNewsRepository,
        MemberRepositoryInterface $memberRepository
    ) {
        $this->communityNewsRepository = $communityNewsRepository;
        $this->memberRepository = $memberRepository;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function showOverview(Request $request)
    {
        $request;
        $communityNews = $this->communityNewsRepository->getAll();

        return new Response(
            $this->getEngine()->render('@admin/communitynews/show.html.twig', [
                'communityNews' => $communityNews,
            ])
        );
    }
}
