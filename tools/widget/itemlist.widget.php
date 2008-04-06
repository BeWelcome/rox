<?php


class ItemlistWidget extends RoxWidget
{
    public function render()
    {
        $this->showItems();
    }
    
    protected function __call($methodname, $args)
    {
        // inject a delegate for implementation!
        $delegate_methodname = $this->delegate_prefix.'_'.$methodname;
        if (method_exists($this->delegate, $delegate_methodname)) {
            $delegate_callback = array($this->delegate, $delegate_methodname);
            return call_user_func_array($delegate_callback, $args);
        } else {
            return false; 
        }
    }
    
    protected function showItems()
    {
        // by default, show as a table
        $this->showItems_table();
    }
    
    protected function showItems_table()
    {
        echo '
        <table>';
        // table headline
        if (!$this->hideColumnTitles()) {
            echo '
            <tr class="title">';
            foreach ($this->getTableColumns() as $key => $value) {
                echo '
                <th class="'.$key.'">' . $value . '</th>';
            }
            echo '
            </tr>';
        }
        // table rows with items
        $items = $this->getItems();
        $index = 0;
        foreach ($items as $item) {
            echo '
            <tr class="' . ($index%2 ? 'odd' : 'even') . '">';
            foreach ($this->getTableColumns() as $key => $value) {
                $methodname = 'tableCell_'.$key;
                echo '
                <td class="'.$key.'">';
                $this->$methodname($item);
                echo '
                </td>';
            }
            echo '
            </tr>';
            ++$index;
        }
        echo '
        </table>';
    }
    
    protected function showItems_float()
    {
        echo '
        <div class="float_table">';
        // table headline
        if (!$this->hideColumnTitles()) {
            echo '
            <div class="float_table_row title">';
            foreach ($this->getTableColumns() as $key => $value) {
                echo '
                <div class="float_table_cell '.$key.'">'.$value.'</div>';
            }
            echo '
            </div>';
        }
        // table rows with items
        $items = $this->getItems();
        $index = 0;
        foreach ($items as $item) {
            echo '
            <div class="float_table_row '.($index%2 ? 'odd' : 'even').'">';
            foreach ($this->getTableColumns() as $key => $value) {
                $methodname = 'tableCell_'.$key;
                echo '
                <div class="float_table_cell '.$key.'">';
                $this->$methodname($item);
                echo '
                </div>';
            }
            echo '
            </div>';
            ++$index;
        }
        echo '
        </div>';
    }
    
    protected function showItems_list()
    {
        echo '
        <div class="list">';
        // table rows with items
        $items = $this->getItems();
        $index = 0;
        foreach ($items as $item) {
            echo '
            <div class="list_element '.($index%2 ? 'odd' : 'even').'">';
            $this->showListItem($item);
            echo '
            </div>';
            ++$index;
        }
        echo '
        </div>';
    }
    
    protected function getItems()
    {
        // set items by dependency injection,
        // or override this method.
        return $this->items;
    }
    
    protected function hideColumnTitles()
    {
        return false;
    }
}



?>