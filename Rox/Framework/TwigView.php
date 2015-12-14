<?php

namespace Rox\Framework;

use Rox\Models\Message;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;

use \AbstractBasePage;
use \Twig_Loader_Filesystem;
use \Twig_Environment;
use \RoxModelBase;
use \FlaglistModel;
use \Illuminate\Database\Capsule\Manager as Capsule;

class TwigView extends AbstractBasePage {

    protected $_loader;
    private $_forms = array();
    private $_container;
    private $_environment;
    private $_template;
    private $_parameters = array();
    protected $_words;
    protected $_translator;
    private $_stylesheets = array(
         'bewelcome.css?1',
    );

    private $_lateScriptFiles = array(
        // 'bootstrap/bootstrap.min.js',
        'common/initialize.js',
    );

    private $_earlyScriptFiles = array(
        'common/common.js?1',
        'jquery/jquery-2.1.4.min.js',
        'select2/select2.js',
        'bootstrap/bootstrap.min.js',
        'bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js',
    );

    /**
     * TwigView constructor.
     * @param Router $router
     * @param bool $container
     */
    public function __construct(Router $router, $container = true) {
        $this->_container = $container;
        $this->_loader = new Twig_Loader_Filesystem();
        $this->addNamespace('base');
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
        $this->_words = $this->getWords();

        if (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = 'en';
        }
        $this->_translator = new Translator($_SESSION['lang'], new MessageSelector());
        if ($_SESSION['lang'] <> 'en') {
            $this->_translator->setFallbackLocales(array('en'));
        }
        $this->_translator->addLoader('database', new DatabaseLoader());
        $this->_translator->addResource('database', null, $_SESSION['lang']);
        $this->_environment->addExtension(new TranslationExtension($this->_translator));
        $this->_environment->addExtension(new RoxTwigExtension());
        if ($router != null) {
            $this->_environment->addExtension(new RoutingExtension($router->getGenerator()));
        }
        $this->_environment->addExtension(new \Twig_Extension_Debug());

        // Setting up the form template
        $defaultFormTheme = '@forms/bootstrap_4_layout.html.twig';

        $appVariableReflection = new \ReflectionClass('\Symfony\Bridge\Twig\AppVariable');
        $vendorTwigBridgeDir = dirname($appVariableReflection->getFileName());
        $this->_loader->addPath($vendorTwigBridgeDir . '/Resources/views/Form');

        $formEngine = new TwigRendererEngine(array($defaultFormTheme));
        $formEngine->setEnvironment($this->_environment);
        // add the FormExtension to Twig
        $this->_environment->addExtension(
            new FormExtension(new TwigRenderer($formEngine))
        );

    }

    /**
     * Adds a namespace for Twig templates (set path accordingly)
     *
     * @param $namespace
     * @throws \Twig_Error_Loader
     */
    public function addNameSpace($namespace) {
        $path = realpath(SCRIPT_BASE . 'templates/twig/' . $namespace);
        $this->_loader->addPath($path, $namespace);
    }

    /**
     * @param Form $form
     * @param string $name
     */
    protected function addForm(Form $form, $name = 'form') {
        $this->_forms[$name] = $form->createView();
    }

    private function _getDefaults() {
        $roxModel = new RoxModelBase();
        $member = $roxModel->getLoggedInMember();
        $loggedIn = ($member !== false);
        $messageCount = 0;
        if ($loggedIn) {
            $messageCount =
                Message::where('IdReceiver', (int)$member->id)
                    ->where('WhenFirstRead', '0000-00-00 00:00')
                    ->where('Status', 'Sent')
                    ->count();
        }
        return array(
            'container' => $this->_container,
            'logged_in' => $loggedIn,
            'messages' => $messageCount,
            'username' => ($loggedIn ? $member->Username : ''),
            'meta.robots' => 'ALL',
            'title' => 'BeWelcome'
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
            $langarr[] = $lang;
        }
        $ascending = function($a, $b) {
            if ($a == $b) {
                return 0;
            }
            return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
        };
        usort($langarr, $ascending);

        return array(
            'language' => $_SESSION['lang'],
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
            $this->addNameSpace($namespace);
            $this->_template = '@' . $namespace . '/' . $template;
        } else {
            $this->_template = $template;
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