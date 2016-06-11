<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Some useful functions
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: functions.lib.php 144 2006-07-16 15:46:25Z kang $
 */
/**
 * Collection of useful functions
 * 
 * May be called statically
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: functions.lib.php 144 2006-07-16 15:46:25Z kang $
 */
class PFunctions {
    const PREGEXP_BASE64 = '%^[a-z0-9\-=]+$%i';
    
	public static function glueParsedUrl($parsed) {
		if (! is_array($parsed)) return false;
		$uri = isset ($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
		$uri .= isset ($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
		$uri .= isset ($parsed['host']) ? $parsed['host'] : '';
		$uri .= isset ($parsed['port']) ? ':'.$parsed['port'] : '';
		$uri .= isset ($parsed['path']) ? $parsed['path'] : '';
		$uri .= isset ($parsed['query']) ? '?'.$parsed['query'] : '';
		$uri .= isset ($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
		return $uri;
	}

	public static function approx($value, $dec) {
		$value += 0.0;
		$unit  = floor( $value * pow( 10, $dec + 1 ) ) / 10;
		$round = round( $unit );
		return $round / pow( 10, $dec );
	}
	
	public static function readCSV ($fileName, $delim, $encl, $optional = TRUE, $lineOffset = 0, $lineLimit = FALSE) {
		try {
			if (!file_exists ($fileName))
				throw new PException ('CSV not found!', PException::ERROR_WARNING);
			$data = file_get_contents ($fileName);
			$data = (preg_match ('%\r\n$%', $data) ? $data : $data . "\r\n");
			$reg = '%((' . $encl . ')' . ($optional ? '?(?(2).*?|[^' . $delim . '\r\n]*)'  :  '([^' . $encl . ']*)' . $encl . '') . ')(?(2)' . $encl . ')('. $delim . '|\r\n)%smi';
	
			$ret = array ();
			$line = 0;
			$i = 1;
			$linesize = FALSE;
			$matches = array();
			if (!preg_match_all ($reg, $data, $matches, PREG_SET_ORDER))
				throw new PException ('Could not read CSV! Maybe it is not in the right format!', PException::ERROR_WARNING);
			foreach ($matches as $match) {
				if ($line < $lineOffset) {
					self::_check_readCSVline ($match, $delim, $i, $line, $linesize);
					continue;
				}
				if ($lineLimit && $line >= $lineLimit)
					break;
				$content = ($match[2] == $encl) ? substr ($match[1], 1, strlen ($match[1]) - 1) : $match[1];
				$ret[$line][] = $content;
				self::_check_readCSVline ($match, $delim, $i, $line, $linesize);
			}
			$ret = array_values ($ret);
			return $ret;
		} catch (PException $e) {
			throw $e;
		} 
	}
    
    public static function hex2base64($h) {
        $tab_fromBin = array (
            '000000' => '0', '000001' => '1', '000010' => '2', '000011' => '3',
            '000100' => '4', '000101' => '5', '000110' => '6', '000111' => '7',
            '001000' => '8', '001001' => '9', '001010' => 'a', '001011' => 'b',
            '001100' => 'c', '001101' => 'd', '001110' => 'e', '001111' => 'f',
            '010000' => 'g', '010001' => 'h', '010010' => 'i', '010011' => 'j',
            '010100' => 'k', '010101' => 'l', '010110' => 'm', '010111' => 'n',
            '011000' => 'o', '011001' => 'p', '011010' => 'q', '011011' => 'r',
            '011100' => 's', '011101' => 't', '011110' => 'u', '011111' => 'v',
            '100000' => 'w', '100001' => 'x', '100010' => 'y', '100011' => 'z',
            '100100' => 'A', '100101' => 'B', '100110' => 'C', '100111' => 'D',
            '101000' => 'E', '101001' => 'F', '101010' => 'G', '101011' => 'H',
            '101100' => 'I', '101101' => 'J', '101110' => 'K', '101111' => 'L',
            '110000' => 'M', '110001' => 'N', '110010' => 'O', '110011' => 'P',
            '110100' => 'Q', '110101' => 'R', '110110' => 'S', '110111' => 'T',
            '111000' => 'U', '111001' => 'V', '111010' => 'W', '111011' => 'X',
            '111100' => 'Y', '111101' => 'Z', '111110' => '-', '111111' => '='
        );
        $d = str_split($h, 1);
        foreach ($d as &$val) {
            $val = str_pad(base_convert($val, 16, 2), 4, '0', STR_PAD_LEFT);
        }
        $d = implode ('', $d);
        if ($pad = strlen($d) % 6) {
            $d = str_repeat('0', 6 - $pad).$d;
        }
        return strtr($d, $tab_fromBin);
    }
    
    public static function base642hex($h) {
        $tab_fromBin = array (
            '000000' => '0', '000001' => '1', '000010' => '2', '000011' => '3',
            '000100' => '4', '000101' => '5', '000110' => '6', '000111' => '7',
            '001000' => '8', '001001' => '9', '001010' => 'a', '001011' => 'b',
            '001100' => 'c', '001101' => 'd', '001110' => 'e', '001111' => 'f',
            '010000' => 'g', '010001' => 'h', '010010' => 'i', '010011' => 'j',
            '010100' => 'k', '010101' => 'l', '010110' => 'm', '010111' => 'n',
            '011000' => 'o', '011001' => 'p', '011010' => 'q', '011011' => 'r',
            '011100' => 's', '011101' => 't', '011110' => 'u', '011111' => 'v',
            '100000' => 'w', '100001' => 'x', '100010' => 'y', '100011' => 'z',
            '100100' => 'A', '100101' => 'B', '100110' => 'C', '100111' => 'D',
            '101000' => 'E', '101001' => 'F', '101010' => 'G', '101011' => 'H',
            '101100' => 'I', '101101' => 'J', '101110' => 'K', '101111' => 'L',
            '110000' => 'M', '110001' => 'N', '110010' => 'O', '110011' => 'P',
            '110100' => 'Q', '110101' => 'R', '110110' => 'S', '110111' => 'T',
            '111000' => 'U', '111001' => 'V', '111010' => 'W', '111011' => 'X',
            '111100' => 'Y', '111101' => 'Z', '111110' => '-', '111111' => '='
        );
        $tab_toBin = array_flip($tab_fromBin);
        $d = strtr($h, $tab_toBin);
        $mod = strlen($d) % 4;
        if (substr($d, 0, $mod) == str_repeat('0', $mod)) {
            $d = substr($d, $mod, strlen($d)-$mod);
        } elseif ($mod) {
        	return false;
        }
        $d = str_split($d, 4);
        foreach ($d as &$val) {
        	$val = base_convert($val, 2, 16);
        }
        return implode('', $d);
    }
	
	private static function _check_readCSVline ($match, $delim, &$i, &$line, &$linesize) {
		if ($match[3] != $delim) {
			if ($i == 0)
				throw new PException ('First line ends without columns!', PException::ERROR_WARNING);
			if (!$linesize)
				$linesize = $i;
			if ($i % $linesize != 0)
				throw new PException ('CSV data inconsistency error in line ' . ($line+1) . ', column ' . ($i - ($line * $linesize) - 1), PException::ERROR_WARNING, '(linesize: ' . $linesize . ', index: ' . $i . ')');
			$line++;
		}
		$i++;
	}

	public static function isEmailAddress ($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

  /**
   * Validates date input.
   * must be in format day.month.year (with one of the following delimiter: './- ').
   */
  public static function isDate ($date) {
    return preg_match('/^(0?[1-9]|[12][0-9]|3[01])[\/\.\- ](0?[1-9]|1[0-2])[\/\.\- ](19|20)\d{2}$/', $date);
  }


    
    public static function isUTF8($string) {
       // From http://w3.org/International/questions/qa-forms-utf-8.html
       return preg_match('%^(?:
             [\x09\x0A\x0D\x20-\x7E]            # ASCII
           | [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
           |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
           | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
           |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
           |  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
           | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
           |  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
       )*$%xs', $string);
    }
    
    public static function generateUUID() {
        if (function_exists('uuid_create')) {
            return uuid_create();
        }
        mt_srand((double)microtime()*1000000);
        $r = mt_rand();
        $uuid = sha1(uniqid($r,true));
        $uuid{12} = '4';
        $n = 8 + (ord($uuid{16}) & 3);
        $hex = '0123456789abcdef';
        $uuid{16} = $hex{$n};

        // return formated uuid
        return substr($uuid,  0, 8)."-"
            .  substr($uuid,  8, 4)."-"
            .  substr($uuid, 12, 4)."-"
            .  substr($uuid, 16, 4)."-"
            .  substr($uuid, 20);
    }
    
    public static function getFileSize($file) {
        if (!file_exists($file) || !is_readable($file))
            return false;
        $s = filesize($file);
        $symbols = array("", "k", "M", "G", "T", "P", "E", "Z", "Y");
        $factor = 1000;
        $digits = 3;
        for($i=0;$i<count($symbols)-1 && $s>=$factor;$i++)
            $s /= $factor;
        $p = strpos($s, ".");
        if($p !== false && $p > $digits) $s = round($s);
        elseif($p !== false) $s = round($s, $digits-$p);
        
        return round($s, $digits) . " " . $symbols[$i] . 'B';
    }

    public static function paginate($s, $currentPage, $itemsPerPage = 5) {
        $max = $s->numRows();
        $maxPage = (int)($max / $itemsPerPage);
        if (($maxPage * $itemsPerPage) < $max) {
            $maxPage++;
        }
        if (!$currentPage) {
            $currentPage = 1;
            $offs = 0;
        } else {
            if ($currentPage > $maxPage) {
                $currentPage = $maxPage;
            }
            $offs = ($currentPage-1) * $itemsPerPage;
        }
        $pages = array();
        $j = 0;
        for ($i = 1; $i <= $maxPage; $i++) {
            if ($i <= ($currentPage - 3) && $i != 1 && $i != 2)
                continue;
            if ($i >= ($currentPage + 3) && $i != ($maxPage) && $i != ($maxPage - 1))
                continue;
            if ($i - $j != 1) {
                $pages[] = 'separator';
            }
            $j = $i;
            $p = array('pageno'=>$i);
            if ($i == $currentPage)
                $p['current'] = true;
            $pages[] = $p; 
        }
        $results = array();
        $s->seek($offs);
        for ($i = 0; $i < $itemsPerPage; $i++) {
            if (!$d = $s->fetch(PDB::FETCH_OBJ))
                break;
            $results[] = $d;
        }
        return array($results, $pages, $maxPage);
    }
    
    function returnBytes($val) {
        $val = trim($val);
        $last = strtolower($val{strlen($val)-1});
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    public static function randomString($len) {
        $times = ($len % 40) + 1;
        $random = '';
        for ($i = 0; $i < $times; $i++) {
            mt_srand((double)microtime()*1000000);
            $r = mt_rand();
            $random .= sha1(uniqid($r,TRUE));
        }
        return substr ($random, 0, $len);   
    }
}

/*
* The ServerToLocalDateTime() function allow to convert server time (whic is GMT) to local time
* localtime will be computed according to preferences
* it use $this->_session->get("TimeOffset") which is initialized according to current member preferences
* @$EntryTimeStamp is the date (it must be a GMT time, taken for the database)to be converted in localtime
* it returns a TimeStamp adjusted according to member local time
*
* WARNING: This function is only to be used in displays !
*
*/
function ServerToLocalDateTime($EntryTimeStamp) {
//	$this->_session->get("TimeOffset")=60*60*2 ; // only used for test at developemnt phase
	if (empty($this->_session->get("TimeOffset"))) {
		return($EntryTimeStamp) ;
	}
	else {
		if ($this->_session->has( 'PreferenceDayLight' ) and ($this->_session->get('PreferenceDayLight')=='Yes')) {
			return($EntryTimeStamp+$this->_session->get("TimeOffset") + $this->_session->get("Param")->DayLightOffset) ;
		}
		else {
			return($EntryTimeStamp+$this->_session->get("TimeOffset")) ;
		}
	}
} // end of LocaldateTime
?>
