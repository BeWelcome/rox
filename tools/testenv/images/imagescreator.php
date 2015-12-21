<?php


class ImagesCreator
{
    public $db; // instance of DatabaseController
    public $imgCount; // number of produced images
    public $lastId = array(); // start id's defined in status.csv
    
    public function __construct()
    {
        $this->getDB();
        $this->imgCount = 0;
        $this->getStatus();
    }

    /**
     * Create images and display result
     *
     * @param integer $limit Number of items to be created maximum
     * @access public
     **/    
    public function getImages($limit = null)
    {
        $limit = $this->getLimit();
        $lastId = $this->getLastId();
        $t1 = time();
        $this->lastId[$this->getType()] = $this->createImages($limit,$lastId);
        $t2 = time();
        $this->displayResult($t2-$t1);
        $this->writeLastId();
    }

    /**
     * Retrieve limit from URL or cli
     *
     * Limit is to be defined as a url paramater, <type>=<number>
     * or a cli parameter, --<type>=<number>
     * where type is one of the relevant types, <number> is the maximum number
     * of imagesets that will be created
     * 'max' can be used as a value to indicate there's no limit
     *
     * @access protected
     * @return integer|boolean Number of images to be created as a maximum, false if no limit applies
     **/
    protected function getLimit()
    {
        $type = $this->getType();
        
        // get variables from cli, in case it is run through there
        $cliOption = getopt('',array($type . '::'));
        
        if (isset($_GET[$type])){
            $limitVar = $_GET[$type];
        } elseif (isset($cliOption[$type])){
            $limitVar = $cliOption[$type];            
        } else {
            $limitVar = '';
        }
        
        // default to zero, if parameter is omitted or empty
        if ($limitVar === ''){
            return 0;
        }
        
        // no limit if value 'max' is given
        if (strtolower($limitVar) === 'max'){
            return false;
        }
        
        // use limit as given in URL
        return (int)$limitVar;
    }

    /**
     * Read status.csv
     *
     * @access protected
     **/    
    protected function getStatus()
    {
        if (is_readable('status.csv')){
            if (($handle = fopen('status.csv', 'r')) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $this->lastId[$data[0]] = $data[1];
                }
            }
        fclose($handle);
        }
    }

    /**
     * Get the last previously processed item for this type
     *
     * @access protected
     **/    
    protected function getLastId(){
        $type = $this->getType();
        if (isset($this->lastId[$type]))
        {
            return (int)$this->lastId[$type];
        }
        return 0;
    }

    /**
     * Write new value to status.csv
     *
     * @access protected
     **/    
    protected function writeLastId()
    {
        $type = $this->getType();
        $handle = fopen('status.csv', 'w');
        foreach ($this->lastId as $key => $val){
            if ($val>0){
                fputcsv($handle,array($key,$val));
            }
        }
        fclose($handle);
    }
    
    /**
     * Create images
     *
     * @param integer $limit Number of items to be created maximum
     * @access protected
     **/    
    protected function createImages($limit,$startId)
    {
        $lastId = $startId;
        foreach ($this->getImageRecords($limit,$startId) as $pic){
            $this->getDummyImage($pic);
            $lastId = $pic['picid'];
        }
        return $lastId;
    }
    
    /**
     * Display number of created images and amount of time taken
     *
     * @param integer $time Number of seconds from start to finish of creation process
     * @access protected
     **/    
    protected function displayResult($time)
    {
        echo $time . ' secs for '.
            (int)$this->imgCount.' '.
            $this->getType().'images.'.
            ($time>1?' ('.(int)($this->imgCount/$time).' imgs/sec)':'').
            '<br>'.PHP_EOL;
    }

    /**
     * Get DB parameters from ini file and instanciate database-object
     *
     * @access protected
     **/    
    protected function getDB()
    {
        $params = parse_ini_file('../../../rox_local.ini');
        $this->db = new DatabaseController($params['dsn'],$params['user'],$params['password']);
    }
    
    /**
    * Check if limit should be used and create code for that
    *
    * @access protected
    * @return string Sql code to limit results
    **/
    protected function getLimitText($limit)
    {
        if ($limit > 0){
            return 'LIMIT ' . (int)$limit;
        } else {
            return '';
        }
    }

}
