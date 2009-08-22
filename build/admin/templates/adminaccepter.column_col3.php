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
?>
</p>
<p>Displaying members with status: <b><?=$this->status;?></b> (<?= count($this->members);?> members in total with that status). Select which status to display below.</p>
<form action='' method='get'>
<label for='status_switch'>Select status:</label> <select name='status'></select><br/>
<input type='submit' value='Update'/>
</form>

<?php
$this->pager->render();
$members = $this->members;
foreach ($members as $member)
{
    echo <<<HTML
<div>
    <p>Username: {$member->Username}</p>
</div>
HTML;
}
