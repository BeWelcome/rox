<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\ActivityAttendee;
use App\Entity\Comment;
use App\Entity\CryptedField;
use App\Entity\ForumPost;
use App\Entity\GroupMembership;
use App\Entity\Log;
use App\Entity\Member;
use App\Entity\MembersPhoto;
use App\Entity\MemberTranslation;
use App\Entity\Message;
use App\Entity\PasswordReset;
use App\Entity\Preference;
use App\Form\FindUserFormType;
use App\Form\ResetPasswordFormType;
use App\Model\MemberModel;
use App\Repository\MemberRepository;
use App\Repository\MessageRepository;
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Html2Text\Html2Text;
use Mockery\Container;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\Constraints\NotBlank;
use ZipArchive;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class MemberController.
 */
class MemberController extends AbstractController
{
    use MailerTrait;
    use ManagerTrait;
    use TranslatorTrait;
    use TranslatedFlashTrait;

    /**
     * @Route("/mydata/{username}", name="member_get_data")
     *
     * @param Request $request
     * @param Member $member
     * @ParamConverter("member", class="App\Entity\Member", options={"mapping": {"username": "username"}})
     * @param ContainerBagInterface $params
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function getPersonalData(Request $request, Member $member, ContainerBagInterface $params)
    {
        // Either the member themselves or a person from the safety or profile team and the admin can access
        if ($member != $this->getUser()) {
            $this->denyAccessUnlessGranted(
                [Member::ROLE_ADMIN_SAFETYTEAM, Member::ROLE_ADMIN_ADMIN, Member::ROLE_ADMIN_PROFILE],
                null,
                'Unable to access this page!');
        }

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

        // Collect information and store in directory
        $this->collectPersonalData($params, $dirname, $member);

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

        // main dir is left over!
        $member = $this->getUser();
        $response = new BinaryFileResponse( $zipFilename, 200, [
            'refresh' => "5;" . $this->generateUrl('members_profile', [ 'username' => $member->getUsername()], UrlGenerator::ABSOLUTE_URL)
        ] );
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    /**
     * @Route("/member/autocomplete", name="members_autocomplete")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $names = [];
        $callback = trim(strip_tags($request->get('callback')));
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        $entities = $memberRepository->loadMembersByUsernamePart($term);

        foreach ($entities as $entity) {
            $names[] = [
                'id' => $entity['username'],
                'label' => $entity['username'],
                'value' => $entity['username'],
            ];
        }

        $response = new JsonResponse();
        $response->setCallback($callback);
        $response->setData($names);

        return $response;
    }

    /**
     * @Route("/resetpassword", name="member_request_reset_password")
     *
     * @param Request     $request
     * @param MemberModel $memberModel
     *
     * @return Response
     */
    public function requestResetPasswordAction(Request $request, MemberModel $memberModel)
    {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('landingpage');
        }

        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('reset.password', SubmitType::class)
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $member = null;
            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            try {
                /** @var Member $member */
                $member = $memberRepository->loadUserByUsername($data['username']);
            } catch (NonUniqueResultException $e) {
            }
            if (null === $member) {
                $form->addError(new FormError($this->getTranslator()->trans('flash.email.reset.password')));
            } else {
                $token = null;
                try {
                    $token = $memberModel->generatePasswordResetToken($member);
                } catch (Exception $e) {
                }
                if (null === $token) {
                    $this->addTranslatedFlash('error', 'flash.no.reset.password');

                    return $this->redirectToRoute('security_login');
                }

                /* Sent the member a link to follow to reset the password */
                $sent = $this->sendPasswordResetLink(
                    $member,
                    'Password Reset for BeWelcome',
                    $token
                );
                if ($sent) {
                    $this->addTranslatedFlash('notice', 'flash.email.reset.password');

                    return $this->redirectToRoute('security_login');
                }
                $form->addError(new FormError('There was an error sending the password reset link.'));
            }
        }

        return $this->render('member/request.password.reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/resetpassword/{username}/{token}", name="member_reset_password",
     *     requirements={"key": "[a-z0-9]{32}"})
     *
     * @param Request $request
     * @param Member  $member
     * @param $token
     *
     * @return Response
     */
    public function resetPasswordAction(Request $request, Member $member, $token)
    {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('landingpage');
        }

        $repository = $this->getDoctrine()->getRepository(PasswordReset::class);
        /** @var PasswordReset $passwordReset */
        $passwordReset = $repository->findOneBy(['member' => $member, 'token' => $token]);

        if (null === $passwordReset) {
            $this->addTranslatedFlash('error', 'flash.reset.password.invalid');

            return $this->redirectToRoute('member_request_reset_password');
        }

        $diffInDays = $passwordReset->getGenerated()->diffInDays();
        if ($diffInDays > 2) {
            $this->addFlash('error', 'flash.reset.password.invalid');

            return $this->redirectToRoute('member_request_reset_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['password'];
            $member->setPassword($newPassword);
            $em = $this->getDoctrine()->getManager();
            $em->persist($member);
            $em->flush();
            $this->addTranslatedFlash('notice', 'flash.password.reset');

            return $this->redirectToRoute('security_login');
        }

        return $this->render('member/reset.password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/count/messages/unread", name="count_messages_unread")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getUnreadMessagesCount(Request $request)
    {
        $member = $this->getUser();
        $countWidget = $toastWidget = '';
        $lastUnreadCount = (int) ($request->request->get('current'));

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadMessageCount = $messageRepository->getUnreadMessagesCount($member);

        if (($unreadMessageCount !== $lastUnreadCount) && ($unreadMessageCount > $lastUnreadCount)) {
            $countWidget = $this->renderView('widgets/messagescount.hml.twig', [
                'messageCount' => $unreadMessageCount,
            ]);
            $toastWidget = $this->renderView('widgets/messages.toast.html.twig', [
                'messageCount' => $unreadMessageCount,
                'lastMessageCount' => $lastUnreadCount,
            ]);
        }
        $response = new JsonResponse();
        $response->setData([
            'oldCount' => $lastUnreadCount,
            'newCount' => $unreadMessageCount,
            'html' => $countWidget,
            'toast' => $toastWidget,
        ]);

        return $response;
    }

    /**
     * @Route("/count/requests/unread", name="count_requests_unread")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getUnreadRequestsCount(Request $request)
    {
        $member = $this->getUser();
        $countWidget = $toastWidget = '';
        $lastUnreadCount = (int) ($request->request->get('current'));

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        $unreadRequestsCount = $messageRepository->getUnreadRequestsCount($member);

        if (($unreadRequestsCount !== $lastUnreadCount) && ($unreadRequestsCount > $lastUnreadCount)) {
            $countWidget = $this->renderView('widgets/requestscount.html.twig', [
                'requestCount' => $unreadRequestsCount,
            ]);
            $toastWidget = $this->renderView('widgets/requests.toast.html.twig', [
                'requestCount' => $unreadRequestsCount,
                'lastRequestCount' => $lastUnreadCount,
            ]);
        }
        $response = new JsonResponse();
        $response->setData([
            'oldCount' => $lastUnreadCount,
            'newCount' => $unreadRequestsCount,
            'html' => $countWidget,
            'toast' => $toastWidget,
        ]);

        return $response;
    }

    private function sendPasswordResetLink(Member $receiver, $subject, $token)
    {
        $this->sendTemplateEmail('password@bewelcome.org', $receiver, 'reset.password', [
            'receiver' => $receiver,
            'subject' => $subject,
            'token' => $token,
        ]);

        return true;
    }

    private function collectPersonalData(ContainerBagInterface $params, string $dirname, Member $member)
    {
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit','128M');

        /** @var Member $member */
        $memberId = $member->getId();

        $filesystem = new Filesystem();
        $projectDir = $params->get('kernel.project_dir');
        $galleryPath = $projectDir.'/data/gallery/member-'.$memberId.'/';

        // First copy all files for the gallery into the gallery subdirectory
        if (is_dir($galleryPath)) {
            // create gallery sub directory
            $galleryDir = $dirname.'gallery/';
            @mkdir($galleryDir);
            if ($dh = opendir($galleryPath)) {
                while (($file = readdir($dh)) !== false) {
                    if (!is_dir($file)) {
                        $filesystem->copy($galleryPath . $file, $galleryDir . $file);
                    }
                }
                closedir($dh);
            }
        }

        // Copy all profile photos
        $photoDir = $dirname.'photos/';
        @mkdir($photoDir);
        $em = $this->getDoctrine()->getManager();
        $photoRepository = $em->getRepository(MembersPhoto::class);
        /** @var MembersPhoto[] $photos */
        $photos = $photoRepository->findBy(['idmember' => $memberId]);
        foreach($photos as $photo)
        {
            if (is_file($photo->getFilepath())) {
                $filesystem->copy($photo->getFilepath(), $photoDir . basename($photo->getFilepath()));
            }
        }

        // Write all messages into files
        $messageDir = $dirname.'messages/';
        @mkdir($messageDir);
        $messageRepository = $em->getRepository(Message::class);
        /** @var Message[] $messages */
        $messages = $messageRepository->findBy(['sender' => $member]);
        $i = 1;
        foreach($messages as $message)
        {
            $handle = fopen($messageDir."message.".$i.".txt", "w");
            fwrite($handle, $message->getMessage());
            fclose($handle);
            $i++;
        }

        // Add all log information about member
        $logRepository = $em->getRepository(Log::class);
        /** @var Log[] $logs */
        $logs = $logRepository->findBy([ 'member' => $member]);
        if (!empty($logs)) {
            $handle = fopen($dirname."logs.txt", "w");
            foreach($logs as $log)
            {
                fwrite($handle, $log->getType() . ": " . $log->getLogMessage() . " (" . $log->getCreated()->toDateTimeString() . ")".PHP_EOL);
            }
            fclose($handle);
        }

        // now all posts to the forum or groups including status
        $forumRepository = $em->getRepository(ForumPost::class);
        /** @var ForumPost $posts */
        $posts = $forumRepository->findBy(['author' => $member]);
        $postsDir = $dirname.'posts/';
        @mkdir($postsDir);
        foreach($posts as $post)
        {
            $handle = fopen($postsDir."post.".$i.".html", "w");
            fwrite($handle, "<p>Created: ".$post->getCreated()->toDateTimeString()."<br>Status: ".$post->getPostDeleted()."</p>".PHP_EOL);
            fwrite($handle, $post->getMessage());
            fclose($handle);
            $i++;
        }

        // Groups the member is in and why
        $groupMemberships = $member->getGroupMemberships();
        if (!empty($groupMemberships)) {
            $handle = fopen($dirname."groups.txt", "w");
            foreach($groupMemberships as $groupMembership)
            {
                try {
                    fwrite($handle, $groupMembership->getGroup()->getName() . ": " . $groupMembership->getStatus() . " (" . $groupMembership->getCreated()->toDateTimeString() . ")".PHP_EOL);
                }
                catch (Exception $e) {
                    fwrite($handle, "Delected Group: " . $groupMembership->getStatus() . " (" . $groupMembership->getCreated()->toDateTimeString() . ")".PHP_EOL);
                }
                /** @var MemberTranslation $comment */
                foreach($groupMembership->getComments()->getValues() as $comment) {
                    fwrite($handle, $comment->getSentence() . PHP_EOL);
                }
            }
            fclose($handle);
        }

        // Activities the member joined with comment
        $commentsDir = $dirname.'activities/';
        @mkdir($commentsDir);
        $attendeeRepository = $em->getRepository(ActivityAttendee::class);
        /** @var ActivityAttendee[] $activities */
        $activitiesOfMember = $attendeeRepository->findActivitiesOfMember($member);
        if (!empty($activitiesOfMember)) {
            /** @var ActivityAttendee $attendee */
            $i = 0;
            foreach($activitiesOfMember as $attendee)
            {
                $handle = fopen($commentsDir."activitity".$i.".txt", "w");
                fwrite($handle, $attendee->getActivity()->getTitle()."(".$attendee->getActivity()->getId().")".PHP_EOL);
                fwrite($handle, $attendee->getActivity()->getDescription()."(".$attendee->getActivity()->getId().")".PHP_EOL);
                if($attendee->getOrganizer()) {
                    fwrite($handle, "You organized this activity" .PHP_EOL);
                }
                fwrite($handle, $attendee->getComment() . PHP_EOL);
                fclose($handle);
                $i++;
            }
        }


        // Comments the member left others
        $commentsDir = $dirname.'comments/';
        @mkdir($commentsDir);
        $commentRepository = $em->getRepository(Comment::class);
        /** @var Comment[] $comments */
        $comments = $commentRepository->findBy(['fromMember' => $member]);
        if (!empty($comments)) {
            /** @var Comment $comment */
            $i = 0;
            foreach($comments as $comment)
            {
                $handle = fopen($commentsDir."comment".$i.".txt", "w");
                fwrite($handle, $comment->getToMember()->getUsername()."(".$comment->getQuality().")".PHP_EOL);
                fwrite($handle, $comment->getTextwhere() . PHP_EOL);
                fwrite($handle, $comment->getTextfree() . PHP_EOL);
                fclose($handle);
                $i++;
            }
        }

        // Write member information into file:
        $handle = fopen($dirname."memberinfo.txt", "w");
        fwrite($handle, "Username: ".$member->getUsername().PHP_EOL);
        fwrite($handle, "Location:".$member->getCity()->getName().PHP_EOL);
        fwrite($handle, "Birthdate:".$member->getBirthdate().PHP_EOL);
        fwrite($handle, "Email address:".$member->getEmail().PHP_EOL);
        fwrite($handle, "Accommodation".$member->getAccommodation().PHP_EOL);

        $cryptedFields = $member->getCryptedFields();
        /** @var CryptedField $crypted*/
        foreach($cryptedFields as $crypted) {
            fwrite($handle, $crypted->getTablecolumn().":".$crypted->getMemberCryptedValue().PHP_EOL);
        }
        fclose($handle);
        ini_set('memory_limit', $memoryLimit);
    }
}
