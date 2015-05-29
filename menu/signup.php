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
    <div style="font-size:10px; text-align:center;"></div>
    <form action="signup.php" method="post">
        <input type="text" class="input" name="username" placeholder="Desired Username"><br>
        <input type="text" class="input" name="email" placeholder="Email Address"><br>
        <input type="password" class="input" name="password" placeholder="Password"><br>
        <input type="password" class="input" name="confirm" placeholder="Confirm Password"><br>
        <input type="hidden" id="character" name="character" value="jeff">

        <table style="width:100%; text-align:center;margin:auto;">
            <tr>
                <td><div id="jeff" name="characterSelection" class="selection" style="background:url(/characters/jeff.png) -36px -96px; width:36px; height:48px;" onclick="selectCharacter(this.id);"></div></td>
                <td><div id="amia" name="characterSelection" class="selection" style="background:url(/characters/amia.png) -32px 0px; width:32px; height:32px;" onclick="selectCharacter(this.id);"></div></td>
            </tr>
        </table>
    </form>
</div>