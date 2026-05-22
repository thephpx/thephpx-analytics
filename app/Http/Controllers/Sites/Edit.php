<?php

namespace App\Http\Controllers\Sites;

use App\Http\Controllers\AdminController;
use App\Models\Site;
use Illuminate\Http\Request;

class Edit extends AdminController
{
    public function __invoke(Request $request, Site $site)
    {
        if (auth()->user()->cannot('edit sites')) {
            abort(403);
        }

        $data = $this->baseData();

        if ($request->isMethod('POST')) {
            $request->validate([
                'name'    => 'required|string|max:255',
                'url'     => 'required|url|max:500',
                'api_key' => 'required|string|max:255',
            ]);

            $site->update($request->only('name', 'url', 'api_key'));

            return redirect()->route('sites.index')
                ->with('success', 'Site updated successfully.');
        }

        $data['page_title']  = 'Edit Site';
        $data['breadcrumbs'] = [
            ['label' => 'Sites', 'url' => route('sites.index')],
            ['label' => 'Edit Site'],
        ];
        $data['item'] = $site;

        return view('default.sites.edit', compact('data'));
    }
}
