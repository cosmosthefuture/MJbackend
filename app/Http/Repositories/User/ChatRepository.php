<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\ChatMessage;
use App\Models\GameRoom;

class ChatRepository extends BaseRepo
{
    public function __construct(ChatMessage $model)
    {
        parent::__construct($model);
    }

    public function get_game_type($id)
    {
        $data = GameRoom::with('game')->find($id);
        return $data->game->name;
    }

    public function saveMessage($data)
    {
        $result = $this->model->create([
            'game_room_id' => $data['game_room_id'],
            'user_id' => auth('api-user')->id(),
            'message' => $data['message'],
        ]);
        return $result;
    }
}
