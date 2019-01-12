<?php
  /* what is this supposed to do?   It should probably become a widget. 
   * This seems to be a pager template and is used in build/places and somewhere else?
   *
   * */

if (!is_array($pages) || count($pages) == 0) {
    return false;
}
?>
<div class="row px-3">
    <ul class="pagination pull-right">
       <li class="page-item <?php if ($currentPage == 1){ echo 'disabled'; } ?>"><a href="<?=sprintf($request, ($currentPage - 1))?>" class="page-link">&lt;&lt;</a></li>
        <?php
        foreach ($pages as $page) {
            if (!is_array($page)) {
		echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                continue;
            }
            if (!isset($page['current'])) {
                echo '<li class="page-item"><a href="'.sprintf($request, $page['pageno']).'" class="page-link">'.$page['pageno'].'</a></li>';
            } else {
                echo '<li class="page-item active""><a class="page-link">'. $page['pageno'] .'</a></li>';
            }
          } ?>
        <li class="page-item <?php if ($currentPage == $maxPage) { echo ' disabled'; } ?>"><a href="<?=sprintf($request, ($currentPage + 1))?>" class="page-link">&gt;&gt;</a></li>
    </ul>
</div>
