<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ subject }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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

        h1, h1 a {
            color: #fff;
        }

        #message {
            background: #e5e5e5;
            border-radius: 5px;
            margin: 1rem;
        }

        .header {
            background-color: #f37000;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            min-height: 50px;
        }

        .logo {
            float: left;
            padding: 0.5rem 2rem 0;
        }

        .content {
            padding: 0.5rem 1rem 0;
        }

        .from {
            background-color: rgba(0,0,0,.25);
            font-weight: bold;
            padding: 1rem;
            color: #eee;
        }

        div.from a:link {
            color: #000;
        }

        .bodytext {

        }

        #footer {
            background-color: #f37000;
            padding-left: 0.5rem;
            font-size: 0.8rem;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
            font-weight: bold;
        }

        #footer a {
            color: #fff;
        }
        --></style>
</head>
<body>
<div id="message">
    <div class="header" style="display: flex;">
        <div>
            <a href="<?php echo $siteUrl; ?>"><img src="<?php echo $siteUrl; ?>/images/logo_index_top.png" alt="BeWelcome" class="logo"></a>
        </div>
        <?php if($title) { ?>
            <div><h1 style="font-size:1em; font-weight: bold; line-height: 1em;"><?= $title ?></h1></div>
        <?php } ?>
    </div>
    <div class="content">
        <?= $body ?>
    </div>
    <div id="footer">
        <?php echo $footer_message; ?>
    </div>
</body>
</html>