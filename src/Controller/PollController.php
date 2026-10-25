<?php

namespace App\Controller;

use App\Dto\PollDto;
use App\Entity\Member;
use App\Entity\Poll;
use App\Entity\PollChoice;
use App\Form\PollChoiceType;
use App\Form\PollType;
use App\Form\PollVoteType;
use App\Model\PollModel;
use App\Repository\PollRepository;
use App\Security\PollVoter;
use App\Utilities\ItemsPerPageTraits;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The polls pages (formerly build/polls). Route names and URLs of the pages are the legacy ones.
 */
class PollController extends AbstractController
{
    use ItemsPerPageTraits;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    /** List name in the URL => list of PollRepository; "new" were the drafts on the legacy pages. */
    private const array LISTS = [
        'open' => PollRepository::LIST_OPEN,
        'closed' => PollRepository::LIST_CLOSED,
        'new' => PollRepository::LIST_DRAFTS,
        'all' => PollRepository::LIST_ALL,
        'contributed' => PollRepository::LIST_CONTRIBUTED,
    ];

    public function __construct(
        private readonly PollModel $pollModel,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/polls', name: 'polls')]
    #[Route(path: '/polls/list/open', name: 'polls_list_open', defaults: ['list' => 'open'])]
    #[Route(path: '/polls/list/closed', name: 'polls_list_closed', defaults: ['list' => 'closed'])]
    #[Route(path: '/polls/list/new', name: 'polls_list_new', defaults: ['list' => 'new'])]
    #[Route(path: '/polls/listProject', name: 'polls_listProject', defaults: ['list' => 'new'])]
    #[Route(path: '/polls/list/all', name: 'polls_list_all', defaults: ['list' => 'all'])]
    #[Route(path: '/polls/list/contributed', name: 'polls_list_contributed', defaults: ['list' => 'contributed'])]
    #[Route(
        path: '/polls/list/{list}/page/{page}',
        name: 'polls_list_pages',
        requirements: ['list' => 'open|closed|new|all|contributed', 'page' => '\d+']
    )]
    public function list(string $list = 'open', int $page = 1): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        /** @var PollRepository $repository */
        $repository = $this->entityManager->getRepository(Poll::class);

        try {
            $polls = $repository->findList(
                self::LISTS[$list],
                $member,
                $this->isGranted(Member::ROLE_ADMIN_POLL),
                max(1, $page),
                max(1, $this->getItemsPerPage($member))
            );
        } catch (OutOfRangeCurrentPageException) {
            throw $this->createNotFoundException();
        }

        return $this->render('poll/list.html.twig', [
            'list' => $list,
            'polls' => $polls,
            'submenu' => $this->getSubmenu($list),
        ]);
    }

    #[Route(path: '/polls/create', name: 'polls_create')]
    public function create(Request $request): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $data = new PollDto();
        $form = $this->createForm(PollType::class, $data, ['groups' => $member->getGroups()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $poll = $this->pollModel->create($data, $member);
            $this->addTranslatedFlash('success', 'poll.created');

            return $this->redirectToRoute('polls_update', ['pollId' => $poll->getId()]);
        }

        return $this->render('poll/edit.html.twig', [
            'form' => $form,
            'poll' => null,
            'submenu' => $this->getSubmenu('create'),
        ]);
    }

    #[Route(path: '/polls/update/{pollId}', name: 'polls_update', requirements: ['pollId' => '\d+'])]
    #[IsGranted(PollVoter::EDIT, subject: 'poll')]
    public function edit(Request $request, #[MapEntity(id: 'pollId')] Poll $poll): Response
    {
        $this->pollModel->loadForEditing($poll);
        $data = PollDto::fromEntity($poll);
        $form = $this->createForm(PollType::class, $data, ['edit' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // A poll needs something to choose from before members can vote.
            if (Poll::STATUS_OPEN === $data->status && $poll->getChoices()->count() < 2) {
                $form->get('status')->addError(new FormError($this->getTranslator()->trans('poll.open.needs.choices', [], 'validators')));
            }
            if ($form->isValid()) {
                $this->pollModel->update($poll, $data);
                $this->addTranslatedFlash('success', 'poll.updated');

                return $this->redirectToRoute('polls_update', ['pollId' => $poll->getId()]);
            }
        }

        $choiceForms = [];
        foreach ($poll->getChoices() as $choice) {
            $choiceForms[$choice->getId()] = $this->createForm(PollChoiceType::class, ['text' => $choice->getText()], [
                'action' => $this->generateUrl('poll_choice_edit', ['pollId' => $poll->getId(), 'choiceId' => $choice->getId()]),
            ])->createView();
        }

        return $this->render('poll/edit.html.twig', [
            'form' => $form,
            'poll' => $poll,
            'choiceForms' => $choiceForms,
            'newChoiceForm' => $this->createForm(PollChoiceType::class, null, [
                'action' => $this->generateUrl('poll_choice_add', ['pollId' => $poll->getId()]),
            ])->createView(),
            'submenu' => $this->getSubmenu(null),
        ]);
    }

    #[Route(path: '/polls/update/{pollId}/choices', name: 'poll_choice_add', requirements: ['pollId' => '\d+'], methods: ['POST'])]
    #[IsGranted(PollVoter::EDIT, subject: 'poll')]
    public function addChoice(Request $request, #[MapEntity(id: 'pollId')] Poll $poll): RedirectResponse
    {
        $form = $this->createForm(PollChoiceType::class);
        $form->handleRequest($request);
        if ($this->canChangeChoices($poll) && $form->isSubmitted() && $form->isValid()) {
            $this->pollModel->addChoice($poll, (string) $form->get('text')->getData());
            $this->addTranslatedFlash('success', 'poll.choice.added');
        } else {
            $this->addTranslatedFlash('error', 'poll.choice.not.saved');
        }

        return $this->redirectToRoute('polls_update', ['pollId' => $poll->getId(), '_fragment' => 'choices']);
    }

    #[Route(
        path: '/polls/update/{pollId}/choices/{choiceId}',
        name: 'poll_choice_edit',
        requirements: ['pollId' => '\d+', 'choiceId' => '\d+'],
        methods: ['POST']
    )]
    #[IsGranted(PollVoter::EDIT, subject: 'poll')]
    public function editChoice(
        Request $request,
        #[MapEntity(id: 'pollId')] Poll $poll,
        #[MapEntity(id: 'choiceId')] PollChoice $choice,
    ): RedirectResponse {
        if ($choice->getPoll() !== $poll) {
            throw $this->createNotFoundException();
        }
        $form = $this->createForm(PollChoiceType::class);
        $form->handleRequest($request);
        if ($this->canChangeChoices($poll) && $form->isSubmitted() && $form->isValid()) {
            $this->pollModel->updateChoice($choice, (string) $form->get('text')->getData());
            $this->addTranslatedFlash('success', 'poll.choice.updated');
        } else {
            $this->addTranslatedFlash('error', 'poll.choice.not.saved');
        }

        return $this->redirectToRoute('polls_update', ['pollId' => $poll->getId(), '_fragment' => 'choices']);
    }

    /**
     * The poll with the vote form when the member can vote.
     */
    #[Route(path: '/polls/contribute/{pollId}', name: 'polls_contribute', requirements: ['pollId' => '\d+'])]
    #[IsGranted(PollVoter::VIEW, subject: 'poll')]
    public function show(Request $request, #[MapEntity(id: 'pollId')] Poll $poll): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $voteForm = null;
        if ($this->isGranted(PollVoter::VOTE, $poll)) {
            $form = $this->createForm(PollVoteType::class, null, ['poll' => $poll]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $choices = $form->get('choices')->getData();
                $choices = null === $choices ? [] : (is_iterable($choices) ? iterator_to_array($choices) : [$choices]);
                $comment = $form->has('comment') ? (string) $form->get('comment')->getData() : '';
                $this->pollModel->vote($poll, $member, $choices, $comment);
                $this->addTranslatedFlash('success', 'polls_votedone');

                return $this->redirectToRoute('polls_contribute', ['pollId' => $poll->getId()]);
            }
            $voteForm = $form;
        }

        return $this->render('poll/show.html.twig', [
            'poll' => $poll,
            'voteForm' => $voteForm,
            'hasVoted' => $this->pollModel->hasVoted($poll, $member),
            'submenu' => $this->getSubmenu(null),
        ]);
    }

    #[Route(path: '/polls/cancelvote/{pollId}', name: 'polls_cancelvote', requirements: ['pollId' => '\d+'], methods: ['POST'])]
    #[IsGranted(PollVoter::CANCEL_VOTE, subject: 'poll')]
    #[IsCsrfTokenValid('poll_cancel_vote')]
    public function cancelVote(#[MapEntity(id: 'pollId')] Poll $poll): RedirectResponse
    {
        /** @var Member $member */
        $member = $this->getUser();
        $this->pollModel->cancelVote($poll, $member);
        $this->addTranslatedFlash('notice', 'polls_cancelvote');

        return $this->redirectToRoute('polls_contribute', ['pollId' => $poll->getId()]);
    }

    #[Route(path: '/polls/results/{pollId}', name: 'polls_view_results', requirements: ['pollId' => '\d+'])]
    #[Route(path: '/polls/seeresults/{pollId}', name: 'polls_see_results', requirements: ['pollId' => '\d+'])]
    #[IsGranted(PollVoter::VIEW, subject: 'poll')]
    public function results(#[MapEntity(id: 'pollId')] Poll $poll): Response
    {
        if (!$this->isGranted(PollVoter::RESULTS, $poll)) {
            $this->addTranslatedFlash('notice', 'polls_resultsnotyetavailable');

            return $this->redirectToRoute('polls_contribute', ['pollId' => $poll->getId()]);
        }

        return $this->render('poll/results.html.twig', [
            'poll' => $poll,
            'results' => $this->pollModel->getResults($poll),
            'submenu' => $this->getSubmenu(null),
        ]);
    }

    /**
     * Choices can change while nobody has voted yet (drafts; poll admins also later, as long as there are no votes).
     */
    private function canChangeChoices(Poll $poll): bool
    {
        return $poll->isDraft() || 0 === $poll->getVotes()->count();
    }

    private function getSubmenu(?string $active): array
    {
        $items = [
            'open' => ['key' => 'polls_open', 'url' => $this->generateUrl('polls')],
            'closed' => ['key' => 'polls_closed', 'url' => $this->generateUrl('polls_list_closed')],
            'contributed' => ['key' => 'polls_contributed', 'url' => $this->generateUrl('polls_list_contributed')],
            'new' => ['key' => 'polls.drafts', 'url' => $this->generateUrl('polls_list_new')],
            'all' => ['key' => 'polls.all', 'url' => $this->generateUrl('polls_list_all')],
            'create' => ['key' => 'polls_createlink', 'url' => $this->generateUrl('polls_create')],
        ];

        return ['active' => $active, 'items' => $items];
    }
}
