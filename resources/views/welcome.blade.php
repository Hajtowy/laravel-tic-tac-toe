<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <style>
        .row .col-4 {
            height: 100px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <h3>Jesteś: <span class="my-char"></span></h3>
    </div>

    <div class="row">
        <h4 class="winner"></h4>
    </div>

    <div class="row">
        <div class="col-4 border-right border-bottom d-flex align-items-center justify-content-center" data-game="11">
            <p class="answer display-1"></p>
        </div>
        <div class="col-4 border-right border-bottom d-flex align-items-center justify-content-center" data-game="12">
            <p class="answer display-1"></p>
        </div>
        <div class="col-4 border-bottom d-flex align-items-center justify-content-center" data-game="13">
            <p class="answer display-1"></p>
        </div>
    </div>

    <div class="row">
        <div class="col-4 border-right border-bottom d-flex align-items-center justify-content-center" data-game="21">
            <p class="answer display-1"></p>
        </div>
        <div class="col-4 border-right border-bottom d-flex align-items-center justify-content-center" data-game="22">
            <p class="answer display-1"></p>
        </div>
        <div class="col-4 border-bottom d-flex align-items-center justify-content-center" data-game="23"><p
                class="answer display-1"></p>
        </div>
    </div>

    <div class="row">
        <div class="col-4 border-right d-flex align-items-center justify-content-center" data-game="31">
            <p class="answer display-1"></p>
        </div>
        <div class="col-4 border-right d-flex align-items-center justify-content-center" data-game="32">
            <p class="answer display-1"></p>
        </div>
        <div class="col-4 d-flex align-items-center justify-content-center" data-game="33">
            <p class="answer display-1"></p>
        </div>
    </div>

    <div class="row">
        <button class="btn btn-success start-game mr-3">START</button>
        <button class="btn btn-warning reset-game mr-3">RESET</button>

        <button class="btn btn-danger stop-game text-right">EXIT</button>
    </div>
</div>

<script>
    $(document).ready(function () {
        var conn;
        var myChar;
        var disableChoice = false;
        var winner = '';

        $('.start-game').on('click', function () {
            conn = new WebSocket('ws://localhost:8080');
            conn.onopen = function (e) {
                fillBoard();
            };

            conn.onmessage = function (e) {
                fillBoard();
                disableChoice = false;
            };

            onClickEvent();
            getMyChar();
        });

        $('.stop-game').on('click', function () {
            if (conn) {
                conn.close();
            }
        });

        $('.reset-game').on('click', function () {
            $.ajax({
                url: '/game/1/reset',
                data: {},
                success: function (response) {
                    insertDataToBoard(response);
                    $('.col-4').removeClass('disabled');
                    conn.send('');
                    disableChoice = false;
                }
            })
        });


        $('.check-game').on('click', function () {
            checkIfSomebodyWin();
        });


        var getMyChar = function () {
            $.ajax({
                url: '/game/1/my-char',
                data: {},
                success: function (response) {
                    myChar = response.char;
                    $('.my-char').text(myChar);
                }
            });
        };

        var fillBoard = function () {
            $.ajax({
                url: '/game/1',
                data: {},
                success: function (response) {
                    if (response) {
                        insertDataToBoard(response);
                        checkIfSomebodyWin();
                        checkIfEndGame();
                    }
                },
                error: function () {

                }
            });
        };

        var onClickEvent = function () {
            $('.col-4').on('click', function () {
                if (disableChoice) {
                    alert('Teraz nie Twoja kolej! Poczekaj!');
                } else {
                    if (!$(this).hasClass('disabled')) {
                        disableChoice = true;

                        $(this).find('p').text(myChar);

                        let data = {};

                        $('.col-4').each(function () {
                            data[$(this).data('game')] = $(this).find('p').text();
                        });

                        $.ajax({
                            url: '/game/1/update',
                            data: {
                                "board": data
                            },
                            success: function (response) {
                                insertDataToBoard(response);
                                conn.send('');

                                checkIfSomebodyWin();
                                checkIfEndGame();
                            },
                            error: function () {

                            }
                        });
                    }
                }
            });
        };

        var insertDataToBoard = function (response) {
            var data = JSON.parse(response.board);

            $.each(data, function (key, value) {
                let field = $('.col-4[data-game=' + key + ']');

                if (field.length > 0) {
                    field.find('.answer').text(value);

                    if (value !== null) {
                        field.addClass('disabled');
                    }
                }
            });
        };

        var checkIfSomebodyWin = function () {
            let board = {};

            $('.col-4').each(function () {
                board[$(this).data('game')] = $(this).find('p').text();
            });

            // check ---->
            var xIndex = [];
            var yIndex = [];

            for (var i = 1; i <= 3; i++) {
                var x = 0;
                var y = 0;

                for (var j = 1; j <= 3; j++) {
                    var index = i + '' + j;

                    if (board[index] === 'X') {
                        x++;
                        xIndex.push(index);
                    }
                    if (board[index] === 'Y') {
                        y++;
                        yIndex.push(index);
                    }
                }

                if (x == 3 || y == 3) {
                    if (x == 3) {
                        winner = 'X';
                    }
                    if (y == 3) {
                        winner = 'Y';
                    }
                    break;
                }
            }

            if (winner.length === 0) {
                // check |||
                var xIndex = [];
                var yIndex = [];

                for (var i = 1; i <= 3; i++) {
                    var x = 0;
                    var y = 0;

                    for (var j = 1; j <= 3; j++) {
                        var index = j + '' + i;

                        if (board[index] === 'X') {
                            x++;
                            xIndex.push(index);
                        }
                        if (board[index] === 'Y') {
                            y++;
                            yIndex.push(index);
                        }

                        if (x == 3 || y == 3) {
                            if (x == 3) {
                                winner = 'X';
                            }
                            if (y == 3) {
                                winner = 'Y';
                            }
                            break;
                        }
                    }
                }
            }

            if (winner.length === 0) {
                // check \\\
                var xIndex = [];
                var yIndex = [];
                var x = 0;
                var y = 0;

                for (var i = 1; i <= 3; i++) {
                    var index = i + '' + i;

                    if (board[index] === 'X') {
                        x++;
                        xIndex.push(index);
                    }
                    if (board[index] === 'Y') {
                        y++;
                        yIndex.push(index);
                    }

                    if (x == 3 || y == 3) {
                        if (x == 3) {
                            winner = 'X';
                        }
                        if (y == 3) {
                            winner = 'Y';
                        }
                        break;
                    }
                }
            }

            if (winner.length === 0) {
                // check ///
                var xIndex = [];
                var yIndex = [];
                var j = 3;
                var x = 0;
                var y = 0;

                for (var i = 1; i <= 3; i++) {
                    var index = i + '' + j;

                    if (board[index] === 'X') {
                        x++;
                        xIndex.push(index);
                    }
                    if (board[index] === 'Y') {
                        y++;
                        yIndex.push(index);
                    }

                    if (x == 3 || y == 3) {
                        if (x == 3) {
                            winner = 'X';
                        }
                        if (y == 3) {
                            winner = 'Y';
                        }
                        break;
                    }
                    j--;
                }
            }

            if (winner.length > 0) {
                $('.winner').text('Wygrał ' + winner + ' gratulacje!');
            }
        };

        checkIfEndGame = function () {
            if (winner.length === 0) {
                let board = {};
                let notEmpty = 0;

                $('.col-4').each(function () {
                    if ($(this).find('p').text().length > 0) {
                        notEmpty++;
                    }
                });

                if (notEmpty === 9) {
                    $('.winner').text('Remis!');
                    disableChoice = true;
                }
            }
        };
    });
</script>
</body>
</html>
