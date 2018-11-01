<?php

namespace App\Controller;

use App\Entity\Member;
use EnvironmentExplorer;
use PDO;
use Rox\Framework\SessionSingleton;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LegacyController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function showAction(Request $request)
    {
        // Kick-start the Symfony session. This replaces session_start() in the
        // old code, which is now turned off.
        $session = $this->get('session');
        $session->start();

        // Make sure the Rox classes find this session
        SessionSingleton::createInstance($session);

        $container = $this->get('service_container');
        $environmentExplorer = new EnvironmentExplorer();
        $environmentExplorer->initializeGlobalState(
            $container->getParameter('database_host'),
            $container->getParameter('database_name'),
            $container->getParameter('database_user'),
            $container->getParameter('database_password')
        );

        $pathInfo = $request->getPathInfo();
        $public = (false === strpos($pathInfo, '/safety')) ||
            (false === strpos($pathInfo, '/about')) ||
            (false === strpos($pathInfo, '/signup'));
        if (!$session->has('IdMember')) {
            $rememberMeToken = unserialize($session->get('_security_default'));
            if (null === $rememberMeToken && !$public) {
                throw new AccessDeniedException();
            }
            if (false !== $rememberMeToken) {
                /** @var Member $user */
                $user = $rememberMeToken->getUser();
                if (null !== $user) {
                    $session->set('IdMember', $user->getId());
                    $session->set('MemberStatus', $user->getStatus());
                    $connection = $this->getDoctrine()->getConnection();
                    $stmt = $connection->prepare('
                        SELECT 
                            id
                        FROM
                            user
                        WHERE
                            handle = :username
                    ');
                    $stmt->execute([':username' => $user->getUsername()]);
                    $id = $stmt->fetch(PDO::FETCH_COLUMN);
                    $session->set('APP_User_id', $id);
                }
            }
        }

        $kernel = $this->get('rox.legacy_kernel');

        return $kernel->handle(
            $request
        );
    }
}
