<title>Lord of Realms : Signup</title>
<meta name="viewport" content="width=device-width">

<style>
    body {
        margin:0;
        background:url(/images/bg.png);
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
</style>
<div style="width:300px; background:white; text-align:center; font-size:12px; margin:auto; border:1px outset gray;">
    <h1 style="text-shadow:1px 0px 1px #888; color:darkgray;">Sign Up Form</h1>
    <div style="font-size:10px; text-align:center;"></div>
    <form action="signup.php" method="post">
        <input type="text" class="input" name="username" placeholder="Desired Username"><br>
        <input type="text" class="input" name="email" placeholder="Email Address"><br>
        <input type="password" class="input" name="password" placeholder="Password"><br>
        <input type="password" class="input" name="confirm" placeholder="Confirm Password"><br>
    </form>
</div>