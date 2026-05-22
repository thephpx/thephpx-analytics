<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    protected function baseData(): array
    {
        return [
            'auth_user'        => auth()->user(),
            'auth_permissions' => auth()->user()?->getAllPermissions()->pluck('name'),
            'auth_roles'       => auth()->user()?->getRoleNames(),
            'page_title'       => '',
            'breadcrumbs'      => [],
        ];
    }
}
