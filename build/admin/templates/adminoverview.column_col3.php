<ul>
<?php
    $right_names = array_keys($this->rights);
    $expand_class = count($right_names) == 1 ? 'expanded' : 'expandable';
    if (in_array('Debug', $right_names))
    {
        $description = htmlspecialchars($this->rights['Debug']['Description'], ENT_QUOTES);
        echo <<<HTML
<li class='{$expand_class}'><a href='#' title='{$description}' class='header'>+/- Debug</a>
    <ul>
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'php'))}' title='Check the PHP error logs'>PHP error logs</a></li>
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'exception'))}' title='Check the Exception logs'>Exception logs</a></li>
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'mysql'))}' title='Check the MySQL logs'>MySQL logs</a></li>
        <li><a href='{$this->router->url('admin_debug_logs', array('log_type' => 'apache'))}' title='Check the MySQL logs'>Apache error logs</a></li>
    </ul>
</li>
HTML;
    }

    if (in_array('Accepter', $right_names))
    {
        $description = htmlspecialchars($this->rights['Accepter']['Description'], ENT_QUOTES);
        echo <<<HTML
<li><a href='{$this->router->url('admin_accepter')}' title='{$description}' class='header'>Accepter</a></li>
HTML;
    }

    if (in_array('Comments', $right_names))
    {
        $description = htmlspecialchars($this->rights['Comments']['Description'], ENT_QUOTES);
        echo <<<HTML
<li><a href='{$this->router->url('admin_comments_overview')}' title='{$description}' class='header'>Comments</a></li>
HTML;
    }
    if (in_array('Words', $right_names))
    {
        $description = htmlspecialchars($this->rights['Words']['Description'], ENT_QUOTES);
        echo <<<HTML
<li><a href='{$this->router->url('admin_words_overview')}' title='{$description}' class='header'>Words</a></li>
HTML;
    }
    if (in_array('MassMail', $right_names))
    {
        $description = htmlspecialchars($this->rights['MassMail']['Description'], ENT_QUOTES);
        echo <<<HTML
<li><a href='{$this->router->url('admin_massmail_overview')}' title='{$description}' class='header'>Massmail</a></li>
HTML;
    }
?>
</ul>

<script type='text/javascript'>
late_loader.queueObjectMethod('common', 'makeExpandableLinks');
</script>
