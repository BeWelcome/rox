<?php

namespace App\Model;

use App\Entity\ActivityAttendee;
use App\Entity\BroadcastMessage;
use App\Entity\Comment;
use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Entity\CryptedField;
use App\Entity\Donation;
use App\Entity\FamilyAndFriend;
use App\Entity\ForumPost;
use App\Entity\Group;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\MembersPhoto;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\Newsletter;
use App\Entity\PasswordReset;
use App\Entity\Poll;
use App\Entity\PollChoice;
use App\Entity\PollContribution;
use App\Entity\PollRecordOfChoice;
use App\Entity\PrivilegeScope;
use App\Entity\RightVolunteer;
use App\Entity\Shout;
use App\Entity\Word;
use App\Repository\ActivityAttendeeRepository;
use App\Repository\CommentRepository;
use App\Repository\FamilyAndFriendRepository;
use App\Repository\MessageRepository;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception as Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;
use ZipArchive;

class MemberModel
{
    use ManagerTrait;
    use TranslatorTrait;

    /** @var EntrypointLookup  */
    private $entrypointLookup;

    /** @var UrlGeneratorInterface  */
    private $urlGenerator;

    /** @var Environment  */
    private $environment;

    /** @var string */
    private $tempDir;

    /** @var string */
    private $projectDir;

    public function __construct(UrlGeneratorInterface $urlGenerator, Environment $environment, EntrypointLookupInterface $entrypointLookup)
    {
        $this->urlGenerator = $urlGenerator;
        $this->environment = $environment;
        $this->entrypointLookup = $entrypointLookup;
    }

    /**
     * @param Member $member
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return string
     */
    public function generatePasswordResetToken(Member $member)
    {
        try {
            $token = random_bytes(32);
        } catch (Exception $e) {
            $token = openssl_random_pseudo_bytes(32);
        }
        $token = bin2hex($token);

        // Persist token into password reset table
        $passwordReset = new PasswordReset();
        $passwordReset
            ->setMember($member)
            ->setToken($token);
        $this->getManager()->persist($passwordReset);
        $this->getManager()->flush();

        return $token;
    }

