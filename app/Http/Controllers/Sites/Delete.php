<?php

namespace App\Http\Controllers\Sites;

use App\Http\Controllers\AdminController;
use App\Models\Site;
use Illuminate\Http\Request;

class Delete extends AdminController
{
    public function __invoke(Request $request, Site $site)
    {
        if (auth()->user()->cannot('delete sites')) {
            abort(403);
        }

        $site->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Site removed successfully.',
            ]);
        }

        return redirect()->route('sites.index')
            ->with('success', 'Site removed successfully.');
    }
}
