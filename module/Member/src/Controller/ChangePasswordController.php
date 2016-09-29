<?php

namespace Rox\Member\Controller;

use Rox\Core\Controller\AbstractController;
use Rox\Member\Service\MemberService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;

class ChangePasswordController extends AbstractController
{
    /**
     * @var MemberService
     */
    protected $memberService;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var RememberMeServicesInterface
     */
    protected $rememberMeServices;

    /**
     * @var string
     */
    protected $rememberMeFormParameter;

    /**
     * @var string
     */
    protected $rememberMeCookieName;

    public function __construct(
        MemberService $memberService,
        EncoderFactoryInterface $encoderFactory,
        RememberMeServicesInterface $rememberMeServices
    ) {
        $this->memberService = $memberService;
        $this->encoderFactory = $encoderFactory;
        $this->rememberMeServices = $rememberMeServices;
    }

    /**
     * @todo This function isn't utilised by a frontend page yet. It is here as
     * a reminder to regenerate the remember me token using rememberMeServices
     * whenever a password is changed.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function changePassword(Request $request)
    {
        $member = $this->getMember();

        $encoder = $this->encoderFactory->getEncoder($member);

        if (!$encoder->isPasswordValid(
            $member->getPassword(),
            $request->request->get('old_password'),
            $member->getSalt()
        )) {
            throw new AccessDeniedHttpException;
        }

        $this->memberService->changePassword($member, $request->request->get('password'));

        $response = new Response();

        // If the remember me cookie is set, then regenerate it.
        if ($request->cookies->has($this->rememberMeCookieName)) {
            // Hint to rememberMeServices, as if the checkbox was ticked on the login form
            $request->request->set($this->rememberMeFormParameter, '1');

            $this->rememberMeServices->loginSuccess($request, $response, $this->getTokenStorage()->getToken());
        }

        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        return $response;
    }

    /**
     * @param string $rememberMeFormParameter
     *
     * @return $this
     */
    public function setRememberMeFormParameter($rememberMeFormParameter)
    {
        $this->rememberMeFormParameter = $rememberMeFormParameter;

        return $this;
    }

    /**
     * @param string $rememberMeCookieName
     *
     * @return $this
     */
    public function setRememberMeCookieName($rememberMeCookieName)
    {
        $this->rememberMeCookieName = $rememberMeCookieName;

        return $this;
    }
}