    /**
     * @param ContainerBagInterface $params
     * @param Member $member
     * @return string
     * @throws Exception
     */
    public function collectPersonalData(ContainerBagInterface $params, Member $member)
    {
        // Create temp directory
        $i = 0;
        $dirname = "";
        while ($i < 1000) {
            $dirname = sys_get_temp_dir() . '/' . uniqid('mydata_', true);
            if (!is_file($dirname) && !is_dir($dirname)) {
                mkdir($dirname);
                break;
            }
        }
        if ($i === 1000) {
            // 1000 tries to create a temp directory failed, oh my
            throw new Exception('Can\'t generate temp dir');
        }
        // Ensure directory name ends with / and store it in private variable $tempDir as it is used all over the place
        // and clutters function signatures
        $this->tempDir = $dirname . "/";
        $this->projectDir = $params->get('kernel.project_dir');

        $this->preparePersonalData($member);

        $zipFilename = $this->tempDir . 'bewelcome-' . $member->getUsername() . "-" . date('Y-m-d') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipFilename, ZipArchive::CREATE);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tempDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $filesToDelete = [];
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($this->tempDir));

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
                $filesToDelete[] = $filePath;
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        // Cleanup as this is personal data
        foreach ($filesToDelete as $name => $file) {
            unlink($file);
        }
        return $zipFilename;
    }

    /**
     * @param Member $member
     */
    private function preparePersonalData(Member $member)
    {
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        $extracted = [];
        $this->createStylesheetAndImageFolder();
        $extracted[] = $this->prepareGalleryItems($member);
        $extracted[] = $this->prepareProfilePictures($member);
        $extracted[] = $this->prepareMessages($member);
        $extracted[] = $this->prepareRequests($member);
        $extracted[] = $this->prepareLogs($member);
        $extracted[] = $this->prepareForumPosts($member);
        $extracted[] = $this->prepareGroupInformation($member);
        $extracted[] = $this->prepareActivities($member);
        $extracted[] = $this->prepareComments($member);
        $extracted[] = $this->prepareSpecialRelations($member);
        $extracted[] = $this->prepareMemberData($this->tempDir, $member);
        $extracted[] = $this->prepareNewsletters($member);
        $extracted[] = $this->prepareBroadcasts($member);
        $extracted[] = $this->prepareCommunityNews($member);
        $extracted[] = $this->prepareCommunityNewsComments($member);
        $extracted[] = $this->prepareDonations($member);
        $extracted[] = $this->prepareTranslations($member);
        $extracted[] = $this->prepareRights($member);
        $extracted[] = $this->preparePrivileges($member);
        $extracted[] = $this->preparePolls($member);
        $extracted[] = $this->prepareShouts($member);
        $this->writePersonalDataFile(['member' => $member, 'extracted' => $extracted],'index');

        ini_set('memory_limit', $memoryLimit);
    }

    /**
     * copy all files for the gallery into the gallery subdirectory
     *
     * @param Member $member
     * @return string
     */
    private function prepareGalleryItems(Member $member): string
    {
        $memberId = $member->getId();

        $filesystem = new Filesystem();
        $galleryPath = $this->projectDir . '/data/gallery/member' . $memberId . '/';

        if (is_dir($galleryPath)) {
            // create gallery sub directory
            $galleryDir = $this->tempDir . 'gallery/';
            $hrefs = [];
            @mkdir($galleryDir);
            if ($directoryHandle = opendir($galleryPath)) {
                while (($file = readdir($directoryHandle)) !== false) {
                    if (!is_dir($file)) {
                        $ext = $this->imageExtension($galleryPath . $file);
                        $destination = $galleryDir . pathinfo($file, PATHINFO_FILENAME) . $ext;
                        $filesystem->copy($galleryPath . $file, $galleryDir . pathinfo($file, PATHINFO_FILENAME) . $ext);
                        $hrefs[] = str_replace($this->tempDir, '', $destination);
                    }
                }
                closedir($directoryHandle);
            }
        }
        return $this->writePersonalDataFile(['hrefs' => $hrefs],'gallery');
    }

    private function imageExtension(string $filename): string
    {
        $mimetype = mime_content_type($filename);
        switch ($mimetype) {
            case 'image/png':
                $ext = '.png';
                break;
            case 'image/jpeg':
                $ext = '.jpg';
                break;
            case 'image/gif':
                $ext = '.gif';
                break;
            case 'image/bmp':
                $ext = '.bmp';
                break;
            default:
                $ext = '';
        }
        return $ext;
    }

    /**
     * @param Member $member
     * @return string
     */
    private function prepareProfilePictures(Member $member): string
    {
        $pictures = [];
        $variants = ['_xs', '_30_30', '_75_75', '_150', '_200', '_500', '_original'];

        // Copy all profile pictures
        $filesystem = new Filesystem();
        $pictureDir = $this->tempDir . 'pictures/';
        @mkdir($pictureDir);
        $photoRepository = $this->getManager()->getRepository(MembersPhoto::class);
        /** @var MembersPhoto[] $photos */
        $photos = $photoRepository->findBy(['member' => $member]);
        foreach ($photos as $photo) {
            if (is_file($photo->getFilepath())) {
                $filesystem->copy($photo->getFilepath(), $pictureDir
                    . pathinfo($photo->getFilepath(), PATHINFO_FILENAME)
                    . $this->imageExtension($photo->getFilepath()));
                $pictures[] =
                    pathinfo($photo->getFilepath(), PATHINFO_FILENAME)
                    .$this->imageExtension($photo->getFilepath());
            }
            foreach ($variants as $variant) {
                $filepath = $photo->getFilepath() . $variant;
                $filename = pathinfo($filepath, PATHINFO_FILENAME);
                if (is_file($filepath)) {
                    $filesystem->copy($filepath, $pictureDir
                        . $filename
                        . $this->imageExtension($filepath));
                    $pictures[] = $filename . $this->imageExtension($filepath);
                }
            }
        }
        return $this->writePersonalDataFile([ 'pictures' => $pictures ],"pictures" );
    }

    private function processMessagesOrRequests($items, $directory, $sent)
    {
        $i = 1;
        foreach ($items as $message) {
            $isRequest = ($message->getRequest() !== null);
            $filename = ($isRequest) ? "request" : "message";
            $this->writePersonalDataFileSubDirectory(
                [
                    'message' => $message,
                ],
                'message_or_request',
                $directory,
                $filename . "-" . $message->getCreated()->toDateString() . "-" . $i . ($sent ? "-sent" : "-received")
            );
            $i++;
        }
    }

    /**
     * @param Member $member
     * @return string
     */
    private function prepareMessages(Member $member): string
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository(Message::class);

        $messagesSentBy = $messageRepository->getMessagesSentBy($member);
        $messagesReceivedBy = $messageRepository->getMessagesReceivedBy($member);
        $this->processMessagesOrRequests($messagesSentBy, 'messages', true);
        $this->processMessagesOrRequests($messagesReceivedBy, 'messages', false);

        return $this->writePersonalDataFile(
            [
                'messagesSent' => count($messagesSentBy),
                'messagesReceived' => count($messagesReceivedBy),
            ],
            'messages'
        );
    }

    /**
     * @param Member $member
     * @return string
     */
    private function prepareRequests(Member $member): string
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository(Message::class);

        $requestsSentBy = $messageRepository->getRequestsSentBy($member);
        $requestsReceivedBy = $messageRepository->getRequestsReceivedBy($member);
        $this->processMessagesOrRequests($requestsSentBy, 'requests', true);
        $this->processMessagesOrRequests($requestsReceivedBy, 'requests', false);

        return $this->writePersonalDataFile(
            [
                'requestsSent' => count($requestsSentBy),
                'requestsReceived' => count($requestsReceivedBy),
            ],
            'requests'
        );
    }

    /**
     * @param $filename
     * @param $template
     * @param $parameters
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function writeRenderedTemplate($filename, $template, $parameters)
    {
        $this->entrypointLookup->reset();
        $parameters = array_merge($parameters, ['date_generated' => new DateTime()]);

        $handle = fopen($this->tempDir.$filename.".html", "w");
        fwrite($handle, $this->environment->render('private/' . $template . '.html.twig', $parameters));
        fclose($handle);
    }

    /**
     * @param array $parameters
     * @param string $template
     * @param string $filename
     * @return string
     */
    private function writePersonalDataFile($parameters, $template, $filename = null) : string
    {
        $filename = (null === $filename) ? $template : $filename;

        $this->writeRenderedTemplate(
            $filename,
            $template,
            $parameters
        );
        return $template;
    }

    /**
     * @param array $parameters
     * @param string $template Template (without .html.twig) to be used (located in private/)
     * @param string $subDirectory Subdirectory name (no trailing /)
     * @param string $filename File to be written (.html is added)
     */
    private function writePersonalDataFileSubDirectory($parameters, $template, $subDirectory, $filename = null)
    {
        if (!is_dir($this->tempDir.$subDirectory)) {
            @mkdir($this->tempDir.$subDirectory);
        }

        $filename = (null === $filename) ? $template : $filename;

        $parameters = array_merge($parameters, [
            'isSubDir' => true,
        ]);

        $this->writeRenderedTemplate(
            $subDirectory . '/' . $filename,
            $template,
            $parameters
        );
    }

    /**
     * @param Member $member
     * @return string
     */
    private function prepareLogs(Member $member): string
    {
        // Add all log information about member
        $logRepository = $this->getManager()->getRepository(Log::class);
        /** @var Log[] $logs */
        $logs = $logRepository->findBy(['member' => $member]);
        return $this->writePersonalDataFile(
            [
                'logs' => $logs,
            ],
            'logs'
        );
    }

    private function prepareForumPosts(Member $member): string
    {
        // now all posts to the forum or groups including status
        $forumRepository = $this->getManager()->getRepository(ForumPost::class);
        /** @var ForumPost $posts */
        $posts = $forumRepository->findBy(['author' => $member], ['created' => 'DESC']);
        $i = 1;
        $postsPerYear = [];
        $threadsPerYear = [];
        $threadsContributed = [];
        /** @var ForumPost $post */
        foreach ($posts as $post) {
            try {
                // Some posts do not have an valid thread id. We check by trying to access the thread's title
                $thread = $post->getThread();
                if ($thread) {
                    $thread->getTitle();
                }
            } catch (Exception $e) {
                $thread = null;
            }
            if (null === $thread) {
                $group = null;
            } else {
                try {
                    // As database column for group has 0 instead of null we need to check if group is valid
                    $group = $thread->getGroup();
                    if ($group) {
                        $group->getName();
                    }
                } catch (Exception $e) {
                    $group = null;
                }
            }
            $this->writePersonalDataFileSubdirectory(
                [
                    'thread' => $thread,
                    'group' => $group,
                    'post' => $post,
                ],
                'post',
                'posts',
                "post-" . $post->getCreated()->toDateString() . "-" . $i
            );
            $year = $post->getCreated()->year;
            if (!isset($threadsPerYear[$year])) {
                $threadsPerYear[$year] = [];
                $postsPerYear[$year] = 0;
            }
            $thread = $post->getThread();
            $threadId = (null === $thread) ? 0 : $thread->getThreadId();
            if (!isset($threadsPerYear[$year][$threadId]))
            {
                if (!in_array($threadId, $threadsContributed)) {
                    $threadsContributed[] = $threadId;
                }
                $threadsPerYear[$year][$threadId] = [
                    'thread' => $thread,
                    'posts' => [],
                    'count' => 0,
                ];
            }
            $threadsPerYear[$year][$threadId]['count'] = $threadsPerYear[$year][$threadId]['count'] + 1;
            $threadsPerYear[$year][$threadId]['posts'][$i] = $post;
            $postsPerYear[$year] = $postsPerYear[$year] + 1;
            $i++;
        }
        if (!empty($threadsPerYear))
        {
            foreach(array_keys($threadsPerYear) as $year)
            {
                $this->writePersonalDataFileSubDirectory(
                    [
                        'year' => $year,
                        'post_count' => $postsPerYear[$year],
                        'threads' => $threadsPerYear[$year],
                        'thread_count' => count(array_keys($threadsPerYear[$year])),
                    ],
                    "posts_year",
                    'posts',
                    "posts-".$year
                );
            }
        }
        return $this->writePersonalDataFile(
            [
                'years' => array_keys($threadsPerYear),
                'threadsPerYear' => $threadsPerYear,
                'postsPerYear' => $postsPerYear,
                'threads_contributed' => count($threadsContributed),
                'posts_written' => $i - 1,
            ],
            'posts'
        );
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareSubscriptions(string $tempDir, Member $member)
    {
        // \todo
    }

    /**
     * @param Member $member
     */
    private function prepareGroupInformation(Member $member)
    {
        // Groups the member is in and why
        $memberships = [];
        $groupMemberships = $member->getGroupMemberships();
        if (!empty($groupMemberships)) {
            foreach ($groupMemberships as $groupMembership) {
                try {
                    // Database is messy. Check if group still exists
                    if ($groupMembership->getGroup()->getName())
                    {
                        $memberships[] = $groupMembership;
                    };
                } catch (Exception $e) {
                    // Deleted Group
                }
            }
        }
        return $this->writePersonalDataFile(['groupmemberships' => $memberships],'groups');
    }

    /**
     *  Activities the member joined with comment
     *
     * @param Member $member
     * @return string
     */
    private function prepareActivities(Member $member): string
    {
        /** @var ActivityAttendeeRepository $attendeeRepository */
        $attendeeRepository = $this->getManager()->getRepository(ActivityAttendee::class);
        /** @var ActivityAttendee[] $activities */
        $activitiesOfMember = $attendeeRepository->findActivitiesOfMember($member);
        if (!empty($activitiesOfMember)) {
            /** @var ActivityAttendee $attendee */
            $i = 1;
            foreach ($activitiesOfMember as $attendee) {
                $this->writePersonalDataFileSubDirectory(
                    [
                        'activity' => $attendee->getActivity(),
                        'organizer' => $attendee->getOrganizer(),
                        'status' => $attendee->getStatus(),
                        'comment' => $attendee->getComment(),
                    ],
                    'activity',
                    'activities',
                    "activity-" . $i
                );
                $activities[$i] = $attendee->getActivity();
                $i++;
            }
        }
        return $this->writePersonalDataFile([ 'activities' => $activities ], 'activities');
    }

    /**
     * @param Member $member
     * @return string
     */
    private function prepareComments(Member $member): string
    {
        // Comments the member left others
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->getManager()->getRepository(Comment::class);
        /** @var Comment[] $comments */
        $commentsForMember = $commentRepository->getCommentsForMember($member);
        $commentsFromMember = $commentRepository->getCommentsFromMember($member);
        $comments = [];
        /** @var Comment $comment */
        foreach($commentsForMember as $comment)
        {
            $commentArray = [
                'to' => null,
                'from' => $comment,
            ];
            $comments[$comment->getFromMember()->getId()] = $commentArray;
        }

        /** @var Comment $comment */
        foreach($commentsFromMember as $comment)
        {
            if (isset($comments[$comment->getToMember()->getId()]))
            {
                $commentArray = $comments[$comment->getToMember()->getId()];
                $commentArray['to'] = $comment;
            } else {
                $commentArray = [
                    'to' => $comment,
                    'from' => null,
                ];
            }
            $comments[$comment->getToMember()->getId()] = $commentArray;
        }
        return $this->writePersonalDataFile(['comments' => $comments],'comments');
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareMemberData(string $tempDir, Member $member)
    {
        return $this->writePersonalDataFile(
            [
                'member' => $member,
                'profilepicture' => 'images/empty_avatar.png',
            ],
            'profile'
        );

        // Write member information into file:
        $handle = fopen($tempDir . "memberinfo.txt", "w");
        fwrite($handle, json_encode($member));
        fwrite($handle, "Username: " . $member->getUsername() . PHP_EOL);
        fwrite($handle, "Location: " . $member->getCity()->getName() . PHP_EOL);
        fwrite($handle, "Birthdate: " . $member->getBirthdate() . PHP_EOL);
        fwrite($handle, "Email address: " . $member->getEmail() . PHP_EOL);
        fwrite($handle, "Accommodation: " . $member->getAccommodation() . PHP_EOL);

        $cryptedFields = $member->getCryptedFields();
        /** @var CryptedField $crypted */
        foreach ($cryptedFields as $crypted) {
            fwrite($handle, $crypted->getTablecolumn() . ":" . $crypted->getMemberCryptedValue() . PHP_EOL);
        }

        $memberFields = $member->getMemberFields();
        /** @var MemberTranslation $memberField */
        foreach ($memberFields as $memberField) {
            fwrite($handle, $memberField->getTablecolumn() . " (" . $memberField->getLanguage()->getName() . "): " . $memberField->getSentence() . PHP_EOL);
        }
        fclose($handle);
    }

    private function prepareNewsletters(Member $member)
    {
        // Get newsletters the member wrote
        $newsletterRepository = $this->getManager()->getRepository(Newsletter::class);
        $newsletters = $newsletterRepository->findBy(['createdBy' => $member]);

        return $this->writePersonalDataFile(['newsletters' => $newsletters, 'creator' => $member], 'newsletters');

    }

    private function prepareBroadcasts(Member $member)
    {
        // Get all broadcasts the member received
        $broadcastMessageRepository = $this->getManager()->getRepository(BroadcastMessage::class);
        $broadcastMessages = $broadcastMessageRepository->findBy(['receiver' => $member]);

        return $this->writePersonalDataFile(['broadcasts' => $broadcastMessages], 'broadcasts');
    }

    private function prepareCommunityNews(Member $member)
    {
        // Get community news the member wrote
        $newsRepository = $this->getManager()->getRepository(CommunityNews::class);
        $news = $newsRepository->findBy(['createdBy' => $member]);

        return $this->writePersonalDataFile(['communitynews' => $news], 'communitynews');
    }

    private function prepareCommunityNewsComments(Member $member)
    {
        // Get community news comments the member wrote
        $commentRepository = $this->getManager()->getRepository(CommunityNewsComment::class);
        $comments = $commentRepository->findBy(['author' => $member]);

        $newsAndComments = [];
        /** @var CommunityNewsComment $comment */
        foreach($comments as $comment)
        {
            if (!isset($newsAndComments[$comment->getCommunityNews()->getId()]))
            {
                $commentWithNews = [
                    'news' => $comment->getCommunityNews(),
                    'comments' => [],
                ];
            } else {
                $commentWithNews = $newsAndComments[$comment->getCommunityNews()->getId()];
            }
            $commentWithNews['comments'][] = $comment;
            $newsAndComments[$comment->getCommunityNews()->getId()] = $commentWithNews;
        }
        return $this->writePersonalDataFile(['newsAndComments' => $newsAndComments], 'communitynews_comments');
    }

    private function prepareDonations(Member $member): string
    {
        // Get donations the member did
        $donationRepository = $this->getManager()->getRepository(Donation::class);
        $donations = $donationRepository->findBy(['donor' => $member]);
        return $this->writePersonalDataFile(['donations' => $donations], 'donations');
    }

    private function prepareTranslations(Member $member): string
    {
        // Get translations the member did
        $translationRepository = $this->getManager()->getRepository(Word::class);
        $translations = $translationRepository->findBy(['author' => $member]);
        return $this->writePersonalDataFile(['translations' => $translations], 'translations');
    }

    /**
     * @param Member $member
     * @return string
     */
    private function prepareRights(Member $member): string
    {
        /** @var RightVolunteer[] $volunteerRights */
        $volunteerRights = $member->getVolunteerRights();
        return $this->writePersonalDataFile(['volunteerrights' => $volunteerRights],"rights");
    }

    /**
     * @param Member $member
     * @return string
     */
    private function preparePrivileges(Member $member): string
    {
        $privilegesCombined = [];
        /** @var EntityRepository $privilegesRepository */
        $privilegesRepository = $this->getManager()->getRepository(PrivilegeScope::class);
        $privileges = $privilegesRepository->findBy(['member' => $member]);
        if (!empty($privileges)) {
            /** @var PrivilegeScope $privilege */
            foreach ($privileges as $privilege) {
                $type = $privilege->getPrivilege()->getType();
                $scope = $privilege->getType();
                $realScope = $scope;
                $privilegeCombined = [];
                $privilegeCombined['privilege'] = $type;
                if ($type == 'Group') {
                    // Naming is a bit odd here
                    if (is_numeric($scope)) {
                        // Check if this group still exists
                        $groupRepository = $this->getManager()->getRepository(Group::class);
                        /** @var Group $group */
                        $group = $groupRepository->findOneBy(['id' => $scope]);
                        if (null !== $group) {
                            $realScope = $group->getName();
                        } else {
                            $realScope = 'Deleted group (' . $scope . ')';
                        }
                    }
                }
                $privilegeCombined['scope'] = $realScope;
                $privilegeCombined['role'] = $privilege->getRole()->getName();
                $privilegeCombined['assigned'] = $privilege->getUpdated();
                $privilegesCombined[] = $privilegeCombined;
            }
        }
        return $this->writePersonalDataFile(['privileges' => $privilegesCombined],"privileges");
    }

    /**
     * @param PollContribution|PollRecordOfChoice $related
     * @param $handle
     */
    private function writePollInformation($related, $handle)
    {
        $poll = $related->getPoll();
        // Check if a title exists and output it
        $title = $poll->getTitles()->first();
        fwrite($handle, "Poll voted: ");
        if ($title) {
            fwrite($handle, $title->getSentence());
        } else {
            fwrite($handle, "Unknown poll");
        }
        fwrite($handle, " (" . $poll->getId() . ")" . PHP_EOL);
    }

    /**
     * @param Member $member
     * @return string
     */
    private function preparePolls(Member $member): string
    {
        $pollsDir = $this->tempDir . 'polls/';
        @mkdir($pollsDir);

        /** @var EntityRepository $pollsRepository */
        $pollsRepository = $this->getManager()->getRepository(Poll::class);
        $polls = $pollsRepository->findBy(['creator' => $member]);
        $this->writePersonalDataFileSubDirectory(['polls' => $polls],'polls_created','polls');

        /** @var EntityRepository $contributionsRepository */
        $contributionsRepository = $this->getManager()->getRepository(PollContribution::class);
        $contributions = $contributionsRepository->findBy(['member' => $member]);
        $this->writePersonalDataFileSubDirectory(['contributions' => $contributions],'polls_contributed','polls');

        /** @var EntityRepository $resultsRepository */
        $votesRepository = $this->getManager()->getRepository(PollRecordOfChoice::class);
        $votes = $votesRepository->findBy(['member' => $member], ['poll' => 'DESC', 'pollChoice' => 'DESC']);
        $this->writePersonalDataFileSubDirectory(['votes' => $votes],'polls_voted','polls');

        return $this->writePersonalDataFile([], 'polls');
    }

    /**
     * Prepares a list of comments left by the current user (also knows as shouts)
     *
     * @param Member $member
     * @return string
     */
    private function prepareShouts(Member $member): string
    {
        /** @var EntityRepository $shoutsRepository */
        $shoutsRepository = $this->getManager()->getRepository(Shout::class);
        $shouts = $shoutsRepository->findBy(['member' => $member]);
        return $this->writePersonalDataFile(['shouts' => $shouts], 'shouts');
    }

    private function prepareSpecialRelations(Member $member)
    {
        $relations = [];
        /** @var FamilyAndFriendRepository $relationsRepository */
        $relationsRepository = $this->getManager()->getRepository(FamilyAndFriend::class);
        $rawRelations = $relationsRepository->findRelationsFor($member);
        if (!empty($rawRelations)) {
            // build list of relations from raw data (list contains relations from both sides)
            $memberId = $member->getId();
            /** @var FamilyAndFriend $relation */
            foreach ($rawRelations as $relation) {
                $author = $relation->getOwner();
                $authorId = $author->getId();
                $recipient = $relation->getRelation();
                $recipientId = $recipient->getId();
                if ($recipient !== $member)
                {
                    $relations[$recipientId] = [];
                    $relations[$recipientId]['right'] = $relation;
                }
                elseif (key_exists($authorId, $relations)) {
                    $relations[$authorId]['left'] = $relation;
                }
                else
                {
                    $relations[$authorId] = [];
                    $relations[$authorId]['left'] = $relation;
                }
            }
        }
        return $this->writePersonalDataFile(['relations' => $relations], 'relations');
    }

    private function createStylesheetAndImageFolder()
    {
        $filesystem = new Filesystem();

        $cssFiles = $this->entrypointLookup->getCssFiles('bewelcome');
        foreach($cssFiles as $cssFile)
        {
            $source = $this->projectDir . '/public' . $cssFile;
            $destination = $this->tempDir . $cssFile;
            $filesystem->copy($source, $destination);
        }

        $jsFiles = $this->entrypointLookup->getJavaScriptFiles('gallery');
        foreach($jsFiles as $jsFile)
        {
            $source = $this->projectDir . '/public' . $jsFile;
            $destination = $this->tempDir . $jsFile;
            $filesystem->copy($source, $destination);
        }

        $jsFiles = $this->entrypointLookup->getJavaScriptFiles('bewelcome');
        foreach($jsFiles as $jsFile)
        {
            $source = $this->projectDir . '/public' . $jsFile;
            $destination = $this->tempDir . $jsFile;
            $filesystem->copy($source, $destination);
        }

        // Add the Bewelcome logo
        $filesystem->copy($this->projectDir . '/public/images/logo_index_top.png', $this->tempDir.'images/logo_index_top.png');

        // We also need to empty avatar image
        $filesystem->copy($this->projectDir . '/public/images/empty_avatar.png', $this->tempDir.'images/empty_avatar.png');

        // The accommodation images
        $filesystem->copy($this->projectDir . '/public/images/icons/wheelchairblue.png', $this->tempDir.'images/wheelchairblue.png');
        $filesystem->copy($this->projectDir . '/public/images/icons/anytime.png', $this->tempDir.'images/anytime.png');
        $filesystem->copy($this->projectDir . '/public/images/icons/dependonrequest.png', $this->tempDir.'images/dependonrequest.png');
        $filesystem->copy($this->projectDir . '/public/images/icons/neverask.png', $this->tempDir.'images/neverask.png');
    }
}
