<?php

namespace Rox\Framework;

use Rox\Models\Message;
use Rox\Security\RoxUserProvider;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

use \AbstractBasePage;
use \Twig_Loader_Filesystem;
use \Twig_Environment;
use \FlaglistModel;

class TwigView extends AbstractBasePage {
    protected $_loader;
    private $_forms = array();
    private $_container;
    private $_request;

    private $_environment;
    private $_template;
    private $_parameters = array();
    protected $_words;
    protected $_translator;
    private $_stylesheets = array(
        'bewelcome.css',
        '/script/tether-1.1.1/css/tether.min.css'
    );

    private $_lateScriptFiles = array(
        'tether-1.1.1/js/tether.js',
        'bootstrap/bootstrap.js',
        'bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js',
        'common/initialize.js',
    );

    private $_earlyScriptFiles = array(
        'jquery/jquery-2.1.4.js',
        'select2/select2.js',
        'common/common.js?1',
    );

    /**
     * TwigView constructor.
     * @param Router $router
     * @param Request $request
     * @param bool $container
     */
    public function __construct(Router $router, $container = true, Request $request = null) {
        parent::__construct();
        $this->_container = $container;
        $this->_request = $request;
        $this->_loader = new Twig_Loader_Filesystem();
        $this->addNamespace('base');
        $this->addNamespace('start');
        $this->addNamespace('macros');
        $this->addNamespace('forms');

        $this->_environment = new Twig_Environment(
            $this->_loader ,
            array(
                'cache' => SCRIPT_BASE . 'data/twig',
                'auto_reload' => true,
                'debug' => true,
            )
        );
        $lang = $this->_session->get( 'lang', 'en' );
        \PVars::get()->lang = $lang;
        $this->_words = $this->getWords();
        if (!$this->_session->has( 'lang' )) {
            $this->_session->set( 'lang', 'en' );
        }
        $this->_translator = new Translator($this->_session->get('lang'), new MessageSelector());
        if ($this->_session->get('lang') <> 'en') {
            $this->_translator->setFallbackLocales(array('en'));
        }
        $this->_translator->addLoader('database', new DatabaseLoader());
        $this->_translator->addResource('database', null, $this->_session->get('lang', 'en'));
        $this->_environment->addExtension(new TranslationExtension($this->_translator));
        $this->_environment->addExtension(new RoxTwigExtension());

        if ($router != null) {
            $this->_environment->addExtension(new RoutingExtension($router->getGenerator()));
        }
        $this->_environment->addExtension(new \Twig_Extension_Debug());
        $this->_environment->getExtension('core')->setTimezone('Europe/Paris');
    }

    /**
     * Adds a namespace for Twig templates (set path accordingly)
     *
     * @param $namespace
     * @throws \Twig_Error_Loader
     */
    public function addNamespace($namespace) {
        $path = realpath(SCRIPT_BASE . 'templates/twig/' . $namespace);
        $this->_loader->addPath($path, $namespace);
    }

    public function initializeFormComponent($inlineForms = false) {
        $formTheme = '@forms/bs4' . (($inlineForms) ? '.inline' : '') . '.html.twig';

        $appVariableReflection = new \ReflectionClass(
            '\Symfony\Bridge\Twig\AppVariable'
        );
        $vendorTwigBridgeDir = dirname(
            $appVariableReflection->getFileName()
        );
        $this->_loader->addPath(
            $vendorTwigBridgeDir.'/Resources/views/Form'
        );

        $formEngine = new TwigRendererEngine(array($formTheme));
        $formEngine->setEnvironment($this->_environment);
        // add the FormExtension to Twig
        $this->_environment->addExtension(
            new FormExtension(new TwigRenderer($formEngine))
        );

    }
    /**
     * @param Form|FormInterface $form
     * @param string $name
     */
    public function addForm(Form $form, $name = 'form') {
        $this->_forms[$name] = $form->createView();
    }

