<?php

namespace App\Http\Controllers\Sites;

use App\Http\Controllers\AdminController;
use App\Models\Site;
use Illuminate\Http\Request;

class Index extends AdminController
{
    public function __invoke(Request $request)
    {
        if (auth()->user()->cannot('view sites')) {
            abort(403);
        }

        $data = $this->baseData();

        $data['page_title']  = 'Sites';
        $data['breadcrumbs'] = [['label' => 'Sites']];
        $data['items']       = Site::query()
            ->when($request->input('search'),
                fn($q, $s) => $q->where('name', 'like', "%$s%")
                                 ->orWhere('url', 'like', "%$s%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('default.sites.index', compact('data'));
    }
}
