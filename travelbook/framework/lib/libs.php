<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Adding libraries to autoloading
 * 
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: libs.php 122 2006-07-13 16:48:45Z kang $
 */
if (!defined('SCRIPT_BASE'))
    throw new Exception('Internal base error!', 0);
if (!defined('LIB_DIR'))
    throw new Exception('Internal base error!', 0);

$version = '0.0';
/**
 * The version of this library set 
 */
define('PLATFORM_VERSION', $version);
require_once SCRIPT_BASE.'lib/misc/classes.autoload.php';
$Classes = Classes::get();
//***************************************************************
// Miscellaneous
//***************************************************************
$Classes->addClass('PException',    SCRIPT_BASE.'lib/misc/exception.lib.php');
$Classes->addClass('PPHP',          SCRIPT_BASE.'lib/misc/phpi.lib.php');
$Classes->addClass('PVars',         SCRIPT_BASE.'lib/misc/vars.lib.php');
$Classes->addClass('PVarObj',       SCRIPT_BASE.'lib/misc/var_obj.lib.php');
$Classes->addClass('PFunctions',    SCRIPT_BASE.'lib/misc/functions.lib.php');
$Classes->addClass('PModules',      SCRIPT_BASE.'lib/misc/modules.lib.php');
$Classes->addClass('PDate',         SCRIPT_BASE.'lib/misc/date.lib.php');
$Classes->addClass('PSurveillance', SCRIPT_BASE.'lib/misc/surveillance.lib.php');
$Classes->addClass('PDataDir',      SCRIPT_BASE.'lib/misc/datadir.lib.php');
//***************************************************************
// DB
//***************************************************************
$Classes->addClass('PDB',                 SCRIPT_BASE.'lib/db/db.lib.php');
$Classes->addClass('PDB_frame',           SCRIPT_BASE.'lib/db/db_interface.php');
$Classes->addClass('PDBStatement',        SCRIPT_BASE.'lib/db/db_statement.lib.php');
$Classes->addClass('PDBStatement_mysql',  SCRIPT_BASE.'lib/db/db_statement_mysql.lib.php');
$Classes->addClass('PDBStatement_mysqli', SCRIPT_BASE.'lib/db/db_statement_mysqli.lib.php');
$Classes->addClass('PDB_mysql',           SCRIPT_BASE.'lib/db/db_mysql.lib.php');
$Classes->addClass('PDB_mysqli',          SCRIPT_BASE.'lib/db/db_mysqli.lib.php');
//***************************************************************
// Handler
//***************************************************************
$Classes->addClass('PPostHandler', SCRIPT_BASE.'lib/handler/posthandler.lib.php');
$Classes->addClass('PRequest',     SCRIPT_BASE.'lib/handler/requesthandler.lib.php');
//***************************************************************
// Application control
//***************************************************************
$Classes->addClass('PApplication',   SCRIPT_BASE.'lib/application/app_interface.php');
$Classes->addClass('PApps',          SCRIPT_BASE.'lib/application/apps.lib.php');
$Classes->addClass('PAppModel',      SCRIPT_BASE.'lib/application/app_model.lib.php');
$Classes->addClass('PAppView',       SCRIPT_BASE.'lib/application/app_view.lib.php');
$Classes->addClass('PAppController', SCRIPT_BASE.'lib/application/app_controller.lib.php');
//***************************************************************
// XML
//***************************************************************
$Classes->addClass('PData',     SCRIPT_BASE.'lib/xml/xml_data.lib.php');
$Classes->addClass('PSafeHTML', SCRIPT_BASE.'lib/xml/safehtml.lib.php');
//***************************************************************
// PEAR
//***************************************************************
$Classes->addClass('Mail', 'Mail.php');
?>