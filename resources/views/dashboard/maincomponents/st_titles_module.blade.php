@extends('layouts.app')

@section('content')
<style>
    .st-shell { max-width:1200px; margin:0 auto; padding:20px; }
    .st-hero { background: linear-gradient(135deg,#0b2540,#175d8f); color:#fff; padding:20px; border-radius:16px; margin-bottom:18px; }
    .st-card { background:#fff; border:1px solid #e6eef7; border-radius:12px; padding:16px; }
    .st-table { width:100%; border-collapse:collapse; margin-top:12px; }
    .st-table th, .st-table td { padding:10px; border-bottom:1px solid #eef4fb; text-align:left; }
</style>

<div class="st-shell">
    <div class="st-hero">
        <h1>All Social Technology Titles</h1>
        <p style="margin:6px 0 0; opacity:0.9">Listing of all social technology titles from the database.</p>
    </div>

    <section class="st-card">
        <h3 style="margin-top:0">Titles</h3>
        <div style="overflow:auto">
            <table class="st-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Social Technology</th>
                        <th>Sector</th>
                        <th>Created By</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($titles as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->social_technology }}</td>
                            <td>{{ $t->sector ?: '-' }}</td>
                            <td>{{ $t->createdby ?: '-' }}</td>
                            <td>{{ $t->updated_at?->format('M d, Y h:i A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No titles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@endsection
