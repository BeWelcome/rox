
<h3>Create a new Group</h3>

<form>
<label for="name">Name:</label><br />
<input name="name" id="name" /><br /><br />

<label for="description">Description:</label><br />
<textarea id="description"  name="description" 
         cols="50" rows="5"></textarea><br /><br />
        <?php /* ?>
        <h3>Group options</h3>
        Tools:<br>
        <input type="checkbox" checked> Group forum<br>
        <input type="checkbox"> Group blog<br>
        <br>
        <?php */ ?>

<h3>Who can join?</h3>
<ul>
<li><input type="radio" checked> Any BeWelcome member</li>
<li><input type="radio"> Any BeWelcome member, approved by moderators</li>
<li><input type="radio"> Only invited BeWelcome members</li>
<li><input type="radio"> Noone can join (it's not really a group)</li>
</ul>

<h3>Create it now!</h3>
        <input type="submit" value="Create"><br />
</form> 


