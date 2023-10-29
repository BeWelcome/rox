<?php

namespace App\Controller\Admin;

use App\Doctrine\SubtripOptionsType;
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
use App\Model\TranslationModel;
use App\Twig\MockupExtension;
use Carbon\Carbon;
use DateTime;
use Mockery;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TranslationController.
 *
 * @SuppressWarnings(PHPMD)
 */
class MockupController extends TranslationController
{
    private $mockups = [];
    /** @var iterable|MockupProviderInterface[] */
    private $providers;

    public function __construct(TranslationModel $translationModel, string $locales, iterable $providers)
    {
        parent::__construct($translationModel, $locales);

        foreach ($providers as $provider) {
            $feature = $provider->getFeature();
            $this->providers[$feature] = $provider;
            $this->mockups = array_merge([$feature => $provider->getMockups()], $this->mockups);
        }
    }

    /**
     * @Route("/admin/translations/mockups", name="translations_mockups")
     */
    public function selectMockup(Request $request): Response
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($request->getLocale())) {
            return $this->redirectToRoute('translations_no_permissions');
        }

        return $this->render('admin/translations/mockups.html.twig', [
            'features' => array_keys($this->mockups),
            'submenu' => [
                'active' => 'mockups',
                'items' => $this->getSubmenuItems($request->getLocale()),
            ],
        ]);
    }

    /**
     * @Route("/admin/translations/mockups/{feature}", name="translations_mockups_feature")
     */
    public function selectMockupsFeature(Request $request, string $feature): Response
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        if (!\array_key_exists($feature, $this->providers)) {
            return $this->redirectToRoute('translations_mockups');
        }

        /** @var Member $translator */
        $translator = $this->getUser();
        if (!$translator->hasRightsForLocale($request->getLocale())) {
            return $this->redirectToRoute('translations_no_permissions');
        }

        return $this->render('admin/translations/feature.html.twig', [
            'feature' => $feature,
            'provider' => $this->providers[$feature],
            'submenu' => [
                'active' => 'mockups',
                'items' => $this->getSubmenuItems($request->getLocale()),
            ],
        ]);
    }

    /**
     * @Route("/admin/translate/mockup/{feature}/{name}", name="translation_mockup",
     *     requirements={"template"=".+"})
     *
     * @return Response
     */
    public function translateMockup(Request $request, string $feature, string $name)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        return $this->renderMockup($feature, $name, $request->getLocale(), ['name' => $name]);
    }

    /**
     * @Route("/admin/translate/mockup/{feature}/with_params", name="translation_mockup_with_parameters",
     *     methods={"POST"},
     *     priority=1
     * )
     *
     * @return Response
     */
    public function translateMockupWithParameters(Request $request, string $feature)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $name = $request->request->get('name');
        if (null === $name) {
            $this->redirectToRoute('translations_mockups');
        }

        $parameters = $request->request->all();

        return $this->renderMockup($feature, $name, $request->getLocale(), $parameters);
    }

    private function getMockTemplateParams($template, $name = null): array
    {
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
            'member' => $this->getUser(),
            'profilepicture' => '/members/avatar/' . $this->getUser()->getUsername() . '/48',
        ];

        if (false === strpos($name, 'some')) {
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
        // Use the bwAdmin account as counter part for all of this
        $memberRepository = $this->getDoctrine()->getRepository(Member::class);
        $bwAdmin = $memberRepository->find(1);

        // Use a public group like Berlin
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
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
                $formFactory = $this->get('form.factory');
                $searchFormRequest = new SearchFormRequest($this->getDoctrine()->getManager());
                $searchFormRequest->show_map = true;
                $searchFormRequest->accommodation_neverask = true;
                $searchFormRequest->inactive = true;
                $searchFormRequest->distance = 100;
                $searchForm = $formFactory->createNamed('map', SearchFormType::class, $searchFormRequest, [
                    'action' => '/search/map',
                ]);

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
                $params['sender'] = $this->getUser();
                $params['receiver'] = $bwAdmin;
                break;
            case 'emails/request.html.twig':
            case 'emails/reply_from_guest.html.twig':
                $params['host'] = $bwAdmin;
                $params['sender'] = $this->getUser();
                $params['receiver'] = $bwAdmin;
                $params['receiverLocale'] = 'en';
                $params['changed'] = true;
                break;
            case 'emails/reply_from_host.html.twig':
                $params['host'] = $bwAdmin;
                $params['sender'] = $bwAdmin;
                $params['receiver'] = $this->getUser();
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
                $params['receiver'] = $this->getUser();
                $params['group'] = $group;
                $params['subject'] = 'group.invitation';
                $params['reason'] = 'I just want to be a member of something.';
                break;
            case 'emails/reset.password.html.twig':
                $params['sender'] = $bwAdmin;
                $params['receiver'] = $this->getUser();
                $params['token'] = '91aeecc7154b8fc9b2855a331e975bc8aafb088b6617d9aefe543e5fee427ae7';
                break;
            case 'emails/notifications.html.twig':
                if ('forum post' === substr($name, 0, 10)) {
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
                } elseif ('group post' === substr($name, 0, 10)) {
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
                if (false !== strpos($name, 'not')) {
                    $subscription = 0;
                } else {
                    $subscription = 123456;
                }
                $params['sender'] = $bwAdmin;
                $params['receiver'] = $this->getUser();
                $params['notification'] = [
                    'post' => $mockPost,
                    'subscription' => $subscription,
                ];
                break;
            case 'security/login.html.twig':
                $params['error'] = null;
                $params['last_username'] = $this->getUser()->getUsername();
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

        return [
            'username' => $this->getUser()->getUsername(),
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

        switch ($indicator) {
            case '(regular)':
                $type = Newsletter::REGULAR_NEWSLETTER;
                break;
            case '(specific)':
                $type = Newsletter::SPECIFIC_NEWSLETTER;
                break;
            case '(terms of use)':
                $type = Newsletter::TERMS_OF_USE;
                break;
            default:
                $type = Newsletter::TERMS_OF_USE;
        }

        $newsletterRepository = $this->getDoctrine()->getRepository(Newsletter::class);
        $newsletters = $newsletterRepository->findBy(['type' => $type], ['created' => 'DESC']);

        if (0 === \count($newsletters)) {
            throw new \Exception('Sorry, no newsletter of type ' . $type . ' found, please create one.');
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
        $leg1 = Mockery::mock(SubTrip::class, [
            'getId' => 1,
            'getArrival' => Carbon::instance(new DateTime('2021-02-22')),
            'getDeparture' => Carbon::instance(new DateTime('2021-02-24')),
            'getOptions' => [SubtripOptionsType::MEET_LOCALS],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $this->getUser(),
        ]);
        $leg2 = Mockery::mock(SubTrip::class, [
            'getId' => 2,
            'getArrival' => Carbon::instance(new DateTime('2021-03-23')),
            'getDeparture' => Carbon::instance(new DateTime('2021-03-24')),
            'getOptions' => [SubtripOptionsType::LOOKING_FOR_HOST],
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
        $memberRepository = $this->getDoctrine()->getRepository(Member::class);
        $bwAdmin = $memberRepository->find(1);

        return [
            'admin' => $bwAdmin,
            'user' => $this->getUser(),
        ];
    }

    private function renderMockup(string $feature, string $name, string $language, array $parameters): Response
    {
        $mockup = $this->mockups[$feature][$name] ?? null;
        if (null === $mockup) {
            return $this->redirectToRoute('translations_mockups');
        }

        $url = $mockup['url'] ?? '';
        $template = $mockup['template'];
        $description = $mockup['description'] ?? '';

        $parameters = array_merge($parameters, $this->getGeneralMockupParameters());
        $variables = $this->providers[$feature]->getMockupVariables($parameters);

        return $this->render(
            'admin/translations/mockup.page_with_parameters.html.twig',
            array_merge(
                $variables,
                [
                    'url' => $url,
                    'name' => $parameters['fullname'] ?? $name,
                    'feature' => $feature,
                    'description' => $description,
                    'template' => $template,
                    'html_template' => $template,
                    'email' => new MockupExtension(),
                    'language' => $language,
                    'submenu' => [
                        'active' => 'mockups',
                        'items' => $this->getSubmenuItems($language, 'mockup', $name),
                    ],
                ]
            )
        );
    }
}
