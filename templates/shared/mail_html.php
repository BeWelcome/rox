<!DOCTYPE html>
<html lang="en">
<head>
    <title>Title</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css"><!--
        blockquote {
            color: #444;
            background: #f8f8f8;
            border: 1px #ddd solid;
            border-left: 8px #ddd solid;
            padding: 5px 20px;
            margin: 0 0 20px 5px;
        }

        body {
            background-color: #cccccc;
            color: #333;
            font-size: 1em;
            line-height: 1.5em;
            font-family: arial, verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .unsubscribe {
            font-size: 0.7em;
            line-height: 1.1em;
        }
        --></style>
</head>
<body>
<div style="margin: 20px 20px 0 20px;
padding: 5px 10px;
background: #e5e5e5;
border-top-left-radius: 10px;
border-top-right-radius: 10px;
height:50px;">
<div style="float: left"><?php if($title) { ?>
        <h1 style="font-size:1.2em; font-weight: bold; line-height: 1.2em"><?= $title ?></h1>
    <?php } ?></div>
    <div style="float:right;">
    <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="<?php echo PVars::getObj('env')->baseuri; ?>images/misc/email_logo.gif" alt="<?php echo PVars::getObj('env')->sitename; ?>" style="border:0"/></a></div>
</div>
<div  style="border-bottom-right-radius: 20px;
border-bottom-left-radius: 20px;
margin: 0 20px 0 20px; padding: 5px 10px; background: #fff;
clear: both;">
    <?= $body ?>
</div>
<div style="font-size: .7em; padding: 5px 30px;">
    <?php echo $footer_message; ?>
</div>
</body>
</html>
