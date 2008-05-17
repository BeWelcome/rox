<?php


class SqltestPage extends RoxPageView
{
    protected function column_col3()
    {
        $result = $this->model->analyse();
        
        echo '<h3>BW members without a BW member record</h3>';
        
        echo '<table><tr><th>m->id</th><th>m->handle</th></tr>';
        foreach ($result->orphan_m as $m) {
            echo '<tr><td>'.$m->id.'</td><td>'.$m->Username.'</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>TB users without a BW member record</h3>';
        
        echo '<table><tr><th>u->id</th><th>u->handle</th></tr>';
        foreach ($result->orphan_u as $u) {
            echo '<tr><td>'.$u->id.'</td><td>'.$u->handle.'</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>id mismatch in users / members table</h3>';
        
        echo '<table><tr><th>m->id</th><th>u->id</th><th>username</th></tr>';
        foreach ($result->id_mismatch as $x) {
            echo '<tr><td>'.$x->m->id.'</td><td>'.$x->u->id.'</td><td>'.$x->m->Username.'</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>All</h3>';
        
        foreach ($result->all as $username => $x) {
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