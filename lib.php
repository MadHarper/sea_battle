<?php


function putSea($sea, $sessID){
    global $db;
    $sql = "SELECT * FROM game WHERE status = 'wait' ORDER BY id LIMIT 1";

    if(!$result = mysqli_query($db, $sql))
        return false;
    if(mysqli_num_rows($result) > 0){
        $arr = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);

        $_SESSION['number'] = 'second'; // номер игрока
        $_SESSION['table'] = $arr[0]['id']; // номер игрового стола, а в сущности id записи в БД

        $sql = "UPDATE game SET second_player = '$sessID', second_sea = '$sea',
                status = 'play' WHERE id = {$arr[0]['id']} LIMIT 1";

        if(!$res = mysqli_query($db, $sql))
            return false;
        mysqli_close($db);

        // 2 - значит вы второй игрок, игра начинается сразу
        return 2;

    }else{
        $_SESSION['number'] = 'first';
        //вставка моря в БД
        $sql = "INSERT INTO game (first_player, first_sea, status) VALUES('$sessID', '$sea', 'wait')";
        if(!$res = mysqli_query($db, $sql))
            return false;
        $_SESSION['table'] = mysqli_insert_id($db);
        mysqli_close($db);


        // 1 - значит вы первый игрок, ожидаем второго
        return 1;
    }

    //$rs = mysqli_fetch_all($result, MYSQLI_ASSOC);
    //mysqli_free_result($result);

}



/*-----------------чей ход---------------------*/
function whoPlayer($nmbr){
    global $db;
    $sql = "SELECT cur_player, winner FROM game WHERE id = {$_SESSION['table']} LIMIT 1";
    if(!$result = mysqli_query($db, $sql))
        return 'error';
    $arr = mysqli_fetch_all($result, MYSQLI_ASSOC);
    //mysqli_close($db);

    if($arr[0]['winner'] != 'none'){
        if($arr[0]['winner'] == $_SESSION['number'])
            return 'winner';
        else
            return 'looser';
    }
    if($nmbr == $arr[0]['cur_player'])
       return 'current';
    else
        return 'another';
}


function changePlayer(){
    global $db;
    if($_SESSION['number'] == 'first'){
        $sql = "UPDATE game SET cur_player = 'second' WHERE id = {$_SESSION['table']} LIMIT 1";
    } else {
        $sql = "UPDATE game SET cur_player = 'first' WHERE id = {$_SESSION['table']} LIMIT 1";
    }
    if(!$result = mysqli_query($db, $sql))
        return 'error';
    mysqli_close($db);
}



function getEnemySea(){
    global $db;
    if($_SESSION['number'] == 'first') {
        $sea = 'second_sea';
        $ships = 'ships_second';
    }
    else {
        $sea = 'first_sea';
        $ships = 'ships_first';
    }
    $sql = "SELECT {$sea}, {$ships}  FROM game WHERE id = {$_SESSION['table']} LIMIT 1";
    if(!$result = mysqli_query($db, $sql))
        return 'error';
    $arr = mysqli_fetch_all($result, MYSQLI_ASSOC);
    //mysqli_close($db);
    return $arr[0];
}


function saveEnemySea($enemy, $shipNum, $ships){
    global $db;

    if($_SESSION['number'] == 'first')
        $sql = "UPDATE game SET second_sea = '$enemy', $ships = $shipNum WHERE id = {$_SESSION['table']} LIMIT 1";

    else
        $sql = "UPDATE game SET first_sea = '$enemy', $ships = $shipNum WHERE id = {$_SESSION['table']} LIMIT 1";

    if(!$result = mysqli_query($db, $sql))
        return false;
    return true;
}


function filterSea($ftrSea){
    for($i = 0; $i < 10; $i++)
        for($j = 0; $j < 10; $j++){
            if(($ftrSea[$i][$j] == 0) OR ($ftrSea[$i][$j] == 'k') OR ($ftrSea[$i][$j] > 10))
                continue;
            else
                $ftrSea[$i][$j] = 0;
        }
    return $ftrSea;
}

function getMySea(){
    global $db;
    if($_SESSION['number'] == 'first') {
        $sea = 'first_sea';
    }
    else {
        $sea = 'second_sea';
    }
    $sql = "SELECT $sea  FROM game WHERE id = {$_SESSION['table']} LIMIT 1";
    if(!$result = mysqli_query($db, $sql))
        return 'error';
    $arr = mysqli_fetch_all($result, MYSQLI_ASSOC);
    //mysqli_close($db);
    return $arr[0]["$sea"];
}


class MySea{
    public $stat;
    public $mySea;
    public $winner;

   /*
    function __construct($status, $mySea){
        $this->stat = $status;
        $this->mySea = $mySea;
    }
   */
}

function endGame(){
    global $db;
    $winner = $_SESSION['number'];
    $sql = "UPDATE game SET winner = '$winner', status = 'end' WHERE id = {$_SESSION['table']} LIMIT 1";
    if(!$result = mysqli_query($db, $sql))
        return 'error';

}