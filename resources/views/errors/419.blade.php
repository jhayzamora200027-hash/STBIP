@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="w-100 d-flex justify-content-center">
            <div class="text-center" style="max-width: 520px; background:#ffffff; border-radius:24px; padding:32px 28px 28px 28px; box-shadow:0 14px 35px rgba(15,23,42,0.25); opacity:0.98;">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="width:76px;height:76px;border-radius:999px;background:linear-gradient(135deg,#0ea5e9,#22c55e);box-shadow:0 10px 25px rgba(14,165,233,0.35);">
                    <span style="font-size:2.2rem;color:#ecfeff;">419</span>
                </div>
                <h1 class="h3 mb-3" style="font-weight:700;color:#0f172a;">Session Expired</h1>
                <p class="mb-4" style="color:#4b5563;font-size:0.98rem;">
                    Your session has expired or you logged out in another tab.<br>
                    Please sign in again to continue uploading attachments or using the dashboard.
                </p>
                <a href="{{ route('main') }}" class="btn btn-primary" style="background:linear-gradient(90deg,#10aeb5,#1de9b6);border:none;border-radius:999px;padding:9px 30px;font-weight:600;box-shadow:0 4px 12px rgba(16,174,181,0.35);">
                    Go to Login / Home
                </a>
            </div>
        </div>
    </div>
@endsection
