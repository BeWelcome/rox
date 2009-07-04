<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Contains the abstract view base
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: app_view.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * View base class
 * 
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @abstract
 */
abstract class PAppView 
{
    /**
     * Each view has a model property
     * 
     * @var object
     */
    private $_model;
    
    /**
     * @param void
     */    
    public function __construct() 
    {
    }
} 
