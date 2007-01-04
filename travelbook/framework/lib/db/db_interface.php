<?php
/**
 * Interface for data access objects
 *
 * @package db
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: db.lib.php 58 2006-06-21 11:52:18Z kang $
 */
interface PDB_frame
{
    public function createSequence($name);
    public function dropSequence($name);
    public function escape($str);
    public function exec($statement);
    public function getErrMsg();
    public function getErrNo();
    public function nextId($name);
    public function prepare($statement);
    public function query($statement);
    public function ready();
}
?>