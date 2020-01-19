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
use App\Repository\MessageRepository;
use App\Utilities\ManagerTrait;
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
use ZipArchive;

class MemberModel
{
    use ManagerTrait;

    /** @var UrlGeneratorInterface  */
    private $urlGenerator;

    /** @var EngineInterface  */
    private $engine;

    public function __construct(UrlGeneratorInterface $urlGenerator, EngineInterface $engine)
    {
        $this->urlGenerator = $urlGenerator;
        $this->engine = $engine;
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
        // Ensure directory name ends with /
        $dirname = $dirname . "/";

        $this->preparePersonalData($dirname, $params->get('kernel.project_dir'), $member);

        $zipFilename = $dirname . 'bewelcome-' . $member->getUsername() . "-" . date('Y-m-d') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipFilename, ZipArchive::CREATE);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $filesToDelete = [];
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($dirname));

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
     * @param string $tempDir
     * @param string $projectDir
     * @param Member $member
     */
    private function preparePersonalData(string $tempDir, string $projectDir, Member $member)
    {
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        $this->prepareGalleryItems($tempDir, $projectDir, $member);
        $this->prepareProfilePictures($tempDir, $member);
        $this->prepareMessagesAndRequests($tempDir, $member);
        $this->prepareLogs($tempDir, $member);
        $this->prepareForumPosts($tempDir, $member);
        $this->prepareGroupInformation($tempDir, $member);
        $this->prepareActivities($tempDir, $member);
        $this->prepareComments($tempDir, $member);
        $this->prepareSpecialRelations($tempDir, $member);
        $this->prepareMemberData($tempDir, $member);
        $this->prepareNewsletterInformation($tempDir, $member);
        $this->prepareCommunityNewsInformation($tempDir, $member);
        $this->prepareDonations($tempDir, $member);
        $this->prepareTranslations($tempDir, $member);
        $this->prepareRightsAndPrivileges($tempDir, $member);
        $this->preparePolls($tempDir, $member);
        $this->prepareShouts($tempDir, $member);

        ini_set('memory_limit', $memoryLimit);
    }

    /**
     * copy all files for the gallery into the gallery subdirectory
     *
     * @param string $tempDir
     * @param string $projectDir
     * @param Member $member
     */
    private function prepareGalleryItems(string $tempDir, string $projectDir, Member $member): void
    {
        /** @var Member $member */
        $memberId = $member->getId();

        $filesystem = new Filesystem();
        $galleryPath = $projectDir . '/data/gallery/member' . $memberId . '/';

        if (is_dir($galleryPath)) {
            // create gallery sub directory
            $galleryDir = $tempDir . 'gallery/';
            @mkdir($galleryDir);
            if ($directoryHandle = opendir($galleryPath)) {
                while (($file = readdir($directoryHandle)) !== false) {
                    if (!is_dir($file)) {
                        $ext = $this->imageExtension($galleryPath . $file);
                        $filesystem->copy($galleryPath . $file, $galleryDir . pathinfo($file, PATHINFO_FILENAME) . $ext);
                    }
                }
                closedir($directoryHandle);
            }
        }
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
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareProfilePictures(string $tempDir, Member $member)
    {
        $variants = ['_xs', '_30_30', '_75_75', '_150', '_200', '_500', '_original'];

        // Copy all profile pictures
        $filesystem = new Filesystem();
        $pictureDir = $tempDir . 'pictures/';
        @mkdir($pictureDir);
        $photoRepository = $this->getManager()->getRepository(MembersPhoto::class);
        /** @var MembersPhoto[] $photos */
        $photos = $photoRepository->findBy(['member' => $member]);
        foreach ($photos as $photo) {
            if (is_file($photo->getFilepath())) {
                $filesystem->copy($photo->getFilepath(), $pictureDir
                    . pathinfo($photo->getFilepath(), PATHINFO_FILENAME)
                    . $this->imageExtension($photo->getFilepath()));
            }
            foreach ($variants as $variant) {
                $filepath = $photo->getFilepath() . $variant;
                if (is_file($filepath)) {
                    $filesystem->copy($filepath, $pictureDir
                        . pathinfo($filepath, PATHINFO_FILENAME)
                        . $this->imageExtension($filepath));
                }
            }
        }
    }

    private function processMessagesOrRequests($items, $directory, $sent)
    {
        $i = 1;
        foreach ($items as $message) {
            $isRequest = ($message->getRequest() !== null);
            $filename = ($isRequest) ? "request" : "message";
            $handle = fopen($directory . $filename . "-" . $message->getCreated()->toDateString() . "-" . $i
                . ($sent ? "-sent" : "-received") . ".html", "w");
            fwrite($handle, $this->engine->render('private/message_or_request.html.twig', [
                'message' => $message,
            ]));
            fclose($handle);
            $i++;
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareMessagesAndRequests(string $tempDir, Member $member): void
    {
        // Write all messages into files
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository(Message::class);

        $messageDir = $tempDir . 'messages/';
        @mkdir($messageDir);
        $this->processMessagesOrRequests($messageRepository->getMessagesSentBy($member), $messageDir, true);
        $this->processMessagesOrRequests($messageRepository->getMessagesReceivedBy($member), $messageDir, false);

        $requestDir = $tempDir . 'requests/';
        @mkdir($requestDir);
        $this->processMessagesOrRequests($messageRepository->getRequestsSentBy($member), $requestDir, true);
        $this->processMessagesOrRequests($messageRepository->getRequestsReceivedBy($member), $requestDir, false);
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareLogs(string $tempDir, Member $member)
    {
        // Add all log information about member
        $logRepository = $this->getManager()->getRepository(Log::class);
        /** @var Log[] $logs */
        $logs = $logRepository->findBy(['member' => $member]);
        if (!empty($logs)) {
            $handle = fopen($tempDir . "logs.txt", "w");
            foreach ($logs as $log) {
                fwrite($handle, $log->getType() . ": " . $log->getLogMessage() . " (" . $log->getCreated()->toDateTimeString() . ")" . PHP_EOL);
            }
            fclose($handle);
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareForumPosts(string $tempDir, Member $member)
    {
        // now all posts to the forum or groups including status
        $forumRepository = $this->getManager()->getRepository(ForumPost::class);
        /** @var ForumPost $posts */
        $posts = $forumRepository->findBy(['author' => $member]);
        $postsDir = $tempDir . 'posts/';
        @mkdir($postsDir);
        $i = 1;
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
                } catch (Exception $e) {
                    $group = null;
                }
            }
            $handle = fopen($postsDir . "post-" . $post->getCreated()->toDateString() . "-" . $i . ".html", "w");
            fwrite($handle, $this->engine->render('private/post.html.twig', [
                'thread' => $thread,
                'group' => $group,
                'post' => $post,
                ]));
            fclose($handle);
            $i++;
        }
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
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareGroupInformation(string $tempDir, Member $member)
    {
        // Groups the member is in and why
        $groupMemberships = $member->getGroupMemberships();
        if (!empty($groupMemberships)) {
            $handle = fopen($tempDir . "groups.txt", "w");
            foreach ($groupMemberships as $groupMembership) {
                try {
                    fwrite($handle, $groupMembership->getGroup()->getName() . " ["
                        . $this->urlGenerator->generate(
                            'group_start',
                            [ 'group_id' => $groupMembership->getGroup()->getId(), UrlGenerator::ABSOLUTE_URL ]
                        )
                        . "]");
                } catch (Exception $e) {
                    fwrite($handle, "Deleted Group");
                }
                fwrite($handle, PHP_EOL);
                fwrite($handle, "Status: " . $groupMembership->getStatus() . " (joined: " . $groupMembership->getCreated()->toDateTimeString() . ")" . PHP_EOL);
                /** @var MemberTranslation $comment */
                foreach ($groupMembership->getComments()->getValues() as $comment) {
                    fwrite($handle, $comment->getSentence() . PHP_EOL);
                }
                fwrite($handle, PHP_EOL);
            }
            fclose($handle);
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareActivities(string $tempDir, Member $member)
    {
        // Activities the member joined with comment
        $commentsDir = $tempDir . 'activities/';
        @mkdir($commentsDir);
        /** @var ActivityAttendeeRepository $attendeeRepository */
        $attendeeRepository = $this->getManager()->getRepository(ActivityAttendee::class);
        /** @var ActivityAttendee[] $activities */
        $activitiesOfMember = $attendeeRepository->findActivitiesOfMember($member);
        if (!empty($activitiesOfMember)) {
            /** @var ActivityAttendee $attendee */
            $i = 1;
            foreach ($activitiesOfMember as $attendee) {
                $handle = fopen($commentsDir . "activitity" . $i . ".txt", "w");
                fwrite($handle, $attendee->getActivity()->getTitle() . "(" . $attendee->getActivity()->getId() . ")" . PHP_EOL);
                fwrite($handle, $attendee->getActivity()->getDescription() . "(" . $attendee->getActivity()->getId() . ")" . PHP_EOL);
                if ($attendee->getOrganizer()) {
                    fwrite($handle, "You organized this activity" . PHP_EOL);
                }
                fwrite($handle, $attendee->getComment() . PHP_EOL);
                fclose($handle);
                $i++;
            }
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareComments(string $tempDir, Member $member)
    {
        // Comments the member left others
        $commentsDir = $tempDir . 'comments/';
        @mkdir($commentsDir);
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->getManager()->getRepository(Comment::class);
        /** @var Comment[] $comments */
        $comments = $commentRepository->getCommentsForMember($member);
        if (!empty($comments)) {
            /** @var Comment $comment */
            $i = 1;
            foreach ($comments as $comment) {
                $handle = fopen($commentsDir . "comment-" . $i . "-received.html", "w");
                fwrite($handle, $this->engine->render('private/comment.html.twig', [
                    'comment' => $comment,
                ]));
                fclose($handle);
                $i++;
            }
        }
        $comments = $commentRepository->getCommentsFromMember($member);
        if (!empty($comments)) {
            /** @var Comment $comment */
            foreach ($comments as $comment) {
                $handle = fopen($commentsDir . "comment-" . $i . "-given.html", "w");
                fwrite($handle, $this->engine->render('private/comment.html.twig', [
                    'comment' => $comment,
                ]));
                fclose($handle);
                $i++;
            }
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareMemberData(string $tempDir, Member $member)
    {
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

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareNewsletterInformation(string $tempDir, Member $member)
    {
        // Get newsletters the member wrote
        $newsletterRepository = $this->getManager()->getRepository(Newsletter::class);
        $newsletters = $newsletterRepository->findBy(['createdBy' => $member]);
        if (!empty($newsletters)) {
            $newslettersDir = $tempDir . 'newsletters/';
            @mkdir($newslettersDir);
            $i = 1;
            /** @var Newsletter $newsletter */
            foreach ($newsletters as $newsletter) {
                $handle = fopen($newslettersDir . "newsletter" . $i . ".txt", "w");
                fwrite($handle, $newsletter->getName() . " (" . $newsletter->getCreated() . ")" . PHP_EOL);
                fclose($handle);
                $i++;
            }
        }

        // Get all broadcasts the member received
        $broadcastMessageRepository = $this->getManager()->getRepository(BroadcastMessage::class);
        $broadcastMessages = $broadcastMessageRepository->findBy(['receiver' => $member]);
        if (!empty($broadcastMessages)) {
            $newslettersDir = $tempDir . 'newsletters/';
            @mkdir($newslettersDir);
            $handle = fopen($newslettersDir . "received.txt", "w");
            /** @var BroadcastMessage $broadcastMessage */
            foreach ($broadcastMessages as $broadcastMessage) {
                fwrite($handle, $broadcastMessage->getNewsletter()->getName() . " on " . $broadcastMessage->getUpdated() . PHP_EOL);
            }
            fclose($handle);
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareCommunityNewsInformation(string $tempDir, Member $member)
    {
        // Get community news the member wrote
        $newsRepository = $this->getManager()->getRepository(CommunityNews::class);
        $news = $newsRepository->findBy(['createdBy' => $member]);
        if (!empty($news)) {
            $communityNewsDir = $tempDir . 'communitynews/';
            @mkdir($communityNewsDir);
            $i = 1;
            /** @var CommunityNews $communityNews */
            foreach ($news as $communityNews) {
                $handle = fopen($communityNewsDir . "communitynews" . $i . ".txt", "w");
                fwrite($handle, $communityNews->getTitle() . " (" . $communityNews->getCreatedAt() . ")" . PHP_EOL);
                fwrite($handle, $communityNews->getText());
                fclose($handle);
                $i++;
            }
        }

        // Get community news comments the member wrote
        $commentRepository = $this->getManager()->getRepository(CommunityNewsComment::class);
        $comments = $commentRepository->findBy(['author' => $member]);
        if (!empty($comments)) {
            $communityNewsDir = $tempDir . 'communitynews/';
            @mkdir($communityNewsDir);
            $i = 1;
            /** @var CommunityNewsComment $comment */
            foreach ($comments as $comment) {
                $handle = fopen($communityNewsDir . "comment" . $i . ".txt", "w");
                fwrite($handle, $comment->getTitle() . " (" . $comment->getCreated() . ")" . PHP_EOL);
                fwrite($handle, $comment->getText());
                fclose($handle);
                $i++;
            }
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareDonations(string $tempDir, Member $member): void
    {
        // Get donations the member did
        $donationRepository = $this->getManager()->getRepository(Donation::class);
        $donations = $donationRepository->findBy(['donor' => $member]);
        if (!empty($donations)) {
            $donationDir = $tempDir . 'donations/';
            @mkdir($donationDir);
            $i = 1;
            /** @var Donation $donation */
            foreach ($donations as $donation) {
                $handle = fopen($donationDir . "donation" . $i . ".txt", "w");
                fwrite($handle, "Name: " . $donation->getNamegiven() . PHP_EOL);
                fwrite($handle, "Amount: " . $donation->getAmount() . PHP_EOL);
                fwrite($handle, "Paypal: " . $donation->getReferencepaypal() . PHP_EOL);
                fwrite($handle, "Donated: " . $donation->getCreated() . PHP_EOL);
                fwrite($handle, "System comment: " . $donation->getSystemcomment() . PHP_EOL);
                fwrite($handle, "Member comment: " . $donation->getMembercomment() . PHP_EOL);
                if ($donation->getStatusprivate() === 'showamountonly') {
                    fwrite($handle, "Visible on site: Amount only");
                } else {
                    fwrite($handle, "Visible on site: Full details");
                }
                fclose($handle);
                $i++;
            }
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareTranslations(string $tempDir, Member $member): void
    {
        // Get translations the member did
        $translationRepository = $this->getManager()->getRepository(Word::class);
        $translations = $translationRepository->findBy(['author' => $member]);
        if (!empty($translations)) {
            $translationDir = $tempDir . 'translations/';
            @mkdir($translationDir);
            $handle = fopen($translationDir . "translations.txt", "w");
            /** @var Word $translation */
            foreach ($translations as $translation) {
                fwrite($handle, "Wordcode: " . $translation->getCode() . PHP_EOL);
                fwrite($handle, "Language: " . $translation->getLanguage()->getEnglishname() . PHP_EOL);
                fwrite($handle, "Created: " . $translation->getCreated() . PHP_EOL);
                fwrite($handle, "Text: " . $translation->getSentence() . PHP_EOL);
                fwrite($handle, PHP_EOL);
            }
            fclose($handle);
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareRightsAndPrivileges(string $tempDir, Member $member): void
    {
        /** @var RightVolunteer[] $volunteerRights */
        $volunteerRights = $member->getVolunteerRights();
        if (!empty($volunteerRights)) {
            $rightsDir = $tempDir . 'rights/';
            @mkdir($rightsDir);
            $handle = fopen($rightsDir . "rights.txt", "w");
            /** @var RightVolunteer $rightVolunteer */
            foreach ($volunteerRights as $rightVolunteer) {
                fwrite($handle, "Right: " . $rightVolunteer->getRight()->getName() . PHP_EOL);
                fwrite($handle, "Scope: " . $rightVolunteer->getScope() . PHP_EOL);
                fwrite($handle, "Level: " . $rightVolunteer->getLevel() . PHP_EOL);
                fwrite($handle, "Assigned: " . $rightVolunteer->getCreated() . PHP_EOL);
                fwrite($handle, PHP_EOL);
            }
            fclose($handle);
        }
        /** @var EntityRepository $privilegesRepository */
        $privilegesRepository = $this->getManager()->getRepository(PrivilegeScope::class);
        $privileges = $privilegesRepository->findBy(['member' => $member]);
        if (!empty($privileges)) {
            $rightsDir = $tempDir . 'rights/';
            @mkdir($rightsDir);
            $handle = fopen($rightsDir . "privileges.txt", "w");
            /** @var PrivilegeScope $privilege */
            foreach ($privileges as $privilege) {
                $type = $privilege->getPrivilege()->getType();
                $scope = $privilege->getType();
                fwrite($handle, "Privilege: " . $privilege->getPrivilege()->getType() . PHP_EOL);
                if ($type == 'Group') {
                    // Naming is a bit odd here
                    if (is_numeric($scope)) {
                        // Check if this group still exists
                        $groupRepository = $this->getManager()->getRepository(Group::class);
                        /** @var Group $group */
                        $group = $groupRepository->findOneBy(['id' => $scope]);
                        if (null !== $group) {
                            fwrite($handle, "Scope: " . $group->getName() . PHP_EOL);
                        } else {
                            fwrite($handle, "Scope: Deleted group (" . $scope . ")" . PHP_EOL);
                        }
                    } else {
                        fwrite($handle, "Scope: " . $scope . PHP_EOL);
                    }
                } else {
                    fwrite($handle, "Scope: " . $scope . PHP_EOL);
                }
                fwrite($handle, "Role: " . $privilege->getRole()->getName() . PHP_EOL);
                fwrite($handle, "Updated: " . $privilege->getUpdated() . PHP_EOL);
                fwrite($handle, PHP_EOL);
            }
            fclose($handle);
        }
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

    private function preparePolls(string $tempDir, Member $member)
    {
        /** @var EntityRepository $pollsRepository */
        $pollsRepository = $this->getManager()->getRepository(Poll::class);
        $polls = $pollsRepository->findBy(['creator' => $member]);
        if (!empty($polls)) {
            $pollsDir = $tempDir . 'polls/';
            @mkdir($pollsDir);
            /** @var Poll $poll */
            foreach ($polls as $poll) {
                $handle = fopen($pollsDir . "poll-" . $poll->getId() . ".txt", "w");
                fwrite($handle, "Id: " . $poll->getId() . PHP_EOL);
                $titles = $poll->getTitles();
                foreach ($titles as $title) {
                    fwrite($handle, "Title (" . $title->getLanguage()->getName() . "): " . $title->getSentence() . PHP_EOL);
                }
                // There is only one group allowed for polls at the moment but we drive it safe here
                $groups = $poll->getGroups();
                foreach ($groups as $group) {
                    fwrite($handle, "Limited to group: " . $group->getName() . PHP_EOL);
                }
                $choices = $poll->getChoices();
                /** @var PollChoice $choice */
                foreach ($choices as $choice) {
                    $choiceTexts = $choice->getChoiceTexts();
                    foreach ($choiceTexts as $choiceText) {
                        fwrite($handle, "Choice Text (" . $choiceText->getLanguage()->getName() . "): " . $choiceText->getSentence() . PHP_EOL);
                    }
                }
                fclose($handle);
            }
        }
        /** @var EntityRepository $contributionsRepository */
        $contributionsRepository = $this->getManager()->getRepository(PollContribution::class);
        $contributions = $contributionsRepository->findBy(['member' => $member]);
        if (!empty($contributions)) {
            $pollsDir = $tempDir . 'polls/';
            @mkdir($pollsDir);
            $handle = fopen($pollsDir . "contributions.txt", "w");
            fwrite($handle, "You contributed to the following polls:" . PHP_EOL . PHP_EOL);
            /** @var PollContribution $contribution */
            foreach ($contributions as $contribution) {
                $this->writePollInformation($contribution, $handle);
                if ('' === $contribution->getComment()) {
                    fwrite($handle, "You voted without leaving a comment");
                } else {
                    fwrite($handle, "Comment left: " . $contribution->getComment());
                }
                fwrite($handle, PHP_EOL . PHP_EOL);
            }
            fclose($handle);
        }
        /** @var EntityRepository $resultsRepository */
        $votesRepository = $this->getManager()->getRepository(PollRecordOfChoice::class);
        $votes = $votesRepository->findBy(['member' => $member], ['poll' => 'DESC', 'pollChoice' => 'DESC']);
        if (!empty($votes)) {
            $pollsDir = $tempDir . 'polls/';
            @mkdir($pollsDir);
            $handle = fopen($pollsDir . "votes.txt", "w");
            fwrite($handle, "You following votes have been recorded:" . PHP_EOL . PHP_EOL);
            /** @var PollRecordOfChoice $vote */
            foreach ($votes as $vote) {
                $this->writePollInformation($vote, $handle);
                fwrite($handle, "Choice: " . $vote->getPollChoice()->getChoiceTexts()->first()->getSentence());
                fwrite($handle, PHP_EOL . PHP_EOL);
            }
            fclose($handle);
        }
    }

    private function prepareShouts(string $tempDir, Member $member)
    {
        /** @var EntityRepository $shoutsRepository */
        $shoutsRepository = $this->getManager()->getRepository(Shout::class);
        $shouts = $shoutsRepository->findBy(['member' => $member]);
        if (!empty($shouts)) {
            $shoutsDir = $tempDir . 'shouts/';
            @mkdir($shoutsDir);
            $handle = fopen($shoutsDir . "shouts.txt", "w");
            /** @var Shout $shout */
            foreach ($shouts as $shout) {
                fwrite($handle, "Item: " . $shout->getTable() . " - " . $shout->getTableId() . PHP_EOL);
                fwrite($handle, "Title: " . $shout->getTitle() . PHP_EOL);
                fwrite($handle, "Text: " . $shout->getText() . PHP_EOL);
                fwrite($handle, PHP_EOL);
            }
            fclose($handle);
        }
    }

    private function prepareSpecialRelations(string $tempDir, Member $member)
    {
        /** @var EntityRepository $relationsRepository */
        $relationsRepository = $this->getManager()->getRepository(FamilyAndFriend::class);
        $relations = $relationsRepository->findBy(['owner' => $member]);
        if (!empty($relations)) {
            $relationsDir = $tempDir . 'relations/';
            @mkdir($relationsDir);
            $i = 1;
            /** @var FamilyAndFriend $relation */
            foreach ($relations as $relation) {
                $handle = fopen($relationsDir . "relation." . $i . ".txt", "w");
                fwrite($handle, "Type: " . $relation->getType() . PHP_EOL);
                fwrite($handle, "Relation to: " . $relation->getRelation()->getUsername() . PHP_EOL);
                fwrite($handle, "Comment: " . $relation->getComment() . PHP_EOL);
                fwrite($handle, "Confirmed: " . $relation->getConfirmed() . PHP_EOL);
                fclose($handle);
                $i++;
            }
        }
    }
}
