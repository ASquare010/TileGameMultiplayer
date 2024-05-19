<h1>Welcome to Pirate Ships!</h1>
<?php
    if(!isset($_SESSION['currentplayer'])){     
        echo"<a href='sign-up.html'>Sign-up</a><br>
             <a href='login.html'>Login</a> <br> <a href='resetGame.html'>ResetGame</a>";
    }
else{
        echo "<a href='back.php'>Continue your game</a>";
    }
?>
