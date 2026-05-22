<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Http;

class MatrixService
{
    public function fetchStats(Site $site): array
    {
        try {
            $response = Http::timeout(8)
                ->withToken($site->api_key)
                ->get(rtrim($site->url, '/') . '/matrix/stats');

            if ($response->successful()) {
                return array_merge($response->json(), ['error' => null]);
            }

            return ['user_count' => 0, 'visitors' => [], 'error' => 'API returned ' . $response->status()];
        } catch (\Exception $e) {
            return ['user_count' => 0, 'visitors' => [], 'error' => 'Unreachable'];
        }
    }

    public function lastSevenDays(array $visitors): array
    {
        return array_slice($visitors, -7);
    }
}
