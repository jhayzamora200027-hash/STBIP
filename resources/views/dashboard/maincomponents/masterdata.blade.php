@extends('layouts.app')

@section('content')
<style>
	.masterdata-shell {
		width: min(100%, 1920px);
		max-width: none;
		margin: 0 auto;
		padding-inline: clamp(12px, 2vw, 28px);
		padding-bottom: 32px;
		box-sizing: border-box;
	}
	.masterdata-hero {
		background: linear-gradient(135deg, #0b2540 0%, #0f4c75 52%, #2f7aa2 100%);
		color: #fff;
		border-radius: 28px;
		padding: clamp(20px, 2.2vw, 32px);
		box-shadow: 0 20px 50px rgba(11, 37, 64, 0.18);
		margin-bottom: 24px;
	}
	.masterdata-hero h1 {
		margin: 0;
		font-size: clamp(1.55rem, 2.3vw, 2.2rem);
		font-weight: 800;
	}
	.masterdata-hero p {
		margin: 10px 0 0;
		max-width: min(100%, 1100px);
		opacity: 0.9;
		font-size: clamp(0.95rem, 1.1vw, 1.02rem);
	}
	.masterdata-alert {
		border-radius: 16px;
		padding: 14px 18px;
		margin-bottom: 18px;
		border: 1px solid;
	}
	.masterdata-alert-success {
		background: #ecfdf3;
		color: #166534;
		border-color: #bbf7d0;
	}
	.masterdata-alert-error {
		background: #fef2f2;
		color: #991b1b;
		border-color: #fecaca;
	}
	.masterdata-ajax-feedback {
		margin-bottom: 18px;
	}
	.masterdata-modal {
		position: fixed;
		inset: 0;
		z-index: 1200;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 24px;
		background: rgba(11, 37, 64, 0.42);
		opacity: 0;
		visibility: hidden;
		pointer-events: none;
		transition: opacity 0.22s ease, visibility 0.22s ease;
	}
	.masterdata-modal.is-open {
		opacity: 1;
		visibility: visible;
		pointer-events: auto;
	}
	.masterdata-modal-card {
		width: min(100%, 460px);
		background: #fff;
		border-radius: 24px;
		box-shadow: 0 28px 80px rgba(11, 37, 64, 0.24);
		overflow: hidden;
		transform: translateY(12px) scale(0.98);
		transition: transform 0.22s ease;
	}
	.masterdata-modal.is-open .masterdata-modal-card {
		transform: translateY(0) scale(1);
	}
	.masterdata-modal-header {
		padding: 22px 24px 16px;
		background: linear-gradient(135deg, #ecfdf3 0%, #f6fffa 100%);
		border-bottom: 1px solid #d8f3e4;
	}
	.masterdata-modal-title {
		margin: 0;
		font-size: 1.15rem;
		font-weight: 800;
		color: #166534;
	}
	.masterdata-modal-body {
		padding: 20px 24px;
		color: #244865;
		font-size: 0.97rem;
		line-height: 1.6;
	}
	.masterdata-modal-actions {
		display: flex;
		justify-content: flex-end;
		padding: 0 24px 24px;
	}
	.masterdata-tabs {
		display: flex;
		gap: 12px;
		margin-bottom: 22px;
		flex-wrap: wrap;
	}
	.masterdata-tab-btn {
		border: 1px solid #c9d8e6;
		background: #fff;
		color: #194566;
		padding: 12px 18px;
		border-radius: 999px;
		font-weight: 700;
		cursor: pointer;
		transition: all 0.18s ease;
	}
	.masterdata-tab-btn.active {
		background: linear-gradient(135deg, #0b2540, #175d8f);
		color: #fff;
		border-color: transparent;
		box-shadow: 0 10px 26px rgba(23, 93, 143, 0.22);
	}
	.masterdata-panel {
		display: none;
	}
	.masterdata-panel.active {
		display: block;
	}
	.masterdata-stats {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 18px;
		margin-bottom: 22px;
	}
	.masterdata-stat-card,
	.masterdata-card {
		background: #fff;
		border: 1px solid #dbe4f0;
		border-radius: 22px;
		box-shadow: 0 14px 34px rgba(11, 37, 64, 0.08);
		overflow: hidden;
	}
	.masterdata-stat-card {
		padding: 20px 22px;
	}
	.masterdata-stat-label {
		font-size: 0.82rem;
		font-weight: 800;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #5e7388;
	}
	.masterdata-stat-value {
		margin-top: 10px;
		font-size: 2rem;
		font-weight: 800;
		color: #0b2540;
	}
	.masterdata-stat-note {
		margin-top: 6px;
		font-size: 0.92rem;
		color: #5e7388;
	}
	.masterdata-grid {
		display: grid;
		grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.9fr);
		gap: 22px;
		align-items: start;
		margin-bottom: 22px;
	}
	.masterdata-grid.masterdata-grid-single {
		grid-template-columns: minmax(0, 1fr);
	}
	.masterdata-card-header {
		padding: 18px 22px;
		background: linear-gradient(135deg, #eff6fb, #f8fbfe);
		border-bottom: 1px solid #e4edf6;
	}
	.masterdata-card-header h2,
	.masterdata-card-header h3 {
		margin: 0;
		font-size: 1.08rem;
		font-weight: 800;
		color: #0b2540;
	}
	.masterdata-card-header p {
		margin: 8px 0 0;
		font-size: 0.92rem;
		color: #5e7388;
	}
	.masterdata-card-body {
		padding: 22px;
	}
	.masterdata-form-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
		gap: 16px;
	}
	.masterdata-field {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}
	.masterdata-field.full {
		grid-column: 1 / -1;
	}
	.masterdata-field.is-hidden {
		display: none;
	}
	.masterdata-field label {
		font-size: 0.9rem;
		font-weight: 700;
		color: #244865;
	}
	.masterdata-field-error {
		display: none;
		color: #7f1d1d;
		background: #fff2f2;
		border-left: 4px solid #fca5a5;
		padding: 10px 12px;
		margin-top: 8px;
		border-radius: 8px;
		font-weight: 700;
		font-size: 0.95rem;
	}
	.masterdata-field-error:not(:empty) {
		display: block;
	}
	input.is-invalid,
	select.is-invalid,
	textarea.is-invalid {
		border-color: #ef4444 !important;
		box-shadow: 0 0 0 4px rgba(239,68,68,0.06);
	}
	.masterdata-field input,
	.masterdata-field select {
		width: 100%;
		border: 1px solid #bfd1e4;
		border-radius: 12px;
		padding: 11px 13px;
		font-size: 0.95rem;
		background: #f9fbfd;
	}
	.masterdata-field input:focus,
	.masterdata-field select:focus {
		outline: none;
		border-color: #175d8f;
		box-shadow: 0 0 0 3px rgba(23, 93, 143, 0.12);
	}
	.masterdata-checks {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 10px 14px;
	}
	.masterdata-check {
		position: relative;
		display: grid;
		grid-template-columns: 30px minmax(0, 1fr);
		align-items: start;
		gap: 12px;
		border: 1px solid #dbe4f0;
		background: linear-gradient(180deg, #f8fbff 0%, #f2f8fd 100%);
		border-radius: 14px;
		padding: 12px 14px;
		font-size: 0.92rem;
		color: #1f3f5b;
		cursor: pointer;
		transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, transform 0.18s ease;
	}
	.masterdata-check:hover {
		border-color: #a9c4dd;
		box-shadow: 0 10px 22px rgba(23, 93, 143, 0.08);
		transform: translateY(-1px);
	}
	.masterdata-check-control {
		position: relative;
		width: 30px;
		height: 30px;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.masterdata-check input[type="checkbox"] {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		margin: 0;
		opacity: 0;
		cursor: pointer;
		z-index: 2;
	}
	.masterdata-check-indicator {
		width: 24px;
		height: 24px;
		border-radius: 8px;
		border: 1.6px solid #9eb8d1;
		background: #fff;
		display: inline-grid;
		place-items: center;
		box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
		transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
	}
	.masterdata-check-indicator::after {
		content: '';
		width: 10px;
		height: 6px;
		border-left: 2px solid #fff;
		border-bottom: 2px solid #fff;
		transform: rotate(-45deg) scale(0.4);
		opacity: 0;
		transition: transform 0.18s ease, opacity 0.18s ease;
		margin-top: -1px;
	}
	.masterdata-check input[type="checkbox"]:checked + .masterdata-check-indicator {
		background: linear-gradient(135deg, #0b2540, #175d8f);
		border-color: #175d8f;
		box-shadow: 0 0 0 4px rgba(23, 93, 143, 0.14);
	}
	.masterdata-check input[type="checkbox"]:checked + .masterdata-check-indicator::after {
		opacity: 1;
		transform: rotate(-45deg) scale(1);
	}
	.masterdata-check input[type="checkbox"]:focus-visible {
		outline: none;
	}
	.masterdata-check input[type="checkbox"]:focus-visible + .masterdata-check-indicator {
		box-shadow: 0 0 0 4px rgba(23, 93, 143, 0.16);
	}
	.masterdata-check-text {
		display: flex;
		flex-direction: column;
		gap: 2px;
		min-width: 0;
	}
	.masterdata-check-title {
		font-size: 0.92rem;
		font-weight: 700;
		color: #143752;
	}
	.masterdata-check-note {
		font-size: 0.78rem;
		line-height: 1.35;
		color: #6a7f92;
	}
	.masterdata-btn {
		border: none;
		border-radius: 12px;
		padding: 11px 16px;
		font-weight: 700;
		cursor: pointer;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}
	.masterdata-btn-primary {
		background: linear-gradient(135deg, #0b2540, #175d8f);
		color: #fff;
	}
	.masterdata-btn-secondary {
		background: #eff6fb;
		color: #194566;
		border: 1px solid #d8e5f1;
	}
	.masterdata-btn-danger {
		background: #fef2f2;
		color: #b91c1c;
		border: 1px solid #fecaca;
	}
	.masterdata-region-overview {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
		gap: 14px;
	}
	.masterdata-region-box {
		padding: 16px;
		border-radius: 18px;
		background: linear-gradient(180deg, #f8fbfd 0%, #eef5fb 100%);
		border: 1px solid #d9e6f2;
	}
	.masterdata-region-name {
		font-size: 0.92rem;
		font-weight: 800;
		color: #0b2540;
	}
	.masterdata-region-count {
		margin-top: 8px;
		font-size: 2rem;
		font-weight: 800;
		color: #175d8f;
		text-align: center;
	}
	.masterdata-region-foot {
		margin-top: 4px;
		font-size: 0.86rem;
		color: #5e7388;
	}
	.masterdata-fixed-note {
		margin-top: 16px;
		padding: 14px 16px;
		border-radius: 16px;
		background: #f7fafc;
		border: 1px dashed #cbd8e6;
		color: #4c6276;
		font-size: 0.92rem;
	}
	.masterdata-charts {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
		gap: 22px;
		margin-bottom: 22px;
	}
	.masterdata-chart-card canvas {
		width: 100% !important;
		height: 320px !important;
	}
	.masterdata-table-wrap {
		overflow: auto;
	}
	.masterdata-table {
		width: 100%;
		border-collapse: collapse;
		min-width: 720px;
	}
	.masterdata-table th,
	.masterdata-table td {
		padding: 12px 14px;
		border-bottom: 1px solid #edf2f7;
		text-align: left;
		vertical-align: top;
		font-size: 0.92rem;
	}
	.masterdata-table th {
		background: #f7fafc;
		color: #244865;
		font-weight: 800;
	}
	.masterdata-pill {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 4px 10px;
		border-radius: 999px;
		background: #eef6ff;
		color: #175d8f;
		font-size: 0.82rem;
		font-weight: 700;
	}
	.masterdata-status-ongoing {
		background: #ecfdf3;
		color: #047857;
	}
	.masterdata-status-inactive {
		background: #fef2f2;
		color: #b91c1c;
	}
	.masterdata-toolbar {
		display: flex;
		justify-content: space-between;
		align-items: end;
		gap: 18px;
		margin-bottom: 22px;
		flex-wrap: wrap;
	}
	.masterdata-toolbar .masterdata-field {
		min-width: min(100%, 260px);
		flex: 1 1 260px;
	}
	.masterdata-filter-bar {
		display: flex;
		gap: 14px;
		align-items: end;
		flex-wrap: wrap;
		margin-bottom: 18px;
	}
	.masterdata-filter-bar .masterdata-field {
		min-width: min(100%, 220px);
		flex: 1 1 240px;
	}
	.masterdata-list-meta {
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 12px;
		flex-wrap: wrap;
		margin-bottom: 14px;
		color: #607588;
		font-size: 0.9rem;
	}
	.masterdata-item-stack {
		display: flex;
		flex-direction: column;
		gap: 16px;
	}
	.masterdata-item-list {
		border: 1px solid #dbe4f0;
		border-radius: 20px;
		overflow: hidden;
		background: #fff;
	}
	.masterdata-item-list-head,
	.masterdata-item-row {
		display: grid;
		grid-template-columns: minmax(220px, 1.5fr) minmax(120px, 0.85fr) minmax(140px, 0.9fr) minmax(120px, 0.75fr) minmax(140px, 0.85fr) minmax(160px, 0.9fr) 56px;
		gap: 12px;
		align-items: center;
		padding: 14px 18px;
	}
	.masterdata-item-list-head {
		background: #f7fafc;
		border-bottom: 1px solid #dbe4f0;
		font-size: 0.82rem;
		font-weight: 800;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		color: #607588;
	}
	.masterdata-item-entry {
		border-bottom: 1px solid #edf2f7;
	}
	.masterdata-item-entry:last-child {
		border-bottom: none;
	}
	.masterdata-item-row {
		background: #fff;
		cursor: pointer;
		transition: background 0.18s ease;
	}
	.masterdata-item-row:hover {
		background: #f8fbfe;
	}
	.masterdata-item-row.is-open {
		background: #f3f8fc;
	}
	.masterdata-item-row-title {
		font-size: 0.97rem;
		font-weight: 700;
		color: #0b2540;
	}
	.masterdata-item-row-cell {
		font-size: 0.9rem;
		color: #244865;
	}
	.masterdata-item-row-cell-muted {
		color: #607588;
	}
	.masterdata-row-chevron {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 30px;
		height: 30px;
		border-radius: 999px;
		background: #e8f1f8;
		color: #175d8f;
		font-size: 1rem;
		font-weight: 800;
		transition: transform 0.18s ease, background 0.18s ease;
	}
	.masterdata-item-row.is-open .masterdata-row-chevron {
		transform: rotate(180deg);
		background: #dbeaf5;
	}
	.masterdata-item-detail {
		max-height: 0;
		overflow: hidden;
		padding: 0 18px;
		background: linear-gradient(180deg, #fbfdff 0%, #f4f8fb 100%);
		border-top: 1px solid transparent;
		opacity: 0;
		transform: translateY(-8px);
		pointer-events: none;
		transition: max-height 0.32s ease, padding 0.24s ease, opacity 0.24s ease, transform 0.24s ease, border-color 0.24s ease;
	}
	.masterdata-item-detail.is-open {
		max-height: 1400px;
		padding: 18px;
		border-top-color: #e4edf6;
		opacity: 1;
		transform: translateY(0);
		pointer-events: auto;
	}
	.masterdata-item-card {
		border: 1px solid #dbe4f0;
		border-radius: 20px;
		background: #fff;
		box-shadow: 0 12px 28px rgba(11, 37, 64, 0.06);
		padding: 18px;
	}
	.masterdata-item-head {
		display: flex;
		justify-content: space-between;
		gap: 14px;
		margin-bottom: 14px;
		align-items: start;
		flex-wrap: wrap;
	}
	.masterdata-item-title {
		font-size: 1rem;
		font-weight: 800;
		color: #0b2540;
	}
	.masterdata-item-meta {
		display: flex;
		gap: 10px 14px;
		flex-wrap: wrap;
		font-size: 0.84rem;
		color: #607588;
	}
	.masterdata-item-actions {
		display: flex;
		gap: 10px;
		justify-content: flex-end;
		margin-top: 14px;
		flex-wrap: wrap;
	}
	.masterdata-attachment-panel {
		display: flex;
		justify-content: space-between;
		gap: 16px;
		align-items: center;
		padding: 16px 18px;
		margin-bottom: 18px;
		border: 1px solid #dbe4f0;
		border-radius: 18px;
		background: linear-gradient(180deg, #f8fbff 0%, #f2f7fc 100%);
	}
	.masterdata-attachment-actions {
		display: flex;
		gap: 10px;
		align-items: center;
		justify-content: flex-end;
		flex-wrap: wrap;
	}
	.masterdata-attachment-actions form {
		margin: 0;
	}
	.masterdata-empty {
		padding: 26px;
		text-align: center;
		color: #5b6b7d;
		background: #f8fbfd;
		border: 1px dashed #cdd9e5;
		border-radius: 16px;
	}
	.masterdata-pagination {
		display: flex;
		justify-content: center;
		margin-top: 18px;
	}
	.masterdata-pagination nav {
		width: 100%;
	}
	.masterdata-pagination svg {
		width: 18px;
		height: 18px;
	}
	@media (max-width: 1200px) {
		.masterdata-grid {
			grid-template-columns: 1fr;
		}
	}
	@media (max-width: 820px) {
		.masterdata-shell {
			padding-inline: 12px;
		}
		.masterdata-tabs {
			gap: 10px;
		}
		.masterdata-tab-btn {
			width: 100%;
			justify-content: center;
		}
		.masterdata-item-list-head {
			display: none;
		}
		.masterdata-item-row {
			grid-template-columns: 1fr;
			gap: 10px;
			padding: 16px;
		}
		.masterdata-hero {
			border-radius: 22px;
		}
		.masterdata-card-body,
		.masterdata-card-header,
		.masterdata-stat-card {
			padding-left: 16px;
			padding-right: 16px;
		}
		.masterdata-table {
			min-width: 640px;
		}
	}
	@media (max-width: 560px) {
		.masterdata-modal {
			padding: 14px;
		}
		.masterdata-btn,
		.masterdata-item-actions .masterdata-btn {
			width: 100%;
		}
		.masterdata-item-actions {
			justify-content: stretch;
		}
		.masterdata-attachment-panel {
			align-items: stretch;
			flex-direction: column;
		}
		.masterdata-attachment-actions,
		.masterdata-attachment-actions form,
		.masterdata-attachment-actions .masterdata-btn {
			width: 100%;
		}
	}
</style>

<div class="masterdata-shell">
	<div class="masterdata-hero">
		<h1>Master Data Administration</h1>
	</div>

	@if(session('status'))
		<div class="masterdata-alert masterdata-alert-success">{{ session('status') }}</div>
	@endif

	@if(session('error'))
		<div class="masterdata-alert masterdata-alert-error">{{ session('error') }}</div>
	@endif

	@php($errorFormOrigin = old('form_origin'))
	@if($errors->any() && !in_array($errorFormOrigin, ['region_item_create', 'region_item_update'], true))
		<div class="masterdata-alert masterdata-alert-error">
			<strong>Unable to save master data.</strong>
			<ul class="mb-0 mt-2">
				@foreach($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="masterdata-tabs">
		<button type="button" class="masterdata-tab-btn {{ $activeTab === 'overview' ? 'active' : '' }}" data-masterdata-tab="overview">Overview Dashboard</button>
		<button type="button" class="masterdata-tab-btn {{ $activeTab === 'updates' ? 'active' : '' }}" data-masterdata-tab="updates">Region Item Management</button>
	</div>

	<div id="masterdata-ajax-feedback" class="masterdata-ajax-feedback"></div>

	<div id="masterdata-success-modal" class="masterdata-modal" aria-hidden="true">
		<div class="masterdata-modal-card" role="dialog" aria-modal="true" aria-labelledby="masterdata-success-modal-title">
			<div class="masterdata-modal-header">
				<h2 id="masterdata-success-modal-title" class="masterdata-modal-title">Successfully</h2>
			</div>
			<div class="masterdata-modal-body" id="masterdata-success-modal-message"></div>
			<div class="masterdata-modal-actions">
				<button type="button" class="masterdata-btn masterdata-btn-primary" id="masterdata-success-modal-close">Close</button>
			</div>
		</div>
	</div>

	<div id="masterdata-panel-overview" class="masterdata-panel {{ $activeTab === 'overview' ? 'active' : '' }}">
		<div class="masterdata-stats">
			<div class="masterdata-stat-card">
				<div class="masterdata-stat-label">Regional Offices</div>
				<div class="masterdata-stat-value" style="text-align: center;">{{ $overview['total_regions'] }}</div>
			</div>
			<div class="masterdata-stat-card">
				<div class="masterdata-stat-label">Total number of institutionalizing LGUs and stakeholders</div>
				<div class="masterdata-stat-value" style="text-align: center;">{{ $overview['total_items'] }}</div>
			</div>
			<div class="masterdata-stat-card">
				<div class="masterdata-stat-label">With MOA</div>
				<div class="masterdata-stat-value" style="text-align: center;">{{ $overview['with_moa'] }}</div>
			</div>
			<div class="masterdata-stat-card">
				<div class="masterdata-stat-label">With Resolution</div>
				<div class="masterdata-stat-value" style="text-align: center;">{{ $overview['with_resolution'] }}</div>
			</div>
		</div>

		@php($isSysadmin = auth()->check() && auth()->user()->usergroup === 'sysadmin')
		<div class="masterdata-grid {{ $isSysadmin ? '' : 'masterdata-grid-single' }}">
			<section class="masterdata-card">
				<div class="masterdata-card-header">
					<h2>Regional Office Overview</h2>
				</div>
				<div class="masterdata-card-body">
					<div class="masterdata-region-overview">
						@foreach($regions as $region)
							<div class="masterdata-region-box">
								<div class="masterdata-region-name" style="text-align: center;">{{ $region->name }}</div>
								<div class="masterdata-region-count" style="text-align: center;">{{ $region->items_count }}</div>
							</div>
						@endforeach
					</div>
				</div>
			</section>

			@if($isSysadmin)
				<section class="masterdata-card">
					<div class="masterdata-card-header">
						<h2>Upload and Import</h2>
						<p>The upload source remains available here and imports into the fixed office structure. Matching STs are updated, and new STs are added in bulk.</p>
					</div>
					<div class="masterdata-card-body">
						<form method="POST" action="{{ route('masterdata.import-google-sheet') }}">
							@csrf
							<div class="masterdata-form-grid">
								<div class="masterdata-field full">
									<label for="google-sheet-url">Google Sheet URL</label>
									<input id="google-sheet-url" type="url" name="google_sheet_url" value="{{ old('google_sheet_url', $currentGoogleSheetUrl ?? '') }}" placeholder="https://docs.google.com/spreadsheets/d/...">
								</div>
								<div class="masterdata-field full">
									<label>Current Stored Google Sheet File</label>
									<input type="text" value="{{ $currentGoogleSheetFile ?: 'No stored Google Sheet detected' }}" readonly>
									@if(!empty($currentGoogleSheetFile))
										<input type="hidden" name="stored_excel" value="{{ $currentGoogleSheetFile }}">
									@endif
								</div>
							</div>
							<div class="masterdata-item-actions" style="justify-content:flex-start; margin-top: 18px;">
								<button type="submit" class="masterdata-btn masterdata-btn-primary">Add or Update STs from Sheet</button>
								<a class="masterdata-btn masterdata-btn-secondary" href="{{ route('upload') }}">Open Uploading Document</a>
								<a class="masterdata-btn masterdata-btn-secondary" href="{{ route('masterdata.region-items.export') }}">Export Region Items (Excel)</a>
							</div>
						</form>

						@if($isSysadmin)
							<form method="POST" action="{{ route('masterdata.region-items.import-excel') }}" enctype="multipart/form-data" style="margin-top:16px;">
								@csrf
								<div class="masterdata-form-grid">
									<div class="masterdata-field full">
										<label for="region-items-file">Excel File</label>
										<input id="region-items-file" type="file" name="region_items_excel" accept=".xlsx,.xls" required>
										<div class="masterdata-field-note">Upload an Excel file with the header columns matching the exported template.</div>
									</div>
								</div>
								<div class="masterdata-item-actions" style="justify-content:flex-start; margin-top: 12px; gap:8px;">
									<button type="submit" class="masterdata-btn masterdata-btn-primary">Upload Excel and Import (strict)</button>
									<button type="submit" formaction="{{ route('masterdata.region-items.import-excel-force') }}" formmethod="post" class="masterdata-btn masterdata-btn-danger">Force Import (create/update)</button>
									<a class="masterdata-btn masterdata-btn-secondary" href="{{ route('masterdata.region-items.export') }}">Download Template / Export</a>
								</div>
							</form>

							@if(session('masterdata_import_warnings'))
								<div class="masterdata-alert masterdata-alert-warning" style="margin-top:12px;">
									<strong>Import warnings:</strong>
									<ul style="margin:6px 0 0 18px;">
										@foreach(session('masterdata_import_warnings') as $warning)
											<li>{{ $warning }}</li>
										@endforeach
									</ul>
								</div>
							@endif

							@if(session('masterdata_import_debug'))
								<div class="masterdata-alert" style="margin-top:12px; background:#f8f9fc; border:1px solid #e3e6f0;">
									<strong>Import debug (first rows):</strong>
									<div style="font-size:0.9rem; margin-top:6px;">
										Header row detected: {{ session('masterdata_import_debug.header_index') }}
										<ul style="margin:6px 0 0 18px;">
											@foreach(session('masterdata_import_debug.rows') as $r)
												<li>Row {{ $r['sheet_row'] }} — Region cell: "{{ $r['region_raw'] }}"; Title cell: "{{ $r['title_raw'] }}"</li>
											@endforeach
										</ul>
									</div>
								</div>
							@endif
						@endif
					</div>
				</section>
			@endif
		</div>

		<div class="masterdata-charts">
			<section class="masterdata-card masterdata-chart-card">
				<div class="masterdata-card-header">
					<h3>Items per Regional Office</h3>
					<p>Bar view of the current region-item distribution.</p>
				</div>
				<div class="masterdata-card-body">
					<canvas id="masterdataRegionChart"></canvas>
				</div>
			</section>

			<section class="masterdata-card masterdata-chart-card">
				<div class="masterdata-card-header">
					<h3>Status Distribution</h3>
					<p>Ongoing, inactive, and unspecified items in the current DB source.</p>
				</div>
				<div class="masterdata-card-body">
					<canvas id="masterdataStatusChart"></canvas>
				</div>
			</section>

			<section class="masterdata-card masterdata-chart-card">
				<div class="masterdata-card-header">
					<h3>Replicated vs Adopted</h3>
					<p>Comparison of master data items tagged as adopted or replicated.</p>
				</div>
				<div class="masterdata-card-body">
					<canvas id="masterdataAdoptionChart"></canvas>
				</div>
			</section>

			<section class="masterdata-card masterdata-chart-card">
				<div class="masterdata-card-header">
					<h3>Year of MOA</h3>
					<p>How many items are recorded per MOA year.</p>
				</div>
				<div class="masterdata-card-body">
					<canvas id="masterdataYearChart"></canvas>
				</div>
			</section>

			<section class="masterdata-card masterdata-chart-card">
				<div class="masterdata-card-header">
					<h3>Updates by User</h3>
					<p>Recent item ownership based on the stored updatedby name.</p>
				</div>
				<div class="masterdata-card-body">
					<canvas id="masterdataUpdatedByChart"></canvas>
				</div>
			</section>

			<section class="masterdata-card masterdata-chart-card">
				<div class="masterdata-card-header">
					<h3>Number of SB Resolution</h3>
					<p>Count of items with recorded resolutions per regional office.</p>
				</div>
				<div class="masterdata-card-body">
					<canvas id="masterdataResolutionRegionChart"></canvas>
				</div>
			</section>
		</div>

		<section class="masterdata-card">
			<div class="masterdata-card-header">
				<h2>Latest Item Updates</h2>
				<p>Most recently changed rows in the master data tables.</p>
			</div>
			<div class="masterdata-card-body">
				<div class="masterdata-table-wrap">
					<table class="masterdata-table">
						<thead>
							<tr>
								<th>Region</th>
								<th>Title</th>
								<th>Updated By</th>
								<th>Updated At</th>
								<th>Created By</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@forelse($overview['recent_updates'] as $item)
								<tr>
									<td>{{ $item->region?->name ?: '-' }}</td>
									<td>{{ $item->title }}</td>
									<td>{{ $item->updatedby ?: '-' }}</td>
									<td>{{ $item->updated_at?->format('M d, Y h:i A') ?: '-' }}</td>
									<td>{{ $item->createdby ?: '-' }}</td>
									<td>
										@if($item->status === 'ongoing')
											<span class="masterdata-pill masterdata-status-ongoing">Ongoing</span>
										@elseif(in_array($item->status, ['inactive','dissolved'], true))
											<span class="masterdata-pill masterdata-status-inactive">Inactive</span>
										@else
											<span class="masterdata-pill">Unspecified</span>
										@endif
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="6">No item updates yet.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>

	<div id="masterdata-panel-updates" class="masterdata-panel {{ $activeTab === 'updates' ? 'active' : '' }}">
		<div id="masterdata-updates-panel-content">
			@include('dashboard.maincomponents.partials.masterdata_updates_panel')
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
	(function () {
		const tabButtons = document.querySelectorAll('[data-masterdata-tab]');
		const panels = {
			overview: document.getElementById('masterdata-panel-overview'),
			updates: document.getElementById('masterdata-panel-updates')
		};
		const initialTab = @json($activeTab);

		function setTab(tabName, updateUrl) {
			Object.keys(panels).forEach(function (key) {
				const isActive = key === tabName;
				panels[key].classList.toggle('active', isActive);
			});
			tabButtons.forEach(function (button) {
				button.classList.toggle('active', button.dataset.masterdataTab === tabName);
			});

			if (updateUrl) {
				const url = new URL(window.location.href);
				url.searchParams.set('tab', tabName);
				history.replaceState({}, '', url.toString());
			}
		}

		tabButtons.forEach(function (button) {
			button.addEventListener('click', function () {
				setTab(button.dataset.masterdataTab, true);
			});
		});

		setTab(initialTab, false);

		const regionData = @json($overview['region_counts']);
		const statusData = @json($overview['status_counts']);
		const yearData = @json($overview['year_counts']);
		const adoptionData = @json($overview['adoption_counts']);
		const resolutionRegionData = @json($overview['resolution_counts_by_region']);
		const updatedByData = @json($overview['updated_by_counts']);

		if (typeof Chart === 'undefined') {
			return;
		}

		const regionCanvas = document.getElementById('masterdataRegionChart');
		if (regionCanvas) {
			new Chart(regionCanvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels: regionData.map(function (row) { return row.label; }),
					datasets: [{
						label: 'Items',
						data: regionData.map(function (row) { return row.count; }),
						backgroundColor: '#175d8f',
						borderRadius: 10,
					}]
				},
				options: {
					plugins: { legend: { display: false } },
					responsive: true,
					maintainAspectRatio: false,
					scales: {
						x: { ticks: { maxRotation: 60, minRotation: 35 } },
						y: { beginAtZero: true, ticks: { precision: 0 } }
					}
				}
			});
		}

		const statusCanvas = document.getElementById('masterdataStatusChart');
		if (statusCanvas) {
			new Chart(statusCanvas.getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: Object.keys(statusData),
					datasets: [{
						data: Object.values(statusData),
						backgroundColor: ['#10b981', '#ef4444', '#94a3b8'],
						borderWidth: 0,
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: { legend: { position: 'bottom' } }
				}
			});
		}

		const yearCanvas = document.getElementById('masterdataYearChart');
		if (yearCanvas) {
			new Chart(yearCanvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels: yearData.labels,
					datasets: [{
						label: 'Items',
						data: yearData.values,
						backgroundColor: '#38bdf8',
						borderRadius: 10,
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: { legend: { display: false } },
					scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
				}
			});
		}

		const adoptionCanvas = document.getElementById('masterdataAdoptionChart');
		if (adoptionCanvas) {
			new Chart(adoptionCanvas.getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: Object.keys(adoptionData),
					datasets: [{
						data: Object.values(adoptionData),
						backgroundColor: ['#175d8f', '#38bdf8'],
						borderWidth: 0,
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: { legend: { position: 'bottom' } }
				}
			});
		}

		const updatedByCanvas = document.getElementById('masterdataUpdatedByChart');
		if (updatedByCanvas) {
			new Chart(updatedByCanvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels: updatedByData.labels,
					datasets: [{
						label: 'Updated Items',
						data: updatedByData.values,
						backgroundColor: '#f59e0b',
						borderRadius: 10,
					}]
				},
				options: {
					indexAxis: 'y',
					responsive: true,
					maintainAspectRatio: false,
					plugins: { legend: { display: false } },
					scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
				}
			});
		}

		const resolutionRegionCanvas = document.getElementById('masterdataResolutionRegionChart');
		if (resolutionRegionCanvas) {
			new Chart(resolutionRegionCanvas.getContext('2d'), {
				type: 'bar',
				data: {
					labels: resolutionRegionData.labels,
					datasets: [{
						label: 'SB Resolution Items',
						data: resolutionRegionData.values,
						backgroundColor: '#0f766e',
						borderRadius: 10,
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: { legend: { display: false } },
					scales: {
						x: { ticks: { maxRotation: 60, minRotation: 35 } },
						y: { beginAtZero: true, ticks: { precision: 0 } }
					}
				}
			});
		}

		const ajaxFeedback = document.getElementById('masterdata-ajax-feedback');
		const updatesPanelContent = document.getElementById('masterdata-updates-panel-content');
		const updatesPanelRoute = @json(route('masterdata.updates-panel'));
		const successModal = document.getElementById('masterdata-success-modal');
		const successModalMessage = document.getElementById('masterdata-success-modal-message');
		const successModalClose = document.getElementById('masterdata-success-modal-close');

		function showAjaxFeedback(type, message) {
			if (!ajaxFeedback) return;
			if (!message) {
				ajaxFeedback.innerHTML = '';
				return;
			}
			const alertClass = type === 'error' ? 'masterdata-alert-error' : 'masterdata-alert-success';
			ajaxFeedback.innerHTML = '<div class="masterdata-alert ' + alertClass + '">' + message + '</div>';
		}

		function showSuccessModal(message) {
			if (!successModal || !successModalMessage || !message) {
				return;
			}
			successModalMessage.textContent = message;
			successModal.classList.add('is-open');
			successModal.setAttribute('aria-hidden', 'false');
		}

		function closeSuccessModal() {
			if (!successModal) {
				return;
			}
			successModal.classList.remove('is-open');
			successModal.setAttribute('aria-hidden', 'true');
		}

		function initializeConditionalFields(root) {
			const conditionalToggles = (root || document).querySelectorAll('input[type="checkbox"][data-toggle-target]');
			conditionalToggles.forEach(function (checkbox) {
				const targetId = checkbox.getAttribute('data-toggle-target');
				const target = document.getElementById(targetId);
				if (!target) {
					return;
				}

				const input = target.querySelector('input');
				function syncConditionalField() {
					const isVisible = checkbox.checked;
					target.classList.toggle('is-hidden', !isVisible);
					if (!isVisible && input) {
						input.value = '';
					}
				}

				checkbox.removeEventListener('change', syncConditionalField);
				checkbox.addEventListener('change', syncConditionalField);
				syncConditionalField();
			});
		}

		function initializeRowToggles(root) {
			const toggles = (root || document).querySelectorAll('[data-masterdata-item-toggle]');

			function closeAllDetails(exceptId) {
				toggles.forEach(function (toggle) {
				const detailId = toggle.getAttribute('data-masterdata-item-toggle');
				const detail = document.getElementById(detailId);
				const isTarget = detailId === exceptId;
				toggle.classList.toggle('is-open', isTarget && detail && detail.classList.contains('is-open'));
				if (!isTarget && detail) {
					detail.classList.remove('is-open');
					toggle.classList.remove('is-open');
					toggle.setAttribute('aria-expanded', 'false');
				}
				});
			}

			toggles.forEach(function (toggle) {
				const detailId = toggle.getAttribute('data-masterdata-item-toggle');
				const detail = document.getElementById(detailId);
				if (!detail) {
					return;
				}

				function handleToggle() {
					const willOpen = !detail.classList.contains('is-open');
					closeAllDetails(detailId);
					detail.classList.toggle('is-open', willOpen);
					toggle.classList.toggle('is-open', willOpen);
					toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
				}

				toggle.addEventListener('click', function (event) {
					if (event.target.closest('button, a, input, select, label, textarea, form')) {
						return;
					}
					handleToggle();
				});

				toggle.addEventListener('keydown', function (event) {
					if (event.key === 'Enter' || event.key === ' ') {
						event.preventDefault();
						handleToggle();
					}
				});
			});
		}

		function initializeUpdatesPanel(root) {
			initializeConditionalFields(root);
			initializeRowToggles(root);
			initializeUpdatesPanelAjax(root);
		}

		function openMasterdataAttachmentUploadModal(button) {
			if (!button || typeof bootstrap === 'undefined') {
				return;
			}

			const modalEl = document.getElementById('masterdataAttachmentUploadModal');
			if (!modalEl) {
				return;
			}

			const region = button.getAttribute('data-region') || '';
			const province = button.getAttribute('data-province') || '';
			const municipality = button.getAttribute('data-municipality') || '';
			const title = button.getAttribute('data-title') || '';
			const year = button.getAttribute('data-year') || '';

			const summary = document.getElementById('masterdataAttachmentUploadSummary');
			const regionInput = document.getElementById('masterdataAttachmentRegion');
			const provinceInput = document.getElementById('masterdataAttachmentProvince');
			const municipalityInput = document.getElementById('masterdataAttachmentMunicipality');
			const titleInput = document.getElementById('masterdataAttachmentTitle');
			const yearInput = document.getElementById('masterdataAttachmentYear');
			const fileInput = document.getElementById('masterdataAttachmentFile');

			if (summary) {
				const parts = [title, province, municipality, year ? 'MOA ' + year : ''].filter(Boolean);
				summary.textContent = parts.join(' / ');
			}
			if (regionInput) regionInput.value = region;
			if (provinceInput) provinceInput.value = province;
			if (municipalityInput) municipalityInput.value = municipality;
			if (titleInput) titleInput.value = title;
			if (yearInput) yearInput.value = year;
			if (fileInput) fileInput.value = '';

			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		function openMasterdataAttachmentViewModal(button) {
			if (!button || typeof bootstrap === 'undefined') {
				return;
			}

			const url = button.getAttribute('data-url') || '';
			if (!url) {
				return;
			}

			const modalEl = document.getElementById('masterdataAttachmentViewModal');
			const frame = document.getElementById('masterdataAttachmentViewFrame');
			const titleEl = document.getElementById('masterdataAttachmentViewModalLabel');
			const uploaderEl = document.getElementById('masterdataAttachmentViewUploadedBy');

			if (!modalEl || !frame) {
				return;
			}

			frame.src = url;
			if (titleEl) {
				titleEl.textContent = button.getAttribute('data-title') || 'View Attachment';
			}
			if (uploaderEl) {
				const uploadedBy = button.getAttribute('data-uploader') || '';
				if (uploadedBy) {
					uploaderEl.textContent = 'Uploaded by: ' + uploadedBy;
					uploaderEl.style.display = 'inline-block';
				} else {
					uploaderEl.textContent = '';
					uploaderEl.style.display = 'none';
				}
			}

			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}

		function setFormBusy(form, isBusy) {
			if (!(form instanceof HTMLFormElement)) {
				return;
			}

			form.classList.toggle('is-busy', isBusy);
			form.querySelectorAll('button, input, select, textarea').forEach(function (element) {
				if (element.type === 'hidden') {
					return;
				}
				element.disabled = isBusy;
			});
		}

		function buildFormActionUrl(form) {
			const actionUrl = new URL(form.action, window.location.origin);
			const searchParams = new URLSearchParams(new FormData(form));
			actionUrl.search = searchParams.toString();
			return actionUrl;
		}

		async function handleUpdatesPanelQuery(form, message) {
			const actionUrl = buildFormActionUrl(form);
			setFormBusy(form, true);
			showAjaxFeedback('success', '');
			try {
				await fetchUpdatesPanel(buildUpdatesPanelUrl(actionUrl.toString()), routeMasterdataUrl(actionUrl.search));
			} catch (error) {
				showAjaxFeedback('error', error.message);
			} finally {
				setFormBusy(form, false);
			}
		}

		function updateBrowserUrl(url) {
			if (!url) return;
			history.replaceState({}, '', url);
		}

		function buildUpdatesPanelUrl(sourceUrl) {
			const url = new URL(sourceUrl, window.location.origin);
			const panelUrl = new URL(updatesPanelRoute, window.location.origin);
			panelUrl.search = url.search;
			return panelUrl.toString();
		}

		async function fetchUpdatesPanel(url, browserUrl, message) {
			const response = await fetch(url, {
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'text/html'
				}
			});
			if (!response.ok) {
				throw new Error('Unable to load update items.');
			}
			const html = await response.text();
			updatesPanelContent.innerHTML = html;
			initializeUpdatesPanel(updatesPanelContent);
			updateBrowserUrl(browserUrl);
			if (message) {
				showSuccessModal(message);
			}
		}

		async function submitUpdatesForm(form) {
			const formData = new FormData(form);
			// clear previous inline errors for this form
			form.querySelectorAll('[data-error-for]').forEach(function (el) { el.textContent = ''; el.style.display = 'none'; var input = form.querySelector('[name="' + el.getAttribute('data-error-for') + '"]'); if (input) { input.classList.remove('is-invalid'); } });
			setFormBusy(form, true);
			try {
				const response = await fetch(form.action, {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json'
					}
				});
				const responseType = response.headers.get('content-type') || '';

				if (!response.ok) {
					let errorMessage = 'Unable to save item changes.';
					if (responseType.includes('application/json')) {
						const errorPayload = await response.json();
						if (errorPayload.message) {
							errorMessage = errorPayload.message;
						}
						if (errorPayload.errors) {
							let placedInline = false;
							for (const [fieldName, messages] of Object.entries(errorPayload.errors)) {
								const container = form.querySelector('[data-error-for="' + fieldName + '"]');
								const inputEl = form.querySelector('[name="' + fieldName + '"]');
								const message = (messages && messages.length) ? messages[0] : messages.join(', ');
								if (container) {
									container.textContent = message || '';
									container.style.display = message ? 'block' : 'none';
									if (inputEl) {
										inputEl.classList.add('is-invalid');
									}
									placedInline = true;
									continue;
								}
								// if no container but an input exists, mark input and set a small aria message
								if (inputEl) {
									inputEl.classList.add('is-invalid');
									placedInline = true;
								}
							}
							if (placedInline) {
								setFormBusy(form, false);
								return; // errors shown inline, avoid global error
							}
							// fallback to the first error for global feedback
							const firstError = Object.values(errorPayload.errors).flat()[0];
							if (firstError) {
								errorMessage = firstError;
							}
						}
					}
					throw new Error(errorMessage);
				}

				if (!responseType.includes('application/json')) {
					throw new Error('You do not have permission to modify master data.');
				}

				const payload = await response.json();
				updatesPanelContent.innerHTML = payload.html;
				initializeUpdatesPanel(updatesPanelContent);
				updateBrowserUrl(payload.url);
				showAjaxFeedback('success', '');
				showSuccessModal(payload.message || 'Item saved successfully.');
			} finally {
				setFormBusy(form, false);
			}
		}

		function initializeUpdatesPanelAjax(root) {
			const panelRoot = root || updatesPanelContent;
			if (!panelRoot) {
				return;
			}

			panelRoot.querySelectorAll('[data-masterdata-region-filter]').forEach(function (regionSelect) {
				if (regionSelect.dataset.masterdataBound === '1') {
					return;
				}
				regionSelect.dataset.masterdataBound = '1';
				regionSelect.addEventListener('change', function () {
					const form = regionSelect.closest('form');
					if (form) {
						handleUpdatesPanelQuery(form);
					}
				});
			});

			panelRoot.querySelectorAll('form[data-masterdata-updates-form]').forEach(function (form) {
				if (form.dataset.masterdataBound === '1') {
					return;
				}
				form.dataset.masterdataBound = '1';
				form.addEventListener('submit', function (event) {
					event.preventDefault();
					event.stopPropagation();

					const formType = form.getAttribute('data-masterdata-updates-form');
					if (formType === 'region' || formType === 'filters') {
						handleUpdatesPanelQuery(form);
						return;
					}

					if (formType === 'create' || formType === 'update' || formType === 'delete') {
						submitUpdatesForm(form).catch(function (error) {
							showAjaxFeedback('error', error.message);
						});
					}
				});
			});

			panelRoot.querySelectorAll('[data-masterdata-apply-filters]').forEach(function (filterButton) {
				if (filterButton.dataset.masterdataBound === '1') {
					return;
				}
				filterButton.dataset.masterdataBound = '1';
				filterButton.addEventListener('click', function (event) {
					event.preventDefault();
					const form = filterButton.closest('form');
					if (form) {
						handleUpdatesPanelQuery(form);
					}
				});
			});

			panelRoot.querySelectorAll('a[data-masterdata-updates-clear], .masterdata-pagination a').forEach(function (link) {
				if (link.dataset.masterdataBound === '1') {
					return;
				}
				link.dataset.masterdataBound = '1';
				link.addEventListener('click', function (event) {
					event.preventDefault();
					fetchUpdatesPanel(buildUpdatesPanelUrl(link.href), link.href).catch(function (error) {
						showAjaxFeedback('error', error.message);
					});
				});
			});
		}

		function routeMasterdataUrl(search) {
			const url = new URL(window.location.href);
			url.pathname = @json(route('masterdata.index', [], false));
			url.search = search;
			return url.toString();
		}

		if (successModalClose) {
			successModalClose.addEventListener('click', closeSuccessModal);
		}

		if (successModal) {
			successModal.addEventListener('click', function (event) {
				if (event.target === successModal) {
					closeSuccessModal();
				}
			});
		}

		document.addEventListener('click', function (event) {
			const uploadButton = event.target.closest('.btn-upload-masterdata-attachment');
			if (uploadButton) {
				event.preventDefault();
				openMasterdataAttachmentUploadModal(uploadButton);
				return;
			}

			const viewButton = event.target.closest('.btn-view-masterdata-attachment');
			if (viewButton) {
				event.preventDefault();
				openMasterdataAttachmentViewModal(viewButton);
			}
		});

		const masterdataAttachmentViewModal = document.getElementById('masterdataAttachmentViewModal');
		if (masterdataAttachmentViewModal) {
			masterdataAttachmentViewModal.addEventListener('hidden.bs.modal', function () {
				const frame = document.getElementById('masterdataAttachmentViewFrame');
				const uploaderEl = document.getElementById('masterdataAttachmentViewUploadedBy');
				if (frame) {
					frame.src = '';
				}
				if (uploaderEl) {
					uploaderEl.textContent = '';
					uploaderEl.style.display = 'none';
				}
			});
		}

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && successModal && successModal.classList.contains('is-open')) {
				closeSuccessModal();
			}
		});

		initializeUpdatesPanel(document);
	})();
</script>
@endsection