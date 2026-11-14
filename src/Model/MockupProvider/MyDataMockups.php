<?php

namespace App\Model\MockupProvider;

use App\Doctrine\CommentQualityType;
use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\LegOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Activity;
use App\Entity\BroadcastMessage;
use App\Entity\Comment;
use App\Entity\CommunityNews;
use App\Entity\Donation;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\HostingRequest;
use App\Entity\Leg;
use App\Entity\Location;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\Poll;
use App\Entity\PollChoice;
use App\Entity\Right;
use App\Entity\RightVolunteer;
use App\Entity\Trip;
use App\Model\MemberDataExtractor\MyDataSections;
use Carbon\Carbon;
use DateTime;

/**
 * Utility class for the translation interface mockup section.
 *
 * Rather complicated but understandable. No mess detection needed.
 *
 * @SuppressWarnings("PHPMD")
 */
class MyDataMockups implements MockupProviderInterface
{
    use MockupEntities;

    /** How many items the sections of the mockups have (the sidebar, the overview) */
    private const array SAMPLE_COUNTS = [
        'profile' => 1, 'gallery' => 4, 'trips' => 2, 'relations' => 3, 'comments' => 5,
        'messages' => 12, 'messages_sent' => 40, 'messages_received' => 52,
        'requests' => 3, 'requests_sent' => 4, 'requests_received' => 5,
        'invitations' => 1, 'invitations_sent' => 6, 'invitations_received' => 7,
        'groups' => 4, 'posts' => 18, 'subscriptions' => 6, 'polls' => 2, 'communitynews_comments' => 2,
        'logs' => 30, 'browser_push_subscriptions' => 1,
    ];

    private const array MOCKUPS = [
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
    private readonly InvitationUtility $invitationUtility;

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
        // every page has the sidebar with the sections and the member in the top bar (MemberModel puts them in
        // once the export is written; here they come right away)
        return array_merge(
            ['mydata_sections' => MyDataSections::withCounts(self::SAMPLE_COUNTS), 'member' => $parameters['user'], 'picture' => null],
            $this->pageVariables($parameters),
        );
    }

