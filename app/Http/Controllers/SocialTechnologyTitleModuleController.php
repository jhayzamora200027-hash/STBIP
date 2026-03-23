<?php

namespace App\Http\Controllers;

use App\Models\SocialTechnologyTitle;
use Illuminate\Http\Request;

class SocialTechnologyTitleModuleController extends Controller
{
    public function index(Request $request)
    {
        $titles = SocialTechnologyTitle::query()->latest('updated_at')->get();

        return view('dashboard.maincomponents.st_titles_module', [
            'titles' => $titles,
        ]);
    }
}
