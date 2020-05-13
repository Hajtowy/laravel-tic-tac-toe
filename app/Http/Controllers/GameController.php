<?php

namespace App\Http\Controllers;

use App\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private $emptyBoard = [
        '11' => null, '12' => null, '13' => null,
        '21' => null, '22' => null, '23' => null,
        '31' => null, '32' => null, '33' => null,
    ];

    public function show(int $id)
    {
        $game = Game::where('id', $id)->first();

        if (!$game) {
            return response()->json(['Game doesn\'t exsist'], 404);
        }

        return response()->json($game->toArray());
    }

    public function update(int $id)
    {
        $game = Game::where('id', $id)->first();

        if (!$game) {
            return response()->json(['Game doesn\'t exsist'], 404);
        }

        $data = request()->get('board');

        $game->board = json_encode($data);
        $game->save();

        return response()->json($game->toArray());
    }

    public function reset(int $id)
    {
        $game = Game::where('id', $id)->first();

        if (!$game) {
            return response()->json(['Game doesn\'t exsist'], 404);
        }

        $game->board = json_encode($this->emptyBoard);
        $game->x = null;
        $game->y = null;
        $game->save();

        return response()->json($game);
    }

    public function myChar(int $id)
    {
        $game = Game::where('id', $id)->first();

        if (!$game) {
            return response()->json(['Game doesn\'t exsist'], 404);
        }

        $char = 'Y';
        if ($game->x === null) {
            $char = 'X';
            $game->x = 'blocked';
            $game->save();
        }

        return response()->json(['char' => $char]);
    }
}
