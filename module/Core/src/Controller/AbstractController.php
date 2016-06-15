<?php

namespace Rox\Core\Controller;

use Rox\Core\Exception\InvalidArgumentException;
use Rox\Member\Model\Member;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * The properties here are private because we want to ensure subclasses use the
 * getter methods instead of working with the properties directly.
 *
 * @see http://symfony.com/doc/current/components/dependency_injection/parentservices.html
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractController
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param Router $route
     *
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Gets the associated Router object
     *
     * @return Router
     */
    protected function getRouter()
    {
        return $this->router;
    }

    /**
     * @param FormFactoryInterface $formFactory
     *
     * @return $this
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->_formFactory = $formFactory;

        return $this;
    }

    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param SessionInterface $session
     *
     * @return $this
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * @return SessionInterface
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * @param string $url
     * @param int $status
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * @param string $formType
     *
     * @return FormInterface
     */
    protected function createForm($formType)
    {
        $form = $this->formFactory->create($formType, null, []);

        return $form;
    }

    /**
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param EngineInterface $engine
     *
     * @return $this
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     *
     * @return $this
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    public function render($name, array $parameters = [])
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                '$name parameter must be string reference.'
            );
        }

        return $this->engine->render($name, $parameters);
    }

    /**
     * @return Member|null
     */
    public function getMember()
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || $token instanceof AnonymousToken) {
            return;
        }

        return $token->getUser();
    }
}
