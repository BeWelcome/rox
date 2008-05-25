<?php


class SqltestPage extends RoxPageView
{
    protected function getPageTitle()
    {
        return 'Table Sync - BeWelcome';
    }
    
    
    protected function teaserHeadline()
    {
        echo 'Table Sync Analysis ( TB user / BW members )';
    }
    
    protected function column_col3()
    {
        $result = $this->model->analyse();
        
        
        
        echo '
<style>
.syncbox {
  overflow:auto;
  max-height:300px;
  padding:3px;
  border:1px solid #ddd;
  background:#ffd;
}
.member {
  color:green;
}
.user {
  color:blue;
}
</style>


<h3>Empty TB username</h3>
<div class="syncbox"><table><tr>
<th>u.handle</th>
<th class="user">u.active</th>
<th class="user">u.lastlogin</th>
<th>u.id==m.id</th>
<th class="member">m.Status</th>
<th class="member">m.created</th>
<th class="member">m.updated</th>
<th>m.Username==u2.handle</th>
<th class="user">u2.active</th>
<th class="user">u2.lastlogin</th>
<th>u2.id</th>
</tr>'
        ;
        foreach ($result->all_by_id as $id => $x) {
            if (isset($x->u) && empty($x->u->handle)) {
                echo '
<tr>
<td>'.$x->u->handle.'</td>
<td class="user">u.active = '.$x->u->active .'</td>
<td class="user">u.lastlogin = '.$x->u->lastlogin.'</td>
<td>'.$id.'</td>
<td class="member">'.(isset($x->m) ? 'm.Status = '.$x->m->Status : '') .'</td>
<td class="member">'.(isset($x->m) ? 'm.created = '.$x->m->created : '') .'</td>
<td class="member">'.(isset($x->m) ? 'm.updated = '.$x->m->updated : '') .'</td>
<td>'.(isset($x->m) ? $x->m->Username : '') .'</td>
<td class="user">'.(isset($x->xm->u) ? 'u2.active = '.$x->u->active : '').'</td>
<td class="user">'.(isset($x->xm->u) ? 'u2.lastlogin = '.$x->u->lastlogin : '').'</td>
<td>'.(isset($x->xm->u) ? $x->xm->u->id : '') .'</td>
</tr>'
                ;
            }
        }
        echo '
</table></div>

<hr>

<h3>BW members without a TB user record</h3>
This happens for '.count($result->orphan_m).' usernames
<div class="syncbox"><table><tr><th>m->id</th><th>m->handle</th><th>m->Status</th><th>m->created</th><th>m->updated</th></tr>'
        ;
        foreach ($result->orphan_m as $x) {
            echo '<tr><td>bw: '.$x->m->id.'</td><td>'.$x->m->Username.'</td><td>'.$x->m->Status.'</td><td>'.$x->m->created.'</td><td>'.$x->m->updated.'</td></tr>';
        }
        echo '
</table></div>

<h3>TB users without a BW member record</h3>
This happens for '.count($result->orphan_u).' usernames
<div class="syncbox"><table><tr><th>u->id</th><th>u->handle</th></tr>'
        ;
        foreach ($result->orphan_u as $x) {
            echo '<tr><td>tb: '.$x->u->id.'</td><td>'.$x->u->handle.'</td></tr>';
        }
        echo '
</table></div>

<h3>id mismatch in users / members table</h3>
This happens for '.count($result->id_mismatch).' usernames
<div class="syncbox"><table><tr><th>m->id</th><th>u->id</th><th>username</th></tr>'
        ;
        foreach ($result->id_mismatch as $x) {
            echo '
<tr><td>bw: '.$x->m->id.'</td><td>tb: '.$x->u->id.'</td><td>'.$x->m->Username.'</td></tr>'
            ;
        }
        echo '
</table></div>

<h3>multiple tb users for same username</h3>

This happens for '.count($result->multi_u).' usernames
<div class="syncbox"><table><tr><th>m->id</th><th>u->id</th><th>username</th></tr>'
        ;
        foreach ($result->multi_u as $x) {
            echo '
<tr><td>bw: '.$x->m->id.'</td><td>tb: '
            ;
            foreach ($x->uu as $u) echo $u->id.' ';
            echo '
</td><td>'.$x->m->Username.'</td></tr>'
            ;
        }
        echo '
</table></div>

<h3>multiple tb users for same username, and without a members record</h3>
This happens for '.count($result->multi_orphan_u).' usernames
<div class="syncbox"><table><tr><th>u->id</th><th>username</th></tr>'
        ;
        foreach ($result->multi_orphan_u as $x) {
            echo '
<tr><td>tb: '
            ;
            foreach ($x->uu as $u) echo $u->id.' ';
            echo '
</td><td>'.$x->u->handle.'</td></tr>'
            ;
        }
        echo '
</table></div>

<hr>

<h3>BW members id not found in TB user table</h3>
This happens for '.count($result->orphan_id_m).' ids
<div class="syncbox"><table><tr><th>id</th><th>m->Username</th></tr>'
        ;
        foreach ($result->orphan_id_m as $x) {
            echo '
<tr><td>'.$x->m->id.'</td><td>bw: '.$x->m->Username.'</td></tr>'
            ;
        }
        echo '
</table></div>

<h3>TB user id not found in BW members table</h3>
This happens for '.count($result->orphan_id_u).' ids

<div class="syncbox"><table><tr><th>id</th><th>u->handle</th></tr>'
        ;
        foreach ($result->orphan_id_u as $x) {
            echo '
<tr><td>'.$x->u->id.'</td><td>tb: '.$x->u->handle.'</td></tr>'
            ;
        }
        echo '
</table></div>

<hr>

<h3>Mismatch username</h3>
This happens for '.count($result->username_mismatch).' ids
<div class="syncbox"><table><tr><th>id</th><th>m->Username</th><th>u->handle</th></tr>'
        ;
        foreach ($result->username_mismatch as $x) {
            echo '
<tr><td>'.$x->m->id.'</td><td>bw: '.$x->m->Username.'</td><td>tb: '.$x->u->handle.'</td></tr>'
            ;
        }
        echo '
</table></div>

<hr>

<h3>All</h3>
<div class="syncbox">'
        ;
        
        foreach ($result->all as $username => $x) {
            if (!isset($x->u) && !isset($x->m)) {
                echo '
<br>??'
                ;
            }
            if (!isset($x->u)) {
                echo '
<br>orphan bw member "'.$username.'" with members.id = '.$x->m->id
                ;
            }
            if (!isset($x->m)) {
                echo '
<br>orphan tb user "'.$username.'" with user.id = '.$x->u->id
                ;
            }
        }
        
        echo '
</div>'
        ;
    }
}


?>