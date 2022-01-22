<?php

namespace App\Security;

use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Repository\MemberRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private const LOGIN_CHECK_ROUTE = 'security_check';
    private const LOGIN_ROUTE = 'security_login';

    /**
     * @var MemberRepository
     */
    private $memberRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        MemberRepository $memberRepository,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->memberRepository = $memberRepository;
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_CHECK_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var Member $member */
        $member = $token->getUser();
        $status = $member->getStatus();
        if (MemberStatusType::ACTIVE !== $status && MemberStatusType::CHOICE_INACTIVE !== $status) {
            $member->setStatus(MemberStatusType::ACTIVE);
        }
        $firstLogin = null === $member->getLastLogin();
        if ($firstLogin) {
            $url = $this->urlGenerator->generate('editmyprofile');
        } else {
            $url = $this->getTargetPath($request->getSession(), $providerKey)
                ?? $this->urlGenerator->generate('homepage');
        }
        $member->setLastLogin(new DateTime());
        $member->setRemindersWithOutLogin(0);

        $this->entityManager->persist($member);
        $this->entityManager->flush();

        return new RedirectResponse($url);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