    /**
     * SuppressWarnings(PHPMD.CyclomaticComplexity).
     */
    public function getMockupParameter(?string $locale = null, ?string $feature = null): array
    {
        return match ($feature) {
            'post (thread)', 'post (group)' => ['deleted' => ['yes' => 'yes', 'no' => 'no']],
            'broadcasts', 'comments', 'communitynews', 'communitynews_comments', 'donations', 'gallery', 'logs', 'newsletters', 'groups', 'polls_contributed', 'polls_created', 'polls_voted', 'relations', 'rights', 'privileges', 'trips', 'subscriptions', 'translations' => ['count' => ['none' => '0', 'some' => '2']],
            default => match ($locale) {
                'en', 'de', 'fr', 'es', 'pt', 'pt-br', 'gl' => [
                    'count' => [
                        'none' => 0,
                        'one' => 1,
                        'some' => 3,
                    ],
                ],
                'pl' => [
                    'count' => [
                        'none' => 0,
                        'one' => 1,
                        'few' => 2,
                        'other' => 5,
                    ],
                ],
                default => [
                    'count' => [
                        'none' => 0,
                        'one' => 1,
                        'few' => 2,
                        'other' => 5,
                    ],
                ],
            },
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function pageVariables(array $parameters): array
    {
        $name = $parameters['name'];
        // every page of the export has the date in its footer
        $generated = ['date_generated' => new DateTime()];
        if ('post (thread)' === $name) {
            return array_merge($generated, $this->getPostInForum($parameters));
        }
        if ('post (group)' === $name) {
            return array_merge($generated, $this->getPostInGroup($parameters));
        }
        if ('message' === $name || 'request' === $name || 'invitation' === $name) {
            return array_merge($generated, ['conversation' => $this->getConversation($parameters, 'message' !== $name), 'isSubDir' => true]);
        }

        if (isset($parameters['count'])) {
            return array_merge($generated, $this->getMockEntities($parameters));
        }

        $extracted = $this->getExtractedEntities();

        $member = $parameters['user'];

        $conversations = [$this->getConversation($parameters, 'messages' !== $name)];

        return [
            'extracted' => $extracted,
            'member' => $member,
            'date_generated' => new DateTime(),
            'sections' => MyDataSections::withCounts(self::SAMPLE_COUNTS),
            'counts' => self::SAMPLE_COUNTS,
            'conversations' => $conversations,
            'created' => 1,
            'voted' => 2,
            'messagesSent' => 2,
            'messagesReceived' => 3,
            'requestsSent' => 4,
            'requestsReceived' => 5,
            'invitationsSent' => 6,
            'invitationsReceived' => 7,
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
            'polls_contributed',
            'polls_created',
            'polls_voted',
            'posts',
            'privileges',
            'relations',
            'rights',
            'trips',
            'subscriptions',
            'translations',
        ];
    }

    private function getMockEntities(array $parameters): array
    {
        $mockEntity = null;
        $key = $parameters['name'];
        // from the query string or the variant list, so a string: '0' has to be 0 for the empty variants
        $count = (int) ($parameters['count'] ?? -1);
        switch ($key) {
            case 'activities':
                $mockEntity = new Activity();
                $mockEntity->setTitle('Activity Title');
                $mockEntity->setDescription('Activity description');
                break;
            case 'broadcasts':
                $mockEntity = new BroadcastMessage()
                    ->setNewsletter($this->newsletter())
                    ->setUpdated(new Carbon());
                break;
            case 'comments':
                $mockComment = new Comment()
                    ->setToMember($parameters['user'])
                    ->setFromMember($parameters['admin'])
                    ->setQuality(CommentQualityType::POSITIVE)
                    ->setComment('I\'m so free')
                    ->setCreated(new Carbon())
                    ->setRelations('');
                $mockEntity = [
                    'to' => $mockComment,
                    'from' => $mockComment,
                ];
                break;
            case 'communitynews':
                $mockEntity = $this->communityNews();
                break;
            case 'communitynews_comments':
                $key = 'newsAndComments';
                $news = $this->communityNews();
                $mockEntity = [
                    'news' => $news,
                    'comments' => [
                        [
                            'title' => 'Comment title',
                            'text' => 'Comment text',
                            'created' => new DateTime(),
                        ],
                    ],
                ];
                break;
            case 'donations':
                // Twig reads donation.money through getMoney(): the getters need the values
                $mockEntity = new Donation()
                    ->setMoney('€')
                    ->setAmount('100.00')
                    ->setNameGiven('donor')
                    ->setReferencePaypal('paypal')
                    ->setSystemComment('system comment')
                    ->setMemberComment('member comment')
                    ->setStatusPrivate(1 === $count % 2 ? 'showamountonly' : 'showall');
                break;
            case 'gallery':
                $key = 'hrefs';
                $mockEntity = 'https://source.unsplash.com/random/300×300';
                break;
            case 'groups':
                return ['groupmemberships' => $this->getGroupMemberships($count)];
            case 'logs':
                $mockEntity = new Log();
                $mockEntity->setType('log');
                $mockEntity->setLogMessage('logged message');
                $mockEntity->setCreated(new Carbon());
                break;
            case 'newsletters':
                $mockEntity = $this->newsletter();
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
                    'choices' => [
                        ['texts' => ['en' => 'one', 'fr' => 'un']],
                    ],
                ];
                break;
            case 'posts':
                return $this->postsData($count);
            case 'privileges':
                $mockEntity = $this->getPrivilege();
                break;
            case 'relations':
                $mockEntity = $this->getFriendship();
                break;
            case 'rights':
                // overwrite key as index is different
                $key = 'volunteerrights';
                $right = new Right()
                    ->setName('distinct right')
                    ->setDescription('Right Description');
                $mockEntity = new RightVolunteer();
                $mockEntity->setRight($right);
                $mockEntity->setScope('All');
                $mockEntity->setLevel(10);
                $mockEntity->setCreated(new Carbon());
                break;
            case 'subscriptions':
                return $this->getSubscriptions($count);
            case 'translations':
                return $this->getTranslations($count);
            case 'trips':
                $mockEntity = $this->getTrip($parameters['admin']);
                break;
        }

        $entities = [];
        for ($i = 0; $i < $count; ++$i) {
            $entities[] = $mockEntity;
        }

        return [
            $key => $entities,
            'member' => $parameters['user'],
        ];
    }

    private function getTrip(Member $host): Trip
    {
        $trip = self::withId(new Trip(), 1)
            ->setCreator($host)
            ->setSummary('Mocking Bird')
            ->setDescription('Mocking description')
            ->setCountOfTravellers(2)
            ->setAdditionalInfo(TripAdditionalInfoType::NONE)
            ->setCreated(new DateTime());
        $country = new Location()->setGeonameId(2)->setName('Mocking Republic');
        $location = new Location()->setGeonameId(1)->setName('Mock')->setCountry($country);
        $trip->addLeg(
            self::withId(new Leg(), 1)
                ->setArrival(new Carbon('2021-02-22'))
                ->setDeparture(new Carbon('2021-02-24'))
                ->setOptions([LegOptionsType::MEET_LOCALS])
                ->setLocation($location)
                ->setInvitedBy($host)
        );

        return $trip;
    }

    private function getPoll(): Poll
    {
        $group = new Group();
        $group->setName('group');
        $choice = new PollChoice();
        $choice->setText('English');

        return self::withId(new Poll(), 1)
            ->setTitle('title')
            ->setDescription('description')
            ->addGroup($group)
            ->addChoice($choice);
    }

    private function newsletter(): Newsletter
    {
        $newsletter = self::withId(new Newsletter(), 1);
        $newsletter->setName('Newsletter Title');
        $newsletter->setCreated(new Carbon());

        return $newsletter;
    }

    private function communityNews(): CommunityNews
    {
        return self::withId(new CommunityNews(), 1)
            ->setTitle('Community News')
            ->setText('Community News text')
            ->setCreatedAt(new DateTime());
    }

    private function postsData(int $count): array
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

    private function postsYearData(int $count)
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
            'year' => new DateTime()->format('Y'),
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

    /**
     * A conversation as MessagesExtractor::writeConversations has it: the messages oldest first, with the other
     * member and, for a request or invitation, its stay.
     *
     * @return array<string, mixed>
     */
    private function getConversation(array $parameters, bool $withStay): array
    {
        $host = $parameters['user'];
        $guest = $parameters['admin'];

        $leg = $this->invitationUtility->getLeg($host);
        $messages = array_reverse($this->invitationUtility->getThread($host, $guest, $leg, HostingRequest::REQUEST_TENTATIVELY_ACCEPTED, 4));
        $last = $messages[\count($messages) - 1];

        return [
            'file' => 'messages/message-2026-01-01-1.html',
            'messages' => $messages,
            'first' => $messages[0],
            'last' => $last,
            'other' => $guest,
            'request' => $withStay ? $last->getRequest() : null,
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

    private function getFriendship(): array
    {
        return [
            'other' => ['username' => 'user'],
            'requester' => ['username' => 'admin'],
            'created' => new DateTime('-1 week'),
            'confirmed' => new DateTime(),
        ];
    }

    private function getSubscriptions(int $count): array
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

    private function getTranslations(int $count): array
    {
        if (0 === $count) {
            return ['translations' => []];
        }

        return [
            'translations' => [
                [
                    'code' => 'mydata.translations.headline',
                    'sentence' => 'Traduções',
                    'shortCode' => 'en',
                    'language' => ['shortCode' => 'pt'],
                    'created' => new DateTime()->format('Y-m-d'),
                ],
                [
                    'code' => 'mydata.translations.abstract',
                    'sentence' => 'Traducciones',
                    'shortCode' => 'en',
                    'language' => ['shortCode' => 'es'],
                    'created' => new DateTime()->format('Y-m-d'),
                ],
            ],
        ];
    }

    private function getGroupMemberships(int $count): array
    {
        $groupMemberships = [];
        for ($index = 0; $index < $count; ++$index) {
            $group = self::withId(new Group(), $index + 1);
            $group->setName('group #' . ($index + 1));
            $groupMemberships[] = new GroupMembership()
                ->setGroup($group)
                ->setStatus(GroupMembershipStatusType::CURRENT_MEMBER)
                ->setComment('Why I am in this group')
                ->setCreated(Carbon::now())
                ->setUpdated(Carbon::now());
        }

        return $groupMemberships;
    }
}
