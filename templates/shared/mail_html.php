<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css"><!--
        blockquote {
            color: #444;
            background: #f8f8f8;
            border: 1px #ddd solid;
            border-left: 8px #f37000 solid;
            padding: 0 0.5em;
            margin: 0 0 1em 0.5em;
        }

        body {
            color: #333;
            font-family: arial, verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        a {
            color: #f37000;
            text-decoration: none;
        }

       #message {
            background: #f0f0f0;
       }

        .header {
            background-color: #f37000;
            width:100%;
        }

        h1{
            font-size:1em;
            font-weight: bold;
            line-height: 1em ;
            padding-top: 0.2em;
        }

        .logo {
            float: right;
            padding: 0.25em;
        }

        .content {
            padding: 0.25em;
        }

        div.from a:link {
            color: #000;
        }

        .footer {
            background-color: #f37000;
            width:100%;
            padding: 0.25em;
            font-size: 0.8em;
            color: #eee;
        }

        .footer a {
            color: #fff;
            font-weight: bold;
        }
        --></style>
</head>
<body>
<div id="message">
    <div class="header">
            <a href="<?php echo $siteUrl; ?>"><img src="<?php echo $siteUrl; ?>/images/logo_index_top.png" alt="BeWelcome" class="logo" border="0" style="border:0"></a>
    </div>
    <div class="content">
        <?php if($title) { ?>
            <div class="subheader">
                <strong><?= $title ?></strong>
            </div>
        <?php } ?>

        <?= $body ?>
    </div>
    <div class="footer">
        <?php echo $footer_message; ?>
    </div>
</div>
</body>
</html>