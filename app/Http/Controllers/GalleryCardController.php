<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\GalleryCard;

class GalleryCardController extends Controller
{
    public function index()
    {
        $cards = GalleryCard::with([
            'creator',
            'updater',
            'children' => function($q){ $q->whereNull('parent_child_id')->orderBy('docno','asc'); },
            'children.histories',
            'children.creator',
            'children.updater',
            'children.children' => function($q){ $q->orderBy('docno','asc'); },
            'children.children.histories',
            'children.children.creator',
            'children.children.updater'
        ])->orderBy('docno', 'asc')->get();

        return view('admin.STsReportSectors', ['galleryCards' => $cards]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'nullable|string|max:255',
            'icon_class' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'status' => 'required|in:Completed,On going',
            'image' => 'nullable|image|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $name = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                // store on the public disk so files are available under /storage via storage:link
                $path = $file->storeAs('images/gallerysector', $name, 'public');
                $data['image'] = $path; // e.g. images/gallerysector/...
            }

            $data['is_active'] = !empty($data['is_active']) ? 1 : 0;
        $data['status'] = $data['status'] ?? 'On going';

        // auto-generate unique docno (numeric increment)
        $max = (int) (GalleryCard::max('docno') ?? 0);
        $data['docno'] = $max + 1;
            // set created_by to current logged-in user's user_id (fallback to Auth::id())
            $data['created_by'] = Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null;

            GalleryCard::create($data);

            return redirect()->route('admin.stsreportsectors')->with('success', 'Gallery card added.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add gallery card.');
        }
    }

    public function update(Request $request, GalleryCard $galleryCard)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|string|max:255',
            'icon_class' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:Completed,On going',
            'image' => 'nullable|image|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                // delete previous file from the public disk (if present)
                if ($galleryCard->image && Storage::disk('public')->exists($galleryCard->image)) {
                    Storage::disk('public')->delete($galleryCard->image);
                }

                $file = $request->file('image');
                $name = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/gallerysector', $name, 'public');
                $data['image'] = $path;
            }

            $data['is_active'] = !empty($data['is_active']) ? 1 : 0;

        // Do not overwrite created_by or docno on update
        unset($data['created_by']);
        unset($data['docno']);
            $galleryCard->update($data);

            return redirect()->route('admin.stsreportsectors')->with('success', 'Gallery card updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update gallery card.');
        }
    }

    public function destroy(GalleryCard $galleryCard)
    {
        try {
            if ($galleryCard->image && Storage::disk('public')->exists($galleryCard->image)) {
                Storage::disk('public')->delete($galleryCard->image);
            }
            $galleryCard->delete();

            return redirect()->route('admin.stsreportsectors')->with('success', 'Gallery card deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete gallery card.');
        }
    }
}
