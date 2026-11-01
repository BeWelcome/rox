<?php

namespace App\Controller\Admin;

use App\Doctrine\LegOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Activity;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Newsletter;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\NewsletterUnsubscribeType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Form\SearchFormType;
use App\Model\MockupProvider\MockupProviderInterface;
use App\Model\Translation\MockupPreview;
use App\Model\Translation\TranslationTool;
use App\Model\TranslationModel;
use App\Twig\MockupExtension;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Throwable;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings("PHPMD")
 */
class MockupController extends TranslationController
{
    private $mockups = [];
    /** @var iterable|MockupProviderInterface[] */
    private $providers;

    public function __construct(
        TranslationModel $translationModel,
        EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        array $locales,
        iterable $providers,
    ) {
        parent::__construct($translationModel, $entityManager, $locales);

        foreach ($providers as $provider) {
            $feature = $provider->getFeature();
            $this->providers[$feature] = $provider;
            $this->mockups = array_merge([$feature => $provider->getMockups()], $this->mockups);
        }
    }

    /**
     * The features with their mails and pages, the first one open.
     */
    #[Route(path: '/admin/translations/mockups', name: 'translations_mockups')]
    public function selectMockup(Request $request, TranslationTool $translationTool): Response
    {
        return $this->showMockups($request, $translationTool, null);
    }

    /**
     * The features, this one open: its mails and pages, each to preview in the site's language.
     */
    #[Route(path: '/admin/translations/mockups/{feature}', name: 'translations_mockups_feature')]
    public function selectMockupsFeature(Request $request, string $feature, TranslationTool $translationTool): Response
    {
        if (!\array_key_exists($feature, $this->providers)) {
            return $this->redirectToRoute('translations_mockups');
        }

        return $this->showMockups($request, $translationTool, $feature);
    }

    /**
     * A mail or a page with test data, as members get it, its texts listed for translation below it. Variants are
     * picked by their parameter in the query (?count=2).
     */
    #[Route(path: '/admin/translate/mockup/{feature}/{name}', name: 'translation_mockup', methods: ['GET'])]
    public function translateMockup(
        Request $request,
        string $feature,
        string $name,
        TranslationTool $translationTool,
        MockupPreview $mockupPreview,
        LoggerInterface $logger,
    ): Response {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $mockup = $this->mockups[$feature][$name] ?? null;
        if (null === $mockup) {
            return $this->redirectToRoute('translations_mockups_feature', ['feature' => $feature]);
        }

        /** @var Member $translator */
        $translator = $this->getUser();
        $variants = $this->getVariants($feature, $name, $request->getLocale());
        $variant = $this->getVariant($request, $variants);

        // a broken mockup (its test data or its template) shows as such: the translator can report it
        $preview = null;
        try {
            $parameters = array_merge(['name' => $name], $variant['parameters'] ?? [], $this->getGeneralMockupParameters());
            $variables = $this->providers[$feature]->getMockupVariables($parameters);
            if ('email' === $mockup['type']) {
                // the mailer's wrapped email: images are links to the site instead of attachments
                $variables['email'] = new MockupExtension();
            }
            $preview = $mockupPreview->render($mockup['template'], $variables);
        } catch (Throwable $exception) {
            $logger->error('Mockup {feature}/{name} failed: {message}', [
                'feature' => $feature,
                'name' => $name,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
        }

        return $this->render('admin/translations/tool/mockup.html.twig', [
            'locale' => $this->getToolLocale($request, $translator),
            'languageMenu' => $this->getToolLanguages($translationTool, $translator),
            'feature' => $feature,
            'name' => $name,
            'mockup' => $mockup,
            'variants' => $variants,
            'variant' => $variant,
            'preview' => $preview,
        ]);
    }

    private function showMockups(Request $request, TranslationTool $translationTool, ?string $feature): Response
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($request->getLocale())) {
            return $this->redirectToRoute('translations_no_permissions');
        }

        $features = array_keys($this->providers);
        sort($features);
        $feature ??= $features[0];

        return $this->render('admin/translations/tool/mockups.html.twig', [
            'locale' => $this->getToolLocale($request, $translator),
            'languageMenu' => $this->getToolLanguages($translationTool, $translator),
            'features' => array_map(fn (string $name): array => [
                'name' => $name,
                'count' => \count($this->providers[$name]->getMockups()),
            ], $features),
            'feature' => $feature,
            'provider' => $this->providers[$feature],
        ]);
    }

