<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GalleryChild;
use App\Models\ChildDocnoHistory;
use App\Models\GalleryCard;

class GalleryChildController extends Controller
{
    public function store(Request $request, GalleryCard $galleryCard)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|string|max:255',
            'docno' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:Completed,On going',
            'is_mother' => 'nullable|boolean',
            'parent_child_id' => 'nullable|exists:gallery_children,id',
        ]);

        try {
            $data['gallery_card_id'] = $galleryCard->id;
            $data['is_active'] = !empty($data['is_active']) ? 1 : 0;
            $data['status'] = $data['status'] ?? 'On going';
            $data['is_mother'] = !empty($data['is_mother']) ? 1 : 0;
            $data['parent_child_id'] = $data['parent_child_id'] ?? null;
            $data['created_by'] = Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null;

            // auto-generate child/sub-child docno
            if (empty($data['docno'])) {
                if (!empty($data['parent_child_id'])) {
                    $parentChild = GalleryChild::find($data['parent_child_id']);
                    $motherDocno = $parentChild? $parentChild->docno : ($galleryCard->docno ?? (int) (GalleryCard::max('docno') ?? 0));
                    $seq = GalleryChild::where('parent_child_id', $data['parent_child_id'])->count() + 1;
                } else {
                    $motherDocno = $galleryCard->docno ?? (int) (GalleryCard::max('docno') ?? 0);
                    // sequence among top-level children (parent_child_id IS NULL)
                    $seq = GalleryChild::where('gallery_card_id', $galleryCard->id)->whereNull('parent_child_id')->count() + 1;
                }
                $data['docno'] = $motherDocno . '.' . $seq;
            }

            $child = GalleryChild::create($data);

            // record docno history
            ChildDocnoHistory::create([
                'gallery_child_id' => $child->id,
                'gallery_card_id' => $galleryCard->id,
                'docno' => $child->docno,
                'previous_docno' => null,
                'created_by' => $data['created_by'],
            ]);

            return redirect()->route('admin.stsreportsectors')->with('success', 'Child added.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add child.');
        }
    }

    public function update(Request $request, GalleryChild $galleryChild)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|string|max:255',
            'docno' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'status' => 'nullable|in:Completed,On going',
            'is_mother' => 'nullable|boolean',
            'parent_child_id' => 'nullable|exists:gallery_children,id',
        ]);

        $data['is_active'] = !empty($data['is_active']) ? 1 : 0;
        $data['is_mother'] = !empty($data['is_mother']) ? 1 : 0;
        $data['updated_by'] = Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null;

        // if docno changed, create history record
        try {
            if (array_key_exists('docno', $data) && $data['docno'] !== $galleryChild->docno) {
                ChildDocnoHistory::create([
                    'gallery_child_id' => $galleryChild->id,
                    'gallery_card_id' => $galleryChild->gallery_card_id,
                    'docno' => $data['docno'] ?? '',
                    'previous_docno' => $galleryChild->docno,
                    'created_by' => Auth::check() ? (string) (Auth::user()->user_id ?? Auth::id()) : null,
                ]);
            }

            $galleryChild->update($data);

            return redirect()->route('admin.stsreportsectors')->with('success', 'Child updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update child.');
        }
    }

    public function destroy(GalleryChild $galleryChild)
    {
        try {
            $galleryChild->delete();

            return redirect()->route('admin.stsreportsectors')->with('success', 'Child deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete child.');
        }
    }
}
