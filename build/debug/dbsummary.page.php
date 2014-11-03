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
    
    protected function leftSidebar()
    {
        echo '
        <h3>Search for table names</h3>
        <form action="debug/dbsummary" method="get">
        <input name="table_filter" size="14" value="'.$this->table_filter.'"/>
        <input type="submit" class="button" value="search"/>
        </form>
        <br>
        <h3>Search for column names</h3>
        <form action="debug/dbsummary" method="get">
        <input name="column_filter" size="14" value="'.$this->column_filter.'"/>
        <input type="submit" class="button" value="search"/>
        </form>
        <br>';
    }
    
    protected function column_col3()
    {
        $model = $this->model;
        $tables_sorted = $model->getDatabaseTablesWithFieldsSorted();
        $widget = new DatabaseSummaryWidget;
        echo '
        <style>';
        $widget->printCSS();
        echo '
        </style>';
        
        foreach ($tables_sorted as $schema => $tables_in_schema) {
            echo '
            <h3>Database "'.$schema.'"</h3>';
            $widget = new DatabaseSummaryWidget;
            $tables_filtered = array();
            foreach ($tables_in_schema as $tablename => $table) {
                if ($this->tableFilter($table, $tablename)) {
                    $tables_filtered[$tablename] = $table;
                }
            }
            $widget->items = $tables_filtered;
            $widget->render();
            echo '
            <br>';
        }
    }
    
    protected function tableFilter($table, $tablename)
    {
        if (!$this->column_filter) {
            // return unfiltered
            $column_found = true;
        } else {
            $column_found = false;
            foreach ($table->fields as $type => $fields_with_type) {
                foreach ($fields_with_type as $fieldname => $field) {
                    if (count(explode($this->column_filter, $fieldname)) > 1) {
                        $field->found = true;
                        $column_found = true;
                    }
                }
            }
        }
        if (!$this->table_filter) {
            $table_found = true;
        } else {
            $table_found = (count(explode($this->table_filter, $fieldname)) > 1);
        }
        return $column_found && $table_found;
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
        .sqlcolumn.found {
          background:yellow;
        }
        .sqlcolumn.PRI {
          border:1px solid #888;
          background:white;
        }
        .odd .sqlcolumn {
          // border:1px solid #eee;
          // background:white;
        }
        .hoverme .tooltip {
          display:none;
          position:absolute;
          background:white;
          border:3px solid #aaa;
        }
        .hoverme:hover .tooltip {
          display:block;
        }
        ';
    }
    
    protected function getTableColumns()
    {
        $res = array(
            'name' => 'Table Name',
        );
        foreach ($this->items as $tablename => $table) {
            foreach ($table->fields as $type => $fields_with_type) {
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
    
    protected function tableCell_name($table, $tablename) {
        echo '
        <div class="hoverme">
        '.$tablename.'
        <div class="tooltip">
        <table>';
        foreach ($table as $key => $value) {
            if (!empty($value) && is_string($value)) {
                echo '
                <tr><td style="text-align:right">'.$key.'</td><td>'.$value.'</td></tr>';
            }
        }
        echo '
        </table>
        </div>
        </div>';
    }
    
    protected function tableCell($showtype, $table, $tablename)
    {
        $showfields = array();
        foreach ($table->fields as $type => $fields_with_type) {
            if ($showtype == $this->discriminate($type)) {
                foreach ($fields_with_type as $fieldname => $field) {
                    $showfields[$fieldname] = $field;
                }
            }
        }
        
        foreach ($showfields as $fieldname => $field) {
            echo '
            <div class="sqlcolumn hoverme '.$field->COLUMN_KEY.' '.(isset($field->found) ? 'found' : '').'">
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