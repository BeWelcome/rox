<div class="float_right">
<h4>Search for members</h4>
<input size="20" />
<input type="submit" value="search" />
</div>
<p>
<?php
echo "Hi {$this->member->Username}.<br/>";
if (in_array('All', $this->scope))
{
    echo "You can accept members from any country.";
}
else
{
    echo "You can accept members from the following countries:";
    echo '- ' . implode('<br/>- ', $this->scope);
}
echo <<<HTML
</p>

<h4>The Message board</h4>
<textarea cols="70" rows="8">{$this->board->TextContent}</textarea>



<p>Displaying members with status: <b>{$this->status}</b> ({$this->members_count} members in total with that status). Select which status to display below.</p>

HTML;
$this->pager->render();
$members = $this->members;
foreach ($members as $member)
{
    echo <<<HTML
<div class="adminmembers">
<div class="floatbox memberinfo">
    <a class="float_left" href="people/{$member->Username}">
        <img class="framed"  src="members/avatar/{$member->Username}/?xs"  height="50px"  width="50px"  alt="Profile" />
    </a>
    <a class="username">{$member->Username}</a> ({$member->Name})
    <p>30 years</p>
    <p class="small">Profile created: {$member->created}   |   Last login: {$member->Lastlogin}</p>
</div>
<div class="address">
    <h4>Address</h4>
</div>
    <h4>About Me</h4>
    <h4>Feedback on signup</h4>
    <h4>Actions</h4>
    <ul>
        <li><input type="radio" />Accept</li>
        <li><input type="radio" />Reject</li>
        <li><input type="radio" />Need more Infos</li>
        <li><input type="radio" />Dublicated</li>
         <li><input type="radio" />Do nothing</li>
    </ul>

    <label>Additional text for "Need more Infos"</label>
    <textarea cols="50" rows="5"></textarea>
    
    <div><a class="button" href="#">Contact</a> <a class="button" href="#">Edit Profile</a></div>
    
</div>
HTML;
}
