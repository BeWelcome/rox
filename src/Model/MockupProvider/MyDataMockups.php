<?php

namespace App\Model\MockupProvider;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Activity;
use App\Entity\BroadcastMessage;
use App\Entity\Comment;
use App\Entity\CommunityNews;
use App\Entity\Donations;
use App\Entity\Group;
use App\Entity\GroupMembership;
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
use Mockery;

/**
 * Utility class for the translation interface mockup section.
 *
 * Rather complicated but understandable. No mess detection needed.
 *
 * @SuppressWarnings(PHPMD)
 */
class MyDataMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
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
        'groups' => [
            'type' => 'template',
            'with_parameters' => true,
            'template' => 'private/groups.html.twig',
            'description' => 'Shows the groups of the member',
        ],
    ];
    private InvitationUtility $invitationUtility;

    public function __construct()
    {
        $this->invitationUtility = new InvitationUtility();
    }

    public function getFeature(): string
    {
        return 'my_data';
    }

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
            return $this->getMockEntities($parameters);
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
            case 'groups':
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
                return ['count' => ['none' => '0', 'some' => '2']];
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

    private function getMockEntities(array $parameters): array
    {
        $mockEntity = null;
        $key = $parameters['name'];
        $count = $parameters['count'] ?? -1;
        switch ($key) {
            case 'activities':
                $mockEntity = Mockery::mock(Activity::class, [
                    'getTitle' => 'Activity Title',
                    'getDescription' => 'Activity description',
                ]);
                break;
            case 'broadcasts':
                $mockNewsletter = Mockery::mock(Newsletter::class, [
                    'getTitle' => 'Newsletter Title',
                    'getText' => 'Newsletter text',
                    'getTranslations' => ['en', 'de', 'zh-hant'],
                ]);
                $mockEntity = Mockery::mock(BroadcastMessage::class, [
                    'getNewsletter' => $mockNewsletter,
                    'getTitle' => 'Broadcast Title',
                    'getDescription' => 'Broadcast description',
                    'getUpdated' => new Carbon(),
                ]);
                break;
            case 'comments':
                $mockComment = Mockery::mock(Comment::class, [
                    'getToMember' => $parameters['user'],
                    'getFromMember' => $parameters['admin'],
                    'getQuality' => 'good',
                    'getTextWhere' => 'Somewhere over the rainbow',
                    'getTextFree' => 'I\'m so free',
                    'getCreated' => new Carbon(),
                    'getRelations' => '',
                ]);
                $mockEntity = [
                    'to' => $mockComment,
                    'from' => $mockComment,
                ];
                break;
            case 'communitynews':
                $mockEntity = Mockery::mock(CommunityNews::class, [
                    'getId' => 1,
                    'getTitle' => 'Community News',
                    'getText' => 'Community News text',
                ]);
                break;
            case 'communitynews_comments':
                $key = 'newsAndComments';
                $news = Mockery::mock(CommunityNews::class, [
                    'getId' => 1,
                    'getTitle' => 'Community News',
                    'getText' => 'Community News text',
                ]);
                $mockEntity = [
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
                $mockEntity = Mockery::mock(Donations::class, [
                    'money' => '€',
                    'amount' => 100.00,
                    'namegiven' => 'donor',
                    'referencepaypal' => 'paypal',
                    'systemcomment' => 'system comment',
                    'membercomment' => 'member comment',
                    'statusprivate' => ($parameters['count'] % 2 === 1),
                ]);
                break;
            case 'gallery':
                $key = 'hrefs';
                $mockEntity = 'https://source.unsplash.com/random/300×300';
                break;
            case 'groups':
                return ['groupmemberships' => $this->getGroupMemberships($parameters['count'])];
            case 'logs':
                $mockEntity = Mockery::mock(Log::class, [
                    'getType' => 'log',
                    'getLogMessage' => 'logged message',
                    'getCreated' => new Carbon(),
                ]);
                break;
            case 'newsletters':
                $mockEntity = Mockery::mock(Newsletter::class, [
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
                $mockEntity = $this->getPoll();
                break;
            case 'polls_contributed':
                $key = 'contributions';
                $poll = $this->getPoll();
                $mockEntity = [
                    'poll' => $poll,
                    'comment' => 'comment',
                ];
                break;
            case 'polls_voted':
                $key = 'votes';
                $poll = $this->getPoll();
                $mockEntity = [
                    'poll' => $poll,
                    'pollChoice' => [
                        'texts' => ['one', 'two'],
                    ],
                ];
                break;
            case 'posts':
                return $this->postsData($parameters['count']);
            case 'privileges':
                $mockEntity = $this->getPrivilege();
                break;
            case 'relations':
                $mockEntity = $this->getSpecialRelation();
                break;
            case 'rights':
                // overwrite key as index is different
                $key = 'volunteerrights';
                $mockEntity = Mockery::mock(Right::class, [
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
                $mockEntity = $this->getTrip($parameters['admin']);
                break;
        }

        $entities = [];
        for ($i = 0; $i < $parameters['count']; ++$i) {
            $entities[] = $mockEntity;
        }

        return [
            $key => $entities,
            'member' => $parameters['user'],
        ];
    }

    private function getTrip($host): Trip
    {
        $mockTrip = Mockery::mock(Trip::class, [
            'getId' => 1,
            'getCreator' => $host,
            'getSummary' => 'Mocking Bird',
            'getDescription' => 'Mocking description',
            'getCountOfTravellers' => 2,
            'getAdditionalInfo' => TripAdditionalInfoType::NONE,
            'getCreated' => new DateTime(),
        ]);
        $country = Mockery::mock(Location::class, [
            'getId' => 1,
            'getName' => 'Mocking Republic',
        ]);
        $location = Mockery::mock(Location::class, [
            'getId' => 1,
            'getName' => 'Mock',
            'getCountry' => $country,
        ]);

        $leg = Mockery::mock(SubTrip::class, [
            'getId' => 1,
            'getArrival' => Carbon::instance(new DateTime('2021-02-22')),
            'getDeparture' => Carbon::instance(new DateTime('2021-02-24')),
            'getOptions' => [SubtripOptionsType::MEET_LOCALS],
            'getLocation' => $location,
            'getTrip' => $mockTrip,
            'getInvitedBy' => $host,
        ]);
        $mockTrip->shouldReceive('addSubtrip')
            ->once()
            ->with($leg)
            ->andReturn($mockTrip)
        ;
        $mockTrip->addSubtrip($leg);
        $mockTrip
            ->shouldReceive('getSubtrips')
            ->andReturn(new ArrayCollection([$leg]))
        ;

        return $mockTrip;
    }

    private function getPoll(): Poll
    {
        return Mockery::mock(Poll::class, [
            'getId' => 1,
            'getTitles' => ['title'],
            'getDescriptions' => [
                'en' => 'description',
                'fr' => 'description',
            ],
            'getGroups' => [
                Mockery::mock(Group::class, [
                    'getName' => 'group',
                ]),
            ],
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

        $leg = $this->invitationUtility->getLeg($parameters);
        $thread = $this->invitationUtility->getThread($host, $guest, $leg, HostingRequest::REQUEST_TENTATIVELY_ACCEPTED, 4);

        return [
            'message' => $thread[0],
            $parameters['name'] => $thread[0],
            'leg' => $leg,
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

    private function getGroupMemberships(int $count): array
    {
        $groupMemberships = [];
        for ($index = 0; $index < $count; $index++) {
             $groupMemberships[] = Mockery::mock(GroupMembership::class, [
                 'getGroup' => Mockery::mock(Group::class, [
                     'getName' => 'group #' . ($index + 1),
                     'getId' => ($index + 1),
                 ]),
                 'getCreated' => new DateTime(),
                 'getStatus' => GroupMembershipStatusType::CURRENT_MEMBER,
                 'getComments' => [
                     [
                        'code' => 'mydata.translations.headline',
                        'Sentence' => 'Translations',
                        'shortCode' => 'en',
                        'Language' => ['WordCode' => 'lang_pt'],
                        'created' => (new DateTime())->format('Y-m-d'),
                    ],
                    [
                        'code' => 'mydata.translations.abstract',
                        'Sentence' => 'Traducions',
                        'shortCode' => 'en',
                        'Language' => ['WordCode' => 'lang_es'],
                        'created' => (new DateTime())->format('Y-m-d'),
                    ],
                 ],
             ]);
        }

        return $groupMemberships;
    }
}
