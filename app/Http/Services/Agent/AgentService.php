<?php

namespace App\Http\Services\Agent;

use App\Http\Repositories\Agent\AgentRepository;
use Exception;

class AgentService
{
    protected $agent_repository;

    public function __construct(AgentRepository $agent_repository)
    {
        $this->agent_repository = $agent_repository;
    }

    public function generateAccessToken($agent)
    {
        try {
            $result = $this->agent_repository->generateAccessToken($agent);
            $agent->update(['last_logined' => now()]);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to generate agent access token: ' . $e->getMessage());
            throw $e;
        }
    }

    public function logout()
    {
        try {
            $currentAccessToken = auth()->user()->currentAccessToken();

            $currentAccessToken->delete();

            auth()->user()->tokens()->where(
                'id',
                $currentAccessToken->id
            )->delete();
            return true;
        } catch (Exception $e) {
            logger()->error('Error : Failed to agent logout: ' . $e->getMessage());
            throw $e;
        }
    }

    public function whereFirst($column, $value)
    {
        try {
            $result = $this->agent_repository->whereFirst($column, $value);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to find agent with whereFirst: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getIncentiveTransactions($page = 1, $per_page = 10, $agentId): array
    {
        try {
            $result = $this->agent_repository->getIncentiveTransactions($page, $per_page, $agentId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent incentive transactions: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDailyWalletSummary($page = 1, $per_page = 10, $agentId): array
    {
        try {
            $result = $this->agent_repository->getDailyWalletSummary($page, $per_page, $agentId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent daily wallet summary: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getMonthlyIncentiveSummary($page = 1, $per_page = 12, $agentId)
    {
        try {
            $result = $this->agent_repository->getMonthlyIncentiveSummary($page, $per_page, $agentId);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to fetch agent monthly incentive summary: ' . $e->getMessage());
            throw $e;
        }
    }

    public function storeFcmToken($agent, $token)
    {
        try {
            $result = $this->agent_repository->storeFcmToken($agent, $token);
            return $result;
        } catch (Exception $e) {
            logger()->error('Error : Failed to store agent fcm token: ' . $e->getMessage());
            throw $e;
        }
    }
}
