<?php
 # include("t_config.php");
?>
<html>
<head>
 <title>WikiCommander:CommandLine</title>
 <link rel="stylesheet" type="text/css" href="80x25.css">
</head>
<body bgcolor="#000000" text="#dddddd" class="CommandLine" onLoad="document.forms[0].elements[0].focus()">
<nobr><form action="action.php" target="page" method="GET">
ewiki@<?php echo $_SERVER["SERVER_NAME"]; ?>$&nbsp;<input type="text" name="input" size="40"
onKeypress="if (event.ctrlKey) { this.value = this.value + parent.id + ' '; return false; }">
<input type="hidden" name="cmd" value="1">
</form></nobr></body>
</html>
