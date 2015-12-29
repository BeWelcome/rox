<?php


class ItemlistWidget extends RoxWidget
{
    public function render()
    {
        $this->showItems();
    }
    
    function __call($methodname, $args)
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
        <table class="table table-striped">';
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
        foreach ($items as $itemkey => $item) {
            echo '
            <tr class="' . ($index%2 ? 'highlight' : 'blank') . '">';
            foreach ($this->getTableColumns() as $key => $value) {
                $methodname = 'tableCell_'.$key;
                echo '
                <td class="'.$key.'">';
                if (method_exists($this, $methodname)) {
                    $this->$methodname($item, $itemkey);
                } else {
                    $this->tableCell($key, $item, $itemkey);
                }
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
        <div class="itemlist">';
        // table rows with items
        $items = $this->getItems();
        if (!is_array($items)) {
            echo 'not an array.<br>';
            print_r($items);
        } else {
            $i_row = 0;
            if ($item = array_shift($items)) {
                echo '
                <div class="itemlist_element '.($i_row%2 ? 'odd' : 'even').'">';
                $this->showListItem($item, $i_row);
                echo '
                </div>';
                $i_row = 1;
                $prev_item = $item;
                foreach ($items as $item) {
                    $this->showBetweenListItems($prev_item, $item, $i_row);
                    echo '
                    <div class="itemlist_element '.($i_row%2 ? 'odd' : 'even').'">';
                    $this->showListItem($item, $i_row);
                    echo '
                    </div>';
                    ++$i_row;
                    $prev_item = $item;
                }
            }
        }
        echo '
        </div>';
    }
    
    
    protected function showBetweenListItems($prev_item, $item, $i_row)
    {
        // by default, show nothing
    }
    

    /**
     * Override this method to get a different presentation
     *
     * @param unknown_type $item - the item for this row of the list
     */
    protected function showListItem($item, $i_row)
    {
        echo __METHOD__;
        echo '<pre>';
        print_r($item);
        echo '</pre>';
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
