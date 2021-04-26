<?php

namespace App\Controller\Admin;

use App\Entity\Activity;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Newsletter;
use App\Form\CustomDataClass\SearchFormRequest;
use App\Form\NewsletterUnsubscribeType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Form\SearchFormType;
use App\Twig\MockupExtension;
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
    private const MOCKUPS = [
        'signup' => [
            'signup_finish' => [
                'type' => 'page',
                'url' => 'signup/finish',
                'template' => 'signup/finish.html.twig',
                'description' => 'Successful signup.',
            ],
            'signup_error' => [
                'type' => 'page',
                'url' => 'signup/finish',
                'template' => 'signup/error.html.twig',
                'description' => 'Error during signup.',
            ],
        ],
        'error' => [
            'error 403' => [
                'type' => 'page',
                'template' => 'bundles/TwigBundle/Exception/error403.html.twig',
                'description' => 'Access to a resource was denied.',
            ],
            'error 404' => [
                'type' => 'page',
                'template' => 'bundles/TwigBundle/Exception/error404.html.twig',
                'description' => 'The page doesn\'t exists.',
            ],
            'error 500' => [
                'type' => 'page',
                'template' => 'bundles/TwigBundle/Exception/error500.html.twig',
                'description' => 'A server problem (something bad happened).',
            ],
        ],
        'home and login' => [
            'homepage' => [
                'type' => 'page',
                'url' => '/',
                'template' => 'home/home.html.twig',
                'description' => 'The page that is shown to unauthenticated visitors.',
            ],
            'Login' => [
                'type' => 'page',
                'url' => '/login',
                'template' => 'security/login.html.twig',
                'description' => 'The login page (without error message)',
            ],
        ],
        'policies' => [
            'Terms of Use' => [
                'type' => 'page',
                'url' => 'terms',
                'template' => 'policies/tou_translated.html.twig',
                'description' => 'The terms of use. Make sure to translate them fully before asking for publication.',
            ],
            'Privacy Policy' => [
                'type' => 'page',
                'url' => 'privacy_policy',
                'template' => 'policies/pp_translated.html.twig',
                'description' => 'The privacy policy. Make sure to translate them fully before asking for publication.',
            ],
            'Data Privacy' => [
                'type' => 'page',
                'url' => 'datarights/',
                'template' => 'policies/dp_translated.html.twig',
                'description' => 'The data privacy policy. Make sure to translate them fully before asking for publication.',
            ],
        ],
        'password' => [
            'Reset Password Request' => [
                'type' => 'page',
                'url' => '/resetpassword',
                'template' => 'member/request.password.reset.html.twig',
                'description' => 'The page that is shown when a member asks for a new password',
            ],
            'Reset Password Email' => [
                'type' => 'email',
                'template' => 'emails/reset.password.html.twig',
                'description' => 'Mail send to the user when a password reset request was done',
            ],
        ],
        'Newsletter' => [
            'Newsletter (regular)' => [
                'type' => 'email',
                'template' => 'emails/newsletter.html.twig',
                'description' => 'Email send to users who signed up for newsletters',
                'setup' => 'getNewsletterParameters',
            ],
            'Newsletter (specific)' => [
                'type' => 'email',
                'template' => 'emails/newsletter.html.twig',
                'description' => 'Email send to users who signuped for local event notifications',
                'setup' => 'getNewsletterParameters',
            ],
            'Newsletter (terms of use)' => [
                'type' => 'email',
                'template' => 'emails/newsletter.html.twig',
                'description' => 'Email send to users who signuped for local event notifications',
                'setup' => 'getNewsletterParameters',
            ],
        ],
        'Newsletter unsubscribe' => [
            'Unsubscribe Newsletter' => [
                'type' => 'page',
                'url' => '/unsubscribe/newsletter/{username}/{token}',
                'template' => 'newsletter/unsubscribe_confirm.html.twig',
                'description' => 'Shown to a user when following the link in a regular newsletter',
                'setup' => 'getUnsubscribeParameters',
            ],
            'Unsubscribe Newsletter Success' => [
                'type' => 'page',
                'url' => '/unsubscribe/local/{username}/{token}',
                'template' => 'newsletter/unsubscribe_local_successful.html.twig',
                'description' => 'The page that is shown when a member unsubscribed without issues from a local event newsletter',
                'setup' => 'getUnsubscribeParameters',
            ],
            'Unsubscribe Newsletter Failed' => [
                'type' => 'page',
                'url' => '/unsubscribe/local/{username}/{token}',
                'template' => 'newsletter/unsubscribe_local_failed.html.twig',
                'description' => 'The page that is shown when a member unsubscribed with issues from a local event newsletter',
                'setup' => 'getUnsubscribeParameters',
            ],
        ],
        'Specific Newsletter Unsubscribe' => [
            'Unsubscribe Local Event' => [
                'type' => 'page',
                'url' => '/unsubscribe/local/{username}/{token}',
                'template' => 'newsletter/unsubscribe_local_confirm.html.twig',
                'description' => 'Shown to a user when following the link in a local events newsletter',
                'setup' => 'getUnsubscribeParameters',
            ],
            'Unsubscribe Local Success' => [
                'type' => 'page',
                'url' => '/unsubscribe/local/{username}/{token}',
                'template' => 'newsletter/unsubscribe_local_successful.html.twig',
                'description' => 'The page that is shown when a member unsubscribed without issues from a local event newsletter',
                'setup' => 'getUnsubscribeParameters',
            ],
            'Unsubscribe Local Failed' => [
                'type' => 'page',
                'url' => '/unsubscribe/local/{username}/{token}',
                'template' => 'newsletter/unsubscribe_local_failed.html.twig',
                'description' => 'The page that is shown when a member unsubscribed with issues from a local event newsletter',
                'setup' => 'getUnsubscribeParameters',
            ],
        ],
        'Message' => [
            'message' => [
                'type' => 'email',
                'template' => 'emails/message.html.twig',
            ],
        ],
        'Requests' => [
            'request (initial)' => [
                'type' => 'email',
                'template' => 'emails/request.html.twig',
            ],
            'request (guest)' => [
                'type' => 'email',
                'template' => 'emails/reply_from_guest.html.twig',
            ],
            'request (host)' => [
                'type' => 'email',
                'template' => 'emails/reply_from_host.html.twig',
            ],
        ],
        'Groups' => [
            'group invitation' => [
                'type' => 'email',
                'template' => 'emails/group/invitation.html.twig',
            ],
            'group want in' => [
                'type' => 'email',
                'template' => 'emails/group/wantin.html.twig',
            ],
            'accepted invite' => [
                'type' => 'email',
                'template' => 'emails/group/accepted.invite.html.twig',
            ],
            'declined invite' => [
                'type' => 'email',
                'template' => 'emails/group/declined.invite.html.twig',
            ],
            'join approved' => [
                'type' => 'email',
                'template' => 'emails/group/join.approved.html.twig',
            ],
            'join declined' => [
                'type' => 'email',
                'template' => 'emails/group/join.declined.html.twig',
            ],
            'group post (subscribed)' => [
                'type' => 'email',
                'template' => 'emails/notifications.html.twig',
            ],
            'group post (not subscribed)' => [
                'type' => 'email',
                'template' => 'emails/notifications.html.twig',
            ],
        ],
        'Forums' => [
            'forum post' => [
                'type' => 'email',
                'template' => 'emails/notifications.html.twig',
            ],
        ],
        'My data' => [
            'start page' => [
                'type' => 'template',
                'template' => 'private/index.html.twig',
                'description' => 'Index page of the data dump created by /mydata (profile)',
            ],
            'activities, none' => [
                'type' => 'template',
                'template' => 'private/activities.html.twig',
                'description' => 'Resulting page for the own data export with no activities',
            ],
            'mydata (activities, some)' => [
                'type' => 'template',
                'template' => 'private/activities.html.twig',
                'description' => 'Resulting page for the own data export with some activities',
            ],
            'mydata (profile)' => [
                'type' => 'template',
                'template' => 'private/profile.html.twig',
                'description' => 'Your profile in the data dump',
            ],
        ],
    ];

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
            'mockups' => self::MOCKUPS,
            'submenu' => [
                'active' => 'mockups',
                'items' => $this->getSubmenuItems($request->getLocale()),
            ],
        ]);
    }

    /**
     * @Route("/admin/translate/mockup/page/{name}", name="translation_mockup_page",
     *     requirements={"template"=".+"})
     *
     * @return Response
     */
    public function translateMockupPage(Request $request, string $name)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $mockup = $this->findMockup($name, 'page');
        if (null === $mockup) {
            return $this->redirectToRoute('translations_mockups');
        }

        $url = $mockup['url'] ?? '';
        $template = $mockup['template'];
        $description = $mockup['description'] ?? '';
        $setupFunction = $mockup['setup'] ?? 'getMockParams';
        $parameters = \call_user_func([$this, $setupFunction], $template, $name);

        return $this->render(
            'admin/translations/mockup.page.html.twig',
            array_merge(
                $parameters,
                [
                    'url' => $url,
                    'description' => $description,
                    'template' => $template,
                    'html_template' => $template,
                    'submenu' => [
                        'active' => 'mockups',
                        'items' => $this->getSubmenuItems($request->getLocale(), 'mockup', $name),
                    ],
                ]
            ),
        );
    }

    /**
     * @Route("/admin/translate/mockup/email/{name}", name="translation_mockup_email")
     *
     * @return Response
     */
    public function translateMockupEmail(Request $request, string $name)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $mockup = $this->findMockup($name, 'email');
        if (null === $mockup) {
            return $this->redirectToRoute('translations_mockups');
        }

        $template = $mockup['template'];
        $description = $mockup['description'] ?? '';
        $setupFunction = $mockup['setup'] ?? 'getMockParams';
        $parameters = \call_user_func([$this, $setupFunction], $template, $name);

        return $this->render(
            'admin/translations/mockup.email.html.twig',
            array_merge(
                $parameters,
                [
                    'template' => $template,
                    'language' => $request->getLocale(),
                    'email' => new MockupExtension(),
                    'description' => $description,
                    'submenu' => [
                        'active' => 'mockups',
                        'items' => $this->getSubmenuItems($request->getLocale(), 'mockup', $template),
                    ],
                ]
            ),
        );
    }

    /**
     * @Route("/admin/translate/mockup/template/{name}", name="translation_mockup_template",
     *     requirements={"template"=".+"})
     *
     * @return Response
     */
    public function translateMockupTemplate(Request $request, string $name)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_ADMIN_WORDS, null, 'Unable to access this page!');

        $mockup = $this->findMockup($name, 'template');
        if (null === $mockup) {
            return $this->redirectToRoute('translations_mockups');
        }

        $template = $mockup['template'];
        $description = $mockup['description'] ?? '';
        $parameters = $this->getMockTemplateParams($template, $name);

        return $this->render(
            'admin/translations/mockup.template.html.twig',
            array_merge(
                $parameters,
                [
                    'description' => $description,
                    'template' => $template,
                    'submenu' => [
                        'active' => 'mockups',
                        'items' => $this->getSubmenuItems($request->getLocale(), 'mockup', $name),
                    ],
                ]
            ),
        );
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
                'posts_year',
                'privileges',
                'profile',
                'relations',
                'requests',
                'rights',
                'shouts',
                'subscriptions',
                'subscriptions',
                'translations',
            ],
            'member' => $this->getUser(),
            'profilepicture' => '/members/avatar/' . $this->getUser()->getUsername() . '/50',
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
            'getArrival' => new DateTime(),
            'getDeparture' => new DateTime(),
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

    private function findMockup(string $name, string $type): ?array
    {
        $found = null;
        foreach (self::MOCKUPS as $key => $mockups) {
            foreach (array_keys($mockups) as $mockupName) {
                if ($name === $mockupName && self::MOCKUPS[$key][$mockupName]['type'] === $type) {
                    $found = self::MOCKUPS[$key][$mockupName];
                    break;
                }
            }
            if (null !== $found) {
                break;
            }
        }

        return $found;
    }
}