    private function getMockTemplateParams($template, $name = null): array
    {
        /** @var Member $member */
        $member = $this->getUser();
        $params = [
            'extracted' => [
                'activities',
                'broadcasts',
                'comments',
                'communitynews',
                'communitynews_comments',
                'donations',
                'gallery',
                'logs',
                'messages',
                'newsletters',
                'pictures',
                'polls',
                'polls_contributed',
                'polls_created',
                'polls_voted',
                'posts',
                'privileges',
                'relations',
                'requests',
                'rights',
                'shouts',
                'subscriptions',
                'subscriptions',
                'translations',
            ],
            'member' => $member,
            'profilepicture' => '/members/avatar/' . $member->getUsername() . '/48',
        ];

        if (!str_contains((string) $name, 'some')) {
            $params['activities'] = [];
        } else {
            $mockActivity = Mockery::mock(Activity::class, [
                'getTitle' => 'Activity Title',
                'getDescription' => 'Activity Description',
            ]);
            $params['activities'] = [
                0 => $mockActivity,
                1 => $mockActivity,
            ];
        }

        return $params;
    }

    private function getMockParams($template, $name = null): array
    {
        /** @var Member $member */
        $member = $this->getUser();
        // Use the bwAdmin account as counterpart for all of this
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $bwAdmin = $memberRepository->find(1);

        // Use a public group like Berlin
        $groupRepository = $this->entityManager->getRepository(Group::class);
        $group = $groupRepository->find(70);

        $mockMessage = Mockery::mock(Message::class, [
            'getId' => 1,
            'getMessage' => 'Message text',
        ]);
        $mockMessage->shouldReceive('getSender')->andReturn($this->getUser());
        $mockMessage->shouldReceive('getReceiver')->andReturn($bwAdmin);

        $mockRequest = Mockery::mock(HostingRequest::class, [
            'getId' => 1,
            'getArrival' => new Carbon(),
            'getDeparture' => new Carbon(),
            'getNumberOfTravellers' => 2,
            'getFlexible' => true,
            'getStatus' => HostingRequest::REQUEST_DECLINED,
        ]);
        $mockMessage->shouldReceive('getSender')->andReturn($this->getUser());
        $mockMessage->shouldReceive('getReceiver')->andReturn($bwAdmin);

        $params = [
            'html_template' => $template,
            'username' => 'username',
            'email_address' => 'mockup@example.com',
            'subject' => 'Subject',
            'email' => new MockupExtension(),
            'message' => $mockMessage,
            'request' => $mockRequest,
        ];
        switch ($template) {
            case 'home/home.html.twig':
                $searchFormRequest = new SearchFormRequest();
                $searchFormRequest->show_map = true;
                $searchFormRequest->accommodation_no = true;
                $searchFormRequest->distance = 100;
                $searchForm = $this->formFactory->createNamed(
                    'map',
                    SearchFormType::class,
                    $searchFormRequest,
                    [
                        'action' => '/search/map',
                    ]
                );

                $usernameForm = $this->createFormBuilder()
                    ->add('username', TextType::class, [
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ])
                    ->getForm()
                ;
                $params['stats'] = [
                    'members' => 100000,
                    'languages' => 210,
                    'countries' => 192,
                    'comments' => 50000,
                    'activities' => 1300,
                ];
                $params['username'] = $usernameForm->createView();
                $params['search'] = $searchForm->createView();
                break;
            case 'emails/message.html.twig':
                $params['sender'] = $member;
                $params['receiver'] = $bwAdmin;
                break;
            case 'emails/request.html.twig':
            case 'emails/reply_from_guest.html.twig':
                $params['host'] = $bwAdmin;
                $params['sender'] = $member;
                $params['receiver'] = $bwAdmin;
                $params['receiverLocale'] = 'en';
                $params['changed'] = true;
                break;
            case 'emails/reply_from_host.html.twig':
                $params['host'] = $bwAdmin;
                $params['sender'] = $bwAdmin;
                $params['receiver'] = $member;
                $params['receiverLocale'] = 'en';
                $params['changed'] = true;
                break;
            case 'emails/group/invitation.html.twig':
            case 'emails/group/accepted.invite.html.twig':
            case 'emails/group/approve.join.html.twig':
            case 'emails/group/declined.invite.html.twig':
            case 'emails/group/wantin.html.twig':
            case 'emails/group/join.approved.html.twig':
            case 'emails/group/join.declined.html.twig':
                $params['sender'] = $bwAdmin;
                $params['admin'] = $bwAdmin;
                $params['receiver'] = $member;
                $params['group'] = $group;
                $params['subject'] = 'group.invitation';
                $params['reason'] = 'I just want to be a member of something.';
                break;
            case 'emails/reset.password.html.twig':
                $params['sender'] = $bwAdmin;
                $params['receiver'] = $member;
                $params['token'] = '91aeecc7154b8fc9b2855a331e975bc8aafb088b6617d9aefe543e5fee427ae7';
                break;
            case 'emails/notifications.html.twig':
                $mockPost = null;
                $mockThread = null;
                if (str_starts_with((string) $name, 'forum post')) {
                    $mockThread = Mockery::mock(ForumThread::class, [
                        'getId' => 1,
                        'getGroup' => null,
                        'getTitle' => 'Thread title',
                    ]);

                    $mockPost = Mockery::mock(ForumPost::class, [
                        'getId' => 1,
                        'getMessage' => 'Post text',
                        'getThread' => $mockThread,
                    ]);
                } elseif (str_starts_with((string) $name, 'group post')) {
                    $mockThread = Mockery::mock(ForumThread::class, [
                        'getId' => 1,
                        'getGroup' => $group,
                        'getTitle' => 'Thread title',
                    ]);

                    $mockPost = Mockery::mock(ForumPost::class, [
                        'getId' => 1,
                        'getMessage' => 'Post text',
                        'getThread' => $mockThread,
                    ]);
                }
                if (str_contains((string) $name, 'not')) {
                    $subscription = 0;
                } else {
                    $subscription = 123456;
                }
                $params['sender'] = $bwAdmin;
                $params['receiver'] = $member;
                $params['notification'] = [
                    'post' => $mockPost,
                    'subscription' => $subscription,
                ];
                break;
            case 'security/login.html.twig':
                $params['error'] = null;
                $params['last_username'] = $member->getUsername();
                $params['invalid_credentials'] = false;
                $params['resend_confirmation'] = false;
                $params['member_banned'] = false;
                $params['member_expired'] = false;
                $params['member_not_allowed_to_login'] = false;
                break;
            case 'member/request.password.reset.html.twig':
                $params['form'] = $this->createForm(ResetPasswordRequestFormType::class)->createView();
                break;
            case 'member/reset.password.html.twig':
                $params['form'] = $this->createForm(ResetPasswordFormType::class)->createView();
                break;
            case 'policies/tou_translated.html.twig':
                $params['policy_english'] = 'terms';
                break;
            case 'policies/pp_translated.html.twig':
                $params['policy_english'] = 'privacy';
                break;
            case 'policies/dp_translated.html.twig':
                $params['policy_english'] = 'datarights';
                break;
            default:
                $params['host'] = $bwAdmin;
                break;
        }

        return $params;
    }

