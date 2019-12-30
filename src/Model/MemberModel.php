<?php

namespace App\Model;

use App\Entity\ActivityAttendee;
use App\Entity\BroadcastMessage;
use App\Entity\Comment;
use App\Entity\CommunityNews;
use App\Entity\CommunityNewsComment;
use App\Entity\CryptedField;
use App\Entity\Donation;
use App\Entity\ForumPost;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\MembersPhoto;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\Newsletter;
use App\Entity\PasswordReset;
use App\Repository\ActivityAttendeeRepository;
use App\Repository\MessageRepository;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception as Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class MemberModel
{
    use ManagerTrait;

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
        $em = $this->getManager();
        $this->getManager()->persist($passwordReset);
        $this->getManager()->flush();

        return $token;
    }

    public function collectPersonalData(ContainerBagInterface $params, Member $member)
    {
        // Create temp directory
        $i = 0;
        while ($i < 1000) {
            $dirname = sys_get_temp_dir().'/'.uniqid('mydata_', true);
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
        $dirname = $dirname."/";

        $this->preparePersonalData($dirname, $params->get('kernel.project_dir'), $member);

        $zipFilename = $dirname.'bewelcome-data-'.date('Y-m-d').'.zip';
        $zip = new ZipArchive();
        $zip->open($zipFilename, ZipArchive::CREATE);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $filesToDelete = [];
        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
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
        foreach($filesToDelete as $name => $file) {
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
        ini_set('memory_limit','512M');

        $this->prepareGalleryItems($tempDir, $projectDir, $member);
        $this->prepareProfilePictures($tempDir, $member);
        $this->prepareMessages($tempDir, $member);
        $this->prepareRequests($tempDir, $member);
        $this->prepareLogs($tempDir, $member);
        $this->prepareForumPosts($tempDir, $member);
        $this->prepareGroupInformation($tempDir, $member);
        $this->prepareActivities($tempDir, $member);
        $this->prepareCommentsLeft($tempDir, $member);
        $this->prepareMemberData($tempDir, $member);
        $this->prepareNewsletterInformation($tempDir, $member);
        $this->prepareCommunityNewsInformation($tempDir, $member);
        $this->prepareDonations($tempDir, $member);

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
            if ($dh = opendir($galleryPath)) {
                while (($file = readdir($dh)) !== false) {
                    if (!is_dir($file)) {
                        $ext = $this->imageExtension($galleryPath . $file);
                        $filesystem->copy($galleryPath . $file, $galleryDir . pathinfo($file, PATHINFO_FILENAME) . $ext);
                    }
                }
                closedir($dh);
            }
        }
    }

    private function imageExtension(string $filename) : string
    {
        $mimetype = mime_content_type($filename);
        switch($mimetype)
        {
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
            foreach($variants as $variant) {
                $filepath = $photo->getFilepath().$variant;
                if (is_file($filepath)) {
                    $filesystem->copy($filepath, $pictureDir
                        . pathinfo($filepath, PATHINFO_FILENAME)
                        . $this->imageExtension($filepath));
                }
            }
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareMessages(string $tempDir, Member $member): void
    {
        // Write all messages into files
        $messageDir = $tempDir . 'messages/';
        @mkdir($messageDir);
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository(Message::class);
        /** @var Message[] $messages */
        $messages = $messageRepository->getMessagesSentBy($member);
        $i = 1;
        foreach ($messages as $message) {
            $handle = fopen($messageDir . "message-".$message->getCreated()->toDateString()."-".$i.".html", "w");
            fwrite($handle, $message->getMessage());
            fclose($handle);
            $i++;
        }
    }

    /**
     * @param string $tempDir
     * @param Member $member
     */
    private function prepareRequests(string $tempDir, Member $member): void
    {
        // Write all requests into files
        $requestDir = $tempDir . 'requests/';
        @mkdir($requestDir);
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getManager()->getRepository(Message::class);
        /** @var Message[] $requests */
        $requests = $messageRepository->getRequestsSentBy($member);
        $i = 1;
        foreach ($requests as $request) {
            $handle = fopen($requestDir . "request-".$request->getCreated()->toDateString()."-".$i.".html", "w");
            fwrite($handle, $request->getMessage());
            fclose($handle);
            $i++;
        }
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
            $handle = fopen($postsDir . "post-".$post->getCreated()->toDateString()."-".$i.".html", "w");
            fwrite($handle, "<p>Created: " . $post->getCreated()->toDateTimeString() . "<br>Status: " . $post->getPostDeleted() . "</p>" . PHP_EOL);
            fwrite($handle, $post->getMessage());
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
        // now all posts to the forum or groups including status
        $forumRepository = $this->getManager()->getRepository(ForumPost::class);
        /** @var ForumPost $posts */
        $posts = $forumRepository->findBy(['author' => $member]);
        $postsDir = $tempDir . 'posts/';
        @mkdir($postsDir);
        $i = 1;
        /** @var ForumPost $post */
        foreach ($posts as $post) {
            $handle = fopen($postsDir . "post-".$post->getCreated()->toDateString()."-".$i.".html", "w");
            fwrite($handle, "<p>Created: " . $post->getCreated()->toDateTimeString() . "<br>Status: " . $post->getPostDeleted() . "</p>" . PHP_EOL);
            fwrite($handle, $post->getMessage());
            fclose($handle);
            $i++;
        }
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
                    fwrite($handle, $groupMembership->getGroup()->getName() . ": " . $groupMembership->getStatus() . " (" . $groupMembership->getCreated()->toDateTimeString() . ")" . PHP_EOL);
                } catch (Exception $e) {
                    fwrite($handle, "Deleted Group: " . $groupMembership->getStatus() . " (" . $groupMembership->getCreated()->toDateTimeString() . ")" . PHP_EOL);
                }
                /** @var MemberTranslation $comment */
                foreach ($groupMembership->getComments()->getValues() as $comment) {
                    fwrite($handle, $comment->getSentence() . PHP_EOL);
                }
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
    private function prepareCommentsLeft(string $tempDir, Member $member)
    {
        // Comments the member left others
        $commentsDir = $tempDir . 'comments/';
        @mkdir($commentsDir);
        $commentRepository = $this->getManager()->getRepository(Comment::class);
        /** @var Comment[] $comments */
        $comments = $commentRepository->findBy(['fromMember' => $member]);
        if (!empty($comments)) {
            /** @var Comment $comment */
            $i = 1;
            foreach ($comments as $comment) {
                $handle = fopen($commentsDir . "comment" . $i . ".txt", "w");
                fwrite($handle, $comment->getToMember()->getUsername() . "(" . $comment->getQuality() . ")" . PHP_EOL);
                fwrite($handle, $comment->getTextwhere() . PHP_EOL);
                fwrite($handle, $comment->getTextfree() . PHP_EOL);
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
                fwrite($handle, "Member comment: " . $donation->getStatusprivate() . PHP_EOL);
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
}
