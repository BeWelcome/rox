<?php

namespace App\Controller\Admin;

use App\Entity\CommunityNews;
use App\Entity\Member;
use App\Form\CommunityNewsType;
use App\Model\CommunityNewsModel;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The news admin (templates/admin/communitynews): the news as cards, filtered by All, Published and Hidden; writing
 * and editing a news with a preview as members see it; hiding and publishing one.
 */
#[IsGranted(Member::ROLE_ADMIN_COMMUNITYNEWS)]
class CommunityNewsController extends AbstractController
{
    use TranslatorTrait;

    /** The CSRF token id of hiding and publishing. */
    public const string TOKEN = 'admin_communitynews';

    private const array FILTERS = ['all' => null, 'published' => true, 'hidden' => false];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommunityNewsModel $communityNewsModel,
    ) {
    }

    #[Route(path: '/admin/communitynews', name: 'admin_communitynews_overview')]
    public function showOverviewAction(Request $request): Response
    {
        $filter = \array_key_exists($request->query->getString('show'), self::FILTERS) ? $request->query->getString('show') : 'all';
        $news = $this->communityNewsModel->getAdminPaginator(self::FILTERS[$filter], $request->query->getInt('page', 1), 20);

        return $this->render('admin/communitynews/list.html.twig', [
            'communityNews' => $news,
            'filter' => $filter,
            'counts' => $this->communityNewsModel->countByVisibility(),
            'comments' => $this->communityNewsModel->countComments($news->getCurrentPageResults()),
        ]);
    }

    #[Route(path: '/admin/communitynews/create', name: 'admin_communitynews_create')]
    public function createAction(Request $request): Response
    {
        return $this->editor($request, new CommunityNews());
    }

    #[Route(path: '/admin/communitynews/{id}/edit', name: 'admin_communitynews_edit', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, CommunityNews $communityNews): Response
    {
        return $this->editor($request, $communityNews);
    }

    #[Route(path: '/admin/communitynews/{id}/hide', name: 'admin_communitynews_hide', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function hideAction(Request $request, CommunityNews $communityNews): RedirectResponse
    {
        return $this->setVisibility($request, $communityNews, false);
    }

    #[Route(path: '/admin/communitynews/{id}/show', name: 'admin_communitynews_unhide', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(self::TOKEN)]
    public function unhideAction(Request $request, CommunityNews $communityNews): RedirectResponse
    {
        return $this->setVisibility($request, $communityNews, true);
    }

    /**
     * Writes or edits a news: who wrote it and when, who changed it last.
     */
    private function editor(Request $request, CommunityNews $communityNews): Response
    {
        $creating = null === $communityNews->getId();
        $form = $this->createForm(CommunityNewsType::class, $communityNews);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Member $member */
            $member = $this->getUser();
            $now = new DateTime();
            if ($creating) {
                $communityNews->setCreatedBy($member);
                $communityNews->setCreatedAt($now);
                $this->entityManager->persist($communityNews);
            }
            $communityNews->setUpdatedBy($member);
            $communityNews->setUpdatedAt($now);
            $this->entityManager->flush();
            $this->addFlash('notice', $this->getTranslator()->trans(
                $creating ? 'admin.communitynews.created' : 'admin.communitynews.saved',
                ['title' => $communityNews->getTitle()],
            ));

            return $this->redirectToRoute('admin_communitynews_overview');
        }

        return $this->render('admin/communitynews/editcreate.html.twig', [
            'form' => $form,
            'communityNews' => $communityNews,
            'creating' => $creating,
            'comments' => $creating ? 0 : $this->communityNewsModel->countVisibleComments($communityNews),
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    private function setVisibility(Request $request, CommunityNews $communityNews, bool $public): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();
        $communityNews->setPublic($public);
        $communityNews->setUpdatedAt(new DateTime());
        $communityNews->setUpdatedBy($member);
        $this->entityManager->flush();
        $this->addFlash('notice', $this->getTranslator()->trans(
            $public ? 'admin.communitynews.published' : 'admin.communitynews.hidden',
            ['title' => $communityNews->getTitle()],
        ));

        // back to the list with its filter
        $return = $request->request->getString('return');

        return $this->redirect(preg_match('~^/admin/communitynews(\?[^#\\\\]*)?$~', $return) ? $return : $this->generateUrl('admin_communitynews_overview'));
    }
}
