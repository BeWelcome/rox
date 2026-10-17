<?php

namespace App\Model\MockupProvider;

use App\Doctrine\CommentRelationsType;
use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Activity;
use App\Entity\BroadcastMessage;
use App\Entity\Comment;
use App\Entity\CommunityNews;
use App\Entity\Donation;
use App\Entity\Group;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Log;
use App\Entity\Newsletter;
use App\Entity\Poll;
use App\Entity\PollChoice;
use App\Entity\Right;
use App\Entity\Shout;
use App\Entity\Subtrip;
use App\Entity\Trip;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Override;
use RuntimeException;

/**
 * Utility class for the translation interface mockup section.
 *
 * Rather complicated but understandable. No mess detection is needed.
 *
 * @SuppressWarnings(PHPMD)
 */
class MyDataMockups extends AbstractMockups
{
    protected const string FEATURE = 'mydata';

    protected const array MOCKUPS = [
        'start page' => [
            'type' => 'template',
            'template' => 'private/index.html.twig',
            'description' => 'Index page of the data dump created by /mydata (profile)',
        ],
        'footer' => [
            'type' => 'template',
            'template' => 'private/footer.html.twig',
            'description' => 'Footer included on every page of the generated data including the date and time.',
        ],
        'message' => [
            'type' => 'template',
            'template' => 'private/message.html.twig',
            'description' => 'Statistics for messages in the data dump',
        ],
        'messages' => [
            'type' => 'template',
            'template' => 'private/messages.html.twig',
            'description' => 'Statistics for messages in the data dump',
        ],
        'request' => [
            'type' => 'template',
            'template' => 'private/request.html.twig',
            'description' => 'Statistics for requests in the data dump',
        ],
        'requests' => [
            'type' => 'template',
            'template' => 'private/requests.html.twig',
            'description' => 'Statistics for requests in the data dump',
        ],
        'invitation' => [
            'type' => 'template',
            'template' => 'private/invitation.html.twig',
            'description' => 'Statistics for invitations in the data dump',
        ],
        'invitations' => [
            'type' => 'template',
            'template' => 'private/invitations.html.twig',
            'description' => 'Statistics for invitations in the data dump',
        ],
        'profile' => [
            'type' => 'template',
            'template' => 'private/profile.html.twig',
            'description' => 'Your profile in the data dump',
        ],
        'polls' => [
            'type' => 'template',
            'template' => 'private/polls.html.twig',
            'description' => 'Overview page for polls',
        ],
        'post (thread)' => [
            'type' => 'template',
            'with_parameters' => true,
            'template' => 'private/post.html.twig',
            'description' => 'Shows a single post to groups or forum',
        ],
        'post (group)' => [
            'type' => 'template',
            'with_parameters' => true,
            'template' => 'private/post.html.twig',
            'description' => 'Shows a single post to groups or forum',
        ],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InvitationUtility $invitationUtility,
    ) {
    }

    #[Override]
    public function getMockups(): array
    {
        $mockups = self::MOCKUPS;
        foreach ($this->getExtractedEntities() as $key) {
            $mockups[$key] = [
                'type' => 'template',
                'with_parameters' => true,
                'template' => 'private/' . $key . '.html.twig',
                'description' => 'Resulting page for the own data export',
            ];
        }

        return $mockups;
    }

    public function getMockupVariables(array $parameters): array
    {
        $name = $parameters['name'];
        if ('post (thread)' === $name) {
            return $this->getPostInForum($parameters);
        }
        if ('post (group)' === $name) {
            return $this->getPostInGroup($parameters);
        }
        if ('message' === $name || 'request' === $name || 'invitation' === $name) {
            return $this->getMessage($parameters);
        }

        if (isset($parameters['count'])) {
            return $this->getEntities($parameters);
        }

        $extracted = $this->getExtractedEntities();

        $member = $parameters['user'];

        return [
            'extracted' => $extracted,
            'member' => $member,
            'date_generated' => new DateTime(),
            'profilepicture' => '/members/avatar/' . $member->getUsername() . '/48',
            'messagesSent' => 2,
            'messagesReceived' => 3,
            'requestsSent' => 4,
            'requestsReceived' => 5,
            'invitationsSent' => 6,
            'invitationsReceived' => 7,
        ];
    }

