<?php
session_start();
$_SESSION['fuck'] = 'fucking fuck';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link href="../css/style.css" rel="stylesheet" type="text/css">
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/main.js"></script>
</head>
<body>
	<div id="wrapper">
		<div class="top">
            <h3>Морской бой</h3><br />
            <div id="centerInTop">

            </div>

        </div>

		<div id="left">

        </div>
		<div id="center">

            <?php
            // $field = array();
            for($y = 0; $y < 10; $y++)
                for($x = 0; $x < 10; $x++){
                    echo "<div class='zero cell' id='$x$y'></div>";
                    // $field[$x][$y] = 0;
                }
            // $_SESSION['field'] = $field;
            ?>

            <div id="orient"><button>Повернуть</button></div><br />
            <div id="startButton" class="hid"><br /><button>Начать игру!</button></div><br />

        </div>
        <div id="middle">
            <div id="res"></div>
        </div>
        <div id="ultra_left"></div>
		<div id="right" >
            <div class="hid">
                <?php
                // $enemyField = array();
                for($y = 0; $y < 10; $y++)
                    for($x = 0; $x < 10; $x++){
                        echo "<div class='zero cell' id='enemy$x$y'></div>";
                        // $enemyField[$x][$y] = 0;
                    }
                ?>
            </div>
        </div>


        <div id="footer"></div>
	</div>
</body>
</html>

