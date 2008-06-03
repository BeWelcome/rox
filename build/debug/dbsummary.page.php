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
    protected function getTableColumns()
    {
        return array(
            'name' => 'Table Name',
            'fields' => 'Field Names',
        );
    }
    
    protected function tableCell_name($fieldnames, $tablename) {
        echo $tablename;
    }
    
    protected function tableCell_fields($fieldnames, $tablename) {
        echo implode(', ', array_keys($fieldnames));
    }
}


?>