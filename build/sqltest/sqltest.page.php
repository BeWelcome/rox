<?php


class SqltestPage extends RoxPageView
{
    protected function teaserHeadline()
    {
        echo 'Table Sync Analysis ( TB user / BW members )';
    }
    
    protected function column_col3()
    {
        $result = $this->model->analyse();
        
        echo '<h3>BW members without a TB user record</h3>';
        echo 'This happens for '.count($result->orphan_m).' usernames';
        
        echo '<table><tr><th>m->id</th><th>m->handle</th><th>m->Status</th><th>m->created</th></tr>';
        foreach ($result->orphan_m as $x) {
            echo '<tr><td>bw: '.$x->m->id.'</td><td>'.$x->m->Username.'</td><td>'.$x->m->Status.'</td><td>'.$x->m->created.'</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>TB users without a BW member record</h3>';
        echo 'This happens for '.count($result->orphan_u).' usernames';
        
        echo '<table><tr><th>u->id</th><th>u->handle</th></tr>';
        foreach ($result->orphan_u as $x) {
            echo '<tr><td>tb: '.$x->u->id.'</td><td>'.$x->u->handle.'</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>id mismatch in users / members table</h3>';
        echo 'This happens for '.count($result->id_mismatch).' usernames';
        
        echo '<table><tr><th>m->id</th><th>u->id</th><th>username</th></tr>';
        foreach ($result->id_mismatch as $x) {
            echo '<tr><td>bw: '.$x->m->id.'</td><td>tb: '.$x->u->id.'</td><td>'.$x->m->Username.'</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>multiple tb users for same username</h3>';
        echo 'This happens for '.count($result->multi_u).' usernames';
        
        echo '<table><tr><th>m->id</th><th>u->id</th><th>username</th></tr>';
        foreach ($result->multi_u as $x) {
            echo '<tr><td>bw: '.$x->m->id.'</td><td>tb: ';
            foreach ($x->uu as $u) echo $u->id.' ';
            echo '</td><td>'.$x->m->Username.'</td></tr>';
        }
        echo '</table>';
        
        echo '<h3>multiple tb users for same username, and without a members record</h3>';
        echo 'This happens for '.count($result->multi_orphan_u).' usernames';
        
        echo '<table><tr><th>u->id</th><th>username</th></tr>';
        foreach ($result->multi_orphan_u as $x) {
            echo '<tr><td>tb: ';
            foreach ($x->uu as $u) echo $u->id.' ';
            echo '</td><td>'.$x->u->handle.'</td></tr>';
        }
        echo '</table>';
        
        echo '<hr>';
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