    private function getUnsubscribeParameters(string $template, string $name)
    {
        $unsubscribeForm = $this->createForm(NewsletterUnsubscribeType::class);
        /** @var Member $member */
        $member = $this->getUser();

        return [
            'username' => $member->getUsername(),
            'form' => $unsubscribeForm->createView(),
        ];
    }

    private function getSignupParameters(string $template, string $name)
    {
        /** @var Member $user */
        $user = $this->getUser();

        return [
            'username' => $user->getUsername(),
            'gender' => $user->getGender(),
            'key' => hash('sha256', $user->getUsername()),
            'email_address' => $user->getEmail(),
        ];
    }

    private function getNewsletterParameters(string $template, string $name)
    {
        $indicator = substr($name, strpos($name, '('));

        $type = match ($indicator) {
            '(regular)' => Newsletter::REGULAR_NEWSLETTER,
            '(specific)' => Newsletter::SPECIFIC_NEWSLETTER,
            '(terms of use)' => Newsletter::TERMS_OF_USE,
            default => Newsletter::TERMS_OF_USE,
        };

        $newsletterRepository = $this->entityManager->getRepository(Newsletter::class);
        $newsletters = $newsletterRepository->findBy(['type' => $type], ['created' => 'DESC']);

        if (0 === \count($newsletters)) {
            throw new Exception('Sorry, no newsletter of type ' . $type . ' found, please create one.');
        }

        return [
            'html_template' => $template,
            'wordcode' => strtolower('broadcast_body_' . $newsletters[0]->getName()),
            'unsubscribe_key' => '91aeecc7154b8fc9b2855a331e975bc8aafb088b6617d9aefe543e5fee427ae7',
            'newsletter' => $newsletters[0],
            'receiver' => $this->getUser(),
        ];
    }

