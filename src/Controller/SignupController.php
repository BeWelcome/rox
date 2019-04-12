<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Html2Text\Html2Text;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignupController extends AbstractController
{
    /**
     * @Route("/signup/finish", name="signup_finish")
     *
     * @param Request             $request
     * @param TranslatorInterface $translator
     * @param \Swift_Mailer       $mailer
     *
     * @return Response
     */
    public function finishSignup(Request $request, TranslatorInterface $translator, \Swift_Mailer $mailer)
    {
        $signupVars = $request->getSession()->get('SignupBWVars');

        if (!empty($signupVars)) {
            // \todo Write info in to database
            $email = $signupVars['email'];
            $username = $signupVars['username'];
            $key = hash('sha256', $email.' - '.$username);
            $subject = $translator->trans('signup.confirm.email');
            $parameters = [
                'subject' => $subject,
                'username' => $username,
                'email' => $email,
                'key' => $key,
            ];
            $body = $this->renderView(
                'emails/signup.html.twig',
                $parameters
            );
            $converter = new Html2Text($body, [
                'do_links' => 'table',
                'width' => 75
            ]);
            $plainText = $converter->getText();

            // Send email with confirmation link
            $message = new \Swift_Message();
            $message
                ->setSubject($subject)
                ->setFrom(
                    [
                        'signup@bewelcome.org' => 'BeWelcome',
                    ]
                )
                ->setTo($email)
                ->setBody(
                    $body,
                    'text/html'
                )
                ->addPart(
                    $plainText,
                    'text/plain'
                )
            ;
            $recipientsCount = $mailer->send($message);
            if (1 !== $recipientsCount) {
                // \todo Mail couldn't be sent
                // Do something about it!
            }

            // show finish page
            return $this->render('signup/finish.html.twig', $parameters);
        }
        return $this->render('signup/error.html.twig');
    }

    /**
     * @Route("/signup/confirm/{username}/{regkey}", name="signup_confirm")
     *
     * @param TranslatorInterface $translator
     * @param $username
     * @param $regkey
     *
     * @return Response
     * @throws \Exception
     */
    public function confirmEmailAddressAction(TranslatorInterface $translator, $username, $regkey)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        /** @var Member $member */
        $member = $memberRepository->findOneBy(['username' => $username]);
        if (null === $member) {
            $this->addFlash('error', $translator->trans('flash.username.invalid'));

            return $this->redirectToRoute('login');
        }
        $email = $member->getEmail();
        $key = hash('sha256', $email.' - '.$username);
        if ($regkey === $key) {
            // Yeah, successfully confirmed email address
            $member->setStatus('Active');
            $member->setLastlogin(new \DateTime());
            $em->persist($member);
            $em->flush();

            $this->addFlash('notice', $translator->trans('flash.signup.activated'));

            return $this->redirect('/login');
        }
        $this->addFlash('error', $translator->trans('flash.key.invalid'));

        return $this->redirect('/login');
    }
}
