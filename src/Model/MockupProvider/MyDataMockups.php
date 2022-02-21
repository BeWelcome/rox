<?php

namespace App\Model\MockupProvider;

use App\Doctrine\GroupType;
use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\Activity;
use App\Entity\BroadcastMessage;
use App\Entity\Comment;
use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Entity\Donations;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Gallery;
use App\Entity\Group;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\MemberThreadSubscription;
use App\Entity\Message;
use App\Entity\Newsletter;
use App\Entity\Poll;
use App\Entity\PollChoice;
use App\Entity\Privilege;
use App\Entity\Right;
use App\Entity\Shout;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Entity\Word;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

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
            'description' =>
                'Footer included on every page of the generated data including the date and time.',
        ],
        'profile' => [
            'type' => 'template',
            'template' => 'private/profile.html.twig',
            'description' => 'Your profile in the data dump',
        ],
        'messages' => [
            'type' => 'template',
            'template' => 'private/messages.html.twig',
            'description' => 'Statistics for messages in the data dump',
        ],
        'requests' => [
            'type' => 'template',
            'template' => 'private/requests.html.twig',
            'description' => 'Statistics for requests in the data dump',
        ],
        'invitations' => [
            'type' => 'template',
            'template' => 'private/invitations.html.twig',
            'description' => 'Statistics for invitations in the data dump',
        ],
        'polls' => [
            'type' => 'template',
            'template' => 'private/polls.html.twig',
            'description' => 'Overview page for polls',
        ],
    ];

    public function getFeature(): string
    {
        return 'my_data';
    }

    public function getMockups(): array
    {
        $mockups = self::MOCKUPS;
        $extracted = array_keys($this->getExtractedEntities());
        foreach ($extracted as $key) {
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
        if (isset($parameters['count'])) {
            return $this->getMockEntities($parameters);
        }

        $extracted = array_keys($this->getExtractedEntities());

        $member = $parameters['user'];

        return [
            'extracted' => $extracted,
            'member' => $member,
            'date_generated' => new DateTime(),
            'profilepicture' => '/members/avatar/' . $member->getUsername() . '/50',
            'messagesSent' => 2,
            'messagesReceived' => 3,
            'requestsSent' => 4,
            'requestsReceived' => 5,
            'invitationsSent' => 6,
            'invitationsReceived' => 7,
        ];
    }

    public function getMockupParameter(): array
    {
        return [
            'count' => [
                'none' => 0,
                'one' => 1,
                'two' => 2,
                'some' => random_int(3, 5),
            ]
        ];
    }

    private function getExtractedEntities(): array
    {
        return [
            'activities' => Activity::class,
            'broadcasts' => BroadcastMessage::class,
            'comments' => Comment::class,
            'communitynews' => CommunityNews::class,
            'communitynews_comments' => CommunityNewsComment::class,
            'donations' => Donations::class,
            'gallery' => Gallery::class,
            'logs' => Log::class,
            'newsletters' => BroadcastMessage::class,
            'pictures' => null,
            'polls_contributed' => Poll::class,
            'polls_created' => Poll::class,
            'polls_voted' => Poll::class,
            'posts' => ForumPost::class,
            'posts_year' => ForumPost::class,
            'privileges' => Privilege::class,
            'relations' => null,
            'rights' => Right::class,
            'trips' => Trip::class,
            'shouts' => Shout::class,
            'subscriptions' => MemberThreadSubscription::class,
            'translations' => Word::class,
        ];
    }

    private function getMockEntities(array $parameters): array
    {
        $mockEntity = null;
        $key = $parameters['name'];
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
                    'getTranslations' => [ 'en', 'de', 'zh-hant'],
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
                $mockEntity = Mockery::mock(CommunityNews::class, [
                    'getId' => 1,
                    'getTitle' => 'Community News',
                    'getText' => 'Community News text',
                    'getCommunityNews' => $news,
                ]);
                break;
            case 'donations':
                $mockEntity = Mockery::mock(Donations::class, [
                    'money' => '€',
                    'amount' => 100.00,
                    'namegiven' => 'donor',
                    'referencepaypal' => 'paypal',
                    'systemcomment' => 'system comment',
                    'membercomment' => 'member comment',
                    'statusprivate' => ($parameters['count'] % 2 == 1),
                ]);
                break;
            case 'gallery':
                $key = 'hrefs';
                $mockEntity = 'https://source.unsplash.com/random/300×300';
                break;
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
                            'title' => 'Title - zh-hant'
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
                        'texts' => [ 'one', 'two' ],
                    ],
                ];
                break;
            case 'rights':
                // overwrite key as index is different
                $key = 'volunteerrights';
                $mockEntity = Mockery::mock(Right::class, [
                    'right' => [
                        'name' => 'right'
                    ],
                    'scope' => 'All',
                    'level' => '10',
                    'getCreated' => new Carbon(),
                    'getDescription' => 'Right Description',
                ]);
                break;
            case 'trips':
                $mockEntity = $this->getTrip($parameters['admin']);
                break;
        }

        $entities = [];
        for ($i = 0;$i < $parameters['count']; $i++) {
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
            'getTitles' => [ 'title' ],
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
            ]
        ]);
    }
}