    private function _getDefaults() {
        $teams = [];
        $loggedIn = false;
        $messageCount = 0;
        if ($this->_session) {
            $user = null;
            $username = $this->_session->get('username');
            if (!empty($username)) {
                $userProvider = new RoxUserProvider();
                $user = $userProvider->loadUserByUsername($username);
            }
            if ($user) {
                $loggedIn = true;
                $messageCount =
                    Message::where('IdReceiver', (int)$user->id)
                        ->where('WhenFirstRead', '0000-00-00 00:00')
                        ->where('Status', 'Sent')
                        ->count();
                // Check if member is part of volunteer teams
                $R = \MOD_right::get();
                $allTeams = [
                    [
                        'Words',
                        'AdminWord',
                        'admin/word'
                    ],
                    [
                        'Flags',
                        'AdminFlags',
                        'admin/flags'
                    ],
                    [
                        'Rights',
                        'AdminRights',
                        'admin/rights'
                    ],
                    [
                        'Logs',
                        'AdminLogs',
                        'bw/admin/adminlogs.php'
                    ],
                    [
                        'Comments',
                        'AdminComments',
                        'bw/admin/admincomments.php'
                    ],
                    [
                        'NewMembersBeWelcome',
                        'AdminNewMembers',
                        'admin/newmembers',
                    ],
                    [
                        'MassMail',
                        'AdminMassMail',
                        'admin/massmail'
                    ],
                    [
                        'Treasurer',
                        'AdminTreasurer',
                        'admin/treasurer'
                    ],
                    [
                        'FAQ',
                        'AdminFAQ',
                        'bw/faq.php'
                    ],
                    [
                        'SqlForVolunteers',
                        'AdminSqlForVolunteers',
                        'bw/admin/adminquery.php'
                    ],
                ];
                foreach ($allTeams as $team) {
                    if ($R->hasRight($team[0], "", $this->_session->get('id'))) {
                        $cls = new \stdClass();
                        $cls->link = $team[2];
                        $cls->trans = $team[1];
                        $teams[] = $cls;
                    }
                }
            }
        }

        return array(
            'container' => $this->_container,
            'logged_in' => $loggedIn,
            'messagecount' => $messageCount,
            'username' => ($loggedIn ? $user->Username : ''),
            'meta.robots' => 'ALL',
            'title' => 'BeWelcome',
            'teams' => $teams
        );
    }

    private function _getLanguages() {
        $model = new FlaglistModel();
        $langarr = array();
        foreach($model->getLanguages() as $language) {
            $lang = new \stdClass;
            $lang->NativeName = $language->Name;
            $lang->TranslatedName = $this->_words->getSilent($language->WordCode);
            $lang->ShortCode = $language->ShortCode;
            $langarr[$language->ShortCode] = $lang;
        }
        $defaultLanguage = $langarr[$this->_session->get( 'lang' , 'en')];
        usort($langarr, function($a, $b) {
            if ($a->TranslatedName == $b->TranslatedName) {
                return 0;
            }
            return (strtolower($a->TranslatedName) < strtolower($b->TranslatedName)) ? -1 : 1;
        });

        return array(
            'language' => $defaultLanguage,
            'languages' => $langarr
        );
    }

    protected function addStylesheet($stylesheet) {
        $this->_stylesheets[] = $stylesheet;
    }

    protected function addEarlyJavascriptFile($scriptFile, $prepend = false) {
        if ($prepend) {
            array_unshift($this->_earlyScriptFiles, $scriptFile);
        } else {
            $this->_earlyScriptFiles[] = $scriptFile;
        }
    }

    protected function addLateJavascriptFile($scriptFile, $prepend = false) {
        if ($prepend) {
            array_unshift($this->_lateScriptFiles, $scriptFile);
        } else {
            $this->_lateScriptFiles[] = $scriptFile;
        }
    }

    protected function _getStylesheets() {
        return array(
            'stylesheets' => $this->_stylesheets
        );
    }

    protected function _getEarlyJavascriptFiles() {
        return array(
            'earlyScriptFiles' => $this->_earlyScriptFiles
        );
    }

    protected function _getLateJavascriptFiles() {
        return array(
            'lateScriptFiles' => $this->_lateScriptFiles
        );
    }

    public function setTemplate($template, $namespace = false, $parameters = array()) {
        if ($namespace) {
            $this->addNamespace($namespace);
            $this->_template = '@' . $namespace . '/' . $template;
        } else {
            $this->_template = $template;
        }
        if (empty($parameters)) {
            $parameters = ['title' => 'BeWelcome'];
        }
        $this->addParameters($parameters);
    }

    /**
     * Set the parameters to be used during rendering
     *
     * @param array $parameters
     */
    public function addParameters($parameters) {
        $this->_parameters = array_merge($this->_parameters, $parameters);
    }

    /**
     * Actually renders the page
     *
     * @return string
     */
    public function render() {
        $finalParameters = array_merge(
            $this->_parameters,
            $this->_forms,
            $this->_getStylesheets(),
            $this->_getLanguages(),
            $this->_getEarlyJavascriptFiles(),
            $this->_getLateJavascriptFiles(),
            $this->_getDefaults()
        );
        return $this->_environment->render($this->_template, $finalParameters);
    }
}