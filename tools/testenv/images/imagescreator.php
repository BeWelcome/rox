<?php

class ImagesCreator
{
    public $db; // instance of DatabaseController
    public $imgCount; // number of produced images
    
    public function __construct()
    {
        $this->getDB();
        $this->imgCount = 0;
    }

    /**
     * Create images and display result
     *
     * @param integer $limit Number of items to be created maximum
     * @access public
     **/    
    public function getImages($limit = false)
    {
        $t1 = time();
        $this->createImages($limit);
        $t2 = time();
        $this->displayResult($t2-$t1);
    }

    /**
     * Create images
     *
     * @param integer $limit Number of items to be created maximum
     * @access protected
     **/    
    protected function createImages($limit)
    {
        foreach ($this->getImageRecords($limit) as $pic){
            $this->getDummyImage($pic);
        }
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
            '<br>';
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
        if ($limit !== false){
            return 'LIMIT ' . (int)$limit;
        } else {
            return '';
        }
    }
}
