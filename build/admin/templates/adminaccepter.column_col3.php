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
<p>Displaying members with status: <b><?=$this->status;?></b>. Select which status to display below.</p>
<form action='' method='get'>
<label for='status_switch'>Select status:</label> <select name='status'></select><br/>
<input type='submit' value='Update'/>
</form>
