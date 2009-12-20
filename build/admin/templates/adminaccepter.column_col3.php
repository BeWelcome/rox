<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/

    /** 
     * accepter overview template
     * 
     * @package    Apps
     * @subpackage Admin
     * @author     Fake51
     * @author     Globetrotter_tt
     */

    echo <<<HTML
<div class="float_right">
    <form action='{$this->router->url('admin_accepter_search')}' method='get'>
        <h4>Search for members</h4>
        <input size="20" type='text' name='member'/>
        <input type="submit" value="search" />
    </form>
</div>

<p>
HTML;
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
HTML;
// Displaying Shouts for Accepter Team
$shoutsCtrl = new ShoutsController;
$shoutsCtrl->format = 'compact';
$shoutsCtrl->shoutsList('admin_accepter', 1);

if ($this->status)
{
    echo <<<HTML
    <h4>Displaying members with status: <b>{$this->status}</b> ({$this->members_count} members in total with that status).</h4>
HTML;
}
elseif ($this->term)
{
    echo <<<HTML
    <h4>Displaying members from search: <b>{$this->term}</b> ({$this->members_count} members in total with something like that username).</h4>
HTML;
}

$this->pager->render();
$members = $this->members;
echo <<<HTML
<form id="acceptmembers" action='' method='post'>
{$this->getCallbackOutput('AdminController', 'accepterProcessMembers')}
HTML;
foreach ($members as $member)
{
    $firstname  = ($name = MOD_crypt::AdminReadCrypted($member->FirstName)) ? $name : '<b>[No first name set]</b>';
    $secondname = ($name = MOD_crypt::AdminReadCrypted($member->SecondName)) ? $name : '';
    $lastname   = ($name = MOD_crypt::AdminReadCrypted($member->LastName)) ? $name : '<b>[No last name set]</b>';
    $name       = $firstname . ' ' . $secondname . ' ' . $lastname;
    $age        = (date('Y', time()) - date('Y', strtotime($member->BirthDate)))  . ' years old';
    $summary    = MOD_crypt::AdminReadCrypted($member->ProfileSummary);
    $address    = $member->getFirstAddress();
    if ($address)
    {
        $city       = ($city = $address->getCity()) ? $city->name : '<b>[No city set]</b>';
        $region     = ($region = $address->getRegion()) ? $region->name : '<b>[No region set]</b>';
        $country    = ($country = $address->getCountry()) ? $country->name : '<b>[No country set]</b>';
        $zip        = ($zip = MOD_crypt::AdminReadCrypted($address->Zip)) ? $zip : '<b>[Postcode not set]</b>';
        $street     = MOD_crypt::AdminReadCrypted($address->StreetName) . ' ' . MOD_crypt::AdminReadCrypted($address->HouseNumber);
    }
    else
    {
        $city = $region = $country = $zip = $street = '<b>[No address info]</b>';
    }
    $feedback = $member->getSignupFeedback();
    $feedback = $feedback ? $feedback->Discussion : '';
    echo <<<HTML
<div class="adminmembers">

    <div class="floatbox memberinfo">
        <a class="float_left" href="people/{$member->Username}">
            <img class="framed" src="members/avatar/{$member->Username}/?xs"  height="50px"  width="50px"  alt="Profile" />
        </a>
        <a href="people/{$member->Username}" class="username">{$member->Username}</a> ({$name})
        <p>Age: {$age}</p>
        <p class="small">Profile created: {$member->created} | Last login: {$member->Lastlogin}</p>
    </div>
    
    <h4>Address</h4>
        <ul>
            <li>{$street}</li>
            <li>{$zip} {$city}</li>
            <li>{$region}</li>
            <li>{$country}</li>
        </ul>
    
    <h4>About Me</h4>
    <p>{$summary}</p>
    
    <h4>Feedback on signup</h4>
    <p>{$feedback}</p>
    
    <h4>Actions</h4>

HTML;

    // note: if you need to add actions for a given status, stick them in a case statement
    ///      like it's done below
    switch (strtolower($member->Status))
    {
        case 'mailtoconfirm':
            echo <<<HTML
    <ul>
        <li><input type="radio" name="accept_action[{$member->id}]" value="accept"/>Accept</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="reject"/>Reject</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="needmore"/>Need more Infos</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="FIXME"/>Send confirmation mail again</li>
        <li><input type="radio" name="accept_action[{$member->id}]"/>Do nothing</li>
    </ul>
HTML;
            break;
            
        case 'pending':
            echo <<<HTML
    <ul>
        <li><input type="radio" name="accept_action[{$member->id}]" value="accept"/>Accept</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="reject"/>Reject</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="needmore"/>Need more Infos</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="duplicated"/>Dublicated</li>
        <li><input type="radio" name="accept_action[{$member->id}]"/>Do nothing</li>
    </ul>

    <h4>Additional text for "Need more Infos"</h4>
    <textarea cols="50" rows="5" name="accept_info[{$member->id}]"></textarea>
HTML;
            break;
        
        case 'duplicatesigned':
            echo <<<HTML
    <ul>
        <li><input type="radio" name="accept_action[{$member->id}]" value="accept"/>Accept</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="reject"/>Reject</li>
        <li><input type="radio" name="accept_action[{$member->id}]"/>Do nothing</li>
    </ul>
    <p>FIXME: INSERT LINKS TO PROFILES THAT WERE USING THE SAME EMAILADRESS</p>
HTML;
            break;
        
        case 'needmore':
            echo <<<HTML
    <ul>
        <li><input type="radio" name="accept_action[{$member->id}]" value="accept"/>Accept</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="reject"/>Reject</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="duplicated"/>Dublicated</li>
        <li><input type="radio" name="accept_action[{$member->id}]"/>Do nothing</li>
    </ul>
HTML;
            break;
            
        case 'rejected':
            echo <<<HTML
    <ul>
        <li><input type="radio" name="accept_action[{$member->id}]" value="accept"/>Accept</li>
        <li><input type="radio" name="accept_action[{$member->id}]"/>Do nothing</li>
    </ul>
HTML;
            break;
            
        case 'active':
            echo <<<HTML
    <ul>
        <li><input type="radio" name="accept_action[{$member->id}]" value="reject"/>Reject</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="needmore"/>Need more Infos</li>
        <li><input type="radio" name="accept_action[{$member->id}]" value="duplicated"/>Dublicated</li>
        <li><input type="radio" name="accept_action[{$member->id}]"/>Do nothing</li>
    </ul>
HTML;
            break;
    
    }

    echo <<<HTML
    <div><a class="button" href="messages/compose/{$member->Username}">Contact</a> <a class="button" href="members/{$member->Username}/adminedit">Edit Profile</a></div>
    
</div>
HTML;
}
echo <<<HTML
<input type='submit' value='{$this->getWords()->getSilent('ProcessMembers')}'/>
</form>

HTML;
