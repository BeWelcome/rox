<?php

namespace App\Controller\Admin;

use App\Entity\BrowserPushNotification;
use App\Entity\LoginMessage;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\UploadedImage;
use App\Form\ChangeUsernameFormType;
use App\Form\CustomDataClass\Tools\ChangeUsernameRequest;
use App\Form\CustomDataClass\Tools\FindUserRequest;
use App\Form\FindUserFormType;
use App\Form\LoginMessageType;
use App\Logger\Logger;
use App\Repository\BrowserPushNotificationRepository;
use App\Repository\MemberRepository;
use App\Repository\MessageRepository;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * The tools of the volunteers (templates/admin/tools): a sidebar with the tools grouped (Members, Spam checks,
 * Community, Site), each tool in a card next to it. TOOLS says which rights open a tool; the header shows the
 * volunteer's rights that open any.
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class VolunteerToolController extends AbstractController
{
    use TranslatorTrait;

    /** The CSRF token id of expiring a login message. */
    public const string LOGIN_MESSAGE_TOKEN = 'login_message_expire';

    /**
     * The tools in the order of the sidebar: route, icon, group and the rights that open it.
     *
     * @var array<string, array{route: string, icon: string, group: string, rights: list<string>}>
     */
    private const array TOOLS = [
        'find_member' => ['route' => 'admin_tools_find_user', 'icon' => 'magnifying-glass', 'group' => 'members',
            'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_PROFILE, Member::ROLE_ADMIN_ACCEPTER]],
        'change_username' => ['route' => 'admin_tools_change_username', 'icon' => 'pen', 'group' => 'members',
            'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_PROFILE]],
        'messages_by_member' => ['route' => 'admin_tools_messages_by_member', 'icon' => 'comments', 'group' => 'members',
            'rights' => [Member::ROLE_ADMIN_SAFETYTEAM]],
        'messages_sent' => ['route' => 'admin_tools_messages_sent', 'icon' => 'envelope', 'group' => 'spam',
            'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_PROFILE]],
        'requests_sent' => ['route' => 'admin_tools_requests_sent', 'icon' => 'bed', 'group' => 'spam',
            'rights' => [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_PROFILE]],
        'top_spammers' => ['route' => 'admin_tools_top_spammer', 'icon' => 'ban', 'group' => 'spam',
            'rights' => [Member::ROLE_ADMIN_CHECKER]],
        'damage_done' => ['route' => 'admin_tools_damage_done', 'icon' => 'triangle-exclamation', 'group' => 'spam',
            'rights' => [Member::ROLE_ADMIN_CHECKER]],
        'age_by_country' => ['route' => 'admin_tools_age_by_country', 'icon' => 'chart-simple', 'group' => 'community',
            'rights' => [Member::ROLE_ADMIN_ACCEPTER, Member::ROLE_ADMIN_CHECKER, Member::ROLE_ADMIN_ADMIN]],
        'uploaded_images' => ['route' => 'admin_tools_uploaded_images', 'icon' => 'images', 'group' => 'site',
            'rights' => [Member::ROLE_ADMIN_ADMIN]],
        'login_messages' => ['route' => 'admin_tools_login_messages_show', 'icon' => 'bullhorn', 'group' => 'site',
            'rights' => [Member::ROLE_ADMIN_ADMIN]],
    ];

    /** The rights that open tools, as the header names them. */
    private const array RIGHTS = [
        Member::ROLE_ADMIN_SAFETYTEAM => 'admin.tools.right.safetyteam',
        Member::ROLE_ADMIN_PROFILE => 'admin.tools.right.profile',
        Member::ROLE_ADMIN_ACCEPTER => 'admin.tools.right.accepter',
        Member::ROLE_ADMIN_CHECKER => 'admin.tools.right.checker',
        Member::ROLE_ADMIN_ADMIN => 'admin.tools.right.admin',
    ];

    /** Columns of Age by country it can be sorted by. */
    private const array AGE_SORTS = ['Name', 'Count', 'BirthYear', 'Age'];

    #[Route(path: '/admin/tools', name: 'admin_volunteer_tools')]
    public function showOverview(): RedirectResponse
    {
        $tools = $this->allowedTools();
        if ([] === $tools) {
            throw $this->createAccessDeniedException('admin.tools.not.allowed');
        }

        return $this->redirectToRoute(self::TOOLS[array_key_first($tools)]['route']);
    }

    #[Route(path: '/admin/tools/findmember', name: 'admin_tools_find_user')]
    public function findUser(Request $request, EntityManagerInterface $entityManager, Logger $logger): Response
    {
        $this->denyUnlessAllowed('find_member');

        $form = $this->createForm(FindUserFormType::class, new FindUserRequest());
        $form->handleRequest($request);

        $members = null;
        $term = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $term = $form->getData()->term;
            $logger->write('Searched for members using search term: ' . $term . '.', 'adminquery');

            /** @var MemberRepository $memberRepository */
            $memberRepository = $entityManager->getRepository(Member::class);
            $members = $memberRepository->findByProfileInfo($term);
        }

        return $this->tool('find_member', 'admin/tools/find.user.html.twig', [
            'form' => $form,
            'members' => $members,
            'term' => $term,
        ]);
    }

    #[Route(path: '/admin/tools/change', name: 'admin_tools_change_username')]
    public function changeUsername(Request $request, EntityManagerInterface $entityManager, Logger $logger): Response
    {
        $this->denyUnlessAllowed('change_username');

        $form = $this->createForm(ChangeUsernameFormType::class, new ChangeUsernameRequest());
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // both names are checked, each once its own field is fine: all errors show at once
            $data = $form->getData();
            $memberRepository = $entityManager->getRepository(Member::class);
            $member = null;
            if (0 === \count($form->get('oldUsername')->getErrors())) {
                $member = $memberRepository->findOneBy(['username' => $data->oldUsername]);
                if (null === $member) {
                    $form->get('oldUsername')->addError(new FormError('admin.tools.change_username.error.unknown'));
                }
            }
            $newUsernameFine = 0 === \count($form->get('newUsername')->getErrors());
            if ($newUsernameFine && null !== $memberRepository->findOneBy(['username' => $data->newUsername])) {
                $form->get('newUsername')->addError(new FormError('admin.tools.change_username.error.taken'));
            }

            if (null !== $member && $form->isValid()) {
                $logger->write(
                    'Changed member username from ' . $data->oldUsername . ' to ' . $data->newUsername . '.',
                    'adminquery'
                );
                $entityManager->getConnection()->transactional(static function () use ($data, $entityManager, $member): void {
                    /** @var BrowserPushNotificationRepository $browserNotificationRepository */
                    $browserNotificationRepository = $entityManager->getRepository(BrowserPushNotification::class);
                    $browserNotificationRepository->updateSenderUsername($data->oldUsername, $data->newUsername);
                    $member->setUsername($data->newUsername);
                    $entityManager->flush();
                });
                $this->addFlash('notice', $this->getTranslator()->trans('admin.tools.change_username.done', [
                    'old' => $data->oldUsername,
                    'new' => $data->newUsername,
                ]));

                return $this->redirectToRoute('admin_tools_change_username');
            }
        }

        return $this->tool('change_username', 'admin/tools/change.username.html.twig', [
            'form' => $form,
        ], $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK);
    }

    #[Route(path: '/admin/tools/messages/member', name: 'admin_tools_messages_by_member')]
    public function showMessagesByMember(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('messages_by_member');

        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'label' => 'admin.tools.username',
                'attr' => ['class' => 'member-autocomplete'],
                'constraints' => [new NotBlank()],
            ])
            ->setMethod('POST')
            ->getForm();
        $form->handleRequest($request);

        $results = [];
        $member = null;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MemberRepository $memberRepository */
            $memberRepository = $entityManager->getRepository(Member::class);
            $member = $memberRepository->findOneBy(['username' => $form->getData()['username']]);
            if (null === $member) {
                $form->get('username')->addError(new FormError('admin.tools.change_username.error.unknown'));
            } else {
                /** @var MessageRepository $messageRepository */
                $messageRepository = $entityManager->getRepository(Message::class);
                $results = $this->correspondents($member, $messageRepository->findAllMessagesWithMember($member));
            }
        }

        return $this->tool('messages_by_member', 'admin/tools/messages.by.member.html.twig', [
            'form' => $form,
            'member' => $member,
            'results' => $results,
        ]);
    }

    #[Route(path: '/admin/tools/messages/sent', name: 'admin_tools_messages_sent')]
    public function showMessagesLastWeekAction(EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('messages_sent');

        return $this->tool('messages_sent', 'admin/tools/ranked.html.twig', [
            'results' => $entityManager->getRepository(Message::class)->findFrequentSenders(7, requestsOnly: false),
            'unit' => 'admin.tools.messages_sent.unit',
        ]);
    }

    #[Route(path: '/admin/tools/requests/sent', name: 'admin_tools_requests_sent')]
    public function showRequestsLastTwoWeeks(EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('requests_sent');

        return $this->tool('requests_sent', 'admin/tools/ranked.html.twig', [
            'results' => $entityManager->getRepository(Message::class)->findFrequentSenders(14, requestsOnly: true),
            'unit' => 'admin.tools.requests_sent.unit',
        ]);
    }

    #[Route(path: '/admin/tools/topspammer', name: 'admin_tools_top_spammer')]
    public function showTopSpammer(EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('top_spammers');

        // the banned members with the number of messages they sent in the last two months
        return $this->tool('top_spammers', 'admin/tools/top.spammer.html.twig', [
            'messagesSent' => $entityManager->getRepository(Message::class)->findTopSpammers(),
        ]);
    }

    #[Route(path: '/admin/tools/damagedone', name: 'admin_tools_damage_done')]
    public function showDamageDone(EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('damage_done');

        return $this->tool('damage_done', 'admin/tools/damage.done.html.twig', [
            'damageDone' => $entityManager->getRepository(Message::class)->findDamageDone(),
        ]);
    }

    /**
     * The countries with their members' average birth year and age; ?sort= a column, ?dir=asc or desc.
     */
    #[Route(path: '/admin/tools/countryage', name: 'admin_tools_age_by_country')]
    public function showAverageAgePerCountryAction(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('age_by_country');

        $sort = \in_array($request->query->getString('sort'), self::AGE_SORTS, true) ? $request->query->getString('sort') : 'Count';
        $ascending = 'asc' === $request->query->getString('dir', 'Name' === $sort ? 'asc' : 'desc');
        $results = $entityManager->getRepository(Member::class)->getAgeByCountry();
        usort($results, static function (array $a, array $b) use ($sort, $ascending): int {
            $order = \is_string($a[$sort]) ? strcoll($a[$sort], $b[$sort]) : $a[$sort] <=> $b[$sort];

            return $ascending ? $order : -$order;
        });

        return $this->tool('age_by_country', 'admin/tools/age.country.html.twig', [
            'results' => $results,
            'sort' => $sort,
            'ascending' => $ascending,
        ]);
    }

    #[Route(path: '/admin/tools/uploaded_images/{page}', name: 'admin_tools_uploaded_images')]
    public function showUploadedImages(EntityManagerInterface $entityManager, int $page = 1): Response
    {
        $this->denyUnlessAllowed('uploaded_images');

        $query = $entityManager->getRepository(UploadedImage::class)->createQueryBuilder('i')->orderBy('i.created', 'desc');
        $images = new Pagerfanta(new QueryAdapter($query));
        $images->setMaxPerPage(60);
        $images->setNormalizeOutOfRangePages(true);
        $images->setCurrentPage(max(1, $page));

        return $this->tool('uploaded_images', 'admin/tools/uploaded.images.html.twig', [
            'results' => $images,
        ]);
    }

    #[Route(path: '/admin/tools/login_messages/show', name: 'admin_tools_login_messages_show')]
    public function showLoginMessages(EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('login_messages');

        $loginMessages = $entityManager->getRepository(LoginMessage::class)->findBy([], ['expires' => 'DESC', 'id' => 'DESC']);
        $now = new DateTime();

        return $this->tool('login_messages', 'admin/tools/login.messages.show.html.twig', [
            'login_messages' => $loginMessages,
            'active' => \count(array_filter($loginMessages, static fn (LoginMessage $message): bool => $message->getExpires() > $now)),
            'now' => $now,
        ]);
    }

    #[Route(path: '/admin/tools/login_message/add', name: 'admin_tools_login_message_add')]
    public function addLoginMessage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('login_messages');

        $form = $this->createForm(LoginMessageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($form->getData());
            $entityManager->flush();
            $this->addFlash('notice', $this->getTranslator()->trans('admin.tools.login_message.added'));

            return $this->redirectToRoute('admin_tools_login_messages_show');
        }

        return $this->tool('login_messages', 'admin/tools/login.message.html.twig', [
            'form' => $form,
        ], $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK, 'login_message_add');
    }

    #[Route(
        path: '/admin/tools/login_message/{id}/expire',
        name: 'admin_tools_login_message_expire',
        methods: ['POST'],
    )]
    #[IsCsrfTokenValid(self::LOGIN_MESSAGE_TOKEN)]
    public function expireLoginMessage(LoginMessage $loginMessage, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessAllowed('login_messages');

        $loginMessage->setExpires(new DateTime());
        $entityManager->flush();
        $this->addFlash('notice', $this->getTranslator()->trans('admin.tools.login_message.expired'));

        return $this->redirectToRoute('admin_tools_login_messages_show');
    }

    /**
     * Renders a tool in the layout: the sidebar with the volunteer's tools, the header with their rights.
     *
     * @param array<string, mixed> $parameters
     * @param string|null          $page       the page within the tool (its title and description), if not the tool
     */
    private function tool(string $tool, string $template, array $parameters, int $status = Response::HTTP_OK, ?string $page = null): Response
    {
        $groups = [];
        foreach ($this->allowedTools() as $key => $definition) {
            $groups[$definition['group']][$key] = $definition;
        }
        $rights = array_values(array_filter(
            self::RIGHTS,
            fn (string $label, string $role): bool => $this->isGranted($role),
            \ARRAY_FILTER_USE_BOTH,
        ));

        return $this->render($template, $parameters + [
            'tool' => $tool,
            'page' => $page ?? $tool,
            'tool_groups' => $groups,
            'tool_rights' => $rights,
            'inline_notices' => true,
        ], new Response(null, $status));
    }

    /**
     * @return array<string, array{route: string, icon: string, group: string, rights: list<string>}> the tools the
     *                                                                                                volunteer may use
     */
    private function allowedTools(): array
    {
        return array_filter(self::TOOLS, function (array $tool): bool {
            foreach ($tool['rights'] as $role) {
                if ($this->isGranted($role)) {
                    return true;
                }
            }

            return false;
        });
    }

    private function denyUnlessAllowed(string $tool): void
    {
        if (!\array_key_exists($tool, $this->allowedTools())) {
            throw $this->createAccessDeniedException('admin.tools.not.allowed');
        }
    }

    /**
     * Everyone the member exchanged messages with: the kinds (1 message, 2 invitation, 4 stay request), the direction
     * (1 received, 2 sent) and the last of each, the latest first.
     *
     * @param iterable<Message> $messages
     *
     * @return list<array{username: string, type: int, direction: int, last_sent: ?DateTime, last_received: ?DateTime}>
     */
    private function correspondents(Member $member, iterable $messages): array
    {
        $results = [];
        foreach ($messages as $message) {
            $received = $message->getSender() !== $member;
            $username = ($received ? $message->getSender() : $message->getReceiver())->getUsername();
            $result = $results[$username] ?? ['username' => $username, 'type' => 0, 'direction' => 0, 'last_sent' => null, 'last_received' => null];
            $result['type'] |= $this->getConversationType($message);
            $result['direction'] |= $received ? 1 : 2;
            $last = $received ? 'last_received' : 'last_sent';
            if ($message->getCreated() > $result[$last]) {
                $result[$last] = $message->getCreated();
            }
            $results[$username] = $result;
        }
        $latest = static fn (array $result): ?DateTime => max($result['last_sent'], $result['last_received']);
        usort($results, static fn (array $a, array $b): int => $latest($b) <=> $latest($a));

        return $results;
    }

    private function getConversationType(Message $message): int
    {
        if (null === $message->getRequest()) {
            return 1;
        }

        // an invitation, else a stay request
        return null !== $message->getRequest()->getInviteForLeg() ? 2 : 4;
    }
}
