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
<form action='' method='post'>
    {$this->getCallbackOutput('AdminController', 'updateVolunteerBoard')}
    <input type='hidden' name='boardname' value='{$this->board->Name}'/>
    <input type='hidden' name='tool_url' value='{$this->router->url('admin_accepter')}'/>
    <textarea cols="70" rows="8" name='TextContent'>{$this->board->TextContent}</textarea>
    <input type='submit' value='{$this->getWords()->getSilent('UpdateBoard')}'/>
</form>

<p>Displaying members with status: <b>{$this->status}</b> ({$this->members_count} members in total with that status). Select which status to display below.</p>
<div>
HTML;
$this->pager->render();
$members = $this->members;
echo <<<HTML
</div>
<form action='' method='post'>
{$this->getCallbackOutput('AdminController', 'accepterProcessMembers')}
HTML;
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
        <p class="small">Profile created: {$member->created} | Last login: {$member->Lastlogin}</p>
    </div>
    <div class="address">
        <h4>Address</h4>
    </div>
    <h4>About Me</h4>
    <h4>Feedback on signup</h4>
    <h4>Actions</h4>
HTML;

    // note: if you need to add actions for a given status, stick them in a case statement
    ///      like it's done below
    switch (strtolower($this->status))
    {
        case 'pending':
            echo <<<HTML
    <ul>
        <li><input type="radio" name="accept_action[{$member->id}]" value="accept"/>Accept</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="reject"/>Reject</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="needmore"/>Need more Infos</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="duplicated"/>Dublicated</li>
        <li><input type="radio" name="accept_action[{$member->id}]"/>Do nothing</li>
    </ul>

    <label>Additional text for "Need more Infos"</label>
    <textarea cols="50" rows="5" name="accept_info[{$member->id}]"></textarea>
HTML;
            break;
    }

    echo <<<HTML
    <div><a class="button" href="/messages/compose/{$member->Username}">Contact</a> <a class="button" href="/members/{$member->Username}/adminedit">Edit Profile</a></div>
    
</div>
HTML;
}
echo <<<HTML
<input type='submit' value='{$this->getWords()->getSilent('ProcessMembers')}'/>
</form>

HTML;
