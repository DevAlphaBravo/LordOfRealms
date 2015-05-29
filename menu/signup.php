<?php
include "../includes/db.php";

if(isset($_COOKIE['session'])) {
    die("<script>window.location = '/';</script>");
}

function cleanString($txt) {
    return htmlspecialchars($txt);
}

if(isset($_POST['username'])) {
    $username = cleanString($_POST['username']);
    $email = cleanString($_POST['email']);
    $password = cleanString($_POST['password']);
    $confirm = cleanString($_POST['confirm']);
    $char = cleanString($_POST['character']);

    $charCheck = false;

    $etxt = "";

    if($username == "") {
        $etxt = "Please enter an username.";
    }

    if($email == "") {
        $etxt = "Please enter an email.";
    }

    if($password == "") {
        $etxt = "Plaes enter a password.";
    }

    switch($char) {
        case "jeff":
            $charCheck = true;
            break;
        case "amia":
            $charCheck = true;
            break;
        case "jack":
            $charCheck = true;
            break;
    }

    if($charCheck == false) {
        $etxt = "You did not select a valid character.";
    }
    die("Works");
    $aon = query("SELECT * FROM members WHERE username=:username",array(
        "username" => $username
    ));
    if($aon->rowCount() > 0) {
        $etxt = "That username has already been taken.";
    }

    $bon = query("SELECT * FROM members WHERE email=:email",array(
        ":email" => $email
    ));
    if($bon->rowCount() > 0) {
        $etxt = "That email is already in our system.";
    }

    if($password != $confirm) {
        $etxt = "Your passwords do not match.";
    }

    if($etxt == "") {

    }
}
?>
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
    .selection {
        overflow:hidden;
        border:1px solid black;
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
</style>
<script type="text/javascript">
    function selectCharacter(id) {
        for(i=0;i<document.getElementsByName("characterSelection").length;i++) {
            document.getElementsByName("characterSelection")[i].style.border = "1px solid black";
        }

        document.getElementById(id).style.border = "3px solid green";

        document.getElementById("character").value = id;
    }
</script>
<br>
<div style="width:300px; background:white; text-align:center; font-size:12px; margin:auto; border:1px outset gray;">
    <h1 style="text-shadow:1px 0px 1px #888; color:darkgray;">Sign Up Form</h1>
    <div style="font-size:10px; text-align:center;color:red;"><?php echo $etxt; ?></div>
    <form action="/menu/signup.php" method="post">
        <input type="text" class="input" name="username" placeholder="Desired Username"><br>
        <input type="text" class="input" name="email" placeholder="Email Address"><br>
        <input type="password" class="input" name="password" placeholder="Password"><br>
        <input type="password" class="input" name="confirm" placeholder="Confirm Password"><br>
        <input type="hidden" id="character" name="character" value="jeff">

        <table style="width:100%; text-align:center;margin:auto;">
            <tr>
                <td><div id="jeff" name="characterSelection" class="selection" style="background:url(/characters/jeff.png) -36px -96px; width:36px; height:48px;" onclick="selectCharacter(this.id);"></div></td>
                <td><div id="amia" name="characterSelection" class="selection" style="background:url(/characters/amia.png) -32px 0px; width:32px; height:32px;" onclick="selectCharacter(this.id);"></div></td>
                <td><div id="jack" name="characterSelection" class="selection" style="background:url(/characters/jack.png) -32px 0px; width:32px; height:32px;" onclick="selectCharacter(this.id);"></div></td>
            </tr>
        </table><br>
        <input type="submit" class="button-green" value="Create Account">
    </form>
</div>