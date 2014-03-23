<?php

class FullPagePager extends RoxWidget
{
    const block_links = 2;
    private $style;

    public function __construct()
    {
    }

    /**
     * outputs a list of list links, to reflect paging
     *
     * @access public
     * @return void
     */
    public function render()
    {
        if ($this->pages < 2)
        {
            return;
        }
        echo $this->getHtml();
    }

    /**
     * return pagination button
     *
     * @param int $page - page number
     *
     * @access private
     * @return string
     */
    private function outputPage($page)
    {
        if ($page == $this->active_page)
        {
            return '<span class="page">' . $page . '</span>';
        }
        else
        {
            return '<input type="submit" class="page" name="' . $this->pager->outputLink($page, $page) . '" value="' . $page . '" />';
        }
    }

    /**
     * returns rather than renders the pager
     *
     * @access public
     * @return string
     */
    public function getHtml()
    {
        $pager = $this->pager;
        $return = '<div class="fullpager"><span class="page" style="float: left; padding-left: 0; padding-right:0; padding-top: .25em;">' . $this->getWords()->get('PagerShowing', $pager->getActiveStart() + 1,
            $pager->getActiveStart() + $pager->getActiveLength(), $pager->getTotalCount()) . '</span>';
        if ($this->active_page > 1)
        {
            if ($this->pages > self::block_links)
            {
                $return .= '<input type="submit" class="page" name="' . $this->pager->outputLink(1, '&laquo;') . '" value="&laquo;" />';
            }
            $return .= '<input type="submit" class="page" name="' . $this->pager->outputLink($this->active_page - 1, '&lsaquo;') . '" value="&lsaquo;" />';
        }
        else
        {
            if ($this->pages > self::block_links)
            {
                $return .= '<input type="submit" class="page" disabled="disabled" value="&laquo;" />';
            }
            $return .= '<input type="submit" class="page" disabled="disabled" value="&lsaquo;" />';
        }
        for ($i = 1; $i <= self::block_links && $i <= $this->pages; $i++)
        {
            $return .= $this->outputPage($i);
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
                $return .= '<span class="page">...</span>';
            }

            if ($this->active_page >= self::block_links && $this->active_page <= $end_range)
            {
                for ($i = ($this->active_page - 1); $i <= ($this->active_page + 1); $i++)
                {
                    if (self::block_links < $i && $end_range > $i)
                    {
                        $return .= $this->outputPage($i);
                    }
                }

            }

            if ($end_range > ($this->active_page + 1) && $end_range > (self::block_links + 1))
            {
                $return .= '<span class="page">...</span>';
            }

            while ($end_range <= $this->pages)
            {
                $return .= $this->outputPage($end_range);
                $end_range++;
            }
        }

        if ($this->active_page < $this->pages)
        {
            $return .= '<input type="submit" class="page" name="' . $this->pager->outputLink($this->active_page + 1, '&rsaquo;') . '" value="&rsaquo;" />';
            if ($this->pages > self::block_links)
            {
                $return .= '<input type="submit" class="page" name="' . $this->pager->outputLink($this->pages, '&raquo;') . '" value="&raquo;" />';
            }
        }
        else
        {
            $return .= '<input type="submit" class="page" disabled="disabled" value="&rsaquo;" />';
            if ($this->pages > self::block_links)
            {
                $return .= '<input type="submit" class="page" disabled="disabled" value="&raquo;" />';
            }
        }
        $return .= "</div>";
        return $return;
    }
}
