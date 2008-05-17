<?php


class SqltestPage extends RoxPageView
{
    protected function column_col3()
    {
        $all = $this->model->getAll();
        foreach ($all as $username => $x) {
            if (!isset($x->u)) {
                echo '<br>orphan bw user "'.$username.'"';
            }
            if (!isset($x->m)) {
                echo '<br>orphan tb user "'.$username.'"';
            }
        }
    }
}


?>