<?php


class DatabaseSummaryPage extends DebugPage
{
    protected function getSubmenuActiveItem() {
        return 'dbsummary';
    }
    
    protected function teaserHeadline()
    {
        echo 'Debug Page - All Tables';
    }
    
    protected function column_col3()
    {
        $model = $this->model;
        $databases = $model->getTablesByDatabase();
        $widget = new DatabaseSummaryWidget;
        echo '
        <style>';
        $widget->printCSS();
        echo '
        </style>';
        foreach ($databases as $dbname => $tables) {
            echo '
            <h3>Database "'.$dbname.'"</h3>';
            $widget = new DatabaseSummaryWidget;
            $widget->items = $tables;
            $widget->render();
            echo '
            <br>';
        }
    }
}


class DatabaseSummaryWidget extends ScrolltableWidget
{
    function printCSS()
    {
        parent::printCSS();
        echo '
        .sqlcolumn {
          margin:1px;
          padding:2px;
          border:1px solid #ddd;
          // background:#ddf;
          float:left;
          // position:relative;
        }
        .sqlcolumn.PRI {
          border:1px solid #888;
          background:white;
        }
        .odd .sqlcolumn {
          // border:1px solid #eee;
          // background:white;
        }
        .sqlcolumn .tooltip {
          display:none;
          position:absolute;
          background:white;
          border:3px solid #aaa;
        }
        .sqlcolumn:hover .tooltip {
          display:block;
        }
        ';
    }
    
    protected function getTableColumns()
    {
        $res = array(
            'name' => 'Table Name',
        );
        foreach ($this->items as $tablename => $fields_by_type) {
            foreach ($fields_by_type as $type => $fields) {
                $type = $this->discriminate($type);
                $res[$type] = $type;
            }
        }
        return $res;
    }
    
    protected function discriminate($type) {
        if (count(explode('int(', $type)) > 1) {
            return 'int-like';
        } else {
            return 'other';
        }
    }
    
    protected function tableCell_name($fields_by_type, $tablename) {
        echo $tablename;
    }
    
    protected function tableCell($showtype, $fields_by_type, $tablename)
    {
        $showfields = array();
        foreach ($fields_by_type as $type => $fields) {
            if ($showtype == $this->discriminate($type)) {
                foreach ($fields as $fieldname => $field) {
                    $showfields[$fieldname] = $field;
                }
            }
        }
        
        foreach ($showfields as $fieldname => $field) {
            echo '
            <div class="sqlcolumn '.$field->COLUMN_KEY.'">
            '.$fieldname.'
            <div class="tooltip">
            <table>';
            foreach ($field as $key => $value) {
                if (!empty($value)) {
                    echo '
                    <tr><td style="text-align:right">'.$key.'</td><td>'.$value.'</td></tr>';
                }
            }
            echo '
            </table>
            </div>
            </div>';
        }
    }
}


?>