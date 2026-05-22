<?php

namespace App\Http\Controllers\Sites;

use App\Http\Controllers\AdminController;
use App\Models\Site;
use Illuminate\Http\Request;

class Create extends AdminController
{
    public function __invoke(Request $request)
    {
        if (auth()->user()->cannot('create sites')) {
            abort(403);
        }

        $data = $this->baseData();

        if ($request->isMethod('POST')) {
            $request->validate([
                'name'    => 'required|string|max:255',
                'url'     => 'required|url|max:500',
                'api_key' => 'required|string|max:255',
            ]);

            Site::create($request->only('name', 'url', 'api_key'));

            return redirect()->route('sites.index')
                ->with('success', 'Site added successfully.');
        }

        $data['page_title']  = 'Add Site';
        $data['breadcrumbs'] = [
            ['label' => 'Sites', 'url' => route('sites.index')],
            ['label' => 'Add Site'],
        ];

        return view('default.sites.create', compact('data'));
    }
}
