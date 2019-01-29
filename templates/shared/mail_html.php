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
            padding: 0 0.5em;
            margin: 0 0 1em 0.5em;
        }

        body {
            color: #333;
            font-size: 1.2em;
            line-height: 1.5em;
            font-family: arial, verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        a{
            color: #f37000;
            text-decoration: none;
        }

       #message {
            background: #e5e5e5;
        }

        .header {
            background-color: #f37000;
            min-height: 50px;
        }

        h1{
            font-size:1em;
            font-weight: bold;
            line-height: 1em ;
            padding-top: 0.2em;
        }

        .logo {
            float: left;
            padding: 0.5rem 2em 0;
        }

        .content {
            padding: 0.5rem 2em 0;
        }

        div.from a:link {
            color: #000;
        }

        footer {
            background-color: #f37000;
            padding-left: 2em;
            font-size: 0.8rem;
            color: #eee;
        }

        footer a {
            color: #fff;
            font-weight: bold;
        }
        --></style>
</head>
<body>
<div id="message">
    <div class="header">
            <a href="<?php echo $siteUrl; ?>"><img src="<?php echo $siteUrl; ?>/images/logo_index_top.png" alt="BeWelcome" class="logo" border="0"></a>
    </div>

    <div class="content">

        <?php if($title) { ?>
            <div class="subheader">
                <h1><?= $title ?></h1>
            </div>
        <?php } ?>

        <?= $body ?>
    </div>
    <footer>
        <?php echo $footer_message; ?>
    </footer>
</div>
</body>
</html>