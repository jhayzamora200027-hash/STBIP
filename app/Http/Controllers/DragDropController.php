<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DragDropController extends Controller
{
    /**
     * Display the drag & drop editor page.
     *
     * This view uses GrapesJS (loaded via CDN) as a design canvas.  It is
     * intentionally left open so you can play with layout and then export the
     * generated HTML/CSS.  The page itself is not exposed to end users by default.
     */
    public function index()
    {
        
        $html = Storage::exists('dragdrop.html') ? Storage::get('dragdrop.html') : '<h1>Start dragging components here</h1>';
        
        return view('dragdrop.editor', [
            'dashboardHtml' => $html,
            'embed' => true,
        ]);
    }

    /**
     * Receive saved content from the editor and persist it.
     */
    public function save(Request $request)
    {
        $html = $request->input('gjs-html');
        $css  = $request->input('gjs-css');
        
        Storage::put('dragdrop.html', $html);
        Storage::put('dragdrop.css', $css);
        return response('ok');
    }
}
