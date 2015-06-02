<html>
<head>
    <title>Lord of Realms : #1 Browser MMORPG Game</title>
    <meta name="viewport" content="width=device-width">
    <?php
    require "includes/libs.php";
    ?>
    <style>
        body {
            margin:0;
        }
    </style>
</head>
<body>
<div class="uk-height-1-1 uk-width-1-1" style="overflow:hidden;background:url(/images/bg.png);">
    <nav class="uk-navbar">
        <div class="uk-navbar-content"><a href="/"><img src="/images/logo.png" style="width:20%;" alt=""></a></div>
        <div class="uk-navbar-content uk-navbar-flip uk-hidden-small">
            <div class="uk-button-group">
                <a href="/menu/login.php" class="uk-button">Login</a>
                <button class="uk-button uk-button-primary" onclick="window.location = '/menu/register.php';">Register</button>
            </div>
        </div>
    </nav>
</div>
</body>
</html>