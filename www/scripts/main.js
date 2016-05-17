$(function(){

    /* тест работоспособности получения элемента ДОМ */
    //var apple = getDomElement(5, 4);
    //$(apple).removeClass('zero').addClass('ship');

    /*ToDo создание экземпляра конфигурационного объекта*/
    var config = new Config();

    /*ToDo создание массива объектов Cell - для отображения*/
    var View = new Array();

    /*ToDo создание массива размещения кораблей для отправки на сервер*/
    var Place = new Array();


    for(var a = 0; a < 10; a++){
        View[a] = new Array(10);
        Place[a] = new Array(10);
    }

    for(var x = 0; x < 10; x++)
        for(var y = 0; y < 10; y++){
            View[x][y] = new Fabric(x, y);
            Place[x][y] = 0;
        }

    /* ToDo Для всех экземпляров класса Fabric создаем функцию changeClass */
    Fabric.prototype.changeClass = function(){
        $(this.elem).removeClass('zero').addClass('ship');
    };

    Fabric.prototype.changeBack = function(){
        $(this.elem).removeClass('ship').addClass('zero');
    };


    /* наезд мышки на div обертку */
    $('#center').on('mouseover', '.cell', function(){
        if(config.gameStatus != 'place')
            return;

        var string_xy = $(this).attr('id');
        var x = Number(string_xy.substr(0, 1));
        var y = Number(string_xy.substr(1, 1));

        if(outField(x, y))
            return;

        if(notPlace(x, y))
            return;

        if(config.orientation === 'horizontal'){
            for(var i = 0; i < config.Plates[config.restShips - 1]; i++)
                View[x + i][y].changeClass();
        }else{
            for(var i = 0; i < config.Plates[config.restShips - 1]; i++)
                View[x][y + i].changeClass();
        }

    });

    /* съезд мышки на div обертку */
    $('#center').on('mouseleave', '.cell', function(){
        if(config.gameStatus != 'place')
            return;

        var string_xy = $(this).attr('id');
        var x = Number(string_xy.substr(0, 1));
        var y = Number(string_xy.substr(1, 1));

        if(outField(x, y))
            return;

        if(notPlace(x, y))
            return;

        if(config.orientation === 'horizontal'){
            for(var i = 0; i < config.Plates[config.restShips - 1]; i++)
                View[x + i][y].changeBack();
        }else{
            for(var i = 0; i < config.Plates[config.restShips - 1]; i++)
                View[x][y + i].changeBack();
        }
    });

    /* смена ориентации корабля вертикально/горизонтально */
    $('#orient').click(function(){
        if(config.orientation === 'horizontal')
            config.orientation = 'vertical';
        else
            config.orientation = 'horizontal';
    });



    /* клик на div обертку */
    $('#center').on('click', '.cell', function(){
        if(config.gameStatus != 'place')
            return;

        if (config.restShips <= 0)
            return;

        var string_xy = $(this).attr('id');
        var x = Number(string_xy.substr(0, 1));
        var y = Number(string_xy.substr(1, 1));

        if(outField(x, y))
            return;

        if(notPlace(x, y))
            return;

        if(config.orientation === 'horizontal'){
            for(var i = 0; i < config.Plates[config.restShips - 1]; i++) {
                // присвоим данным квадратам номер корабля
                Place[x + i][y] = config.restShips;
                View[x + i][y].changeClass();

            }
        }else{
            for(var i = 0; i < config.Plates[config.restShips - 1]; i++){
                Place[x][y + i] = config.restShips;
                View[x][y + i].changeClass();
            }
        }
        config.restShips = config.restShips - 1;
        if(config.restShips === 0){
            config.gameStatus = 'iamready';
            getStartButton();
        }

    });


    function outField(x, y){
        if(config.orientation === 'horizontal'){
            if((x + config.Plates[config.restShips - 1]) > 10)
                return true;
        } else {
            if((y + config.Plates[config.restShips - 1]) > 10)
                return true;
        }
        return false;
    };


    function notPlace(x, y) {

        if (config.orientation === 'horizontal') {

            for (var i = x - 1; i < x + 1 + config.Plates[config.restShips - 1]; i++) {
                if (i < 0 || i > 9)
                    continue;
                for (var j = y - 1; j < y + 2; j++) {
                    if (j < 0 || j > 9)
                        continue;
                    if (Place[i][j] != 0)
                        return true;
                }
            }
        } else {
            for (var i = y - 1; i < y + 1 + config.Plates[config.restShips - 1]; i++) {
                if (i < 0 || i > 9)
                    continue;
                for (var j = x - 1; j < x + 2; j++) {
                    if (j < 0 || j > 9)
                        continue;
                    if (Place[j][i] != 0)
                        return true;
                }
            }
        }
        return false;
    };

    // появление кнопки "Играть!"

    function getStartButton(){
        if (config.gameStatus === 'iamready'){
            $('.hid').css('visibility', 'visible');
            //$('#center').append("<div id='startButton'><br /><button>Начать игру!</button></div><br />");
        }
    };

    // по нажатию кнопки "Играть!" отсылаются аяксом данные о составленном поле (массив Place)

    $('#startButton').on("click", function(){

        if(config.gameStatus === 'iamready'){
            var mySea = JSON.stringify(Place);

            $.ajax({
                type: 'POST',
                url: 'solver.php',
                data: 'sea=' + mySea,
                success: function(date){
                    if(date == 1){
                        setWait();
                    } else if (date == 2){
                        setYourTurn();
                    }
                }
            });
            /*
            config.gameStatus = 'play';
            startTimer();
            */
            $('#startButton > button').attr('disabled', 'disabled');
            $('#orient > button').attr('disabled', 'disabled');
        }
    });


/------------------------------------------ тестовые таймер и аякс ------------------------/
    var timer;
    function startTimer(){
        timer = setInterval(askSrv, 2000);
    };


    function askSrv(){
        $.ajax({
            type: 'POST',
            url: 'solver.php',
            data: 'ask=alex',
            success: function(date){
                $('#footer').append(date);
            }
        });
    };
/------------------------------------------ конец блока <тестовые таймер и аякс> ------------------------/




    function setWait(){
        config.gameStatus = 'wait';
        $('#centerInTop').html('<img src="img/30.gif" alt="ожидаем соперника"><br />');
        startTimerWait();
    }

    function setYourTurn(){
        config.gameStatus = 'play';
        stopTimerWait();
        $('#centerInTop').html('Ваш ход<br />');
    /* ToDo что делать если пришла команда "твой ход" */

    }


    var timerWait;
    function startTimerWait(){
        if (config.gameStatus === 'wait')
            timerWait = setInterval(askWait, 400);
    };

    function stopTimerWait(){
        clearInterval(timerWait);
    };

    function askWait(){
        $.ajax({
            type: 'POST',
            url: 'solver.php',
            data: 'ask=wait',
            success: function(date){
                /*
                if(date === 'play')
                    setYourTurn();
                else
                    $('#footer').append(date);
                */

                var answer = JSON.parse(date);

                if(answer.stat == 'stop'){
                    for(var i = 0; i < 10; i++)
                        for(var j = 0; j < 10; j++){
                            var line = "#" + i + "" + j;
                            if(answer.mySea[i][j] === 0){

                            }else if(answer.mySea[i][j] == 'b'){
                                $(line).removeClass('zero').addClass('bulk');
                            }
                            else if(answer.mySea[i][j] == 'k'){
                                $(line).removeClass('hurt').removeClass('ship').addClass('kill');
                            }else if(answer.mySea[i][j] > 10){
                                $(line).removeClass('ship').removeClass('zero').addClass('hurt');
                            }
                        }
                    config.gameStatus = 'stop';
                    clearInterval(timerWait);
                    if(answer.winner == 'winner')
                        $('#centerInTop').html('Вы выиграли!<br />');
                    else if (answer.winner = 'looser')
                        $('#centerInTop').html('Увы, Вы проиграли<br />');
                }

                if(answer.stat == 'wait'){
                    for(var i = 0; i < 10; i++)
                        for(var j = 0; j < 10; j++){
                            var line = "#" + i + "" + j;
                            if(answer.mySea[i][j] === 0){

                            }else if(answer.mySea[i][j] == 'b'){
                                $(line).removeClass('zero').addClass('bulk');
                            }
                            else if(answer.mySea[i][j] == 'k'){
                                $(line).removeClass('hurt').removeClass('ship').addClass('kill');
                            }else if(answer.mySea[i][j] > 10){
                                $(line).removeClass('ship').removeClass('zero').addClass('hurt');
                            }
                        }
                }


                else if(answer.stat == 'play'){
                    //var myNewSea = answer.mySea;
                    for(var i = 0; i < 10; i++)
                        for(var j = 0; j < 10; j++){
                            var line = "#" + i + "" + j;
                            if(answer.mySea[i][j] === 0){

                            }else if(answer.mySea[i][j] == 'b'){
                                $(line).removeClass('zero').addClass('bulk');
                            }
                            else if(answer.mySea[i][j] == 'k'){
                                $(line).removeClass('hurt').removeClass('ship').addClass('kill');
                            }else if(answer.mySea[i][j] > 10){
                                $(line).removeClass('ship').addClass('hurt');
                            }
                        }
                    setYourTurn();
                }

            }
        });
    };


/*------------------------------------< Блок управления стрельбы по полю соперника >---------------------------*/
    // отображение движения мышки
    $('#right').on('mouseover', '.cell', function(){
        if(config.gameStatus != 'play')
            return;

        $(this).removeClass('zero').addClass('strike');
    });



    $('#right').on('mouseleave', '.cell', function() {
        if (config.gameStatus != 'play')
            return;

        $(this).removeClass('strike').addClass('zero');
    });




    // стрельба по морю соперника, клик на его море и отсылка аякс запроса с координатами
    $('#right').on('click', '.cell', function(){
        if(config.gameStatus === 'play'){
            config.gameStatus = 'send';
            $('#centerInTop').html('<img src="img/30.gif" alt="отсылается"><br />');

            var string_xy = $(this).attr('id');
            var x = Number(string_xy.substr(5, 1));
            var y = Number(string_xy.substr(6, 1));
            var xy = Number(string_xy.substr(5, 2));
            var that = $(this);

            $.ajax({
                type: 'POST',
                url: 'solver.php',
                data: 'turn_x=' + x + '&turn_y=' + y,
                success: function(date){

                    /*
                    if(parseInt(date) === 0) {
                        that.removeClass('strike').addClass('zero');
                    }else{
                        that.removeClass('strike').addClass('kill');
                    }

                    $('#res').append(date);
                    */
                    var sea = JSON.parse(date);
                    for(var i = 0; i < 10; i++)
                        for(var j = 0; j < 10; j++){
                            var line = "#enemy" + i + "" + j;
                            if(sea[i][j] == 0){
                                $(line).removeClass('strike').addClass('zero');
                            }else if(sea[i][j] == 'b'){
                                $(line).removeClass('strike').removeClass('zero').addClass('bulk');
                            }
                            else if(sea[i][j] == 'k'){
                                $(line).removeClass('hurt').removeClass('strike').removeClass('zero').addClass('kill');
                            }else{
                                $(line).removeClass('strike').removeClass('zero').addClass('hurt');
                            }

                        }
                    //$('#res').append(date);
                    //$('#footer').html(date);

                    setWait();
                }
            });
        }





    });


});


/*----------------------------------------------------------------------------------------------*/
/* Функция конструктор объекта Cell (ячейка) */
function Fabric(x, y){
    this.x = x;
    this.y = y;
    this.elem = getDomElement(x, y);
};

function getDomElement(x, y){
    var target = "#" + x + y;
    return $(target);
};


/*ToDo создание конструктора конфигурационного объекта*/
function Config(){

    // флаг состояния игры: 'place' - стадия размещения, 'iamready' - готовность текущего игрока, 'play' - игра запущена
    this.gameStatus = 'place';

    this.restShips = 10;
    this.orientation = 'horizontal';
    this.currentShip = 9;

    // массив количество палуб
    this.Plates = [1, 1, 1, 1, 2, 2, 2, 3, 3, 4];


function Ship(nmbr, plates){
    this.shipNmbr = nmbr;
    this.plates = plates;
}};



