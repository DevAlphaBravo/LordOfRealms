<title>Lord of Realms</title>
<meta name="viewport" content="width=device-width">
<style>
    body {
        margin:0px;
        background:url(/images/bg.png);
        text-align:center;
    }

    .input {
        background:url(/images/form_bg.png) repeat-x;
        border:1px solid #d1c7ac;
        width: 230px;
        color:#333333;
        padding:3px;
        margin-right:4px;
        margin-bottom:8px;
        font-family:tahoma, arial, sans-serif;
    }

    .button-green {
        background: #2ba812;
        background-image: -webkit-linear-gradient(top, #2ba812, #2a7a0b);
        background-image: -moz-linear-gradient(top, #2ba812, #2a7a0b);
        background-image: -ms-linear-gradient(top, #2ba812, #2a7a0b);
        background-image: -o-linear-gradient(top, #2ba812, #2a7a0b);
        background-image: linear-gradient(to bottom, #2ba812, #2a7a0b);
        -webkit-border-radius: 28;
        -moz-border-radius: 28;
        border-radius: 28px;
        font-family: Arial;
        color: #ffffff;
        font-size: 20px;
        padding: 10px 20px 10px 20px;
        text-decoration: none;
    }

    .button-blue {
        background: #1d46eb;
        background-image: -webkit-linear-gradient(top, #1d46eb, #0c19ab);
        background-image: -moz-linear-gradient(top, #1d46eb, #0c19ab);
        background-image: -ms-linear-gradient(top, #1d46eb, #0c19ab);
        background-image: -o-linear-gradient(top, #1d46eb, #0c19ab);
        background-image: linear-gradient(to bottom, #1d46eb, #0c19ab);
        -webkit-border-radius: 28;
        -moz-border-radius: 28;
        border-radius: 28px;
        font-family: Arial;
        color: #ffffff;
        font-size: 20px;
        padding: 10px 20px 10px 20px;
        text-decoration: none;
    }
</style>
<img src="/images/logo.png" style="width:99%; margin:auto;">
<br>
<div style="width:350px; font-size:12px; text-align:center; margin:auto; background:white; padding:10px;border:1px outset gray;">
    <form action="login.php" method="post">
        <input type="text" class="input" style="width:300px;" placeholder="Username"><br>
        <input type="password" class="input" style="width:300px;" placeholder="Password"><br>
        <input type="button" class="button-blue" value="Sign Up FREE" onclick="window.location='/menu/signup.php';"> <input type="submit" value="Login" class="button-green">
    </form>
</div>