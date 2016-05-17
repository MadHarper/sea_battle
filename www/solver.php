<?php
session_start();
require '../config.php';
require '../lib.php';



    if($_POST['sea']){
        //$sea = json_encode($_POST['sea']);

        //ToDo: надо будет добавить проверку входящих данных (sea)

        $sessID = session_id();
        $message = putSea($_POST['sea'], $sessID);
        echo $message;

        exit;
    }

/*
       $sql = "SELECT * FROM game WHERE status = 'end'";
        if(!$result = mysqli_query($db, $sql))
            return false;

        $rs = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
*/


/*---------------------------------------*/

        //echo $_SESSION['number'];




/*------------------------Блок игры---------------------------------*/





    if ($_POST['ask']){

        $rule = whoPlayer($_SESSION['number']);

        if($rule == 'winner'){
            $arrSea = array();
            $arrSea = getMySea();
            $arrSea = json_decode($arrSea);

            $mySea = new MySea();
            $mySea->stat = 'stop';
            $mySea->mySea = $arrSea;
            $mySea->winner = 'winner';
            $mySea = json_encode($mySea);
            echo $mySea;
            exit;
        }


        if($rule == 'looser'){
            $arrSea = array();
            $arrSea = getMySea();
            $arrSea = json_decode($arrSea);

            $mySea = new MySea();
            $mySea->stat = 'stop';
            $mySea->mySea = $arrSea;
            $mySea->winner = 'looser';
            $mySea = json_encode($mySea);
            echo $mySea;
            exit;
        }

        if($rule == 'current'){
            $arrSea = array();
            $arrSea = getMySea();
            $arrSea = json_decode($arrSea);

            $mySea = new MySea();
            $mySea->stat = 'play';
            $mySea->mySea = $arrSea;
            $mySea->winner = 'none';
            $mySea = json_encode($mySea);
            echo $mySea;
            exit;
        }

        if($rule == 'another') {

            $arrSea = array();
            $arrSea = getMySea();
            $arrSea = json_decode($arrSea);

            $mySea = new MySea();
            $mySea->stat = 'wait';
            $mySea->mySea = $arrSea;
            $mySea->winner = 'none';
            $mySea = json_encode($mySea);
            echo $mySea;
            exit;
        }
    }



    if (isset($_POST['turn_x'])){
        /*toDo Здесь надо получить из turn данные о координате выстрела и свериться с БД*/


        $x = (int)$_POST['turn_x'];
        $y = (int)$_POST['turn_y'];


        $sea = array();
        $temp = getEnemySea();

        if($_SESSION['number'] == 'first') {
            $numSea = 'second_sea';
            $ships = 'ships_second';
        }
        else {
            $numSea = 'first_sea';
            $ships = 'ships_first';
        }
        $sea = json_decode($temp["$numSea"]);
        $shipNum = $temp["$ships"];
        $turnOver = true;

        /*
        changePlayer();
        echo $sea[$x][$y];
        exit;
        */

        /* Если значение элемента массива = 0 - значит молоко. Возвращаем массив через фильтр.

        /* Если значение элемента массива < 0 - значит ранее уже стреляли в эту клетку, здесь раненный корабль,
        Возвращаем массив через фильтр
        */

        /* Если значение элемента массива = 'k', значит ранее уже стреляли в эту клетку, здесь убитый корабль
        Возвращаем массив через фильтр
        */

        if($sea[$x][$y] === 0){

            $sea[$x][$y] = 'b';
            saveEnemySea(json_encode($sea), $shipNum, $ships);
            $sea = filterSea($sea);
            changePlayer();
            echo json_encode($sea);
            exit;
        }


        if(($sea[$x][$y] === 'b') || ($sea[$x][$y] === 'k') || ($sea[$x][$y] > 10)){
            $sea = filterSea($sea);
            changePlayer();
            echo json_encode($sea);
            exit;
        }




        $shipNum = $shipNum - 1;

        //переход хода
        $turnOver = false;

        $flag = 0;
        $nmbr = $sea[$x][$y];
        $sea[$x][$y] = $nmbr*10;
            for($i = 0; $i < 10; $i++) {
                for ($j = 0; $j < 10; $j++) {

                    if ($sea[$i][$j] == $nmbr) {

                        $flag = 1;

                    }
                }
            }
        if($flag == 0)
            $sea[$x][$y] = 'k';

        if($flag == 0 AND $nmbr > 4) {
            for($i = 0; $i < 10; $i++)
                for($j = 0; $j < 10; $j++){
                    if($sea[$i][$j] == 'k')
                        continue;
                    if($sea[$i][$j] == $nmbr*10)
                        $sea[$i][$j] = 'k';
                }
        }



        /*
        for($i = 0; $i < 10; $i++)
            for($j = 0; $j < 10; $j++){
                if($sea[$i][$j] == 'k')
                    continue;
                if(abs($sea[$i][$j]) == $nmbr)
                    $sea[$i][$j] = 'k';
            }
        */

        saveEnemySea(json_encode($sea), $shipNum, $ships);

        if($shipNum <= 0){
            endGame();
        }

        if($turnOver)
            changePlayer();

        $sea = filterSea($sea);
        echo json_encode($sea);
        exit;



        /* Если данный элемент массива не подпадает под предыдущие варианты то это новое попадание,
           запоминаем цифру-значение (скажем $nmbr).

        /* Проверяем, есть ли еще в массиве элементы с такой цифрой. Если есть - значит ранил. Присваеваем клетке
        то же значение со знаком "-" (-$nmbr.
            Если в массиве нет элементов с такой цифрой, значит убил. Присваеваем массиву значение 'k'.
        Обходим массив, и если находим элементы со значением -$nmbr то меняем из на 'k'.
        Возвращаем массив через фильтр.
        */


        /*  В случае попадания уменьшаем общее количество "живых" палуб противника на 1.
            Проверям, не стало ли колчество "живых" палуб = 0. Если = 0, значит игра окончена.
        */


    }




?>