<?php

namespace App\Http\Repositories\User;

use App\Http\Repositories\BaseRepo;
use App\Models\CoinFlipBet;
use App\Models\CoinFlipPayout;
use App\Models\CoinFlipResult;
use App\Models\CoinFlipRound;
use App\Models\DailyHouseCutReport;
use App\Models\DailyProfitReport;
use App\Models\GameRoom;
use App\Models\GlobalCommissionSetting;
use App\Models\SpinWheelBet;
use App\Models\SpinWheelResult;
use App\Models\SpinWheelRound;
use App\Models\UserGameHistory;
use DB;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

class GameRoomRepository extends BaseRepo
{
    public function __construct(GameRoom $model)
    {
        parent::__construct($model);
    }

    public function getDataWithPaginationCached(
        int $perPage,
        int $page,
        ?array $searches = null,
        ?array $conditions = null,
        ?string $status = null,
        array $with = []
    ): array {
        $cacheKey = $this->buildCacheKey(
            'game_rooms:list',
            [
                'page' => $page,
                'per' => $perPage,
                'status' => $status,
                'searches' => $searches,
                'conditions' => $conditions,
                'with' => $with,
            ]
        );

        $ttl = now()->addMinutes(5);

        $tags = [
            'game_rooms',
            isset($conditions['game_id']) ? "game:{$conditions['game_id']}" : null,
        ];

        $tags = array_filter($tags);

        $cache = Cache::getStore() instanceof RedisStore
            ? Cache::tags($tags)
            : Cache::store();

        $cached = $cache->has($cacheKey);

        $results = $cache->remember($cacheKey, $ttl, function () use ($perPage, $page, $searches, $conditions, $status, $with) {
            return $this->getDataWithPagination(
                perPage: $perPage,
                page: $page,
                searches: $searches,
                conditions: $conditions,
                status: $status,
                with: $with
            );
        });

        $results['meta']['cached'] = $cached;

        return $results;
    }

    protected function buildCacheKey(string $prefix, array $params): string
    {
        return $prefix . ':' . collect($params)
            ->filter(fn($v) => $v !== null && $v !== [] && $v !== '')
            ->map(function ($value) {
                if (is_array($value)) {
                    return collect($value)->sortKeys()->toJson();
                }
                return $value;
            })
            ->sortKeys()
            ->map(fn($v, $k) => "{$k}={$v}")
            ->implode(':');
    }


