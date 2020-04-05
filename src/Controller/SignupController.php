<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Utilities\MailerTrait;
use App\Utilities\MessageTrait;
use App\Utilities\TranslatorTrait;
use Exception;
use Html2Text\Html2Text;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignupController extends AbstractController
{
    use TranslatorTrait;
    use MailerTrait;

    /**
     * @Route("/signup/finish", name="signup_finish")
     *
     * @param Request $request
     * @param LoggerInterface $logger
     * @return Response
     * @throws Exception
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
                throw new Exception("No member found in database. Terminating.");
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
     * @param AuthenticationUtils $helper
     * @return Response
     */
    public function resendConfirmationEmail($username, AuthenticationUtils $helper)
    {
        if ($helper->getLastUsername() !== $username)
        {
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
     * @Route("/signup/confirm/{username}/{regkey}", name="signup_confirm")
     *
     * @param $username
     * @param $regkey
     *
     * @throws Exception
     *
     * @return Response
     */
    public function confirmEmailAddress($username, $regkey)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        /** @var Member $member */
        $member = $memberRepository->findOneBy(['username' => $username]);
        if (null === $member) {
            $this->addFlash('error', $this->getTranslator()->trans('flash.username.invalid'));

            return $this->redirectToRoute('login');
        }
        if ($regkey === $member->getRegistrationKey()) {
            // Yeah, successfully confirmed email address
            $member
                ->setStatus('Active')
                ->setRegistrationKey('')
            ;
            $em->persist($member);
            $em->flush();

            $this->addFlash('notice', $this->getTranslator()->trans('flash.signup.activated'));

            return $this->redirect('/login');
        }
        $this->addFlash('error', $this->getTranslator()->trans('flash.key.invalid'));

        return $this->redirect('/login');
    }
}
