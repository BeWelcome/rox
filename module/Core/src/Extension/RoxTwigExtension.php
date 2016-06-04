<?php

namespace Rox\Core\Extension;

use Carbon\Carbon;
use Faker\Factory;
use Rox\Member\Model\Member;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;
use Twig_SimpleFunction;

class RoxTwigExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('ago', [$this, 'ago']),
        ];
    }

    public function ago(Carbon $carbon)
    {
        return $carbon->diffForHumans();
    }

    /**
     * Name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'LayoutKit';
    }

    public function getGlobals()
    {
        return [
            'version'   => trim(file_get_contents('VERSION')),
            'version_dt' => Carbon::createFromTimestamp(filemtime('VERSION')),
            'title'     => 'BeWelcome',
            'my_member' => $this->getMember(),
            'faker' => class_exists(Factory::class) ? Factory::create() : null,
            'language'  => [
                'NativeName' => 'English',
                'ShortCode'  => 'en',
            ],
            'languages' => [
                [
                    'NativeName' => 'English',
                    'ShortCode' => 'en',
                    'TranslatedName' => 'English',
                ],
                [
                    'NativeName' => 'French',
                    'ShortCode' => 'fr',
                    'TranslatedName' => 'Francais',
                ],
            ],
        ];
    }

    /**
     * @return Member|null
     */
    protected function getMember()
    {
        $memberId = $this->session->get('IdMember');

        if (!$memberId) {
            return;
        }

        $memberModel = new Member();

        return $memberModel->getById($memberId);
    }
}
