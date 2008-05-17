<?php


class SqltestPage extends RoxPageView
{
    protected function column_col3()
    {
        $all = $this->model->getAll();
        foreach ($all as $username => $x) {
            if (!isset($x->u) && !isset($x->m)) {
                echo '<br>??';
            }
            if (!isset($x->u)) {
                echo '<br>orphan bw member "'.$username.'" with members.id = '.$x->m->id;
            }
            if (!isset($x->m)) {
                echo '<br>orphan tb user "'.$username.'" with user.id = '.$x->u->id;
            }
        }
    }
}


?>