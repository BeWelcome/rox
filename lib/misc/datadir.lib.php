<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Datadir class
 *
 * @package core  
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
/**
 * Datadir class
 *  
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class PDataDir {
    protected $dir;
    
    public function __construct($dir) {
        $datadir = DATA_DIR.$dir;
        if (PPHP::os() == 'WIN') {
        	$datadir = str_replace('/', '\\', $datadir);
        }
        if (!file_exists($datadir)) {
            @mkdir($datadir, 0700, true);
        }
        if(!is_dir($datadir) || !is_writable($datadir)) {
            throw new PException('Data subdir error!');
        }
        $this->dir = $datadir;
    }
    
    public function copyTo($file, $dest) {
        if (!file_exists($file))
            return false;
        if (!copy($file, $this->dir.'/'.$dest))
            return false;
        return true;
    }
    
    public function dirName() {
        return $this->dir;
    }

    public function fileExists($file) {
        return file_exists($this->dir.'/'.$file);
    }
    
    public function file_Size($file) {
        return filesize($this->dir.'/'.$file);
    }
    
    public function readFile($file) {
        if (!$this->fileExists($file))
            return false;
        @copy($this->dir.'/'.$file, 'php://output');
    }
    
    public function delFile($file) {
        if (!$this->fileExists($file))
            return false;
        return @unlink($this->dir.'/'.$file);
    }
    
    public function remove($contextDir = false, $timeout = false, $rmRootDir = true) {
        if (!$contextDir)
            $dir = $this->dir;
        else
            $dir = $contextDir;
        if ($handle = opendir($dir)) {
            while ($obj = readdir($handle)) {
                if ($obj == '.' || $obj == '..')
                    continue;
                if (is_dir($dir.'/'.$obj)) {
                    if (!$this->remove($dir.'/'.$obj))
                           return false;
                } elseif (is_file($dir.'/'.$obj)) {
                    if ($timeout && filemtime($dir.'/'.$obj) + $timeout > time())
                        continue;
                    if (!@unlink($dir.'/'.$obj))
                        return false;
                }
            }
        }
        closedir($handle);
        if ($rmRootDir && !@rmdir($dir))
            return false;
        return true;
    }
}
?>