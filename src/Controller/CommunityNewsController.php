<?php

namespace App\Controller;

use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Entity\Member;
use App\Form\CommunityNewsCommentReportType;
use App\Form\CommunityNewsCommentType;
use App\Form\CustomDataClass\CommunityNewsCommentRequest;
use App\Model\CommunityNewsModel;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Community news for the members: the list, and an article with its comments as threads (templates/communitynews).
 * Members comment, reply to a comment and report a comment to the checkers.
 */
class CommunityNewsController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        private readonly CommunityNewsModel $communityNewsModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/communitynews', name: 'communitynews')]
    public function listAction(Request $request): Response
    {
        return $this->render('communitynews/list.html.twig', [
            'communityNews' => $this->communityNewsModel->getPaginator($request->query->getInt('page', 1), 10),
        ]);
    }

    #[Route(path: '/communitynews/{id}', name: 'communitynews_show', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function showAction(Request $request, CommunityNews $communityNews): Response
    {
        if (!$communityNews->getPublic() && !$this->isGranted(Member::ROLE_ADMIN_COMMUNITYNEWS)) {
            throw $this->createNotFoundException();
        }
        /** @var Member $member */
        $member = $this->getUser();

        $form = $this->createForm(CommunityNewsCommentType::class, new CommunityNewsCommentRequest());
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $parent = null;
            if (null !== $data->parent) {
                $parent = $this->entityManager->find(CommunityNewsComment::class, $data->parent);
                // a reply belongs to a comment of this news that is still there
                if (null === $parent || $parent->getCommunityNews() !== $communityNews || $parent->isDeleted()) {
                    $form->get('text')->addError(new FormError('communitynews.comment.error.parent'));
                }
            }
            if ($form->isValid()) {
                $comment = $this->communityNewsModel->addComment($communityNews, $member, $data->text, $parent);

                return $this->toComment($communityNews, $comment);
            }
        }

        return $this->render('communitynews/show.html.twig', [
            'communityNews' => $communityNews,
            'thread' => $this->communityNewsModel->getThread($communityNews),
            'comments_count' => $this->communityNewsModel->countVisibleComments($communityNews),
            'reported' => $this->communityNewsModel->getReportedBy($member, $communityNews),
            'form' => $form,
            'report_form' => $this->createForm(CommunityNewsCommentReportType::class),
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    /**
     * A member's report of a comment (the report dialog of the article); not of their own comment, once.
     */
    #[Route(path: '/communitynews/comment/{id}/report', name: 'communitynews_comment_report', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
    public function reportComment(Request $request, CommunityNewsComment $comment): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        if ($comment->isDeleted() || $comment->getAuthor() === $member) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(CommunityNewsCommentReportType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addTranslatedFlash('error', 'communitynews.report.error');

            return $this->toComment($comment->getCommunityNews(), $comment);
        }

        $data = $form->getData();
        $reported = $this->communityNewsModel->report($comment, $member, $data['reason'], $data['note']);
        $this->addTranslatedFlash('notice', $reported ? 'communitynews.report.sent' : 'communitynews.report.already');

        return $this->toComment($comment->getCommunityNews(), $comment);
    }

    private function toComment(CommunityNews $communityNews, CommunityNewsComment $comment): RedirectResponse
    {
        return $this->redirect(
            $this->generateUrl('communitynews_show', ['id' => $communityNews->getId()]) . '#comment-' . $comment->getId()
        );
    }
}
