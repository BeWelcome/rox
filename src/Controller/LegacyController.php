<?php

namespace App\Controller;

use App\Entity\NewMember as Member;
use App\LegacyKernel\LegacyHttpKernel;
use App\Utilities\SessionSingleton;
use App\Utilities\TranslatorSingleton;
use EnvironmentExplorer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class LegacyController extends AbstractController
{
    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function showLegacyPage(
        Request $request,
        LegacyHttpKernel $legacyHttpKernel,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ParameterBagInterface $params,
        Security $securityHelper,
    ): Response {
        // Kick-start the Symfony session. This replaces session_start() in the
        // old code, which is now turned off.
        /** @var Session $session */
        $session = $request->getSession();
        $session->start();

        // Make sure the Rox classes find this session and the translator
        SessionSingleton::createInstance($session);
        TranslatorSingleton::createInstance($translator);

        $environmentExplorer = new EnvironmentExplorer($urlGenerator);
        $environmentExplorer->initializeGlobalState(
            $params->get('database_host'),
            $params->get('database_name'),
            $params->get('database_user'),
            $params->get('database_password'),
            $params->get('manticore.host'),
            $params->get('manticore.port')
        );

        $pathInfo = $request->getPathInfo();
        $public = (!str_contains($pathInfo, '/safety'))
            || (!str_contains($pathInfo, '/about'))
            || (!str_contains($pathInfo, '/signup'));
        if (!$session->has('IdMember')) {
            /** @var Member $member */
            $member = $securityHelper->getUser();
            $rememberMeToken = $securityHelper->getToken();
            if (null === $rememberMeToken && !$public) {
                throw new AccessDeniedException();
            }
            if (false !== $rememberMeToken) {
                if (null !== $member) {
                    $session->set('IdMember', $member->getId());
                    $session->set('MemberStatus', $member->getStatus());
                    $session->set('Username', $member->getUsername());
                }
            }
        }

        return $legacyHttpKernel->handle(
            $request
        );
    }
}