    public function find($id)
    {
        $data = GameRoom::with(['game', 'gameRule'])->find($id);
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function allRooms()
    {
        $data = GameRoom::with(['game', 'gameRule'])->get();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function fetchLatestRound($roomId, $type)
    {
        if ($type == 'spin_wheel') {
            $round = SpinWheelRound::where('game_room_id', $roomId)->latest()->first();
            return $round;
        } else {
            $round = CoinFlipRound::where('game_room_id', $roomId)->latest()->first();
            return $round;
        }
    }

    public function createRoundForSpinWheel($roomId)
    {
        $latest = $this->fetchLatestRound($roomId, 'spin_wheel');
        $round_number = ($latest->round_number ?? 0) + 1;
        $round = SpinWheelRound::create([
            'game_room_id' => $roomId,
            'round_number' => $round_number,
            'status' => 'betting'
        ]);
        return $round;
    }

    public function createRoundForCoinFlip($roomId)
    {
        $latest = $this->fetchLatestRound($roomId, 'coin_flip');
        $round_number = ($latest->round_number ?? 0) + 1;
        $round = CoinFlipRound::create([
            'game_room_id' => $roomId,
            'round_number' => $round_number,
            'status' => 'betting'
        ]);
        return $round;
    }

    public function get_spin_wheel_game_round($column, $value)
    {
        $data = SpinWheelRound::where($column, $value)->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function get_coin_flip_game_round($column, $value)
    {
        $data = CoinFlipRound::where($column, $value)->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function create_spin_wheel_bet($data, $already_bet)
    {
        // if ($already_bet) {
        //     $bet = SpinWheelBet::where('spin_wheel_round_id', $data['spin_wheel_round_id'])
        //         ->where('user_id', $data['user_id'])->first();
        //     $previous_bet_amount = $bet->bet_amount;
        //     $result = $bet->update([
        //         'bet_amount' => $previous_bet_amount + $data['bet_amount'],
        //         'winning_chance_percentage' => $data['winning_chance_percentage']
        //     ]);
        // } else {
        //     $result = SpinWheelBet::create($data);
        // }
        // return $result;
        $result = SpinWheelBet::create($data);
        return $result;
    }

    public function cancel_spin_wheel_bet($game_round)
    {
        $user = auth('api-user')->user();

        $bets = SpinWheelBet::where('spin_wheel_round_id', $game_round->id)
            ->where('user_id', $user->id)
            ->get();


        $totalBetAmount = $bets->sum('bet_amount');
        $bets->each->delete();
        $user->deposit($totalBetAmount, [
            'type' => 'cancel_spin_wheel_bet',
            'round_id' => $game_round->id,
        ]);
    }

    public function create_coin_flip_bet($data, $already_bet)
    {
        if ($already_bet) {
            $bet = CoinFlipBet::where('coin_flip_round_id', $data['coin_flip_round_id'])
                ->where('user_id', $data['user_id'])->first();
            $previous_bet_amount = $bet->bet_amount;
            $result = $bet->update([
                'bet_amount' => $previous_bet_amount + $data['bet_amount'],
            ]);
        } else {
            $result = CoinFlipBet::create($data);
        }
        return $result;
    }

    public function cancel_coin_flip_bet($game_round)
    {
        $user = auth('api-user')->user();
        $bet = CoinFlipBet::where('coin_flip_round_id', $game_round->id)
            ->where('user_id', $user->id)->first();
        $bet->delete();
        $user->deposit($bet->bet_amount, ['type' => 'cancel_coin_flip_bet', 'round_id' => $game_round->id]);
    }

    public function calculateSpinWheelBetInfo($round_id)
    {
        $bets = SpinWheelBet::with('user')
            ->where('spin_wheel_round_id', $round_id)
            ->orderBy('created_at')
            ->get();

        if ($bets->isEmpty()) {
            return [
                'bet_info' => [],
                'spin_segments' => []
            ];
        }

        $totalPot = $bets->sum('bet_amount');

        $grouped = $bets->groupBy('user_id');

        foreach ($grouped as $userId => $userBets) {

            $totalUserBet = $userBets->sum('bet_amount');

            $totalUserPercentage = $totalPot > 0
                ? round(($totalUserBet / $totalPot) * 100, 2)
                : 0;

            foreach ($userBets as $bet) {

                $singleBetPercentage = $totalPot > 0
                    ? round(($bet->bet_amount / $totalPot) * 100, 2)
                    : 0;

                $bet->single_bet_winning_percentage = $singleBetPercentage;
                $bet->total_winning_chance_percentage = $totalUserPercentage;
                $bet->save();
            }
        }


        $betInfo = $grouped->map(function ($userBets) use ($totalPot) {

            $totalUserBet = $userBets->sum('bet_amount');

            $percentage = $totalPot > 0
                ? round(($totalUserBet / $totalPot) * 100, 2)
                : 0;

            $firstBet = $userBets->first();

            return [
                'id' => $firstBet->id,
                'spin_wheel_round_id' => $firstBet->spin_wheel_round_id,
                'user_id' => $firstBet->user_id,
                'user_id_code' => $firstBet->user->identification_code,
                'bet_amount' => $totalUserBet,
                'total_winning_chance_percentage' => $percentage,
                'created_at' => $firstBet->created_at,
                'updated_at' => $firstBet->updated_at,
                'user' => $firstBet->user,
            ];
        })->values();

        $spinSegments = $bets->map(function ($bet) use ($totalPot) {

            return [
                'user_id' => $bet->user_id,
                'user_id_code' => $bet->user->identification_code,
                'user_name' => $bet->user->name,
                'bet_amount' => $bet->bet_amount,
                'single_bet_winning_percentage' => $bet->single_bet_winning_percentage,
            ];
        })->values();

        return [
            'bet_info' => $betInfo,
            'spin_segments' => $spinSegments
        ];
    }

    public function calculateCoinFlipBetInfo($round_id)
    {
        $coin_flip_commission_percentage = GlobalCommissionSetting::where('key', 'coin_flip_house_cut_percentage')->first();
        $houseFeeRate = $coin_flip_commission_percentage->value / 100;

        $bets = CoinFlipBet::where('coin_flip_round_id', $round_id)->get();

        $totalHeads = $bets->where('side', 'HEAD')->sum('bet_amount');
        $totalTails = $bets->where('side', 'TAIL')->sum('bet_amount');
        $totalPot = $totalHeads + $totalTails;

        $headChance = $totalPot > 0 ? round(($totalHeads / $totalPot) * 100, 2) : 50;
        $tailChance = $totalPot > 0 ? round(($totalTails / $totalPot) * 100, 2) : 50;

        CoinFlipRound::where('id', $round_id)->update([
            'total_head_bet' => $totalHeads,
            'total_tail_bet' => $totalTails,
            'head_winning_chance_percentage' => $headChance,
            'tail_winning_chance_percentage' => $tailChance,
        ]);

        $netPot = round($totalPot * (1 - $houseFeeRate), 2);

        $bet_users = $bets->map(function ($bet) use ($totalHeads, $totalTails, $netPot) {

            $totalWinningSide = $bet->side === 'HEAD' ? $totalHeads : $totalTails;

            $possibleWin = ($bet->bet_amount / $totalWinningSide) * $netPot;

            $possibleWin = max($possibleWin, $bet->bet_amount);

            return [
                'user_id' => $bet->user_id,
                'user_name' => $bet->user->name,
                'bet_side' => $bet->side,
                'bet_amount' => $bet->bet_amount,
                'possible_winning_amount' => round($possibleWin, 2),
            ];
        });

        return [
            'round_id' => $round_id,

            'totals' => [
                'HEAD' => $totalHeads,
                'TAIL' => $totalTails,
                'POT' => $totalPot,
            ],

            'winning_chance' => [
                'HEAD' => $headChance,
                'TAIL' => $tailChance,
            ],

            'bet_users' => $bet_users->values(),
        ];
    }

    public function checkIfAlreadyBetOrNotForSpinWheel($round_id, $user_id)
    {
        $data = SpinWheelBet::where('spin_wheel_round_id', $round_id)
            ->where('user_id', $user_id)->exists();
        // if (!$data) {
        //     return null;
        // }
        return $data;
    }

    public function checkIfAlreadyBetOrNotForCoinFlip($round_id, $user_id)
    {
        $data = CoinFlipBet::where('coin_flip_round_id', $round_id)
            ->where('user_id', $user_id)->first();
        if (!$data) {
            return null;
        }
        return $data;
    }

    public function get_bet_side($round_id, $user_id)
    {
        $data = CoinFlipBet::where('coin_flip_round_id', $round_id)
            ->where('user_id', $user_id)->first();

        return $data->side;
    }

    public function calculate_result_for_spin_wheel($game_round)
    {
        $existingResult = SpinWheelResult::where(
            'spin_wheel_round_id',
            $game_round->id
        )->first();

        if ($existingResult) {
            return $this->buildSpinWheelResultFromStoredData($game_round, $existingResult);
        }


        $game_round->status = "spinning";
        $game_round->save();

        $bets = $game_round->bets()->with('user')->get();

        if ($bets->count() === 0) {
            return [];
        }

        $groupedBets = $bets->groupBy('user_id');

        $userBets = $groupedBets->map(function ($userBetGroup) {
            return [
                'user_id' => $userBetGroup->first()->user_id,
                'user' => $userBetGroup->first()->user,
                'total_bet_amount' => $userBetGroup->sum('bet_amount'),
            ];
        })->values()->shuffle();

        $totalPot = $userBets->sum('total_bet_amount');

        $spin_wheel_commission_percentage = GlobalCommissionSetting::where(
            'key',
            'spin_wheel_house_cut_percentage'
        )->first();

        $houseCutPercentage = $spin_wheel_commission_percentage->value ?? 0;
        $houseCutAmount = ($totalPot * $houseCutPercentage) / 100;
        $netPot = $totalPot - $houseCutAmount;

        $R = random_int(0, max($totalPot - 1, 0));

        $cumulative = 0;
        $winnerUserId = null;

        foreach ($userBets as $bet) {
            $cumulative += $bet['total_bet_amount'];
            if ($R < $cumulative) {
                $winnerUserId = $bet['user_id'];
                break;
            }
        }

        SpinWheelResult::create([
            'spin_wheel_round_id' => $game_round->id,
            'winner_user_id' => $winnerUserId,
            'total_pot' => $totalPot,
            'house_cut' => $houseCutAmount,
            'winner_payout' => $netPot,
        ]);
        // put data into daily report for house cut
        $this->putDataIntoReport($houseCutAmount, 'spin_wheel');
        // put data into daily profit report
        $this->putDataIntoProfitReport($houseCutAmount, 'spin_wheel');

        // put data into user game histories
        $this->putDataIntoUserGameHistory($game_round, 'spin_wheel');

        $winner = $userBets->firstWhere('user_id', $winnerUserId);

        if ($winner) {
            $winner['user']->deposit(
                $netPot,
                ['type' => 'spin_wheel_won', 'round_id' => $game_round->id]
            );
        }

        return collect($userBets)->map(function ($bet) use ($winnerUserId, $netPot) {
            return [
                'user_id' => $bet['user_id'],
                'user_name' => $bet['user']->name,
                'status' => $bet['user_id'] === $winnerUserId ? 'win' : 'lost',
                'winning_amount' => $bet['user_id'] === $winnerUserId ? $netPot : 0,
            ];
        })->values()->toArray();
    }

    private function buildSpinWheelResultFromStoredData($game_round, $result)
    {
        $bets = $game_round->bets()->with('user')->get();

        if ($bets->isEmpty()) {
            return [];
        }

        $groupedBets = $bets->groupBy('user_id');

        return $groupedBets->map(function ($userBetGroup) use ($result) {

            $user = $userBetGroup->first()->user;
            $userId = $userBetGroup->first()->user_id;

            $isWinner = $userId === $result->winner_user_id;

            return [
                'user_id' => $userId,
                'user_name' => $user->name,
                'status' => $isWinner ? 'win' : 'lost',
                'winning_amount' => $isWinner ? $result->winner_payout : 0,
            ];

        })->values()->toArray();
    }

    private function putDataIntoReport($houseCutAmount, $target)
    {
        $report = DailyHouseCutReport::firstOrCreate(
            ['report_date' => today()],
            [
                'spin_wheel_house_cut' => 0,
                'coin_flip_house_cut' => 0,
                'total_house_cut' => 0
            ]
        );

        if ($target === 'spin_wheel') {
            $report->increment('spin_wheel_house_cut', $houseCutAmount);
        } else {
            $report->increment('coin_flip_house_cut', $houseCutAmount);
        }

        $report->increment('total_house_cut', $houseCutAmount);
    }

    private function putDataIntoProfitReport($amount, $target)
    {
        $report = DailyProfitReport::firstOrCreate(
            ['report_date' => today()],
            [
                'spin_wheel_profit' => 0,
                'coin_flip_profit' => 0,
                'money_transfer_profit' => 0,
                'total_profit' => 0
            ]
        );

        if ($target === 'spin_wheel') {
            $report->increment('spin_wheel_profit', $amount);
        } elseif ($target === 'coin_flip') {
            $report->increment('coin_flip_profit', $amount);
        } elseif ($target === 'money_transfer') {
            $report->increment('money_transfer_profit', $amount);
        }

        $report->increment('total_profit', $amount);
    }

    private function putDataIntoUserGameHistory($game_round, $type)
    {
        if ($type === 'spin_wheel') {
            $bets = SpinWheelBet::where('spin_wheel_round_id', $game_round->id)
                ->selectRaw('user_id, SUM(bet_amount) as total_bet')
                ->groupBy('user_id')
                ->get();
            foreach ($bets as $bet) {

                $isWinner = $bet->user_id == $game_round->result->winner_user_id;

                UserGameHistory::create([
                    'user_id' => $bet->user_id,
                    'game_type' => 'Spin Wheel',
                    'bet_amount' => $bet->total_bet,
                    'win_amount' => $isWinner ? $game_round->result->winner_payout : 0,
                    'status' => $isWinner ? 'won' : 'lost',
                    'room_name' => $game_round->gameRoom->room_name,
                    'round_number' => $game_round->round_number,
                    'game_round_id' => $game_round->id,
                ]);
            }
        } elseif ($type === 'coin_flip') {

            $bets = CoinFlipBet::where('coin_flip_round_id', $game_round->id)->get();

            $payouts = CoinFlipPayout::where('coin_flip_round_id', $game_round->id)
                ->pluck('win_amount', 'user_id');

            foreach ($bets as $bet) {

                $winAmount = $payouts[$bet->user_id] ?? 0;

                UserGameHistory::create([
                    'user_id' => $bet->user_id,
                    'game_type' => 'Coin Flip',
                    'bet_amount' => $bet->bet_amount,
                    'win_amount' => $winAmount,
                    'status' => $winAmount > 0 ? 'won' : 'lost',
                    'room_name' => $game_round->gameRoom->room_name,
                    'round_number' => $game_round->round_number,
                    'game_round_id' => $game_round->id,
                ]);
            }
        }
    }

    public function calculate_result_for_coin_flip($game_round)
    {
        $existingResult = CoinFlipResult::where('coin_flip_round_id', $game_round->id)->first();
        if ($existingResult) {
            return $this->buildCoinFlipResultFromStoredData($game_round, $existingResult);
        }

        $game_round->status = "flipping";
        $game_round->save();

        $coin_flip_commission_percentage = GlobalCommissionSetting::where('key', 'coin_flip_house_cut_percentage')->first();
        $houseFeeRate = $coin_flip_commission_percentage->value / 100;

        $bets = CoinFlipBet::with('user')
            ->where('coin_flip_round_id', $game_round->id)
            ->get();

        $totalHeads = $bets->where('side', 'HEAD')->sum('bet_amount');
        $totalTails = $bets->where('side', 'TAIL')->sum('bet_amount');
        $totalPot = $totalHeads + $totalTails;

        if ($totalHeads == 0 || $totalTails == 0) {
            throw new \Exception('Both sides must have bets to resolve round.');
        }

        $random = mt_rand(1, $totalPot);
        $winningSide = $random <= $totalHeads ? 'HEAD' : 'TAIL';

        $netPot = round($totalPot * (1 - $houseFeeRate), 2);

        $totalWinningSideBet = $winningSide === 'HEAD' ? $totalHeads : $totalTails;

        CoinFlipResult::create([
            'coin_flip_round_id' => $game_round->id,
            'result_side' => $winningSide,
            'total_head_bet' => $totalHeads,
            'total_tail_bet' => $totalTails,
            'total_pot' => $totalPot,
            'house_cut' => $totalPot - $netPot,
        ]);

        // put data into daily report for house cut
        $this->putDataIntoReport($totalPot - $netPot, 'coin_flip');
        // put data into daily profit report
        $this->putDataIntoProfitReport($totalPot - $netPot, 'coin_flip');

        $userResultLists = [];

        foreach ($bets as $bet) {
            $isWinner = $bet->side === $winningSide;

            if ($isWinner) {
                $winningAmount = ($bet->bet_amount / $totalWinningSideBet) * $netPot;
                $winningAmount = round($winningAmount, 2);
                if ($winningAmount < $bet->bet_amount) {
                    $winningAmount = $bet->bet_amount;
                }

                $bet->user->deposit($winningAmount, ['type' => 'coin_flip_won', 'round_id' => $game_round->id]);

                CoinFlipPayout::Create(
                    [
                        'coin_flip_round_id' => $game_round->id,
                        'user_id' => $bet->user_id,
                        'total_bet_amount' => $bet->bet_amount,
                        'win_amount' => $winningAmount,
                    ]
                );
            } else {
                $winningAmount = 0;
            }

            $userResultLists[] = [
                'user_id' => $bet->user_id,
                'user_name' => $bet->user->name,
                'status' => $isWinner ? 'win' : 'lost',
                'winning_amount' => $winningAmount,
            ];
        }

        // put data into user game histories
        $this->putDataIntoUserGameHistory($game_round, 'coin_flip');

        return [
            'winning_side' => $winningSide,
            'user_result_lists' => $userResultLists,
        ];
    }

    private function buildCoinFlipResultFromStoredData($game_round, $result)
    {
        $bets = CoinFlipBet::with('user')
            ->where('coin_flip_round_id', $game_round->id)
            ->get();

        $userResultLists = $bets->map(function ($bet) use ($result) {
            $isWinner = $bet->side === $result->result_side;
            $payout = CoinFlipPayout::where('coin_flip_round_id', $result->coin_flip_round_id)
                ->where('user_id', $bet->user_id)
                ->first();

            return [
                'user_id' => $bet->user_id,
                'user_name' => $bet->user->name,
                'status' => $isWinner ? 'win' : 'lost',
                'winning_amount' => $payout?->win_amount ?? 0,
            ];
        });

        return [
            'winning_side' => $result->result_side,
            'user_result_lists' => $userResultLists->toArray(),
        ];
    }

    public function finish_round_for_spin_wheel($game_round)
    {
        $game_round->status = "finished";
        $game_round->save();
    }

    public function finish_round_for_coin_flip($game_round)
    {
        $game_round->status = "finished";
        $game_round->save();
    }

    public function getTotalBetAmountOfTargetUserForSpinWheel($roundId, $userId)
    {
        $bet_amount = SpinWheelBet::where('spin_wheel_round_id', $roundId)->where('user_id', $userId)->sum('bet_amount');
        return $bet_amount;
    }

    public function getTotalBetAmountOfTargetUserForCoinFlip($roundId, $userId)
    {
        $bet = CoinFlipBet::where('coin_flip_round_id', $roundId)->where('user_id', $userId)->first();
        return $bet->bet_amount;
    }
}
