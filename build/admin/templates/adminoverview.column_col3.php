<ul>
<?php
    $right_names = array_keys($this->rights);
    $expand_class = count($right_names) == 1 ? 'expanded' : 'expandable';
    if (in_array('Debug', $right_names))
    {
        $description = htmlspecialchars($this->rights['Debug']['Description'], ENT_NOQUOTES);
        echo <<<HTML
<li class='{$expand_class}'><a href='#' title='{$description}' class='header'>+/- Debug</a>
    <ul>
        <li><a href='{$this->router->url('admin_debug_php')}' title='Check the PHP error logs'>PHP error logs</a></li>
        <li><a href='{$this->router->url('admin_debug_exception')}' title='Check the Exception logs'>Exception logs</a></li>
        <li><a href='{$this->router->url('admin_debug_mysql')}' title='Check the MySQL logs'>MySQL logs</a></li>
    </ul>
</li>
HTML;
    }

    if (in_array('Accepter', $right_names))
    {
        $description = htmlspecialchars($this->rights['Accepter']['Description'], ENT_NOQUOTES);
        echo <<<HTML
<li><a href='{$this->router->url('admin_accepter')}' title='{$description}' class='header'>Accepter</a></li>
HTML;
    }
?>
</ul>

<script type='text/javascript'>
    $$('li.expandable a.header').each(function(it){
        it.observe('click', function(e){
            var e = e || window.event;
            var target = e.target || e.srcElement;
            if (target.parentNode.className == 'expandable')
            {
                target.parentNode.className = 'expanded';
            }
            else
            {
                target.parentNode.className = 'expandable';
            }
            Event.stop(e);
        });
    });
</script>
