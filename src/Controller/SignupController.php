<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
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
            $converter = new HtmlConverter([
                'strip_tags' => true,
                'remove_nodes' => 'script'
            ]);
            $plainText = $converter->convert($body);

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
    }

    /**
     * @Route("/signup/confirm/{username}/{regkey}", name="signup_confirm")
     *
     * @param $username
     * @param $regkey
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function confirmEmailAddressAction($username, $regkey)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var MemberRepository $memberRepository */
        $memberRepository = $em->getRepository(Member::class);
        /** @var Member $member */
        $member = $memberRepository->findOneBy(['username' => $username]);
        if (null === $member) {
            $this->addFlash('error', 'Provided key or username isn\'t correct');

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

            $this->addFlash('notice', 'You just confirmed your email address and your profile got activated. Please login now and update your profile.');

            return $this->redirect('/login');
        }
        $this->addFlash('error', 'Provided key isn\'t correct');

        return $this->redirect('/login');
    }
}
