<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Utilities\MailerTrait;
use App\Utilities\TranslatorTrait;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SignupController extends AbstractController
{
    use TranslatorTrait;
    use MailerTrait;

    /**
     * @Route("/signup/finish", name="signup_finish")
     *
     * @throws Exception
     *
     * @return Response
     */
    public function finishSignup(Request $request, LoggerInterface $logger)
    {
        $signupVars = $request->getSession()->get('SignupBWVars');

        if (!empty($signupVars)) {
            $email = $signupVars['email'];
            $username = strtolower($signupVars['username']);
            $key = hash('sha256', strtolower($email) . ' - ' . strtolower($username));

            // Member isn't logged in at this time, so we need to find it in the database.
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
            /** @var Member $member */
            $member = $memberRepository->findOneBy(['username' => $username]);
            if (!$member) {
                throw new Exception('No member found in database. Terminating.');
            }

            $member->setRegistrationKey($key);
            $em = $this->getDoctrine()->getManager();
            $em->persist($member);
            $em->flush();

            $subject = $this->getTranslator()->trans('signup.confirm.email');
            $parameters = [
                'subject' => $subject,
                'username' => $username,
                'email_address' => $email,
                'key' => $key,
            ];

            $this->sendTemplateEmail(
                'signup@bewelcome.org',
                $member,
                'signup',
                $parameters
            );

            // Remove the session variable
            $request->getSession()->remove('SignupBWVars');

            // show finish page
            return $this->render('signup/finish.html.twig', $parameters);
        }

        return $this->render('signup/error.html.twig');
    }

    /**
     * @Route("/signup/resend/{username}", name="resend_confirmation_email")
     *
     * @param $username
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function resendConfirmationEmail($username, AuthenticationUtils $helper)
    {
        if ($helper->getLastUsername() !== $username) {
            throw $this->createAccessDeniedException();
        }

        $memberRepository = $this->getDoctrine()->getRepository(Member::class);
        /** @var Member $member */
        $member = $memberRepository->findOneBy(['username' => $username]);
        if (!$member) {
            throw $this->createAccessDeniedException();
        }

        $subject = $this->getTranslator()->trans('signup.confirm.email');
        $parameters = [
            'subject' => $subject,
            'username' => $username,
            'email_address' => $member->getEmail(),
            'key' => $member->getRegistrationKey(),
        ];

        $this->sendTemplateEmail(
            'signup@bewelcome.org',
            $member,
            'resent',
            $parameters
        );

        return $this->render('signup/resent.html.twig', $parameters);
    }

    /**
     * @Route("/signup/confirm/{username}/{registrationKey}", name="signup_confirm")
     *
     * @param $username
     * @param $registrationKey
     *
     * @return Response
     */
    public function confirmEmailAddress(Request $request, $username, $registrationKey)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        /** @var Member $member */
        $member = $memberRepository->findOneBy(['username' => $username]);
        if (null === $member) {
            $this->addFlash('error', $this->getTranslator()->trans('flash.signup.username.invalid'));

            return $this->redirectToRoute('login');
        }
        if ($registrationKey === $member->getRegistrationKey()) {
            // Yeah, successfully confirmed email address
            $member
                ->setStatus('Active')
                ->setRegistrationKey('')
            ;
            $em->persist($member);
            $em->flush();

            $this->addFlash('notice', $this->getTranslator()->trans('flash.signup.activated'));
            $request->getSession()->set(Security::LAST_USERNAME, $username);

            return $this->redirect('/login');
        }
        $this->addFlash('error', $this->getTranslator()->trans('flash.signup.key.invalid'));

        return $this->redirect('/login');
    }
}
