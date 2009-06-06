<?php

class HalfPagePager extends RoxWidget
{

    const block_links = 2;

    /**
     * outputs a list of list links, to reflect paging
     *
     * @access public
     */
    public function render()
    {
        if ($this->pages < 2)
        {
            return;
        }
        echo "<div class='pages'>\n";
        echo "<ul>\n";
        if ($this->active_page > 1)
        {
            if ($this->pages > self::block_links)
            {
                echo "<li>{$this->pager->outputLink(1, '&lt;&lt;', $this->getWords()->getSilent('PagerToFirstPage'))}</li>\n";
            }
            echo "<li>{$this->pager->outputLink($this->active_page - 1, '&lt;',$this->getWords()->getSilent('PagerToFirstPage'))}</li>\n";
        }
        else
        {
            if ($this->pages > self::block_links)
            {
                echo "<li>&lt;&lt;</li>\n";
            }
            echo "<li>&lt;</li>\n";
        }
        for ($i = 1; $i <= self::block_links && $i <= $this->pages; $i++)
        {
            $this->outputPage($i);
        }

        if ($this->pages > self::block_links)
        {
            $end_range = $this->pages - (self::block_links - 1);
            if ($end_range <= (self::block_links + 1))
            {
                $end_range = self::block_links + 1;
            }

            if (($this->active_page - 1) > (self::block_links + 1))
            {
                echo "<li>...</li>\n";
            }

            if ($this->active_page >= self::block_links && $this->active_page <= $end_range)
            {
                for ($i = ($this->active_page - 1); $i <= ($this->active_page + 1); $i++)
                {
                    if (self::block_links < $i && $end_range > $i)
                    {
                        $this->outputPage($i);
                    }
                }

            }

            if ($end_range > ($this->active_page + 1) && $end_range > (self::block_links + 1))
            {
                echo "<li>...</li>\n";
            }

            while ($end_range <= $this->pages)
            {
                $this->outputPage($end_range);
                $end_range++;
            }
        }

        if ($this->active_page < $this->pages)
        {
            echo "<li>{$this->pager->outputLink($this->active_page + 1, '&gt;', $this->getWords()->getSilent('PagerToNextPage'))}</li>\n";
            if ($this->pages > self::block_links)
            {
                echo "<li>{$this->pager->outputLink($this->pages, '&gt;&gt;', $this->getWords()->getSilent('PagerToLastPage'))}</li>\n";
            }
        }
        else
        {
            echo "<li>&gt;</li>\n";
            if ($this->pages > self::block_links)
            {
                echo "<li>&gt;&gt;</li>\n";
            }
        }
        echo "</ul>\n";
        echo "</div>\n";
        return;

    }

    private function outputPage($page)
    {
            if ($page == $this->active_page)
            {
                echo "<li class='current'>{$page}</li>\n";
            }
            else
            {
                echo "<li>{$this->pager->outputLink($page, $page, $this->getWords()->getSilent('PagerGoToPage', $page))}</li>\n";
            }
    }
}
