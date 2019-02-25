<?php

namespace App\Activator;

use App\Entity\Member;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Translation\Bundle\EditInPlace\ActivatorInterface;

class RoleActivator implements ActivatorInterface
{
    /** @var Security  */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequest(Request $request = null)
    {
        try {
            if ($this->security->isGranted([Member::ROLE_ADMIN_WORDS]))
            {
                /** @var Member $user */
                $user = $this->security->getUser();
                if (null !== $user)
                {
                    if ($user->hasRightsForLocale($request->get('locale')))
                    {
                        return true;
                    }
                }
            }
            return false;

        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }
}