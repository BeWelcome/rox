<?php
if (!is_array($pages) || count($pages) == 0)
    return false;
?>
<div class="pages clearfix">
    <ul>
        <li>
<?php
if ($currentPage != 1) {
?>
            <a href="<?=sprintf($request, ($currentPage - 1))?>">&lt;&lt;</a>
<?php
} else {
?>
                &lt;&lt;
<?php
}
?>
        </li>
<?php
foreach ($pages as $page) {
	if (!is_array($page)) {
		echo '<li class="sep">...</li>';
        continue;
	}
    if (!isset($page['current'])) {
        echo '<li>';
        echo '<a href="'.sprintf($request, $page['pageno']).'">';
        echo $page['pageno'];
        echo '</a>';
        echo '</li>';
    } else {
    	echo '<li class="current">'.$page['pageno'].'</li>';
    }
}
?>
        <li>
<?php
if ($currentPage != $maxPage) {
?>
            <a href="<?=sprintf($request, ($currentPage + 1))?>">&gt;&gt;</a>
<?php
} else {
?>
                &gt;&gt;
<?php
}
?>
        </li>
    </ul>
</div>