    private function getGeneratedDate(string $template, string $name)
    {
        return [
            'date_generated' => new DateTime(),
        ];
    }

    private function getTripsWidgetEmpty(string $template, string $name)
    {
        return [
            'legs' => null,
            'radius' => 10,
        ];
    }

    private function getTripsWidgetTwoLegs(string $template, string $name)
    {
        $trip = Mockery::mock(Trip::class, [
            'getId' => 1,
            'getCreator' => $this->getUser(),
            'getSummary' => 'Mocking Bird',
            'getDescription' => 'Mocking description',
            'getCountOfTravellers' => 2,
            'getAdditionalInfo' => TripAdditionalInfoType::NONE,
            'getCreated' => new DateTime(),
        ]);
        $location = Mockery::mock(Location::class, [
            'getId' => 1,
            'getName' => 'Mock',
        ]);
        $leg1 = Mockery::mock(Subtrip::class, [
            'getId' => 1,
            'getArrival' => Carbon::instance(new DateTime('2021-02-22')),
            'getDeparture' => Carbon::instance(new DateTime('2021-02-24')),
            'getOptions' => [LegOptionsType::MEET_LOCALS],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $this->getUser(),
        ]);
        $leg2 = Mockery::mock(Subtrip::class, [
            'getId' => 2,
            'getArrival' => Carbon::instance(new DateTime('2021-03-23')),
            'getDeparture' => Carbon::instance(new DateTime('2021-03-24')),
            'getOptions' => [LegOptionsType::LOOKING_FOR_HOST],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $this->getUser(),
        ]);

        return [
            'legs' => [
                $leg1,
                $leg2,
            ],
            'radius' => 10,
        ];
    }

    private function findMockup(string $name, string $type): ?array
    {
        $found = null;
        foreach ($this->mockups as $key => $mockups) {
            foreach (array_keys($mockups) as $mockupName) {
                if ($name === $mockupName) {
                    $found = $this->mockups[$key][$mockupName];
                    break;
                }
            }
            if (null !== $found) {
                break;
            }
        }

        return $found;
    }

    private function getGeneralMockupParameters(): array
    {
        // Use the bwAdmin account as counterpart
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $bwAdmin = $memberRepository->find(1);

        return [
            'admin' => $bwAdmin,
            'user' => $this->getUser(),
        ];
    }

    /**
     * The variants of a mockup with parameters: a label and the parameters of each.
     *
     * @return list<array{label: string, parameters: array<string, string>}>
     */
    private function getVariants(string $feature, string $name, string $locale): array
    {
        if (true !== ($this->mockups[$feature][$name]['with_parameters'] ?? false)) {
            return [];
        }

        $variants = [];
        foreach ($this->providers[$feature]->getMockupParameter($locale, $name) as $parameter => $values) {
            foreach ($values as $label => $value) {
                $variants[] = [
                    'label' => ucfirst((string) $parameter) . ': ' . $label,
                    'parameters' => [$parameter => (string) $value],
                ];
            }
        }

        return $variants;
    }

    /**
     * The variant the query asks for, the first one otherwise; null for a mockup without variants.
     *
     * @param list<array{label: string, parameters: array<string, string>}> $variants
     *
     * @return array{label: string, parameters: array<string, string>}|null
     */
    private function getVariant(Request $request, array $variants): ?array
    {
        foreach ($variants as $variant) {
            $asked = array_intersect_key($request->query->all(), $variant['parameters']);
            if ($asked === $variant['parameters']) {
                return $variant;
            }
        }

        return $variants[0] ?? null;
    }
}
