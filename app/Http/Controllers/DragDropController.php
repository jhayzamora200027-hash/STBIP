<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DragDropController extends Controller
{
    public function index()
    {
        
        $html = Storage::exists('dragdrop.html') ? Storage::get('dragdrop.html') : '<h1>Start dragging components here</h1>';
        
        return view('dragdrop.editor', [
            'dashboardHtml' => $html,
            'embed' => true,
        ]);
    }
    public function save(Request $request)
    {
        $html = $request->input('gjs-html');
        $css  = $request->input('gjs-css');
        
        Storage::put('dragdrop.html', $html);
        Storage::put('dragdrop.css', $css);
        return response('ok');
    }
}
