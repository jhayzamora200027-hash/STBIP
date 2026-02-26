@extends('layouts.app')

@section('content')
    <div class="p-4">
        <h2>The GrapesJS editor has been removed.</h2>
        <p>If you previously stored any HTML it appears below:</p>
        <div id="savedHtml" style="border:1px solid #ccc; padding:10px;">
            {!! $dashboardHtml ?? '' !!}
        </div>
    </div>
@endsection