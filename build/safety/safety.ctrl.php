<?php
/**
 * Safety controller class.
 *
 * @author sitatara
 */
class SafetyController extends RoxControllerBase
{
    /**
     * Declaring private variables.
     */
    private $_model;
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new SafetyModel();
    }

    /**
     * Redirects to safety main page
     */
    public function safety() {
        $page = new SafetyMainPage;
		return $page;
    }

    /**
     * Redirects to safety basics
     */
    public function safetyBasics() {
        $page = new SafetyBasicsPage;
		return $page;
    }
	
	/**
     * Redirects to safety whattodo
     */
    public function safetyWhatToDo() {
        $page = new SafetyWhatToDoPage;
		return $page;
    }

	/**
     * Redirects to safety tips
     */
    public function safetyTips() {
        $page = new SafetyTipsPage;
		return $page;
    }
	
	/**
     * Redirects to safety female
     */
    public function safetyFemale() {
        $page = new SafetyFemalePage;
		return $page;
    }
	
	/**
     * Redirects to safety faqs
     */
    public function safetyFaq() {
        $page = new SafetyFaqPage;
		return $page;
    }
	
	/**
     * Redirects to safety team
     */
    public function safetyTeam() {
        $page = new SafetyTeamPage;
		return $page;
    }
	
	/**
     * Redirects to safety contact
     */
    public function safetyContact() {
        $this->redirectAbsolute($this->router->url('feedback?IdCategory=2'));
		return $page;
    }
}
