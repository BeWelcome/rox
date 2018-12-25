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
            border-left: 8px #f37000 solid;
            padding: 0 0.5rem;
            margin: 0 0 1rem 0.5rem;
        }

        body {
            color: #333;
            font-size: 1.2rem;
            line-height: 1.5rem;
            font-family: arial, verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        a{
            color: #f37000;
            text-decoration: none;
        }

        .unsubscribe {
            font-size: 0.7rem;
            line-height: 1.1rem;
        }

        h1 {
            color: #fff;
            padding-top: 0.7rem;
            font-size: 2rem;
        }

        #message {
            background: #e5e5e5;
            border-radius: 10px;
            margin: 1rem;
        }

        .header {
            background-color: #f37000;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            min-height: 50px;
        }

        .logo {
            float: left;
            padding: 0.5rem 2rem 0;
        }

        .content {
            padding: 0.5rem 1rem 0;
        }

        .from{
            background-color: rgba(0,0,0,.4);
            font-size: 0.9rem;
            padding: 0 0 0 1rem;
            color: #ddd;
        }

        #footer {
            background-color: #f37000;
            padding-left: 0.5rem;
            font-size: 0.8rem;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            font-weight: bold;
        }
        --></style>
</head>
<body>
<div id="message">
<div class="header">
    <div>
        <a href="<?php echo PVars::getObj('env')->baseuri; ?>"><img src="<?php echo PVars::getObj('env')->baseuri; ?>images/logo_index_top.png" alt="<?php echo PVars::getObj('env')->sitename; ?>" class="logo"></a>
        <?php if($title) { ?>
            <h1 style="font-size:1.2em; font-weight: bold; line-height: 1.2em"><?= $title ?></h1>
        <?php } ?>
    </div>
</div>
<div class="content">
    <?= $body ?>
</div>
<div id="footer">
    <?php echo $footer_message; ?>
</div>
</body>
</html>
