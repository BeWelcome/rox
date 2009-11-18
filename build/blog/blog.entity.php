<?php


/**
 * represents a single blog post
 *
 */
class BlogEntity extends RoxEntityBase
{

    private $blog_data_columns = array(
        'blog_title',
        'blog_text',
        'blog_start',
        'blog_end',
        'blog_latitude',
        'blog_longitude',
        'blog_geonameid',
        'blog_display_order',
    );

    protected $_table_name = 'blog';

    public function __construct($blog_id = false)
    {
        parent::__construct();
        if (intval($blog_id))
        {
            $this->findById(intval($blog_id));
        }
    }

    /**
     * overloads RoxEntityBase::loadEntity to load related data
     *
     * @param array $data
     *
     * @access protected
     * @return bool
     */
    protected function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
            $query = "SELECT * FROM blog_data WHERE blog_id = {$this->getPKValue()}";
            if ($result = $this->dao->query($query))
            {
                $block = $result->fetch(PDB::FETCH_ASSOC);
                foreach ($block as $key => $value)
                {
                    if ($key != 'blog_id') $this->$key = $value;
                }
            }
        }
        return $status;
    }

    /**
     * overloads RoxEntityBase::insert
     *
     * @access public
     * @return int
     */
    public function insert()
    {
        $this->blog_id = $this->dao->nextId('blog');
        if ($status = parent::insert())
        {
            $data = array();
            foreach ($this->blog_data_columns as $column)
            {
                if ($this->$column) $data[$column] = $this->dao->escape($this->$column);
            }
            if (!empty($data))
            {
                $columns = implode(', ', array_keys($data));
                $column_data = "'" . implode("', '", $data) . "'";
                $query = "INSERT INTO blog_data (blog_id, edited, {$columns}) VALUES ({$this->getPKValue()}, NOW(), {$column_data})";
                $status = !!$this->dao->query($query);
            }
        }
        return $status;
    }

    /**
     * overloads RoxEntityBase::update
     *
     * @access public
     * @return int
     */
    public function update()
    {
        if ($status = parent::update())
        {
            $data = array();
            foreach ($this->blog_data_columns as $column)
            {
                if ($this->$column) $data[$column] = $this->dao->escape($this->$column);
            }
            if (!empty($data))
            {
                $set = array();
                foreach ($data as $key => $value)
                {
                    $set[] = "{$key} = '{$value}'";
                }
                $set = implode(', ', $set);
                $query = "UPDATE blog_data SET {$set} WHERE blog_id = {$this->getPKValue()}";
                $status = !!$this->dao->query($query);
            }
        }
        return $status;
    }

    /**
     * overloads RoxEntityBase::delete
     *
     * @access public
     * @return int
     */
    public function delete()
    {
        $id = $this->getPKValue();
        if ($status = parent::delete())
        {
            $status = !!$this->dao->query("DELETE FROM blog_data WHERE blog_id = {$id}");
        }
        return $status;
    }
}

