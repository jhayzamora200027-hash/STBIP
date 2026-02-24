@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card shadow" style="border-radius:18px;">
            <div class="card-body">
                @include('dashboard.maincomponents.partials.stsattachment_logs', ['logs' => $logs])
            </div>
        </div>
    </div>
@endsection