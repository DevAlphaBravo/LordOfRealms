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
        <a href="/" class="uk-navbar-brand"><img src="/images/logo.png" alt="" style="width:20%;"></a>
        <div class="uk-navbar-flip">
            <div class="uk-navbar-content">
                <div class="uk-button-group">
                    <button class="uk-button-primary" onclick="window.location = '/menu/login.php';">Login</button>
                    <button class="uk-button-primary" onclick="window.location = '/menu/register.php';">Register</button>
                </div>
            </div>
        </div>
    </nav>
</div>
</body>
</html>