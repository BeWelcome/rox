<?php
    echo <<<HTML
<div>
<form action='' method='get'>
<label for='number_lines'>Number of lines to display:</label> <input id='number_lines' type='text' value='{$this->lines}' name='lines'/><br/>
<input type='submit' value='Display'/>
</form>
</div>
<div>
<b>Displaying last {$this->lines} of {$this->logname}:</b><br/>
<hr/>
<pre>
HTML;

$count = 1;
foreach ($this->tailLogFile($this->lines) as $line)
{
    echo "{$count}: " . wordwrap($line, 90, "\n    ", true) . "\n";
    $count++;
}
?>
</pre>
</div>
