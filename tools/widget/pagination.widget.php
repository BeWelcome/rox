<?php


class ItemlistWithPagination extends ItemlistWidget
{
    public function render()
    {
        $this->prepare();
        parent::render();
    }
    
    public function needsPagination()
    {
        return ($this->numberOfPages() > 1);
    }
    
    private $_prepared = false;
    protected function prepare()
    {
        if ($this->_prepared) return;
        $this->_prepared = true;
        
        // check which range we want
        if (!$this->items_per_page || $this->items_per_page < 1) {
            // set a default
            $this->items_per_page = 20;
        }
        
        if (!$this->items_total_begin) {
            $this->items_total_begin = 1;
        }
        
        // need to adjust active page index
        if (!$this->active_page) {
            $this->active_page = 1;
        } else {
            if ($this->active_page > $this->numberOfPages()) {
                $this->active_page = $this->numberOfPages();
            }
            if ($this->active_page < 1) {
                $this->active_page = 1;
            }
        }
    }
    
    
    public function showPagination()
    {
        $this->prepare();
        
        $active_page = $this->active_page;
        $n_pages = $this->numberOfPages();
        
        echo '
        <div class="pages clearfix">
        <ul class="pagination m-t-0 m-b-0 pull-right">';
        if ($active_page > 1) {
            echo '
            <li class="page-item"><a class="page-link" href="'.$this->hrefPage($active_page-1).'">&laquo;</a></li>';
        } else {
            echo '
            <li class="page-item"><a class="page-link disabled">&laquo;</a></li>';
        }
        if ($visible_range = $this->visible_range) {
            
            // show page numbers 1 ... 5 6 7 (8) 9 10 11 ... 31
            // for $visible_range = 3 and $active_page = 8 and $n_pages = 31
            
            // link for page 1
            if ($active_page > 1) {
                $this->showPageLink(1);
            }
            
            if ($active_page - $visible_range > 2) {
                echo '
                <li class="page-item"><a class="page-link disabled">...</a></li>';
            }
            
            // links for pages 5 6 7
            for (
                $i = $active_page - $visible_range;
                ($i < $active_page) && ($i < $n_pages);
                ++$i
            ) {
                if ($i > 1) $this->showPageLink($i);
            }
            
            // link for page (8)
            $this->showActivePageLink($active_page);
            
            // links for pages 9 10 11
            for (
                $i = $active_page+1;
                ($i < $n_pages) && ($i <= $active_page+$visible_range);
                ++$i
            ) {
                if ($i > 1) $this->showPageLink($i);
            }
            
            if ($active_page + $visible_range < $n_pages - 1) {
                echo '
                <li class="page-item"><a class="page-link disabled">...</a></li>';
            }
            
            // link for page 31
            if ($active_page < $n_pages) {
                $this->showPageLink($n_pages);
            }
            
        } else {
            
            // no visible range specified.
            // show links for all pages!
            
            // for pages 1 ... 7
            for ($i = 1;   $i < $active_page && $i <= $n_pages;   ++$i) {
                $this->showPageLink($i);
            }
            
            // for page (8)
            $this->showActivePageLink($active_page);
            
            // for pages 9 ... 31
            for ($i = $active_page+1;   $i <= $n_pages;   ++$i) {
                if ($i > 1) $this->showPageLink($i);
            }
            
        }
        if ($active_page < $n_pages) {
            echo '
            <li class="page-item"><a class="page-link" href="'.$this->hrefPage($active_page+1).'">&raquo;</a></li>';
        } else {
            echo '
            <li class="page-item"><a class="page-link disabled">&raquo;</a></li>';
        }
        echo '
        </ul>
        </div> <!-- pages -->';
    }
    
    
    protected function numberOfPages()
    {
        $this->prepare();
        return ceil($this->itemsTotalCount() / $this->items_per_page);
    }
    
    
    protected function getItems()
    {
        $this->prepare();
        return $this->getItemsForPage($this->active_page);
    }
    
    protected function getItemsForPage($k_page)
    {
        $this->prepare();
        
        $items_per_page = $this->items_per_page;
        
        // $active_page = $this->active_page;
        $items_total_begin = $this->itemsTotalBegin();
        $items_total_end = $this->itemsTotalCount() + $items_total_begin;
        
        $begin = $items_total_begin + ($k_page - 1) * $items_per_page;
        
        $end = $begin + $items_per_page;
        if ($end > $items_total_end) {
            $end = $items_total_end;
        }
        
        return $this->getItemsInRange($begin, $end-$begin);
    }
    
    protected function itemsTotalBegin() {
        return 0;
    }
    
    protected function showPageLink($i_page) {
        echo '<li class="page-item"><a class="page-link" href="'.$this->hrefPage($i_page).'">'.$i_page.'</a></li>';
    }
    
    protected function showActivePageLink($i_page) {
        echo '<li class="page-item active"><a class="page-link">'.$i_page.'</a></li>';
    }

    
    //-----------------------------------------------------------------
    // getting the items
    
    /**
     * Get all items with $begin <= $index < $end.
     *
     * @param int $begin
     * @param int $end
     * @return array items in range
     */
    protected function getItemsInRange($begin, $count)
    {
        $items = $this->getAllItems_cached();
        if (!is_array($items)) {
            echo __METHOD__ . ' $items is not an array.';
            print_r($items);
            return array ('test1', 'test2', 'test3');
        } 
        return array_slice($items, $begin, $count);
    }
    
    /**
     * @return int number of items on all pages together
     */
    protected function itemsTotalCount() {
        return count($this->getAllItems_cached());
    }
    
    
    //-----------------------------------------------------------------
    
    private $_items_cached = false;
    private function getAllItems_cached()
    {
        if (!$this->_items_cached) {
            $this->_items_cached = $this->getAllItems();
        }
        return $this->_items_cached;
    }
}


?>