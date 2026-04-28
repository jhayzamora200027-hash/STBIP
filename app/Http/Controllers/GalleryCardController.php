<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\GalleryCard;
use Illuminate\Support\Facades\DB;

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
                
                $path = $file->storeAs('images/gallerysector', $name, 'public');
                $data['image'] = $path; 
            }

            $data['is_active'] = !empty($data['is_active']) ? 1 : 0;
        $data['status'] = $data['status'] ?? 'On going';

        
        $max = (int) (GalleryCard::max('docno') ?? 0);
        $data['docno'] = $max + 1;
            
            $data['created_by'] = Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null;

            $card = GalleryCard::create($data);

            // record a sector utilities log entry (if table exists)
            try {
                if (DB::getSchemaBuilder()->hasTable('sector_utilities_logs')) {
                    DB::table('sector_utilities_logs')->insert([
                        'action' => 'create',
                        'user' => (Auth::check() ? (Auth::user()->user_id ?? Auth::id()) : null),
                        'details' => 'Created gallery card: ' . ($card->title ?? ''),
                        'gallery_card_id' => $card->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // don't block creation on logging failure
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'card_id' => $card->id, 'docno' => $card->docno]);
            }
            return redirect()->route('admin.stsreportsectors')->with('success', 'Gallery card added (docno: ' . ($card->docno ?? '-') . ').');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to add gallery card.'], 500);
            }
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
                
                if ($galleryCard->image && Storage::disk('public')->exists($galleryCard->image)) {
                    Storage::disk('public')->delete($galleryCard->image);
                }

                $file = $request->file('image');
                $name = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/gallerysector', $name, 'public');
                $data['image'] = $path;
            }

            $data['is_active'] = !empty($data['is_active']) ? 1 : 0;

        
        unset($data['created_by']);
        unset($data['docno']);
            $galleryCard->update($data);

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('admin.stsreportsectors')->with('success', 'Gallery card updated.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update gallery card.'], 500);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to update gallery card.');
        }
    }

    public function destroy(Request $request, GalleryCard $galleryCard)
    {
        try {
            if ($galleryCard->image && Storage::disk('public')->exists($galleryCard->image)) {
                Storage::disk('public')->delete($galleryCard->image);
            }
            $galleryCard->delete();

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('admin.stsreportsectors')->with('success', 'Gallery card deleted.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete gallery card.'], 500);
            }
            return redirect()->back()->with('error', 'Failed to delete gallery card.');
        }
    }

    public function rowPartial(GalleryCard $galleryCard)
    {
        $card = GalleryCard::with([
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
        ])->find($galleryCard->id);

        return view('admin._gallery_card_row', ['card' => $card]);
    }
}
