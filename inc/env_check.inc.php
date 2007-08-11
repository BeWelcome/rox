<?php
/**
 * Check environment
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: env_check.inc.php 122 2006-07-13 16:48:45Z kang $
 */
// example call of requiring extension "xsl"
if (!PPHP::assertExtension('gd')) 
    die('GD lib required!');
//if (!PPHP::assertExtension('xsl')) 
//    die('XSL required!');
?>