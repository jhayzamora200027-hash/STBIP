<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegionItem;
use App\Models\GalleryCard;
use App\Models\StsAttachment;
class MainVersion1Controller extends Controller
{
    public function index(Request $request){
        $filterItems = RegionItem::with('region:id,name')->get();
        $attachmentMap = [];
        foreach (StsAttachment::query()->orderBy('id')->get() as $attachment) {
            $key = implode('|', [
                $attachment->region,
                $attachment->province,
                $attachment->municipality,
                $attachment->title,
                $attachment->year_of_moa,
            ]);
            $attachmentMap[$key] = $attachment;
        }
        $filterItems->each(function ($item) use ($attachmentMap) {
            $key = implode('|', [
                $item->region?->name,
                $item->province,
                $item->municipality,
                $item->title,
                $item->year_of_moa,
            ]);
            $attachment = $attachmentMap[$key] ?? null;
            $item->setAttribute('attachment_url', $attachment?->action === 'added'
                ? route('sts.attachments.show', $attachment->id)
                : null);
            $item->setAttribute('attachment_filename', $attachment?->action === 'added'
                ? ($attachment->original_filename ?: 'MOA attachment.pdf')
                : null);
        });
        $galleryCards = GalleryCard::with([
            'children' => function ($query) {
                $query->whereNull('parent_child_id')->orderBy('docno');
            },
            'children.children' => function ($query) {
                $query->orderBy('docno');
            },
        ])->where('is_active', 1)->orderBy('docno')->get();
        $selectedRegions = array_values(array_filter((array) $request->input('region', [])));
        $selectedProvinces = array_values(array_filter((array) $request->input('province', [])));
        $selectedMunicipalities = array_values(array_filter((array) $request->input('municipality', [])));
        $selectedYears = array_values(array_filter((array) $request->input('year_of_moa', [])));
        $selectedTitles = array_values(array_filter((array) $request->input('title', [])));

        $regionItems = $filterItems->filter(function ($item) use ($request) {
            $regions = array_values(array_filter((array) $request->input('region', [])));
            $provinces = array_values(array_filter((array) $request->input('province', [])));
            $municipalities = array_values(array_filter((array) $request->input('municipality', [])));
            $years = array_values(array_filter((array) $request->input('year_of_moa', [])));
            $titles = array_values(array_filter((array) $request->input('title', [])));

            return (empty($regions) || in_array($item->region?->name, $regions, true))
                && (empty($provinces) || in_array($item->province, $provinces, true))
                && (empty($municipalities) || in_array($item->municipality, $municipalities, true))
                && (empty($years) || in_array((string) $item->year_of_moa, array_map('strval', $years), true))
                && (empty($titles) || in_array($item->title, $titles, true));
        });

        return view('dashboard.mainv1', compact(
            'regionItems',
            'filterItems',
            'galleryCards',
            'selectedRegions',
            'selectedProvinces',
            'selectedMunicipalities',
            'selectedYears',
            'selectedTitles'
        ));
    }
}