    /**
     * SuppressWarnings(PHPMD.CyclomaticComplexity).
     */
    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        switch ($feature) {
            case 'post (thread)':
            case 'post (group)':
                return ['deleted' => ['yes' => 'yes', 'no' => 'no']];
            case 'broadcasts':
            case 'comments':
            case 'communitynews':
            case 'communitynews_comments':
            case 'donations':
            case 'gallery':
            case 'logs':
            case 'newsletters':
            case 'pictures':
            case 'polls_contributed':
            case 'polls_created':
            case 'polls_voted':
            case 'relations':
            case 'rights':
            case 'privileges':
            case 'trips':
            case 'shouts':
            case 'subscriptions':
            case 'translations':
                return ['count' => ['none' => '0', 'some' => '3']];
        }

        switch ($locale) {
            case 'en':
            case 'de':
            case 'fr':
            case 'es':
            case 'pt':
            case 'pt-br':
            case 'gl':
                return [
                    'count' => [
                        'none' => 0,
                        'one' => 1,
                        'some' => 3,
                    ],
                ];
            case 'pl':
                return [
                    'count' => [
                        'none' => 0,
                        'one' => 1,
                        'few' => 2,
                        'other' => 5,
                    ],
                ];
        }

        return [
            'count' => [
                'none' => 0,
                'one' => 1,
                'few' => 2,
                'other' => 5,
            ],
        ];
    }

    private function getExtractedEntities(): array
    {
        return [
            'activities',
            'broadcasts',
            'comments',
            'communitynews',
            'communitynews_comments',
            'donations',
            'gallery',
            'logs',
            'newsletters',
            'pictures',
            'polls_contributed',
            'polls_created',
            'polls_voted',
            'posts',
            'privileges',
            'relations',
            'rights',
            'trips',
            'shouts',
            'subscriptions',
            'translations',
        ];
    }

    private function getEntities(array $parameters): array
    {
        $entity = null;
        $key = $parameters['name'];
        $count = $parameters['count'] ?? -1;
        switch ($key) {
            case 'activities':
                $entity = (new Activity())
                    ->setTitle('Activity Title')
                    ->setDescription('Activity description')
                ;
                break;
            case 'broadcasts':
                // \todo We assume there is a newsletter in the database here: make sure to seed one into
                // the test database (no problem on prod)
                $newsletters = $this->entityManager->getRepository(Newsletter::class)->findAll();
                $newsletter = reset($newsletters);
                if (false === $newsletter) {
                    throw new RuntimeException('No newsletter found');
                }

                $entity = (new BroadcastMessage())
                    ->setNewsletter($newsletter)
                    ->setUpdated(new Carbon())
                ;
                break;
            case 'comments':
                $comment = (new Comment())
                    ->setToMember($parameters['user'])
                    ->setFromMember($parameters['admin'])
                    ->setQuality('good')
                    ->setTextWhere('Somewhere over the rainbow')
                    ->setTextfree('I\'m so free')
                    ->setCreated(new Carbon())
                    ->setRelations(CommentRelationsType::ONLINE_COMMUNICATION)
                ;
                $entity = [
                    'to' => $comment,
                    'from' => $comment,
                ];
                break;
            case 'communitynews':
                $entity = (new CommunityNews())
                    ->setTitle('Community News')
                    ->setText('Community News text')
                ;
                $this->setId($entity, 1);

                break;
            case 'communitynews_comments':
                $key = 'newsAndComments';
                $news = (new CommunityNews())
                    ->setTitle('Community News')
                    ->setText('Community News text')
                ;
                $this->setId($news, 1);
                $entity = [
                    'news' => $news,
                    'comments' => [
                        [
                            'title' => 'Comment title',
                            'text' => 'Comment text',
                        ],
                    ],
                ];
                break;
            case 'donations':
                $entity = (new Donation())
                    ->setMoney('€')
                    ->setAmount(100.00)
                    ->setNamegiven('donor')
                    ->setReferencePaypal('paypal')
                    ->setSystemComment('system comment')
                    ->setMemberComment('member comment')
                    ->setStatusPrivate($parameters['count'] % 2 === 1)
                ;
                break;
            case 'gallery':
                $key = 'hrefs';
                $entity = 'https://source.unsplash.com/random/300×300';
                break;
            case 'logs':
                $entity = (new Log())
                    ->setType('log')
                    ->setLogMessage('logged message')
                    ->setCreated(new Carbon())
                ;
                break;
            case 'newsletters':
                // Leave mockery here for now to ensure that mydata.newsletter.no.title and
                // mydata.newsletter.no.body can be translated
                $entity = Mockery::mock(Newsletter::class, [
                    'getTitle' => 'Newsletter Title',
                    'getText' => 'Newsletter text',
                    'getId' => 1,
                    'getCreated' => new Carbon(),
                    'getTranslations' => [
                        [
                            'locale' => 'en',
                            'author' => $parameters['user'],
                            'title' => 'Title - en',
                            'body' => 'Body - en',
                        ],
                        [
                            'locale' => 'zh-hant',
                            'author' => $parameters['user'],
                            'title' => 'Title - zh-hant',
                        ],
                        [
                            'locale' => 'de',
                            'author' => $parameters['user'],
                            'body' => 'Body - de',
                        ],
                    ],
                ]);
                break;
            case 'pictures':
                // Nothing to do here; included twig template will show the needed amount of
                // profile pictures as broken images
                break;
            case 'polls_created':
                $key = 'polls';
                $entity = $this->getPoll();
                break;
            case 'polls_contributed':
                $key = 'contributions';
                $poll = $this->getPoll();
                $entity = [
                    'poll' => $poll,
                    'comment' => 'comment',
                ];
                break;
            case 'polls_voted':
                $key = 'votes';
                $poll = $this->getPoll();
                $entity = [
                    'poll' => $poll,
                    'pollChoice' => [
                        'texts' => ['one', 'two'],
                    ],
                ];
                break;
            case 'posts':
                return $this->postsData($parameters['count']);
            case 'privileges':
                $entity = $this->getPrivilege();
                break;
            case 'relations':
                $entity = $this->getSpecialRelation();
                break;
            case 'rights':
                // overwrite key as index is different
                $key = 'volunteerrights';
                $entity = Mockery::mock(Right::class, [
                    'right' => [
                        'name' => 'distinct right',
                    ],
                    'scope' => 'All',
                    'level' => '10',
                    'getCreated' => new Carbon(),
                    'getDescription' => 'Right Description',
                ]);
                break;
            case 'shouts':
                return $this->getShouts($count);
            case 'subscriptions':
                return $this->getSubscriptions($count);
            case 'translations':
                return $this->getTranslations($count);
            case 'trips':
                $entity = $this->getTrip($parameters['admin']);
                break;
        }

        $entities = [];
        for ($i = 0; $i < $parameters['count']; ++$i) {
            $entities[] = $entity;
        }

        return [
            $key => $entities,
            'member' => $parameters['user'],
        ];
    }

    private function getTrip($host): Trip
    {
        return $this->invitationUtility->getLeg($host)->getTrip();
    }

    private function getPoll(): Poll
    {
        $group = (new Group())
            ->setName('group')
        ;

        return Mockery::mock(Poll::class, [
            'getId' => 1,
            'getTitles' => ['title'],
            'getDescriptions' => [
                'en' => 'description',
                'fr' => 'description',
            ],
            'getGroups' => $group,
            'getChoices' => [
                Mockery::mock(PollChoice::class, [
                    'getTexts' => [
                        'en' => 'English',
                        'fr' => 'French',
                    ],
                ]),
            ],
        ]);
    }

    private function postsData($count): array
    {
        if (0 === $count) {
            return ['years' => []];
        }

        return [
            'posts_written' => $count,
            'threads_contributed' => $count,
            'years' => [2021],
            'threadsPerYear' => [
                2021 => $count,
            ],
            'postsPerYear' => [
                2021 => $count,
            ],
        ];
    }

    private function postsYearData($count)
    {
        if (0 === $count) {
            return ['year' => 2010];
        }
        $posts = [];
        for ($i = 0; $i < $count; ++$i) {
            $posts[$i] = [
                'created' => 12345,
            ];
        }
        $threads = [];
        for ($i = 0; $i < $count; ++$i) {
            $threads[] = [
                'thread' => [
                    'title' => 'Thread title ' . $i,
                ],
                'posts' => $posts,
            ];
        }

        return [
            'year' => (new DateTime())->format('Y'),
            'post_count' => $count,
            'threads' => $threads,
        ];
    }

    private function getPostInForum(array $parameters): array
    {
        return [
            'thread' => [
                'title' => 'Thread Title',
                'id' => 8,
            ],
            'group' => 0,
            'post' => [
                'id' => 123,
                'created' => 123456,
                'deleted' => 'yes' === $parameters['deleted'] ? 'Deleted' : 'NotDeleted',
                'language' => [
                    'wordcode' => 'lang_zh-hant',
                    'shortCode' => 'zh-hant',
                ],
                'message' => 'Post text',
                'messageTranslations' => [
                    'fr' => [
                        'message' => 'Post Text (fr)',
                        'language' => [
                            'wordcode' => 'lang_fr',
                            'shortCode' => 'fr',
                        ],
                    ],
                    'zh-hant' => [
                        'message' => 'Post Text (zh-hant)',
                        'language' => [
                            'wordcode' => 'lang_zh-hant',
                            'shortCode' => 'zh-hant',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getPostInGroup(array $parameters): array
    {
        $result = $this->getPostInForum($parameters);
        $result['group'] = [
            'id' => 70,
            'name' => 'Group name',
        ];

        return $result;
    }

    private function getMessage(array $parameters): array
    {
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $thread = $this->invitationUtility->getThread($host, $guest, null, HostingRequest::REQUEST_TENTATIVELY_ACCEPTED, 4);

        return [
            'message' => $thread[0],
            $parameters['name'] => $thread[0],
            'host' => $host,
            'guest' => $guest,
            'thread' => $thread,
            'is_spam' => false,
            'show_deleted' => false,
        ];
    }

    private function getPrivilege(): array
    {
        return [
            'privilege' => 'Very big privilege',
            'scope' => 'All',
            'role' => 'Role',
            'assigned' => new DateTime(),
        ];
    }

    private function getSpecialRelation(): array
    {
        $relation = [
            'owner' => [
                'username' => 'admin',
            ],
            'relation' => [
                'username' => 'user',
            ],
            'type' => 'family',
            'confirmed' => 'yes',
            'comments' => [
                [
                    'language' => ['wordcode' => 'lang_el'],
                    'sentence' => 'Comment text',
                ],
            ],
        ];

        return [
            'left' => $relation,
            'right' => $relation,
        ];
    }

    private function getShouts($count): array
    {
        if (0 === $count) {
            return ['shouts' => []];
        }

        return [
            'shouts' => [
                [
                    'title' => 'Shout Title',
                    'text' => 'Image',
                    'table' => Shout::GALLERY_ITEM,
                    'tableId' => 'Image',
                ],
                [
                    'text' => 'Gallery',
                    'table' => Shout::GALLERY,
                    'tableId' => 'Gallery',
                ],
                [
                    'text' => 'Group',
                    'table' => Shout::GROUP,
                    'tableId' => 'Group',
                ],
                [
                    'text' => 'Trip',
                    'table' => Shout::TRIP,
                    'tableId' => 'Trip',
                ],
            ],
        ];
    }

    private function getSubscriptions($count): array
    {
        if (0 === $count) {
            return ['subscriptions' => []];
        }

        return [
            'subscriptions' => [
                [
                    'thread' => [
                        'id' => 1,
                        'title' => 'Thread title',
                    ],
                    'subscribed' => new DateTime(),
                    'notificationsEnabled' => true,
                ],
                [
                    'thread' => [
                        'id' => 2,
                        'title' => 'Thread title',
                    ],
                    'subscribed' => new DateTime(),
                    'notificationsEnabled' => false,
                ],
            ],
        ];
    }

    private function getTranslations($count): array
    {
        if (0 === $count) {
            return ['translations' => []];
        }

        return [
            'translations' => [
                [
                    'code' => 'mydata.translations.headline',
                    'sentence' => 'Translations',
                    'shortCode' => 'en',
                    'language' => ['wordCode' => 'lang_pt'],
                    'created' => (new DateTime())->format('Y-m-d'),
                ],
                [
                    'code' => 'mydata.translations.abstract',
                    'sentence' => 'Traducions',
                    'shortCode' => 'en',
                    'language' => ['wordCode' => 'lang_es'],
                    'created' => (new DateTime())->format('Y-m-d'),
                ],
            ],
        ];
    }
}
