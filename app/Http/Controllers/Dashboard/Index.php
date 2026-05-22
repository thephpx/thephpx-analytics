<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\AdminController;
use App\Models\Site;
use App\Services\MatrixService;
use Illuminate\Http\Request;

class Index extends AdminController
{
    public function __invoke(Request $request, MatrixService $matrix)
    {
        $data = $this->baseData();

        $data['page_title']  = 'Dashboard';
        $data['breadcrumbs'] = [['label' => 'Dashboard']];

        $sites = Site::orderBy('name')->get();

        $data['cards'] = $sites->map(function (Site $site) use ($matrix) {
            $stats = $matrix->fetchStats($site);
            $last7 = $matrix->lastSevenDays($stats['visitors'] ?? []);
            return [
                'site'       => $site,
                'user_count' => $stats['user_count'],
                'last7'      => $last7,
                'chart_labels' => collect($last7)->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('D'))->values()->toArray(),
                'chart_values' => collect($last7)->pluck('visitor_count')->values()->toArray(),
                'error'      => $stats['error'],
            ];
        });

        return view('default.dashboard.index', compact('data'));
    }
}
