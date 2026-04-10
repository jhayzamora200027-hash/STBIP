@extends('layouts.app')

@section('content')
@guest
<style>
    .stb-main-content {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 50px !important;
		padding-right: 50px !important;
        width: 100vw !important;
        max-width: 100vw !important;
        box-sizing: border-box !important;
        overflow-x: hidden;
    }

	body.guest-filter-open .stb-main-content {
		padding-right: 430px !important;
	}
@media (max-width: 767px) {
	.st-dashboard-header .st-header-logo {
		width: auto !important;
		max-width: 160px !important;
		min-width: 220px !important;
		height: auto !important;
		min-height: 100px !important;
		display: block !important;
		margin: 0 auto 8px auto !important;
	}

	.st-dashboard-header .st-dashboard-header-row {
		flex-direction: column !important;
		align-items: center !important;
		justify-content: center !important;
	}

	.st-dashboard-header .st-dashboard-header-row > div {
		width: 100% !important;
		text-align: center !important;
		margin: 0 auto !important;
	}

	.st-dashboard-header .st-dashboard-header-row > div:first-child {
		display: flex !important;
		justify-content: center !important;
		align-items: center !important;
	}

	.st-dashboard-header .st-dashboard-header-row > div:last-child {
		margin-top: 12px !important;
		margin-left: 0 !important;
		flex: none !important;
		font-size: 1.2rem !important;
		line-height: 1.2 !important;
		padding: 0 16px !important;
		white-space: normal !important;
		word-break: break-word !important;
		max-width: 100% !important;
		box-sizing: border-box !important;
	}

	.st-dashboard-header .st-dashboard-header-row {
		min-height: auto !important;
		padding-bottom: 12px !important;
	}
	.st-dashboard-header-fullwidth {
		padding-bottom: 12px !important;
	}

	.st-map-figure-wrapper, .st-map-figure-wrapper object#philippines-map, img, svg, object, iframe {
		max-width: 100% !important;
		width: 100% !important;
		height: auto !important;
	}

	html, body { overflow-x: hidden !important; }
}

@media (max-width: 420px) {
	.mobile-dashboard-container .st-header-logo,
	.st-dashboard-header .st-header-logo,
	.st-center-outer .st-header-logo {
		width: auto !important;
		max-width: 140px !important;
		min-width: 220px !important;
		height: auto !important;
		min-height: 100px !important;
		object-fit: contain !important;
		padding: 0 !important;
		margin-left: auto !important;
		margin-right: auto !important;
		display: block !important;
	}
}

	.stb-main-content .st-dashboard-container {
		width: min(1500px, calc(100vw - 100px)) !important;
		max-width: calc(100vw - 100px) !important;
	}

	body.guest-filter-open .stb-main-content .st-dashboard-container {
		width: min(1500px, calc(100vw - 520px)) !important;
		max-width: calc(100vw - 520px) !important;
	}

	@media (max-width: 1199px) {
		.stb-main-content {
			padding-right: 50px !important;
		}

		.stb-main-content .st-dashboard-container {
			width: min(1500px, calc(100vw - 100px)) !important;
			max-width: calc(100vw - 100px) !important;
		}

		body.guest-filter-open .stb-main-content {
			padding-right: 50px !important;
		}

		body.guest-filter-open .stb-main-content .st-dashboard-container {
			width: min(1500px, calc(100vw - 100px)) !important;
			max-width: calc(100vw - 100px) !important;
		}
    }
</style>
<style>
	.guest-mobile-filter-panel { display: none; }
	.guest-mobile-filter-panel .filter-modal-panel { box-shadow: 0 -12px 28px rgba(11,37,64,0.12); }
	.filter-modal-panel.mobile { width: 100%; max-width: 640px; border-radius: 12px 12px 0 0; }
	@media (max-width: 767px) {
		.guest-mobile-filter-panel { display: flex !important; align-items: flex-end; justify-content: center; }
		.filter-modal-panel.mobile .st-dashboard-card { width: calc(100% - 16px); margin: 0 8px 12px 8px; }
		.filter-modal-panel.mobile .card-body { max-height: 72vh; overflow-y: auto; }
	}
</style>
<style>
	.guest-mobile-filter-panel .filter-modal-panel,
	.guest-mobile-filter-panel .guest-filter-card,
	.guest-mobile-filter-panel .guest-filter-panel {
		display: block !important;
		visibility: visible !important;
		opacity: 1 !important;
	}
	.guest-mobile-filter-panel { background: rgba(6,48,110,0.12) !important; }
</style>

<style>
@media (max-width: 767px) {
	html, body { width:100% !important; max-width:100% !important; overflow-x: hidden !important; }

	.st-center-outer > * {
		width: calc(100vw - 24px) !important;
		max-width: calc(100vw - 24px) !important;
		margin-left: auto !important;
		margin-right: auto !important;
		box-sizing: border-box !important;
		padding-left: 0 !important;
		padding-right: 0 !important;
		transform: none !important;
	}

	.mobile-dashboard-container,
	.st-dashboard-container,
	.container.stb-main-content,
	.stb-main-content {
		width: calc(100vw - 24px) !important;
		max-width: calc(100vw - 24px) !important;
		margin: 0 auto !important;
		padding-left: 12px !important;
		padding-right: 12px !important;
		overflow-x: hidden !important;
	}

	.st-map-figure-wrapper,
	.st-map-figure-wrapper object#philippines-map,
	.st-map-figure-wrapper svg,
	.st-map-figure-wrapper img,
	#philippines-map,
	#philippines-map svg {
		width: 100% !important;
		max-width: 100% !important;
		height: auto !important;
		display: block !important;
		margin: 0 auto !important;
		transform: none !important;
	}

		.ph-mobile-fallback { display: none !important; }
		@media (max-width: 767px) {
			object#philippines-map { display: none !important; }
			.ph-mobile-fallback { display: block !important; }
		}

	.ph-frame::before,
	.st-dashboard-container::before,
	.st-dashboard-container::after {
		display: none !important;
		content: none !important;
	}

	.mobile-dashboard-container { -webkit-clip-path: inset(0 0 0 0); clip-path: inset(0 0 0 0); }
}
</style>

<style>
	#mobile-filter-fab { display:none; }
	@media (max-width: 767px) {
		#mobile-filter-fab {
			display: flex;
			position: fixed;
			right: 14px;
			top: 14px; 
			z-index: 2200;
			align-items: center;
			gap: 8px;
			background: #fff;
			border: 0; border-radius: 999px;
			padding: 8px 12px;
			box-shadow: 0 8px 24px rgba(8,43,81,0.12);
			color: #06306e;
			font-weight:700;
			cursor: pointer;
		}
		#mobile-filter-fab .fab-icon { width:20px; height:20px; display:inline-block; }
		#mobile-filter-panel { display:none; }
		.guest-filter-card, .filter-modal-panel { display: none !important; }
	}

	body.st-details-open .st-summary-table-wrap,
	body.st-details-open .social-listing,
	body.st-details-open .slider-modal-content {
		filter: blur(6px) brightness(0.96);
		transition: filter 0.18s ease;
		pointer-events: none;
		user-select: none;
	}
	body.st-details-open #st-details-modal,
	body.st-details-open #st-attachment-modal,
	body.st-details-open .st-region-modal-dialog,
	body.st-details-open .modal {
		filter: none !important;
		pointer-events: auto !important;
		user-select: auto !important;
	}
</style>
@endguest
<style>
@media (max-width: 767px) {
	.container.stb-main-content, .stb-main-content {
		margin-left: 0 !important;
		padding-left: 0 !important;
		padding-right: 0 !important;
		width: 100% !important;
		max-width: 100% !important;
		box-sizing: border-box !important;
	}
	body.sidebar-open .stb-main-content { margin-left: 0 !important; }
	.st-center-outer { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; }
	.mobile-dashboard-container { margin: 12px auto !important; left: 0 !important; right: 0 !important; }
	html, body { overflow-x: hidden !important; }
}
</style>

<style>
@media (max-width: 767px) {
	.st-map-figure-wrapper,
	.st-map-figure-wrapper object#philippines-map,
	.st-map-figure-wrapper svg,
	.st-map-figure-wrapper img {
		max-width: 100% !important;
		width: 100% !important;
		height: auto !important;
		transform: none !important;
		margin: 0 auto !important;
		display: block !important;
	}

	.ph-frame::before { inset: 0 !important; border: none !important; box-shadow: none !important; }

	.mobile-dashboard-container, .st-center-outer, .st-dashboard-container { overflow-x: hidden !important; }

	.mobile-dashboard-container { -webkit-clip-path: inset(0 0 0 0); clip-path: inset(0 0 0 0); }
}
</style>

<style>
@media (max-width: 767px) {
	.formal-chart-panel.d-none, .formal-chart-panel .d-none { display: block !important; }

	.formal-chart-panel canvas, .mobile-dashboard-container canvas { width: 100% !important; height: auto !important; max-height: 640px !important; }

	.formal-chart-panel, .map-overlay-card, .mobile-dashboard-container { overflow: visible !important; }

	 .st-dashboard-container { display: block !important; overflow: visible !important; max-width: 100% !important; width: 100% !important; padding: 12px !important; }
	 .mobile-dashboard-container { display: block !important; }

	 .st-dashboard-header-fullwidth { position: static !important; z-index: auto !important; }
	 .st-dashboard-container { padding-top: 0 !important; }

	html, body { overflow-y: auto !important; height: auto !important; -webkit-overflow-scrolling: touch; }
}
</style>

<link href="/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
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
<style>
	.modal-backdrop {
		z-index: 1990 !important;
	}
	.modal {
		z-index: 2000 !important;
	}

</style>
<style>
.select2-container--default .select2-selection--multiple {
	background: #f8fafc;
	border: 2px solid #06306e;
	border-radius: 10px;
	min-height: 44px;
	padding: 6px 8px;
	box-shadow: 0 2px 8px rgba(16, 174, 181, 0.08);
	font-size: 1.08rem;
	transition: border 0.2s;
}
.select2-container--default .select2-selection--multiple:focus {
	border: 2px solid #06306e;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
	background: linear-gradient(90deg, #06306e 60%, #06306e 100%);
	color: #fff;
	border: none;
	border-radius: 8px;
	margin: 2px 6px 2px 0;
	padding: 4px 10px;
	font-weight: 500;
	box-shadow: 0 1px 4px #000000;
}
.select2-container--default .select2-selection--multiple .select2-search__field {
	background: transparent;
	color: #222;
	font-size: 1.05rem;
}
.select2-dropdown {
	border-radius: 10px;
	box-shadow: 0 4px 16px rgba(16, 174, 181, 0.13);
	border: 2px solid #06306e;
	padding: 4px 0;
}
.select2-results__option {
	padding: 8px 14px;
	font-size: 1.07rem;
	border-radius: 6px;
	margin: 2px 0;
	transition: background 0.15s;
}
.select2-results__option--highlighted {
	background: #e0f7fa;
	color: #06306e;
}
.select2-container--default .select2-selection--multiple .select2-selection__rendered {
	padding: 0 4px 0 0;
}
.select2-container--default .select2-selection--multiple .select2-selection__placeholder {
	color: #06306e;
	opacity: 0.85;
	font-weight: 500;
}

.select2-results__option,
.select2-dropdown .select2-results__option {
    height: 40px !important;
    line-height: 40px !important;
    padding: 0 14px !important; 
    box-sizing: border-box !important;
}
.select2-results__options,
.select2-dropdown .select2-results__options {
    max-height: calc(40px * 8) !important;
    overflow-y: auto !important;
}
</style>
<style>
	body {
		min-height: 100vh;
	}
	.st-center-outer {
		min-height: 100vh;
		display: flex;
		align-items: center;
		justify-content: flex-start; 
		background: none;
		margin-left: 0 !important;
		margin-right: auto;   
	}
		.st-center-outer > * {
		margin-left: 0 !important;
	}
	.st-dashboard-container {
		background: #fff;
		border-radius: 24px;
		box-shadow: 0 8px 32px rgba(16, 174, 181, 0.13), 0 1.5px 8px rgba(0,0,0,0.04);
		border: 3px solid #06306e;
		padding: 56px 48px 48px 48px;
		max-width: 1200px;
		width: 100%;
		margin: 40px auto;
		position: relative;
	}
	.st-dashboard-header {
		background: linear-gradient(90deg, #06306e 60%, #06306e 100%);
		color: #fff;
		padding: 28px 0 18px 0;
		border-radius: 18px 18px 0 0;
		font-size: 2.2rem;
		font-weight: bold;
		letter-spacing: 1px;
		margin-bottom: 0;
		box-shadow: 0 2px 8px rgba(16, 174, 181, 0.10);
	}
	.st-dashboard-card {
		background: #f8fafc;
		border-radius: 14px;
		box-shadow: 0 2px 12px rgba(16, 174, 181, 0.07);
		margin-bottom: 24px;
		border: 2.5px solid rgba(16, 174, 181, 0.65);
		transition: transform 0.15s, box-shadow 0.15s;
		min-width: 320px;
		max-width: 340px;
		min-height: 180px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}
	.st-dashboard-card:hover {
		transform: translateY(-4px) scale(1.03);
		box-shadow: 0 8px 24px rgba(16, 174, 181, 0.18);
	}

.st-dashboard-card.flex-fill:hover {
    transform: none !important;
    box-shadow: none !important;
}
	.st-dashboard-card .card-header {
		background: linear-gradient(90deg, #06306e 60%, #06306e 100%);
		color: #fff;
		font-weight: 600;
		font-size: 1.15rem;
		border-radius: 14px 14px 0 0;
		border: none;
		padding: 14px 0;
		letter-spacing: 0.5px;
		width: 100%;
		margin: 0;
		text-align: center;
		box-sizing: border-box;
		display: block;
	}
	.st-dashboard-card .card-body {
		padding: 28px 0 16px 0;
	}
	.st-dashboard-card h1 {
		font-size: clamp(1.8rem, 6vw, 4rem);
		font-weight: 800;
		margin: 0;
		color: #06306e;
		text-shadow: 0 1px 0 #fff, 0 2px 8px #e2edf8;
	}
	.st-dashboard-select-card {
		background: linear-gradient(135deg, #06306e 60%, #06306e 100%);
		border-radius: 14px;
		border: 2.5px solid #06306e;
		padding: 32px 18px 18px 18px;
		margin-bottom: 24px;
		min-height: 180px;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		box-shadow: 0 2px 12px rgba(16, 174, 181, 0.07);
	}
	.st-dashboard-select-card select {
		margin-bottom: 14px;
		width: 220px;
		border-radius: 6px;
		border: 1.5px solid #06306e;
		padding: 8px 10px;
		font-size: 1.05rem;
		background: #fff;
		color: #222;
		box-shadow: 0 1px 4px #06306e;
		transition: border 0.2s;
	}
	.st-dashboard-select-card select:focus {
		border: 2px solid #06306e;
		outline: none;
	}


	@media (max-width: 991px) {
		.st-dashboard-container {
			padding: 18px 4vw 18px 4vw;
		}
	}
	@media (max-width: 767px) {
		.st-dashboard-header {
			font-size: 1.2rem;
			padding: 16px 0 8px 0;
		}
		.st-dashboard-card .card-header {
			font-size: 1rem;
		}
		.st-dashboard-card h1 {
			font-size: 2.6rem !important;
			line-height: 1 !important;
		}
		.st-dashboard-select-card select {
			width: 100%;
		}
		.st-dashboard-container {
			max-width: 99vw;
			padding: 8px 2vw 8px 2vw;
		}
		.st-dashboard-card {
			min-width: 0 !important;
			max-width: none !important;
			width: 100% !important;
		}
		.st-map-card-body { grid-template-columns: 1fr !important; }
		#streportFrame {
			height: 500px !important;
			min-height: 500px !important;
			max-height: none !important;
		}
		.st-header-logo { height: auto !important; max-width: 140px !important; min-width: 220px !important; min-height: 100px !important; }
	}
	@media print {
		body {
			background: #ffffff !important;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
			font-size: 0.9rem !important;
		}
		.no-print, .no-print * {
			display: none !important;
		}
		@page {
			margin: 10mm 10mm 12mm 10mm;
		}
		.st-center-outer {
			display: block !important;
			align-items: flex-start;
			justify-content: flex-start;
			background: #ffffff !important;
			padding: 0 !important;
		}
		.st-dashboard-container {
			box-shadow: none !important;
			border: none !important;
			margin: 0 !important;
			padding: 0 !important;
			max-width: 100% !important;
			width: 100% !important;
		}
		.st-dashboard-header-fullwidth {
			position: static !important;
			border-radius: 0 !important;
			margin: 0 0 4mm 0 !important;
			width: calc(100% + 20mm) !important;
			margin-left: -10mm !important;
			padding-top: 4mm !important;
			padding-bottom: 3mm !important;
		}
		.st-dashboard-header {
			font-size: 1.3rem !important;
			box-shadow: none !important;
		}
		.container-fluid,
		.row,
		.col-12,
		.col-lg-4,
		.col-md-6,
		.col-md-12 {
			page-break-inside: avoid;
			break-inside: avoid;
		}
		.st-totals-row {
			display: grid !important;
			grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
			grid-auto-rows: auto;
			column-gap: 6mm !important;
			row-gap: 3mm !important;
			max-width: 90% !important;
			margin-left: auto100px !important;
			margin-right: auto !important;
			align-items: stretch;
		}
		.st-totals-row > .col-lg-4 {
			width: auto !important;
			max-width: none !important;
			flex: initial !important;
		}
		.st-totals-row > .col-lg-4:nth-child(1),
		.st-totals-row > .col-lg-4:nth-child(2) {
			grid-column: 1 / 2 !important;
		}
		.st-totals-row > .col-lg-4:nth-child(3) {
			grid-column: 2 / 3 !important;
			grid-row: 1 / 3 !important;
		}
		.st-dashboard-card,
		.st-title-listing-card,
		.st-dashboard-select-card {
			box-shadow: none !important;
			border-width: 1px !important;
			page-break-inside: avoid;
			break-inside: avoid;
			margin-bottom: 4mm !important;
			min-height: auto !important;
		}
		.st-totals-row .st-dashboard-card h1 {
			font-size: 1.4rem !important;
			margin: 0 !important;
		}
		.st-totals-row .st-dashboard-card .card-header {
			padding: 3px 4px !important;
			font-size: 0.75rem !important;
			line-height: 1.15 !important;
			white-space: normal !important;
		}
		.st-totals-row .st-dashboard-card .card-body {
			padding: 4px 6px 4px 6px !important;
		}
		.st-totals-row .st-filter-label {
			font-size: 0.7rem !important;
			margin-bottom: 1px !important;
		}
		.st-totals-row select.form-control,
		.st-totals-row .select2-container--default .select2-selection--multiple {
			font-size: 0.7rem !important;
			min-height: 26px !important;
		}
		.st-totals-row .select2-container--default .select2-selection--multiple .select2-selection__choice {
			font-size: 0.65rem !important;
			padding: 1px 6px !important;
		}
		.st-totals-row .btn.st-btn-gradient {
			font-size: 0.8rem !important;
			padding: 6px 0 !important;
		}
		.st-dashboard-card .card-header {
			padding: 4px 0 !important;
			font-size: 0.9rem !important;
		}
		.st-dashboard-card .card-body {
			padding: 8px 8px 6px 8px !important;
		}
		.st-title-listing-scroll {
			max-height: none !important;
			overflow: visible !important;
		}
		.st-map-card-body {
			display: flex !important;
			flex-wrap: nowrap !important;
			justify-content: flex-end !important;
			padding-right: 8px !important; 
		}

		.st-map-figure-wrapper { margin-right: 8px !important; }

		@media (max-width: 991.98px) {
			.st-map-card-body { justify-content: center !important; padding-right: 24px !important; }
			.st-map-figure-wrapper, .st-map-region-list { margin-right: 1000 !important; margin-left: 1000; }
		}
		.st-map-figure-wrapper {
			position: relative !important;
			z-index: 1102 !important; 
			flex: 0 0 420px !important;
			max-width: 420px !important;
			width: 420px !important;
		}
		.st-map-region-list {
			flex: 0 0 360px !important;
			max-width: 360px !important;
			width: 360px !important;
			margin-right: 0 !important; 
		}

		@media (max-width: 991.98px) {
			.st-map-figure-wrapper,
			.st-map-region-list {
				flex: 0 0 100% !important;
				max-width: 100% !important;
				width: 100% !important;
			}
		}
		.st-map-figure-wrapper object#philippines-map {
			position: relative !important;
			z-index: 1103 !important; 
			pointer-events: auto !important;
			max-width: 105% !important;
			max-height: 105mm !important;
			display: block !important;
			margin-left: 0 !important;
			margin-right: auto !important;
			
		}
		.st-map-region-list {
			font-size: 0.68rem !important;
			line-height: 1.1 !important;
		}
		.st-map-region-row {
			font-size: 0.68rem !important;
			padding: 2px 3px !important;
		}
		.st-map-region-label,
		.st-map-region-count {
			font-size: 0.68rem !important;
		}
		.st-header-logo {
			height: 70px !important;
			max-width: none !important;
		}
		#map-region-label {
			display: none !important;
		}
		.st-dashboard-header-fullwidth {
			page-break-after: avoid !important;
			break-after: avoid !important;
		}
		.st-first-map-row {
			margin-top: 2mm !important;
			page-break-before: avoid !important;
		}
		#region-titles-modal,
		#st-title-modal,
		#doughnutTooltip,
		#catListTooltip {
			display: none !important;
		}
	}
</style>

<style>
		.st-dashboard-header .st-header-logo { width:600px !important; max-width:600px !important; height:140px !important; }
		@media (max-width: 767px) {
			@if(Auth::check())
			.st-dashboard-header .st-header-logo {
				width: auto !important;
				min-width: 120px !important;
				max-width: 200px !important;
				height: auto !important;
				object-fit: contain !important;
				display: block !important;
				margin: 0 auto 8px !important;
			}
			@else
			.st-dashboard-header .st-header-logo {
				width: auto !important;
				min-width: 120px !important;
				max-width: 200px !important;
				height: auto !important;
				object-fit: contain !important;
				display: block !important;
				margin: 0 auto 8px !important;
			}
			@endif
		}
	.st-map-card-body {
		display: grid !important;
		grid-template-columns: minmax(210px, 260px) minmax(430px, 1.35fr) minmax(250px, 320px);
		gap: 24px;
		align-items: start;
		padding: 24px !important;
	}
	.st-map-figure-wrapper {
		position: relative;
		width: 100%;
		max-width: 560px;
		min-width: 0;
		margin: 0 auto;
		display: flex !important;
		flex-direction: column;
		align-items: center;
		justify-self: center;
	}
	.st-map-figure-wrapper object#philippines-map {
		width: 100%;
		max-width: 560px;
		height: auto;
		display: block;
	}
	.map-overlay-totals {
		position: static;
		width: 100%;
		display: grid;
		grid-template-columns: 1fr;
		gap: 12px;
		background: transparent;
		border-radius: 12px;
		padding: 0;
		box-shadow: none;
		border: none;
		z-index: 1;
		justify-items: stretch;
		pointer-events: auto;
		align-self: stretch;
	}
	.map-overlay-totals .st-dashboard-card { margin: 0; box-shadow: none; background: transparent; width: 100%; max-width: none; min-width: 0; }
	.map-overlay-card { width: 100%; min-width: 0; background: #ffffff; border: 2px solid #1e90ff; box-shadow: 0 6px 18px rgba(16,174,181,0.06), 0 0 0 6px rgba(30,144,255,0.04); border-radius: 10px; padding: 8px 6px; margin: 0; overflow: hidden; box-sizing: border-box; }
	.map-overlay-card:hover { transform: none !important; box-shadow: none !important; }
	.map-overlay-card .card-body { padding: 6px 0; background: transparent; display: flex; align-items: center; justify-content: center; padding-top: 6px; padding-bottom: 6px; min-height: 140px; }
	.map-overlay-totals .st-dashboard-card .card-header {
		display: inline-block;
		width: 100%; 
		max-width: 100%;
		margin: 0 auto -6px;
		font-size: 0.7rem;
		padding: 1px 4px;
		background: transparent; 
		background-image: none !important;
		color: #1e90ff;
		border: none;
		box-shadow: none;
		text-align: center;
		white-space: normal;
		line-height: 1;
	}
	.map-overlay-totals .st-dashboard-card h1 { font-size: 3.2rem; margin: 8px 0 0 0; color: #1e90ff; line-height:1; font-weight:800; white-space:nowrap; display:block; transition: font-size 140ms ease; max-width: 100%; box-sizing: border-box; padding: 0 6px; overflow: hidden; text-overflow: clip; visibility: hidden; }
	.st-map-region-list {
		width: 100% !important;
		max-width: none !important;
		min-width: 0 !important;
		align-self: stretch;
	}

	.ph-frame { position: relative; border-radius: 16px; }
	.ph-frame::before { content: ""; position: absolute; inset: -12px; border-radius: 18px; background: linear-gradient(180deg, rgba(122,235,226,0.08), rgba(16,174,181,0.02)); border: 4px solid rgba(16,174,181,0.22); box-shadow: 0 12px 36px rgba(16,174,181,0.10); pointer-events: none; z-index: -1; }

	@media (max-width: 1199.98px) {
	  .st-map-card-body {
		grid-template-columns: minmax(190px, 220px) minmax(320px, 1.15fr) minmax(220px, 280px);
		gap: 18px;
	  }
	}
	@media (max-width: 991.98px) {
	  .st-map-card-body {
		grid-template-columns: 1fr;
		gap: 18px;
	  }
	  .map-overlay-totals { grid-template-columns: repeat(2, minmax(0, 1fr)); }
	  .st-map-figure-wrapper,
	  .st-map-region-list {
		max-width: none !important;
	  }
	}
@media (min-width: 992px) {
  .st-totals-row > .col-lg-4:nth-child(1),
  .st-totals-row > .col-lg-4:nth-child(2) { display: none !important; }
  .st-totals-row > .col-lg-4:nth-child(3) { margin-left: 0 !important; }
}

@media (max-width: 991.98px) {
  .map-overlay-totals { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 0; box-shadow: none; border: none; background: transparent; }
  .map-overlay-totals .st-dashboard-card .card-header { color: inherit; }
}
</style>

<style>
	.mobile-dashboard-container {
		display: none;
		background: #fff;
		border-radius: 18px;
		box-shadow: 0 4px 16px rgba(16, 174, 181, 0.13);
		border: 2px solid #06306e;
		padding: 18px 8px 18px 8px;
		margin: 16px 0;
		width: 98vw;
		max-width: 99vw;
		position: relative;
	}
	@media (max-width: 767px) {
		.mobile-dashboard-container { display: block; }
		.st-dashboard-container { display: none !important; }
	}
</style>

<style>
	html, body { box-sizing: border-box; }
	*, *::before, *::after { box-sizing: inherit; }
	img, svg, object, iframe { max-width: 100%; height: auto; display: block; }

	.st-center-outer { padding: 8px; }
	.st-dashboard-container { padding-left: 12px; padding-right: 12px; }

	#streportFrame { width: 100%; border: none; }

	.masterdata-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
	.masterdata-table { min-width: 0 !important; width: 100% !important; }

	.small-cards-grid, .formal-st-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; }

	.st-dashboard-card { width: 100% !important; min-width: 0 !important; max-width: none !important; }

	.map-overlay-totals .st-dashboard-card h1 { visibility: visible !important; }

	@media (max-width: 820px) {
		.masterdata-item-list-head { display: none !important; }
		.masterdata-item-row { grid-template-columns: 1fr !important; gap: 8px; padding: 12px !important; }
		.masterdata-item-row-cell { display: block; width: 100%; }
	}

	@media (max-width: 560px) {
		.st-dashboard-header { font-size: 1.0rem !important; padding: 10px 6px !important; }
		.st-dashboard-card .card-header { font-size: 0.95rem !important; }
		.st-dashboard-card h1 { font-size: 2.4rem !important; }
		.st-dashboard-select-card { padding: 18px 12px 12px 12px !important; }
		.st-map-card-body { grid-template-columns: 1fr !important; padding: 8px !important; }
		#streportFrame { height: 500px !important; min-height: 500px !important; }
	}

	@media (max-width: 420px) {
		.st-dashboard-header { font-size: .95rem !important; }
		.st-header-logo { max-width: 120px !important; height: 100px !important; min-width: 220px !important; min-height: 100px !important; }
		.small-cards-grid { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
		.masterdata-card-body, .masterdata-card-header { padding-left: 12px; padding-right: 12px; }
	}
</style>

<style>
	@media (max-width: 420px) {
		html, body { max-width: 100%; overflow-x: hidden; }

		.st-center-outer, .mobile-dashboard-container, .st-dashboard-container {
			box-sizing: border-box !important;
			width: 100vw !important;
			max-width: 100vw !important;
			margin: 0 auto !important;
			padding-left: 8px !important;
			padding-right: 8px !important;
			left: 0 !important;
			transform: none !important;
		}

		object#philippines-map, #streportFrame, iframe, img {
			width: 100% !important;
			max-width: 100% !important;
			box-sizing: border-box !important;
		}

		.st-dashboard-card, .map-overlay-card, .mobile-dashboard-container {
			margin-left: 0 !important;
			margin-right: 0 !important;
			padding-left: 10px !important;
			padding-right: 10px !important;
		}

	}
</style>

<style>
@media (max-width: 767px) {
	.container.stb-main-content, .stb-main-content {
		padding-left: 0 !important;
		padding-right: 0 !important;
		margin-left: 0 !important;
		margin-right: 0 !important;
	}

	.st-center-outer { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; margin: 0 auto !important; }
	.mobile-dashboard-container { width: calc(100% - 24px) !important; max-width: calc(100% - 24px) !important; margin: 12px auto !important; border-radius: 16px !important; padding: 12px !important; left: 0 !important; right: 0 !important; }
	.mobile-dashboard-container, .st-center-outer > * { margin-left: auto !important; margin-right: auto !important; }
	.st-dashboard-container { display: block !important; }

	.st-dashboard-header {
		padding: 16px 12px 20px 12px !important;
		text-align: center !important;
	}
	.st-dashboard-header .st-dashboard-header-row {
		flex-direction: column !important;
		align-items: center !important;
		justify-content: center !important;
		gap: 12px !important;
	}
	.st-dashboard-header .st-dashboard-header-row > div {
		width: 100% !important;
		text-align: center !important;
	}

	.st-dashboard-header .st-dashboard-header-row > div:last-child {
		font-size: 1.1rem !important;
		line-height: 1.3 !important;
		padding: 0 8px !important;
		max-width: 100% !important;
	}

	html, body { overflow-x: hidden !important; }
}
</style>

<style>
canvas#onGoing {
	width: 100% !important;
	height: 100% !important;
	max-height: none !important;
	padding-left: 0 !important;
	display: block !important;
}

.formal-chart-canvas-trend {
	min-height: 430px;
	height: 430px;
	padding: 20px 24px 12px;
	box-sizing: border-box;
}

@media (max-width: 767px) {
	canvas#onGoing { height: 100% !important; }
	.formal-chart-canvas { min-height: 320px !important; }
	.formal-chart-canvas-trend {
		min-height: 260px !important;
		height: clamp(260px, 72vw, 340px) !important;
		padding: 16px 12px 10px !important;
	}
}
</style>

<script>
setTimeout(function(){ if(window.__stb_resizeAllCharts) try{ window.__stb_resizeAllCharts(); }catch(e){} }, 250);
</script>

<div class="st-center-outer">


	{{-- <div class="no-print" style="position:absolute; top:12px; right:24px; z-index:5;">
		   <button type="button" class="btn btn-sm btn-primary" onclick="window.print()" style="background: linear-gradient(90deg, #10aeb5 60%, #1de9b6 100%); border: none; border-radius: 999px; padding: 6px 18px; font-weight: 600; box-shadow: 0 2px 6px rgba(16,174,181,0.35);">
			   Print / Save as PDF
		   </button>
	   </div> --}}
	    	<div class="st-dashboard-container" style="padding-top:0; position:relative; overflow:hidden; width:100%; min-width:0; max-width:1800px !important; margin:40px auto !important; box-sizing:border-box;">
	    	<div class="st-dashboard-header st-dashboard-header-fullwidth">
	   		<div class="st-dashboard-header-row" style="display:flex; flex-direction:row; align-items:center; justify-content:space-between; width:100%; min-height:100px; padding:10px 10px 10px 10px;">
	   			<div style="display:flex; align-items:flex-end; gap:24px; flex-wrap:wrap;">
					<img class="st-header-logo" src="{{ asset('images/dattachments/DSWD STB Bagong Pil logo white.png') }}" alt="DSWD Logo" style="height:200px; max-width:200px; min-height: 100px !important; min-width: 220px !important; background:transparent;">
	   			</div>
	   			<div style="text-align:right; font-size:1.2rem; height:100%; font-weight:800; letter-spacing:0.06em; text-transform:uppercase; flex:1; margin-left:32px; font-family: 'Poppins', sans-serif">
	   				Adopted and Replicated Social Technologies
	   			</div>
	   		</div>
	   	</div>
		<div class="container-fluid" style="max-width: 100%;">
				<div class="row mt-4 st-first-map-row">
					<div class="col-12 p-0">
						<div class="card st-dashboard-card flex-fill" style="width:100%;max-width:none;margin:0 auto;">
							<div class="card-header text-center">PHILIPPINES MAP & REGIONS</div>
								<div class="card-body st-map-card-body" >
									<div class="map-overlay-totals" aria-hidden="false">
								<div class="card st-dashboard-card text-center map-overlay-card">
									<div class="card-header">TOTAL ADOPTED AND REPLICATED</div>
									<div class="card-body">
										<h1>{{ collect($data)->filter(function($row){
											return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
										})->count() }}</h1>
									</div>
								</div>

								<div class="card st-dashboard-card text-center map-overlay-card">
									<div class="card-header">TOTAL EXPRESSION OF INTEREST</div>
									<div class="card-body">
										<h1>{{ collect($data)->filter(function($row){
											$val = $row['with_expr'] ?? null;
											$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
											return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
										})->count() }}</h1>
									</div>
								</div>

								<div class="card st-dashboard-card text-center map-overlay-card">
									<div class="card-header">TOTAL SB RESOLUTION</div>
									<div class="card-body">
										<h1>{{ collect($data)->filter(function($row){
											$val = $row['with_res'] ?? null;
											$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
											return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
										})->count() }}</h1>
									</div>
								</div>

								<div class="card st-dashboard-card text-center map-overlay-card">
									<div class="card-header">TOTAL MEMORANDUM OF AGREEMENT</div>
									<div class="card-body">
										<h1>{{ collect($data)->filter(function($row){
											$val = $row['with_moa'] ?? null;
											$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
											return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
										})->count() }}</h1>
									</div>
								</div>
							</div>

									<div class="st-map-figure-wrapper ph-frame" style="position:relative;">
										<object id="philippines-map" data="{{ asset('images/philippines.svg') }}" type="image/svg+xml"></object>
										<img id="philippines-map-static" class="ph-mobile-fallback" src="{{ asset('images/philippines.svg') }}" alt="Philippines map" style="display:none; width:100%; height:auto;" />
										<div id="philippines-map-inline-mobile" style="display:none; width:100%; height:auto; position:relative;">
											{!! preg_replace('/<\?xml.*\?>/','', file_get_contents(public_path('images/philippines.svg'))) !!}
										</div>
										<script src="{{ asset('js/philippines-map-mobile.js') }}"></script>
										<style>
											@media (max-width: 767px) {
												#philippines-map { display: none !important; }
												#philippines-map-static { display: none !important; }
												#philippines-map-inline-mobile { display: none !important; z-index: auto !important; }
												#ph-map-loading { display: none !important; }
											}
										</style>
										<script>
											document.addEventListener('DOMContentLoaded', function () {
												try {
													const container = document.getElementById('philippines-map-inline-mobile');
													console.log('philippines: inline container present?', !!container);
													if (!container) return;
													const svg = container.querySelector('svg');
													console.log('philippines: inline svg present?', !!svg);
													if (!svg) return;
													svg.id = svg.id || 'philippines-map-inline-svg';
													const pathSelector = 'path, polygon, rect, circle, g';
													const paths = svg.querySelectorAll(pathSelector);
													console.log('philippines: inline svg path count =', paths.length);

													if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches) {
														container.style.display = 'block';
														container.style.zIndex = 1000;
													}

													paths.forEach(p => {
														try {
															p.style.cursor = 'pointer';
															p.addEventListener('click', function (e) {
																e.preventDefault();
																const title = p.getAttribute('title') || (p.querySelector('title') ? p.querySelector('title').textContent : '') || '';
																let regionName = null;
																try {
																	const norm = typeof normalizeProvinceName === 'function' ? normalizeProvinceName(title || '') : null;
																	if (norm && typeof provinceRegionIndex !== 'undefined') {
																		regionName = provinceRegionIndex[norm] || null;
																	}
																} catch (err) { }
																if (typeof handleRegionClick === 'function') {
																	handleRegionClick({ regionName: regionName, path: p });
																} else if (typeof openRegionTitlesModal === 'function') {
																	openRegionTitlesModal(regionName || ('Province: ' + (title || '')), []);
																}
															});
															let touchStart = 0, moved = false;
															p.addEventListener('touchstart', function () { touchStart = Date.now(); moved = false; });
															p.addEventListener('touchmove', function () { moved = true; });
															p.addEventListener('touchend', function (e) {
																if (!moved && (Date.now() - touchStart) < 500) {
																	e.preventDefault();
																	p.dispatchEvent(new Event('click'));
																}
															});
														} catch (innerErr) {
															console.warn('philippines: path attach failed', innerErr);
														}
													});
												} catch (ex) {
													console.warn('philippines inline init failed', ex);
												}
											});
										</script>
										<div id="map-region-label" style="margin-top:10px; font-size:0.95rem; font-weight:600; color:#10aeb5; text-align:center; min-height:22px;">
											Hover a region on the map
										</div>
									</div>

									<div id="map-region-list" class="st-map-region-list"></div>
								</div>
							</div>
						</div>
					</div>

			
		</div>


			<div class="row mt-4">
			    <div class="col-12">
					<iframe id="streportFrame" src="{{ route('streport') }}?embed=1" style="width:100%; height:60vh; min-height:360px; border:none; transition: height 0.3s ease;" title="STsReport"></iframe>
			    </div>
			</div>
<script>
if (!document.getElementById('catListTooltip')) {
	const tooltipDiv = document.createElement('div');
	tooltipDiv.id = 'catListTooltip';
	tooltipDiv.style.position = 'fixed';
	tooltipDiv.style.zIndex = '9999';
	tooltipDiv.style.display = 'none';
	tooltipDiv.style.pointerEvents = 'none';
	tooltipDiv.style.background = 'rgba(34,34,34,0.97)';
	tooltipDiv.style.color = '#fff';
	tooltipDiv.style.padding = '4px 10px';
	tooltipDiv.style.borderRadius = '6px';
	tooltipDiv.style.fontSize = '12px';
	tooltipDiv.style.boxShadow = '0 2px 8px rgba(16,174,181,0.13)';
	tooltipDiv.style.whiteSpace = 'pre-line';
	tooltipDiv.style.maxWidth = '260px';
	tooltipDiv.style.lineHeight = '1.3';
	document.body.appendChild(tooltipDiv);
}
</script>

<script>
(function(){
	var checks = 0;
	var maxChecks = 50; 
	var interval = 300;
	var id = setInterval(function(){
		checks++;
		try{
			var obj = document.getElementById('philippines-map');
			var svgPresent = false;
			if(obj && obj.contentDocument){
				svgPresent = !!obj.contentDocument.querySelector('svg');
			}
			var objRectVisible = false;
			if(obj){
				var r = obj.getBoundingClientRect();
				objRectVisible = (r.width > 2 && r.height > 2 && r.top < window.innerHeight && r.bottom > 0);
			}

			if(svgPresent || objRectVisible){
				console.debug('map-checker: map detected, hiding loader (svgPresent=' + svgPresent + ', rectVisible=' + objRectVisible + ')');
				try{ if(typeof hideOverlay === 'function'){ hideOverlay(); } else {  window.hideOverlay && window.hideOverlay(); } }catch(e){}
				try{ var g = document.getElementById('loading-overlay'); if(g){ g.classList.add('hidden'); g.style.display='none'; g.hidden=true; if(g.parentNode){ g.parentNode.removeChild(g); } } }catch(e){}
				clearInterval(id);
				return;
			}
			if(checks >= maxChecks){
				console.debug('map-checker: max checks reached, removing loader fallback');
				try{ var g2 = document.getElementById('loading-overlay'); if(g2){ g2.classList.add('hidden'); g2.style.display='none'; g2.hidden=true; if(g2.parentNode){ g2.parentNode.removeChild(g2); } } }catch(e){}
				clearInterval(id);
			}
		}catch(e){
			if(checks >= maxChecks) clearInterval(id);
		}
	}, interval);
})();
</script>

<style>
	.ph-map-loading { position:absolute; inset:12px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.92); z-index:1200; border-radius:12px; flex-direction:column; gap:8px; }
	@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
(function(){
	var obj = document.getElementById('philippines-map');
	var overlay = document.getElementById('ph-map-loading');
	var maxWait = 10000; // ms
	var pollInterval = 200;

	function hideOverlay(){
		console.debug('map: hideOverlay called');
		try{ if(overlay) overlay.style.display = 'none'; }catch(e){}
		try{
			var cards = document.querySelectorAll('.map-overlay-totals .st-dashboard-card h1');
			cards.forEach(function(h){ h.style.visibility = 'visible'; });
		}catch(e){}
		try{ if(typeof hideLoader === 'function'){ try{ hideLoader(); }catch(e){} } }catch(e){}
		try{
			var global = document.getElementById('loading-overlay');
			if(global){
				global.classList.add('hidden');
				global.style.display = 'none';
				global.setAttribute('aria-hidden','true');
				try{ global.hidden = true; }catch(e){}
				setTimeout(function(){ try{ if(global && global.parentNode){ global.parentNode.removeChild(global); console.debug('map: removed global loader from DOM'); } }catch(e){} }, 600);
			}
		}catch(e){}
	}

	function showOverlay(){ if(overlay) overlay.style.display = 'flex'; }

	showOverlay();
	obj.addEventListener('load', function(){
		try{
			var svg = obj.contentDocument && obj.contentDocument.querySelector('svg');
			if(svg){
				clearTimeout(timeoutId);
				hideOverlay();
				return;
			}

			var waited = 0;
			var id = setInterval(function(){
				svg = obj.contentDocument && obj.contentDocument.querySelector('svg');
				waited += pollInterval;
				if(svg || waited >= maxWait || timedOut){
					clearInterval(id);
					clearTimeout(timeoutId);
					hideOverlay();
				}
			}, pollInterval);
		}catch(e){
			clearTimeout(timeoutId);
			hideOverlay();
		}
	});
})();
</script>


	<div class="row mt-4">
		<div class="col-12 p-0">
			<div class="card st-dashboard-card no-hover year-of-moa-card flex-fill" style="width:100%;max-width:none;margin:0 auto;">
				<div class="card-header text-center">Social Technology Analytics & Overview</div>
				<div class="card-body total-st-body">
<div class="formal-st-overview">
	<div class="formal-st-top-grid">
		<div class="small-cards-grid formal-st-metrics">
			<div id="card1" class="small-card formal-metric-card formal-metric-card-clickable" role="button" tabindex="0" aria-label="View ongoing ST listing">
				<div class="formal-metric-kicker">Operational Status</div>
				<div class="card-value">{{ $totalOngoingStatus ?? 0 }}</div>
                <div class="card-label">Ongoing STs</div>
                <div class="formal-metric-note">Programs with continuing implementation records.</div>
            </div>
			<div id="card2" class="small-card formal-metric-card formal-metric-card-clickable" role="button" tabindex="0" aria-label="View dissolved ST listing">
                <div class="formal-metric-kicker">Operational Status</div>
				<div class="card-value">{{ $totalDissolvedStatus ?? 0 }}</div>
				<div class="card-label">Inactive STs</div>
                <div class="formal-metric-note">Programs tagged as inactive or dissolved.</div>
            </div>
			<div id="card3" class="small-card formal-metric-card formal-metric-card-clickable" role="button" tabindex="0" aria-label="View replicated ST listing">
                <div class="formal-metric-kicker">Adoption Status</div>
                <div class="card-value">0</div>
                <div class="card-label">Replicated STs</div>
                <div class="formal-metric-note">Titles with documented replication activity.</div>
            </div>
			<div id="card4" class="small-card formal-metric-card formal-metric-card-clickable" role="button" tabindex="0" aria-label="View adopted ST listing">
                <div class="formal-metric-kicker">Adoption Status</div>
                <div class="card-value">0</div>
                <div class="card-label">Adopted STs</div>
                <div class="formal-metric-note">Titles formally adopted in target areas.</div>
            </div>
        </div>

        <div class="formal-st-chart-stack">
			<div class="formal-chart-panel formal-chart-panel-wide">
				<div class="formal-panel-header">
					<div>
						<div class="formal-panel-eyebrow">Trend Overview</div>
						<div class="formal-panel-title">Status Movement Over Time</div>
					</div>
				</div>
				<div class="formal-chart-canvas formal-chart-canvas-large formal-chart-canvas-trend">
					<canvas id="onGoing"></canvas>
                </div>
            </div>

        </div>
	</div>

<div class="st-second-row formal-dashboard-row">
	<div class="year-chart-wrap formal-second-row-wrap">
		<div class="formal-chart-panel formal-chart-panel-yearly">
			<div class="formal-panel-header">
				<div>
					<div class="formal-panel-eyebrow">Distribution</div>
					<div class="formal-panel-title">Year of MOA Count</div>
				</div>
			</div>
			<div class="formal-chart-canvas formal-chart-canvas-medium">
				<canvas id="yearMoaBar" style="width: 760px !imporant; height: 360px;"></canvas>
			</div>
			<div class="formal-year-summary">
				<div class="formal-year-summary-grid">
					<div class="formal-year-stat">
						<div class="formal-year-stat-label">Peak Year</div>
						<div class="formal-year-stat-value" id="yearSummaryPeakYear">-</div>
						<div class="formal-year-stat-meta" id="yearSummaryPeakCount">No records yet</div>
					</div>
					<div class="formal-year-stat">
						<div class="formal-year-stat-label">Average Volume</div>
						<div class="formal-year-stat-value" id="yearSummaryAverage">-</div>
						<div class="formal-year-stat-meta">Average records per year</div>
					</div>
					<div class="formal-year-stat">
						<div class="formal-year-stat-label">Latest Year</div>
						<div class="formal-year-stat-value" id="yearSummaryLatestYear">-</div>
						<div class="formal-year-stat-meta" id="yearSummaryLatestCount">No records yet</div>
					</div>
					<div class="formal-year-stat">
						<div class="formal-year-stat-label">Coverage Span</div>
						<div class="formal-year-stat-value" id="yearSummarySpan">-</div>
						<div class="formal-year-stat-meta">Years represented in MOA data</div>
					</div>
				</div>
			</div>
		</div>
		<div class="formal-mini-panel-group">
			<div class="formal-chart-panel formal-chart-panel-mini">
				<div class="formal-panel-header formal-panel-header-centered">
					<div>
						<div class="formal-panel-eyebrow">Share Analysis</div>
						<div class="formal-panel-title">Ongoing vs Inactive</div>
					</div>
				</div>
				<div class="formal-chart-canvas formal-chart-canvas-mini">
					<canvas id="ongoingDoughnut" style="width: 220px; height: 220px;"></canvas>
				</div>
				<div class="formal-share-summary">
					<div class="formal-share-metrics">
						<div class="formal-share-stat formal-share-stat-teal">
							<div class="formal-share-stat-label">Ongoing</div>
							<div class="formal-share-stat-value" id="ongoingShareCount">{{ $totalOngoingStatus ?? 0 }}</div>
							<div class="formal-share-stat-meta" id="ongoingSharePercent">0%</div>
						</div>
						<div class="formal-share-stat formal-share-stat-rose">
							<div class="formal-share-stat-label">Inactive</div>
							<div class="formal-share-stat-value" id="dissolvedShareCount">{{ $totalDissolvedStatus ?? 0 }}</div>
							<div class="formal-share-stat-meta" id="dissolvedSharePercent">0%</div>
						</div>
					</div>
					<div class="formal-share-insight">
						<div class="formal-share-insight-label">Current lead</div>
						<div class="formal-share-insight-value" id="ongoingShareLead">Awaiting summary</div>
					</div>
				</div>
			</div>
			<div class="formal-chart-panel formal-chart-panel-mini">
				<div class="formal-panel-header formal-panel-header-centered">
					<div>
						<div class="formal-panel-eyebrow">Share Analysis</div>
						<div class="formal-panel-title">Replicated vs Adopted</div>
					</div>
				</div>
				<div class="formal-chart-canvas formal-chart-canvas-mini">
					<canvas id="replicatedDoughnut" style="width: 220px; height: 220px;"></canvas>
				</div>
				<div class="formal-share-summary">
					<div class="formal-share-metrics">
						<div class="formal-share-stat formal-share-stat-blue">
							<div class="formal-share-stat-label">Replicated</div>
							<div class="formal-share-stat-value" id="replicatedShareCount">0</div>
							<div class="formal-share-stat-meta" id="replicatedSharePercent">0%</div>
						</div>
						<div class="formal-share-stat formal-share-stat-gold">
							<div class="formal-share-stat-label">Adopted</div>
							<div class="formal-share-stat-value" id="adoptedShareCount">0</div>
							<div class="formal-share-stat-meta" id="adoptedSharePercent">0%</div>
						</div>
					</div>
					<div class="formal-share-insight">
						<div class="formal-share-insight-label">Current lead</div>
						<div class="formal-share-insight-value" id="replicatedShareLead">Awaiting summary</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="st-third-row formal-dashboard-row">
	<div class="formal-third-row-grid">
		<div class="formal-chart-panel formal-chart-panel-region">
			<div class="formal-panel-header">
				<div>
					<div class="formal-panel-eyebrow">Regional Pattern</div>
					<div class="formal-panel-title">Regional Year Heatmap</div>
				</div>
			</div>
			<div class="formal-chart-canvas formal-chart-canvas-region">
				<canvas id="regionYearLines" style="width: 100%; height: 100%;"></canvas>
			</div>
		</div>
		<div class="formal-linked-st-panels">
			<div class="formal-chart-panel formal-chart-panel-doughnut formal-linked-panel-start">
				<div class="formal-panel-header">
					<div>
						<div class="formal-panel-eyebrow">Title Composition</div>
						<div class="formal-panel-title">Share of ST Titles</div>
					</div>
				</div>
				<div class="formal-chart-canvas formal-chart-canvas-doughnut">
					<div class="formal-doughnut-stage">
						<canvas id="stTitlesDoughnutCopy" class="formal-doughnut-outer"></canvas>
						<canvas id="stTitlesDoughnutLowCopy" class="formal-doughnut-inner"></canvas>
					</div>
				</div>
			</div>
			<div class="formal-list-panel formal-linked-panel-end">
				<div class="formal-panel-header formal-panel-header-list">
					<div>
						<div class="formal-panel-eyebrow">Reference Listing</div>
						<div class="formal-panel-title">ST Titles</div>
					</div>
				</div>
				<div id="stCategoryListCopy" class="formal-st-category-list"></div>
			</div>
		</div>
	</div>
</div>

	<div class="formal-insight-row formal-dashboard-row">
		<div class="formal-insight-grid">
			<div class="formal-chart-panel formal-chart-panel-docflow">
				<div class="formal-panel-header">
					<div>
						<div class="formal-panel-eyebrow">Overall Totals</div>
						<div class="formal-panel-title">Social Technology Totals Snapshot</div>
					</div>
					<div class="formal-panel-caption">Expression of interest, resolution, agreement, status, replication, and adoption totals</div>
				</div>
				<div class="formal-chart-canvas formal-chart-canvas-docflow">
					<canvas id="documentCoverageChart" style="width: 100%; height: 100%;"></canvas>
				</div>
				<div class="formal-doc-summary">
					<div class="formal-doc-summary-item">
						<div class="formal-doc-summary-label">Highest Coverage</div>
						<div class="formal-doc-summary-value" id="docCoverageLeader">-</div>
					</div>
					<div class="formal-doc-summary-item">
						<div class="formal-doc-summary-label">Lowest Coverage</div>
						<div class="formal-doc-summary-value" id="docCoverageLowest">-</div>
					</div>
				</div>
			</div>

			<div class="formal-list-panel formal-ranking-panel">
				<div class="formal-panel-header formal-panel-header-list">
					<div>
						<div class="formal-panel-eyebrow">Geographic Reach</div>
						<div class="formal-panel-title">Top Regions</div>
					</div>
					<div class="formal-panel-caption">Highest record concentration</div>
				</div>
				<div id="topRegionsList" class="formal-ranking-list"></div>
			</div>

			<div class="formal-list-panel formal-ranking-panel">
				<div class="formal-panel-header formal-panel-header-list">
					<div>
						<div class="formal-panel-eyebrow">Local Concentration</div>
						<div class="formal-panel-title">Top Provinces</div>
					</div>
					<div class="formal-panel-caption">Most recorded ST locations</div>
				</div>
				<div id="topProvincesList" class="formal-ranking-list"></div>
			</div>
		</div>
	</div>

<div class="st-fourth-row formal-dashboard-row" style="margin-top:24px;">
	<div class="row mt-4 justify-content-center" style="width:min(1400px, 100%); margin:0;">
		<div class="col-12">
			<div class="card st-dashboard-card st-title-listing-card flex-fill">
				<div class="card-header text-center">SOCIAL TECHNOLOGIES</div>
				<div class="card-body social-listing-body">
					<div class="social-listing-toolbar">
						<div class="social-listing-heading">
							<div class="social-listing-eyebrow">Directory View</div>
							<div class="social-listing-title">Search and review Social Technology implementations</div>
						</div>
						<div class="social-listing-controls">
							<div class="social-listing-control social-listing-control-search">
								<input type="text" id="title-listing-search" class="form-control social-listing-input" placeholder="Search ST title" />
							</div>
							<div class="social-listing-control">
								<select id="title-listing-status-filter" class="form-control social-listing-select">
								<option value="">All statuses</option>
								<option value="ongoing">Ongoing STs</option>
								<option value="dissolved">Inactive STs</option>
							</select>
							</div>
							<div class="social-listing-control">
								<select id="title-listing-adopt-filter" class="form-control social-listing-select">
								<option value="">All types</option>
								<option value="replicated">With Replicated</option>
								<option value="adopted">With Adopted</option>
							</select>
							</div>
						</div>
					</div>
					<div id="title-listing-table-container"></div>
					<script>
					window.fullListingData = @json(collect($data)->filter(function($row){
						return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
					})->values());
					window.fullListingHeaders = @json($headers ?? []);
					window.serverTotals = {
					    totalReplicated: {{ $totalReplicated ?? 0 }},
					    totalAdopted: {{ $totalAdopted ?? 0 }}
					};
					
					window.initialYearStats = @json($yearStats ?? []);
					</script>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.total-st-body {
	padding: 28px 20px 24px 20px !important;
}

.st-dashboard-card.no-hover:hover {
	transform: none !important;
	box-shadow: none !important;
}

.formal-st-overview {
	display: grid;
	gap: 24px;
}

.formal-st-top-grid {
	display: grid;
	grid-template-columns: 420px minmax(0, 1fr);
	gap: 24px;
	align-items: stretch;
}

.formal-st-metrics {
	height: 100%;
}

.small-cards-grid {
    display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	grid-template-rows: repeat(2, minmax(0, 1fr));
    gap: 14px;
	width: 100%;
	min-height: 430px;
}
.small-card {
	width: 100%;
	height: 100%;
	min-height: 0;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid rgba(14, 75, 131, 0.12);
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;
    padding: 18px 18px 16px 18px;
    box-shadow: 0 14px 32px rgba(8, 43, 81, 0.06);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.small-card:hover {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    transform: translateY(-2px);
    box-shadow: 0 18px 36px rgba(8, 43, 81, 0.1);
}
.formal-metric-card-clickable {
	cursor: pointer;
}

.formal-metric-card-clickable:focus-visible {
	outline: 3px solid rgba(59, 130, 246, 0.28);
	outline-offset: 3px;
}
.formal-metric-kicker {
	margin-bottom: 0.9rem;
	color: #6d8296;
	font-size: 0.72rem;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
}
.small-card .card-value {
    font-size: 2.3rem;
    font-weight: 800;
    color: #06306e;
    letter-spacing: -0.04em;
}
.small-card .card-label {
    margin-top: 0.45rem;
    font-size: 1rem;
    color: #16324f;
    text-align: left;
    font-weight: 700;
}
.formal-metric-note {
	margin-top: auto;
	padding-top: 0.95rem;
	color: #6c7f91;
	font-size: 0.86rem;
	line-height: 1.45;
}
#card2 .card-value {
    color: #ff4d4f;
}

#card1 { border-top: 4px solid #2e6fd8; }
#card2 { border-top: 4px solid #dd6378; }
#card3 { border-top: 4px solid #1db2a6; }
#card4 { border-top: 4px solid #d6a638; }

.formal-st-chart-stack,
.formal-third-row-grid,
.formal-second-row-wrap,
.formal-mini-panel-group {
	display: grid;
	gap: 24px;
}

.formal-st-chart-stack {
	grid-template-columns: 1fr;
	height: 100%;
}

.formal-second-row-wrap {
	grid-template-columns: minmax(0, 1fr) 260px 260px;
	width: 100%;
	max-width: 1480px;
	align-items: start;
}

.formal-third-row-grid {
	grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.85fr);
	width: 100%;
	max-width: 100%;
	justify-content: stretch;
	align-items: stretch;
}

.formal-insight-row {
	margin-top: 24px;
}

.formal-insight-grid {
	display: grid;
	grid-template-columns: minmax(0, 1.28fr) minmax(300px, 0.86fr) minmax(300px, 0.86fr);
	gap: 24px;
	width: 100%;
	max-width: 100%;
	align-items: stretch;
}

.formal-linked-st-panels {
	display: grid;
	grid-template-columns: minmax(0, 1.06fr) minmax(340px, 0.94fr);
	gap: 0;
	min-width: 0;
	border: 1px solid rgba(14, 75, 131, 0.1);
	border-radius: 24px;
	background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,251,255,0.98) 100%);
	box-shadow: 0 16px 36px rgba(8, 43, 81, 0.08);
	overflow: hidden;
	position: relative;
}

.formal-mini-panel-group {
	grid-template-columns: 1fr 1fr;
}

.formal-dashboard-row {
	width: 100%;
	display: flex;
	justify-content: center;
}

.formal-chart-panel,
.formal-list-panel {
	border: 1px solid rgba(14, 75, 131, 0.1);
	border-radius: 20px;
	background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,251,255,0.98) 100%);
	box-shadow: 0 16px 36px rgba(8, 43, 81, 0.08);
	padding: 18px 18px 16px;
}

.formal-chart-panel-region,
.formal-chart-panel-doughnut,
.formal-list-panel {
	min-width: 0;
}

.formal-linked-st-panels .formal-chart-panel-doughnut,
.formal-linked-st-panels .formal-list-panel {
	border: 0;
	border-radius: 0;
	background: transparent;
	box-shadow: none;
	min-height: 100%;
	padding: 18px 18px 16px;
	position: relative;
}

.formal-linked-panel-start::after {
	content: '';
	position: absolute;
	top: 20px;
	right: 0;
	bottom: 20px;
	width: 1px;
	background: linear-gradient(180deg, rgba(14, 75, 131, 0) 0%, rgba(14, 75, 131, 0.12) 12%, rgba(14, 75, 131, 0.12) 88%, rgba(14, 75, 131, 0) 100%);
}

.formal-linked-panel-end {
	padding-left: 20px !important;
}

.formal-chart-panel-wide {
	height: 100%;
	display: flex;
	flex-direction: column;
}

.formal-chart-panel-docflow,
.formal-ranking-panel {
	min-width: 0;
}

.formal-chart-panel-mini {
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
}

.formal-chart-panel-yearly {
	align-self: start;
}

.formal-year-summary {
	display: grid;
	gap: 14px;
	margin-top: 14px;
	padding-top: 14px;
	border-top: 1px solid rgba(14, 75, 131, 0.08);
}

.formal-year-summary-grid {
	display: grid;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	gap: 12px;
}

.formal-year-stat {
	border-radius: 14px;
	padding: 12px 14px;
	background: #fff;
	border: 1px solid rgba(14, 75, 131, 0.08);
	box-shadow: 0 8px 18px rgba(8, 43, 81, 0.04);
}

.formal-year-stat-label {
	color: #6d8296;
	font-size: 0.76rem;
	font-weight: 800;
	letter-spacing: 0.05em;
	text-transform: uppercase;
	margin-bottom: 0.35rem;
}

.formal-year-stat-value {
	color: #16324f;
	font-size: 1.3rem;
	font-weight: 800;
	line-height: 1.15;
}

.formal-year-stat-meta {
	margin-top: 0.35rem;
	color: #62778d;
	font-size: 0.84rem;
	font-weight: 700;
	line-height: 1.45;
}

.formal-panel-header {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 1rem;
	margin-bottom: 0.9rem;
}

.formal-panel-header-centered {
	justify-content: center;
	text-align: center;
}

.formal-panel-header-list {
	align-items: flex-end;
}

.formal-panel-eyebrow {
	color: #6d8296;
	font-size: 0.72rem;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	margin-bottom: 0.3rem;
}

.formal-panel-title {
	color: #16324f;
	font-size: 1.05rem;
	font-weight: 800;
	line-height: 1.35;
}

.formal-panel-caption {
	color: #6c7f91;
	font-size: 0.82rem;
	font-weight: 600;
}

.formal-chart-canvas {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	min-height: 220px;
	border-radius: 16px;
	background: #fbfdff;
	border: 1px solid rgba(14, 75, 131, 0.06);
}

.formal-chart-canvas-large {
	min-height: 430px;
	flex: 1 1 auto;
}

.formal-chart-canvas-medium {
	min-height: 360px;
}

.formal-chart-canvas-docflow {
	min-height: 360px;
}

.formal-chart-canvas-mini {
	min-height: 250px;
}

.formal-share-summary {
	display: grid;
	gap: 14px;
	margin-top: 14px;
	padding-top: 14px;
	border-top: 1px solid rgba(14, 75, 131, 0.08);
}

.formal-share-metrics {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 12px;
}

.formal-share-stat {
	border-radius: 14px;
	padding: 12px 12px 10px;
	background: #fff;
	border: 1px solid rgba(14, 75, 131, 0.08);
	box-shadow: 0 8px 18px rgba(8, 43, 81, 0.04);
}

.formal-share-stat-teal {
	border-top: 3px solid rgb(75, 192, 192);
}

.formal-share-stat-rose {
	border-top: 3px solid rgb(255, 99, 132);
}

.formal-share-stat-blue {
	border-top: 3px solid rgb(54, 162, 235);
}

.formal-share-stat-gold {
	border-top: 3px solid rgb(255, 205, 86);
}

.formal-share-stat-label {
	color: #6d8296;
	font-size: 0.76rem;
	font-weight: 800;
	letter-spacing: 0.05em;
	text-transform: uppercase;
	margin-bottom: 0.35rem;
}

.formal-share-stat-value {
	color: #16324f;
	font-size: 1.45rem;
	font-weight: 800;
	line-height: 1.15;
}

.formal-share-stat-meta {
	margin-top: 0.35rem;
	color: #62778d;
	font-size: 0.85rem;
	font-weight: 700;
}

.formal-share-insight {
	padding: 12px 14px;
	border-radius: 14px;
	background: linear-gradient(180deg, #f8fbff 0%, #f2f7fd 100%);
	border: 1px solid rgba(14, 75, 131, 0.08);
}

.formal-share-insight-label {
	color: #6d8296;
	font-size: 0.74rem;
	font-weight: 800;
	letter-spacing: 0.06em;
	text-transform: uppercase;
	margin-bottom: 0.35rem;
}

.formal-share-insight-value {
	color: #16324f;
	font-size: 0.92rem;
	font-weight: 700;
	line-height: 1.45;
}

.formal-chart-canvas-region,
.formal-chart-canvas-doughnut {
	min-height: 380px;
}

.formal-chart-canvas-region canvas,
.formal-chart-canvas-doughnut > canvas:first-child {
	width: 100% !important;
	height: 100% !important;
}

.formal-chart-canvas-doughnut {
	overflow: hidden;
}

.formal-doughnut-stage {
	position: relative;
	width: min(100%, 420px);
	aspect-ratio: 1 / 1;
	margin: 0 auto;
	flex: 0 0 auto;
}

.formal-doughnut-stage canvas {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
}

.formal-doughnut-outer {
	z-index: 2;
	width: 100% !important;
	height: 100% !important;
	max-width: 100%;
	max-height: 100%;
}

.formal-doughnut-inner {
	z-index: 1;
	width: 58% !important;
	height: 58% !important;
	min-width: 205px;
	min-height: 205px;
	max-width: 270px;
	max-height: 270px;
}

@media (max-width: 640px) {
    .formal-chart-panel-doughnut,
    .formal-chart-canvas-doughnut,
    .formal-doughnut-stage {
        display: none !important;
    }
}

.formal-list-panel {
	display: flex;
	flex-direction: column;
	min-height: 420px;
}

.formal-ranking-list {
	display: grid;
	gap: 12px;
	min-height: 0;
}

.formal-ranking-item {
	display: grid;
	grid-template-columns: 50px minmax(0, 1fr) auto;
	gap: 12px;
	align-items: center;
	padding: 12px 14px;
	border: 1px solid rgba(14, 75, 131, 0.08);
	border-radius: 14px;
	background: #fff;
	box-shadow: 0 8px 20px rgba(8, 43, 81, 0.04);
}

.formal-ranking-rank {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 36px;
	height: 36px;
	border-radius: 999px;
	background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
	color: #1d4ed8;
	font-size: 0.88rem;
	font-weight: 800;
}

.formal-ranking-main {
	min-width: 0;
}

.formal-ranking-label {
	color: #16324f;
	font-size: 0.95rem;
	font-weight: 800;
	line-height: 1.35;
}

.formal-ranking-meta {
	margin-top: 0.2rem;
	color: #6b7f92;
	font-size: 0.84rem;
	font-weight: 600;
}

.formal-ranking-value {
	text-align: right;
	color: #0f766e;
	font-size: 0.95rem;
	font-weight: 800;
	white-space: nowrap;
}

.formal-doc-summary {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 12px;
	margin-top: 14px;
	padding-top: 14px;
	border-top: 1px solid rgba(14, 75, 131, 0.08);
}

.formal-doc-summary-item {
	padding: 12px 14px;
	border-radius: 14px;
	background: #fff;
	border: 1px solid rgba(14, 75, 131, 0.08);
	box-shadow: 0 8px 18px rgba(8, 43, 81, 0.04);
}

.formal-doc-summary-label {
	color: #6d8296;
	font-size: 0.75rem;
	font-weight: 800;
	letter-spacing: 0.05em;
	text-transform: uppercase;
	margin-bottom: 0.3rem;
}

.formal-doc-summary-value {
	color: #16324f;
	font-size: 0.94rem;
	font-weight: 800;
	line-height: 1.4;
}

.social-listing-body {
	padding: 20px 16px 16px !important;
}

.social-listing-toolbar {
	display: flex;
	justify-content: space-between;
	align-items: flex-end;
	gap: 18px;
	margin-bottom: 16px;
	padding-bottom: 14px;
	border-bottom: 1px solid rgba(148, 163, 184, 0.16);
	flex-wrap: wrap;
}

.social-listing-heading {
	min-width: 220px;
}

.social-listing-eyebrow {
	color: #6d8296;
	font-size: 0.74rem;
	font-weight: 800;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	margin-bottom: 0.25rem;
}

.social-listing-title {
	color: #16324f;
	font-size: 1rem;
	font-weight: 800;
	line-height: 1.4;
}

.social-listing-controls {
	display: flex;
	gap: 10px;
	align-items: center;
	flex-wrap: wrap;
	flex: 1 1 720px;
}

.social-listing-control {
	flex: 0 0 180px;
	min-width: 160px;
}

.social-listing-control-search {
	flex: 1 1 320px;
	min-width: 240px;
}

.social-listing-input,
.social-listing-select {
	min-height: 44px;
	border-radius: 12px;
	border: 1px solid rgba(148, 163, 184, 0.22);
	background: #fbfdff;
	box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
	color: #1f3b57;
	font-weight: 600;
}

.social-listing-summary {
	display: grid;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	gap: 12px;
	margin-bottom: 16px;
}

.social-listing-stat {
	padding: 12px 14px;
	border-radius: 14px;
	background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
	border: 1px solid rgba(148, 163, 184, 0.16);
	box-shadow: 0 8px 18px rgba(8, 43, 81, 0.04);
}

.social-listing-stat-label {
	color: #6d8296;
	font-size: 0.75rem;
	font-weight: 800;
	letter-spacing: 0.05em;
	text-transform: uppercase;
	margin-bottom: 0.3rem;
}

.social-listing-stat-value {
	color: #16324f;
	font-size: 1.28rem;
	font-weight: 800;
	line-height: 1.15;
}

.social-listing-scroll {
	overflow-y: auto;
	overflow-x: auto;
	-webkit-overflow-scrolling: touch;
	max-height: 440px;
	border: 1px solid rgba(148, 163, 184, 0.16);
	border-radius: 18px;
	background: #fff;
	box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
}

.social-listing-table {
	width: 100%;
	min-width: 960px;
	table-layout: fixed;
	border-collapse: separate;
	border-spacing: 0;
	margin: 0;
	background: #fff;
}

.social-listing-col-title {
	width: 42%;
}

.social-listing-col-province {
	width: 16%;
}

.social-listing-col-city {
	width: 16%;
}

.social-listing-col-status {
	width: 17%;
}

.social-listing-col-attachment {
	width: 9%;
}

.social-listing-table thead th {
	position: sticky;
	top: 0;
	z-index: 1;
	padding: 12px 14px;
	background: #f8fbff;
	color: #50657a;
	font-size: 0.78rem;
	font-weight: 800;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	border-bottom: 1px solid rgba(148, 163, 184, 0.16);
	white-space: nowrap;
	line-height: 1.3;
}

.social-listing-table tbody td {
	padding: 11px 12px;
	border-bottom: 1px solid rgba(148, 163, 184, 0.12);
	color: #1f3b57;
	font-size: 0.94rem;
	vertical-align: top;
	overflow: hidden;
}

.social-listing-table tbody tr:nth-child(even) {
	background: #fcfdff;
}

.social-listing-table tbody tr:hover {
	background: #f4f9ff;
}

.social-listing-title-cell {
	min-width: 0;
}

.social-listing-title-text {
	color: #16324f;
	font-weight: 700;
	line-height: 1.5;
	word-break: break-word;
}

.social-listing-location-text {
	color: #334155;
	font-weight: 600;
	word-break: break-word;
}

.social-listing-status-cell {
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
	gap: 6px;
	flex-wrap: nowrap;
	min-height: 56px;
}

.social-listing-pill {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 5px 10px;
	border-radius: 999px;
	font-size: 0.75rem;
	font-weight: 800;
	line-height: 1;
	white-space: nowrap;
}

.social-listing-pill-status-ongoing {
	background: #e8fbf2;
	color: #0f8c5c;
	border: 1px solid rgba(16, 185, 129, 0.18);
}

.social-listing-pill-status-dissolved {
	background: #fff1f2;
	color: #d7263d;
	border: 1px solid rgba(244, 63, 94, 0.16);
}

.social-listing-pill-type {
	background: #eff6ff;
	color: #1d4ed8;
	border: 1px solid rgba(59, 130, 246, 0.12);
}

.social-listing-pill-type-adopted {
	background: #fff9e8;
	color: #b7791f;
	border: 1px solid rgba(245, 158, 11, 0.16);
}

.social-listing-attach {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 8px;
	width: 100%;
}

.social-listing-action {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 34px;
	height: 34px;
	border-radius: 10px;
	border: 1px solid rgba(59, 130, 246, 0.15);
	background: #fff;
	color: #1d4ed8;
	box-shadow: 0 8px 18px rgba(8, 43, 81, 0.05);
}

.social-listing-action.social-listing-action-view {
	color: #0f766e;
	border-color: rgba(16, 185, 129, 0.18);
}

.social-listing-empty {
	padding: 18px;
	text-align: center;
	color: #64748b;
	font-weight: 700;
}

.social-listing-pagination-wrapper {
	text-align: center;
}

.social-listing-pagination {
	display: inline-flex; 
	justify-content: center;
	align-items: center;
	gap: 14px;
	background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
	border: 1px solid rgba(148, 163, 184, 0.16);
	border-radius: 16px;
	box-shadow: 0 8px 18px rgba(8, 43, 81, 0.04);
	margin: 16px auto 0;
	padding: 10px 12px;
	flex-wrap: wrap;
}

.social-listing-pagination-btn {
	border: none;
	background: linear-gradient(90deg, #06306e 0%, #124c9f 100%);
	color: #fff;
	font-weight: 800;
	border-radius: 10px;
	padding: 9px 20px;
	font-size: 0.98rem;
	box-shadow: 0 10px 24px rgba(8, 43, 81, 0.12);
	transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.social-listing-pagination-btn:disabled {
	background: #e2e8f0;
	color: #94a3b8;
	box-shadow: none;
	cursor: not-allowed;
}

.social-listing-pagination-btn:not(:disabled):hover {
	transform: translateY(-1px);
	box-shadow: 0 14px 28px rgba(8, 43, 81, 0.14);
}

.social-listing-pagination-indicator {
	color: #16324f;
	font-size: 0.96rem;
	font-weight: 800;
	min-width: 120px;
	text-align: center;
}

.st-summary-modal-toolbar {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 1rem;
	margin-bottom: 14px;
	padding-bottom: 12px;
	border-bottom: 1px solid rgba(14, 75, 131, 0.08);
	flex-wrap: wrap;
}

.st-summary-modal-meta {
	color: #64748b;
	font-size: 0.92rem;
	font-weight: 700;
}

.st-summary-table-wrap {
	max-height: 62vh;
	overflow: auto;
	border: 1px solid rgba(14, 75, 131, 0.08);
	border-radius: 14px;
	background: #fff;
}

.st-summary-table {
	width: 100%;
	min-width: 860px;
	border-collapse: separate;
	border-spacing: 0;
}

.st-summary-table thead th {
	position: sticky;
	top: 0;
	background: #f8fbff;
	z-index: 1;
	color: #50657a;
	font-size: 0.78rem;
	font-weight: 800;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	padding: 12px 14px;
	border-bottom: 1px solid rgba(14, 75, 131, 0.08);
}

.st-summary-table tbody td {
	padding: 12px 14px;
	border-bottom: 1px solid rgba(14, 75, 131, 0.06);
	color: #1f3b57;
	font-size: 0.94rem;
	vertical-align: top;
}

.st-summary-table tbody tr:hover {
	background: #f8fbff;
}

.st-summary-empty {
	margin: 0;
	padding: 16px 18px;
	border-radius: 14px;
	background: #f8fbff;
	color: #64748b;
	font-weight: 700;
}

.formal-st-category-list {
	flex: 1 1 auto;
	min-height: 0;
}

.formal-st-category-list .st-cat-list {
	list-style: none;
	margin: 0;
	padding: 0 4px 0 0;
	max-height: 320px;
	overflow-y: auto;
}

.formal-st-category-list .st-cat-list-item {
	display: flex;
	align-items: flex-start;
	gap: 0.8rem;
	margin-bottom: 0.8rem;
	padding: 0.9rem 0.85rem;
	border: 1px solid rgba(14, 75, 131, 0.08);
	border-radius: 14px;
	background: #fff;
	box-shadow: 0 8px 20px rgba(8, 43, 81, 0.04);
	cursor: pointer;
	transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.formal-st-category-list .st-cat-list-item:hover {
	transform: translateY(-1px);
	box-shadow: 0 12px 24px rgba(8, 43, 81, 0.08);
}

.formal-st-category-list .st-cat-list-swatch {
	flex: 0 0 16px;
	width: 16px;
	height: 16px;
	border-radius: 4px;
	margin-top: 0.15rem;
	box-shadow: 0 2px 6px rgba(15, 23, 42, 0.16);
}

.formal-st-category-list .st-cat-list-metric {
	flex: 0 0 88px;
	color: #0f766e;
	font-weight: 800;
	font-size: 0.94rem;
	line-height: 1.4;
}

.formal-st-category-list .st-cat-list-label {
	flex: 1 1 auto;
	color: #334155;
	font-size: 0.94rem;
	line-height: 1.5;
	white-space: pre-line;
	word-break: break-word;
}

.formal-st-category-list .st-cat-pagination {
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 0.7rem;
	margin-top: 0.85rem;
}

.formal-st-category-list .st-cat-page {
	color: #556b81;
	font-size: 0.85rem;
	font-weight: 700;
}

.formal-st-category-list .st-cat-page-btn {
	padding: 0.48rem 0.95rem;
	border-radius: 10px;
	border: 1px solid rgba(14, 75, 131, 0.12);
	background: #fff;
	color: #164a84;
	font-weight: 700;
	box-shadow: 0 8px 18px rgba(8, 43, 81, 0.05);
	transition: background 0.15s ease, border-color 0.15s ease;
}

.formal-st-category-list .st-cat-page-btn:disabled {
	opacity: 0.5;
	box-shadow: none;
}

.formal-st-category-list .st-cat-page-btn:not(:disabled):hover {
	background: #f5f9ff;
	border-color: rgba(14, 75, 131, 0.22);
}

</style>
					</div>
				</div>
			</div>
		</div>
		</div>
<script>
								document.addEventListener('DOMContentLoaded', function(){
									try {
										const yearRow = document.querySelector('.year-of-moa-card')?.closest('.row.mt-4');
										const summaryHeader = Array.from(document.querySelectorAll('.card-header')).find(h => h.textContent && h.textContent.trim() === 'SUMMARY OF ST TITLES');
										const summaryRow = summaryHeader ? summaryHeader.closest('.row.mt-4') : null;
										if (yearRow && summaryRow && summaryRow.parentNode) {
											summaryRow.parentNode.insertBefore(yearRow, summaryRow);
										}
									} catch (e) {  }
								});
							</script>


		<style>
		.st-title-listing-card {
			max-width: 98vw;
			width: 100%;
			margin: 0 auto;
			box-shadow: 0 2px 12px rgba(16, 174, 181, 0.10);
		}
		.st-title-listing-card:hover {
			transform: none !important;
			box-shadow: 0 2px 12px rgba(16, 174, 181, 0.10) !important;
		}
		.st-title-listing-table {
			table-layout: fixed;
			width: 100%;
			min-width: 900px;
		}
		.st-title-listing-table th, .st-title-listing-table td {
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
		.st-title-listing-scroll {
			max-height: 420px;
			overflow-y: auto;
			overflow-x: auto;
			background: #fff;
			border-radius: 10px;
		}
		.st-title-listing-pagination {
			background: #f8fafc;
			border-radius: 8px;
			box-shadow: 0 1px 4px #b2ebf2;
			margin-top: 12px;
			padding: 8px 0 2px 0;
		}
		.st-map-region-list {
			background: #fafdff;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(16, 174, 181, 0.07);
			padding: 12px 12px 8px 12px;
			max-height: none;
			overflow-y: visible;
		}
		.st-map-region-list-title {
			font-weight: 600;
			color: #0f172a;
			margin-bottom: 8px;
			font-size: 0.96rem;
		}
		.st-map-region-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 6px 6px;
			border-radius: 8px;
			background: #ffffff;
			box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
			margin-bottom: 6px;
			font-size: 0.9rem;
			border: 1px solid transparent;
			transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
		}
		.st-map-region-row:hover,
		.st-map-region-row.is-active {
			transform: translateX(4px);
			background: linear-gradient(135deg, #ffffff 0%, #f0fbfc 100%);
			border-color: rgba(16, 174, 181, 0.25);
			box-shadow: 0 10px 24px rgba(16, 174, 181, 0.14);
		}
		.st-map-region-row-main {
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.st-map-region-color-dot {
			width: 14px;
			height: 14px;
			border-radius: 50%;
			box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
		}
		.st-map-region-label {
			font-weight: 500;
			color: #0f172a;
		}
		.st-map-region-row:hover .st-map-region-label,
		.st-map-region-row.is-active .st-map-region-label {
			color: #0b7285;
		}
		.st-map-region-count {
			min-width: 34px;
			text-align: right;
			font-weight: 600;
			color: #10aeb5;
		}
		.st-map-region-row:hover .st-map-region-count,
		.st-map-region-row.is-active .st-map-region-count {
			color: #0f766e;
		}
		@media (max-width: 1400px) {
			.st-title-listing-card { max-width: 99vw; }
			.st-title-listing-table { min-width: 900px; }
		}
		.year-chart-wrap { order: 1; margin-right: 0;}
.year-filter-wrap { order: 2; margin-left: 24px; align-self: flex-start; }

.year-of-moa-card .card-body { position: relative; }
@media (min-width: 992px) {
	.year-of-moa-card .year-filter-wrap { position: absolute !important; right: 24px; top: 50%; transform: translateY(-50%); width: 320px; z-index: 6; }
}

@media (max-width: 991px) {
			.st-title-listing-card { max-width: 100vw; }
			.st-title-listing-table { min-width: 520px; }
			.year-filter-wrap { position: static !important; transform: none !important; right: auto !important; top: auto !important; flex: 0 0 100% !important; max-width: 100% !important; width: 100% !important; margin-top: 16px; order: 2; margin-left: 0; }
			.year-chart-wrap { order: 1; width:100% !important; flex: 1 1 100% !important; min-width: 0;}
			.year-filter-wrap .card { min-height: auto; }
			.formal-st-top-grid,
			.formal-second-row-wrap,
			.formal-third-row-grid,
			.formal-mini-panel-group,
			.formal-linked-st-panels {
				grid-template-columns: 1fr !important;
			}
			.formal-insight-grid {
				grid-template-columns: 1fr !important;
			}
			.formal-share-metrics {
				grid-template-columns: 1fr 1fr;
			}
			.social-listing-summary {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
			.formal-year-summary-grid {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
			.formal-third-row-grid,
			.formal-second-row-wrap {
				max-width: 100%;
			}
			.formal-linked-panel-start::after {
				display: none;
			}
			.formal-linked-panel-end {
				padding-left: 18px !important;
			}
			.formal-st-metrics {
				justify-content: center;
				height: auto;
			}
		}
		@media (max-width: 767px) {
			.st-title-listing-card { max-width: 100vw; }
			.st-title-listing-table { min-width: 600px; font-size: 0.95em; }
			.small-cards-grid {
				grid-template-columns: 1fr;
				grid-template-rows: none;
			}
			.small-card {
				width: 100%;
				height: auto;
				min-height: 168px;
			}
			.small-cards-grid {
				min-height: 0;
			}
			.formal-share-metrics {
				grid-template-columns: 1fr;
			}
			.formal-doc-summary {
				grid-template-columns: 1fr;
			}
			.formal-ranking-item {
				grid-template-columns: 42px minmax(0, 1fr);
			}
			.formal-ranking-value {
				grid-column: 2;
				text-align: left;
			}
			.formal-year-summary-grid {
				grid-template-columns: 1fr;
			}
			.social-listing-controls {
				flex-direction: column;
				align-items: stretch;
			}
			.social-listing-control,
			.social-listing-control-search {
				flex: 1 1 auto;
				min-width: 0;
			}
			.social-listing-summary {
				grid-template-columns: 1fr;
			}
			.social-listing-table {
				min-width: 100%;
			}
			.st-summary-table {
				min-width: 680px;
			}
		}

		</style>

		<style>
		.st-dashboard-header-fullwidth {
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			border-radius: 24px 24px 0 0;
			z-index: 2;
			margin-bottom: 56px !important;
			box-sizing: border-box;
		}
		.st-dashboard-container {
				padding-top: 200px !important;
		}
		@media print {
			.st-dashboard-container {
				padding-top: 0 !important;
			}
			.row {
				margin-top: 4mm !important;
			}
		}
		.st-region-modal-backdrop {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(15, 23, 42, 0.52);
			backdrop-filter: blur(5px);
			-webkit-backdrop-filter: blur(5px);
			z-index: 1980;
		}
		.st-region-modal-dialog {
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
			border-radius: 24px;
			width: min(94vw, 980px);
			max-height: 84vh;
			overflow: hidden;
			box-shadow: 0 28px 90px rgba(15, 23, 42, 0.28);
			border: 1px solid rgba(148, 163, 184, 0.16);
			z-index: 1985;
			display: flex;
			flex-direction: column;
		}
		.st-region-modal-header {
			padding: 18px 22px;
			border-bottom: 0;
			display: flex;
			align-items: center;
			justify-content: space-between;
			background: linear-gradient(135deg, #06306e 0%, #124c9f 100%);
			color: #ffffff;
		}
		.st-region-modal-header h5 {
			margin: 0;
			font-weight: 700;
			font-size: 1.18rem;
			line-height: 1.35;
		}
		.st-region-modal-close {
			border: none;
			background: transparent;
			color: #ffffff;
			font-size: 1.4rem;
			line-height: 1;
			cursor: pointer;
		}
		.st-region-modal-body {
			padding: 18px 20px 20px;
			background: transparent;
			overflow-y: auto;
		}
		.st-title-modal-toolbar {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 1rem;
			flex-wrap: wrap;
			padding: 0 2px 14px;
			margin-bottom: 14px;
			border-bottom: 1px solid rgba(148, 163, 184, 0.16);
		}
		.st-title-modal-count {
			color: #0f766e;
			font-size: 0.92rem;
			font-weight: 800;
		}
		.st-title-modal-subtitle {
			color: #64748b;
			font-size: 0.9rem;
			font-weight: 600;
		}
		.st-title-modal-table-wrap {
			max-height: 60vh;
			overflow: auto;
			border: 1px solid rgba(148, 163, 184, 0.16);
			border-radius: 18px;
			background: #fff;
			box-shadow: inset 0 1px 0 rgba(255,255,255,0.75);
		}
		.st-title-modal-table {
			width: 100%;
			min-width: 860px;
			border-collapse: separate;
			border-spacing: 0;
		}
		.st-title-modal-table thead th {
			position: sticky;
			top: 0;
			z-index: 1;
			padding: 12px 14px;
			background: #f8fbff;
			color: #50657a;
			font-size: 0.78rem;
			font-weight: 800;
			letter-spacing: 0.04em;
			text-transform: uppercase;
			border-bottom: 1px solid rgba(148, 163, 184, 0.16);
		}
		.st-title-modal-table tbody td {
			padding: 12px 14px;
			border-bottom: 1px solid rgba(148, 163, 184, 0.12);
			color: #1f3b57;
			font-size: 0.94rem;
			vertical-align: top;
		}
		.st-title-modal-table tbody tr:nth-child(even) {
			background: #fcfdff;
		}
		.st-title-modal-table tbody tr:hover {
			background: #f4f9ff;
		}
		.st-title-modal-empty {
			margin: 0;
			padding: 14px 16px;
			border-radius: 14px;
			background: #f8fbff;
			color: #64748b;
			font-weight: 700;
		}
		.st-title-modal-attach {
			display: inline-flex;
			gap: 8px;
			align-items: center;
			justify-content: center;
		}
		.st-title-modal-action {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 34px;
			height: 34px;
			border-radius: 10px;
			border: 1px solid rgba(59, 130, 246, 0.15);
			background: #fff;
			color: #1d4ed8;
			box-shadow: 0 8px 18px rgba(8, 43, 81, 0.05);
		}
		.st-title-modal-action.st-title-modal-view {
			color: #0f766e;
			border-color: rgba(16, 185, 129, 0.18);
		}
		@media (max-width: 767px) {
			.st-region-modal-dialog {
				width: min(96vw, 980px);
				max-height: 88vh;
			}
			.st-region-modal-header {
				padding: 16px 18px;
			}
			.st-region-modal-body {
				padding: 16px;
			}
			.st-title-modal-table {
				min-width: 720px;
			}
		}
		.st-region-title-item {
			padding: 8px 10px;
			margin-bottom: 6px;
			border-radius: 8px;
			background: #ffffff;
			box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
		}
		.st-region-title-item-main {
			font-weight: 600;
			color: #0f172a;
		}
		.st-region-title-item-sub {
			font-size: 0.86rem;
			color: #64748b;
		}
		</style>
			@if(!auth()->check())
			<div id="guestFilterDock" class="guest-filter-dock open guest-filter-initial-open">
				<div id="guestFloatingFilter" class="year-filter-wrap guest-filter-panel">
					<div class="card st-dashboard-card filter-modal-panel">
						<div class="guest-filter-header">
							<div class="guest-filter-header-top">
								<div>
									<div class="guest-filter-kicker">Dashboard Filters</div>
									<div class="guest-filter-title">Filter By Location &amp; Year</div>
								</div>
								<button type="button" class="guest-filter-close" aria-label="Close guest filters" onclick="return window.closeGuestFilterUi && window.closeGuestFilterUi(event)">&times;</button>
							</div>
							<div class="guest-filter-subtitle">Refine the dashboard by region, year, province, and city or municipality.</div>
						</div>
						<div class="card-body guest-filter-body">
							<form method="GET" action="" class="w-100 d-flex flex-column">
								<div class="guest-filter-grid">
									<div class="guest-filter-field guest-filter-field-wide">
										<label for="region-select-modal" class="st-filter-label">Region</label>
										<select id="region-select-modal" name="region[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Regions" style="width:100%;">
											@foreach($regions as $region)
											@if (stripos($region, 'Data CY 2020-2022') === false)
											<option value="{{ $region }}" {{ collect(request('region'))->contains($region) ? 'selected' : '' }}>{{ $region }}</option>
											@endif
											@endforeach
										</select>
									</div>

									<div class="guest-filter-field guest-filter-field-wide">
										<label for="year-select-modal" class="st-filter-label">Year</label>
										<select id="year-select-modal" name="year_of_moa[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Years" style="width:100%;">
											@foreach($years as $year)
											<option value="{{ $year }}" {{ collect(request('year_of_moa'))->contains($year) ? 'selected' : '' }}>{{ $year }}</option>
											@endforeach
										</select>
									</div>

									<div class="guest-filter-field guest-filter-field-wide">
										<label for="province-select-modal" class="st-filter-label">Province</label>
										<select id="province-select-modal" name="province[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Provinces" style="width:100%;">
											@foreach($provinces as $province)
											<option value="{{ $province }}" {{ collect(request('province'))->contains($province) ? 'selected' : '' }}>{{ $province }}</option>
											@endforeach
										</select>
									</div>

									<div class="guest-filter-field guest-filter-field-wide">
										<label for="municipality-select-modal" class="st-filter-label">City/Municipality</label>
										<select id="municipality-select-modal" name="municipality[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Cities/Municipalities" style="width:100%;">
											@foreach($municipalities as $municipality)
											<option value="{{ $municipality }}" {{ collect(request('municipality'))->contains($municipality) ? 'selected' : '' }}>{{ $municipality }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="guest-filter-actions">
									<button type="submit" class="btn guest-filter-submit">Apply Filters</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			@endif
		</div> 

		<div id="region-titles-modal" style="display:none;">
			<div class="st-region-modal-backdrop" onclick="window.closeRegionTitlesModal && window.closeRegionTitlesModal()"></div>
			<div class="st-region-modal-dialog">
				<div class="st-region-modal-header">
					<h5 id="region-titles-modal-title">Region</h5>
					<button type="button" class="st-region-modal-close" onclick="window.closeRegionTitlesModal && window.closeRegionTitlesModal()">&times;</button>
				</div>
				<div id="region-titles-modal-body" class="st-region-modal-body"></div>
			</div>
		</div>

		<div id="st-title-modal" style="display:none;">
			<div class="st-region-modal-backdrop" onclick="window.closeStTitleModal && window.closeStTitleModal()"></div>
			<div class="st-region-modal-dialog">
				<div class="st-region-modal-header">
					<h5 id="st-title-modal-title">ST Title</h5>
					<button type="button" class="st-region-modal-close" onclick="window.closeStTitleModal && window.closeStTitleModal()">&times;</button>
				</div>
				<div id="st-title-modal-body" class="st-region-modal-body"></div>
			</div>
		</div>

		<div id="st-summary-modal" style="display:none;">
			<div class="st-region-modal-backdrop" onclick="window.closeStSummaryModal && window.closeStSummaryModal()"></div>
			<div class="st-region-modal-dialog" style="max-width: 1120px;">
				<div class="st-region-modal-header">
					<h5 id="st-summary-modal-title">ST Listing</h5>
					<button type="button" class="st-region-modal-close" onclick="window.closeStSummaryModal && window.closeStSummaryModal()">&times;</button>
				</div>
				<div id="st-summary-modal-body" class="st-region-modal-body"></div>
			</div>
		</div>

		<div id="st-attachment-modal" style="display:none;">
			<div class="st-region-modal-backdrop" onclick="window.closeStAttachmentModal && window.closeStAttachmentModal()"></div>
			<div class="st-region-modal-dialog" style="max-width: 90%; height: 85vh; display:flex; flex-direction:column;">
				<div class="st-region-modal-header" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
					<h5 id="st-attachment-modal-title" style="margin:0;">Attachment</h5>
					<div class="d-flex flex-row align-items-center gap-3">
						<span id="st-attachment-modal-uploader" style="font-size:0.8rem; color:#e0f2fe; opacity:0.9; display:none;">Uploaded by:</span>
						<button type="button" class="st-region-modal-close" onclick="window.closeStAttachmentModal && window.closeStAttachmentModal()">&times;</button>
					</div>
				</div>
				<div class="st-region-modal-body" style="flex:1; padding:0;">
					<iframe id="st-attachment-frame" src="" style="width:100%; height:100%; border:none;" title="ST Attachment PDF"></iframe>
				</div>
			</div>
		</div>

		<div id="st-details-modal" style="display:none;">
			<div class="st-region-modal-backdrop" onclick="window.closeStDetailsModal && window.closeStDetailsModal()"></div>
			<div class="st-region-modal-dialog" style="max-width: 980px;">
				<div class="st-region-modal-header">
					<h5 id="st-details-modal-title">ST Details</h5>
					<button type="button" class="st-region-modal-close" onclick="window.closeStDetailsModal && window.closeStDetailsModal()">&times;</button>
				</div>
				<div id="st-details-modal-body" class="st-region-modal-body"></div>
			</div>
		</div>

		<style>
		body.st-details-open #region-titles-modal .st-region-modal-dialog,
		body.st-details-open #st-summary-modal .st-region-modal-dialog,
		body.st-details-open #st-title-modal .st-region-modal-dialog {
			filter: blur(10px) grayscale(0.12) brightness(0.92) !important;
			transition: filter 0.14s linear !important;
			pointer-events: none !important;
			user-select: none !important;
			opacity: 0.98 !important;
		}

		body.st-details-open #region-titles-modal .st-region-modal-backdrop,
		body.st-details-open #st-summary-modal .st-region-modal-backdrop,
		body.st-details-open #st-title-modal .st-region-modal-backdrop {
			background: rgba(11,37,64,0.48) !important;
		}
		</style>



        <div class="slider-modal" id="sliderModal" style="display:none;">
            <div class="slider-modal-overlay" id="sliderModalOverlay"></div>
            <div class="slider-modal-content" id="sliderModalContent" style="position:relative; flex-direction:column; align-items:center; justify-content:flex-start;">
                <div class="slider-modal-title" aria-hidden="false">
                    <h3 id="sliderModalTitle" style="margin:0; padding:6px 10px; font-weight:600; font-size:18px; color:#fff;">
                        <span class="typed-text line1" aria-hidden="true"></span>
                        <span class="typed-text line2" aria-hidden="true"></span>
                        <span class="caret" aria-hidden="true">|</span>
                    </h3>
                </div>
                <div class="slider-modal-viewport" id="sliderModalViewport" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:1; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <img id="sliderModalImg" src="" alt="" style="width:100%; height:100%; object-fit:contain; border-radius:18px; display:block; pointer-events:auto;" />
                </div>
                <div class="slider-province-wrap" id="sliderProvinceWrap" role="complementary" aria-label="Region panel" aria-hidden="false">
                    <div class="province-panel-header">Region panel</div>
                    <div class="province-panel-body"></div>
                </div>
            </div>
        </div>
		
		
</style>

	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@1.3.0/dist/chartjs-chart-matrix.min.js"></script>
	<script>
	(function(){
		window.__stb_resizeAllCharts = function resizeAllCharts(){
			try{
				document.querySelectorAll('.formal-chart-panel.d-none').forEach(function(el){ el.classList.remove('d-none'); el.style.display = 'block'; });
				document.querySelectorAll('canvas').forEach(function(c){
					try{ c.removeAttribute('width'); c.removeAttribute('height'); }catch(e){}
					try{ c.style.width = '100%'; c.style.height = 'auto'; }catch(e){}
					try{
						if(window.Chart){
							var chart = (Chart.getChart ? Chart.getChart(c) : (Chart.instances && Object.values(Chart.instances).find(function(i){ return i && i.canvas === c; })));
							if(chart && typeof chart.resize === 'function') chart.resize();
							if(chart && typeof chart.update === 'function') chart.update();
						}
					}catch(e){}
				});
			}catch(e){}
		}

		document.addEventListener('DOMContentLoaded', function(){
			setTimeout(function(){ try{ window.__stb_resizeAllCharts(); }catch(e){} }, 600);
			setTimeout(function(){ try{ window.__stb_resizeAllCharts(); }catch(e){} }, 1400);
			setTimeout(function(){ try{ window.__stb_resizeAllCharts(); }catch(e){} }, 2600);

			var observer = new MutationObserver(function(mutations){
				mutations.forEach(function(m){
					if(m.removedNodes && m.removedNodes.length){
						m.removedNodes.forEach(function(n){
							if(n && (n.id === 'loading-overlay' || n.id === 'ph-map-loading')){
								setTimeout(function(){ try{ window.__stb_resizeAllCharts(); }catch(e){} }, 250);
							}
						});
					}
				});
			});
			observer.observe(document.body, { childList: true, subtree: true });
		});
	})();
	</script>
	<script>
	(function() {
		const perPage = 10;
		let currentPage = 1;
		const allData = window.fullListingData || [];
		let data = allData.slice();
		let currentSearchTerm = '';
		let currentStatusFilter = '';
		let currentAdoptFilter = '';

		function escapeAttr(str) {
			return (str || '').toString().replace(/&/g, '&amp;').replace(/"/g, '&quot;');
		}
		function escapeHtml(str) {
			return (str || '').toString()
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;');
		}
		function truthy(v) {
			if (typeof v === 'boolean') return v;
			const s = String(v || '').toLowerCase().trim();
			if (!s) return false;
			if (s === '0' || s === 'false' || s === 'no') return false;
			return true;
		}

		const headers = window.fullListingHeaders || [];
		const lower = h => (h || '').toString().toLowerCase();
		const idxOngoing = headers.findIndex(h => lower(h).includes('ongoing'));
		const idxDissolved = headers.findIndex(h => {
			const lh = lower(h);
			return lh.includes('dissolved') || lh.includes('inactive');
		});
		const hasStatusMark = v => {
			if (typeof v === 'boolean') return v;
			if (v == null) return false;
			const s = String(v).trim();
			if (!s || s === '0') return false;
			if (!isNaN(s)) return Number(s) !== 0;
			return true;
		};
		function deriveStatus(row) {
			let st = (row.status || '').toString().toLowerCase();
			if (!st && idxOngoing !== -1) {
				const cell = row.row && row.row[idxOngoing];
				if (hasStatusMark(cell)) st = 'ongoing';
			}
			if (!st && idxDissolved !== -1) {
				const cell = row.row && row.row[idxDissolved];
				if (hasStatusMark(cell)) st = 'dissolved';
			}
			if (st.includes('ongoing') || st === 'on going') return 'ongoing';
			if (st.includes('dissolved') || st.includes('inactive') || st.includes('completed')) return 'dissolved';
			return '';
		}

		function applyFilters() {
			const term = (currentSearchTerm || '').trim().toLowerCase();
			const status = (currentStatusFilter || '').trim().toLowerCase();
			const adopt = (currentAdoptFilter || '').trim().toLowerCase();
			data = allData.filter(r => {
				const titleMatch = !term || (r.title || '').toLowerCase().includes(term);
				if (!titleMatch) return false;
				if (status) {
					const rowStatus = deriveStatus(r);
					if (status === 'ongoing' && rowStatus !== 'ongoing') return false;
					if (status === 'dissolved' && rowStatus !== 'dissolved') return false;
				}
				if (adopt) {
					if (adopt === 'replicated' && !truthy(r.with_replicated)) return false;
					if (adopt === 'adopted' && !truthy(r.with_adopted)) return false;
				}
				return true;
			});
			currentPage = 1;
		}

		document.addEventListener('DOMContentLoaded', () => {
			const inp = document.getElementById('title-listing-search');
			if (inp) {
				inp.addEventListener('input', function() {
					currentSearchTerm = this.value;
					applyFilters();
					renderTable(currentPage);
				});
			}
			const statusSel = document.getElementById('title-listing-status-filter');
			if (statusSel) {
				statusSel.addEventListener('change', function() {
					currentStatusFilter = this.value || '';
					applyFilters();
					renderTable(currentPage);
				});
			}
			const adoptSel = document.getElementById('title-listing-adopt-filter');
			if (adoptSel) {
				adoptSel.addEventListener('change', function() {
					currentAdoptFilter = this.value || '';
					applyFilters();
					renderTable(currentPage);
				});
			}
			applyFilters();
			renderTable(currentPage);
		});

		function renderTable(page) {
			const start = (page - 1) * perPage;
			const end = start + perPage;
			const pageData = data.slice(start, end);
			const filteredOngoing = data.filter(row => deriveStatus(row) === 'ongoing').length;
			const filteredDissolved = data.filter(row => deriveStatus(row) === 'dissolved').length;
			const filteredReplicated = data.filter(row => truthy(row.with_replicated)).length;
			const filteredAdopted = data.filter(row => truthy(row.with_adopted)).length;
			const totalPages = Math.ceil(data.length / perPage);

			let html = '<div class="social-listing-summary">';
			html += '<div class="social-listing-stat"><div class="social-listing-stat-label">Filtered Results</div><div class="social-listing-stat-value">' + data.length + '</div></div>';
			html += '<div class="social-listing-stat"><div class="social-listing-stat-label">Ongoing</div><div class="social-listing-stat-value">' + filteredOngoing + '</div></div>';
			html += '<div class="social-listing-stat"><div class="social-listing-stat-label">Inactive</div><div class="social-listing-stat-value">' + filteredDissolved + '</div></div>';
			html += '<div class="social-listing-stat"><div class="social-listing-stat-label">With Adoption / Replication</div><div class="social-listing-stat-value">' + Math.max(filteredReplicated, filteredAdopted) + '</div></div>';
			html += '</div>';

			html += '<div class="social-listing-scroll">';
			html += '<table class="social-listing-table">';
			html += '<colgroup>' +
				'<col class="social-listing-col-title">' +
				'<col class="social-listing-col-province">' +
				'<col class="social-listing-col-city">' +
				'<col class="social-listing-col-status">' +
				'<col class="social-listing-col-attachment">' +
			'</colgroup>';
			html += '<thead><tr>' +
				'<th>Title</th>' +
				'<th>Province</th>' +
				'<th>City/Municipality</th>' +
				'<th class="text-center">Status</th>' +
				'<th class="text-center" style= "font-size: 0.7rem">Attachment</th>' +
			'</tr></thead><tbody>';
			if (pageData.length === 0) {
				html += '<tr><td colspan="5" class="social-listing-empty">No data found.</td></tr>';
			} else {
				pageData.forEach(row => {
					const title = row.title || '';
					const province = row.province || '';
					const municipality = row.municipality || '';
					const stStatus = deriveStatus(row);
					const isReplicated = truthy(row.with_replicated);
					const isAdopted = truthy(row.with_adopted);
					let primaryLabel = '';
					let statusClass = '';
					if (stStatus === 'ongoing') {
						primaryLabel = 'Ongoing';
						statusClass = 'social-listing-pill social-listing-pill-status-ongoing';
					} else if (stStatus === 'dissolved') {
						primaryLabel = 'Inactive';
						statusClass = 'social-listing-pill social-listing-pill-status-dissolved';
					}
					const extraParts = [];
					if (isReplicated) {
						extraParts.push('<span class="social-listing-pill social-listing-pill-type">Replicated</span>');
					}
					if (isAdopted) {
						extraParts.push('<span class="social-listing-pill social-listing-pill-type social-listing-pill-type-adopted">Adopted</span>');
					}
					const attachmentUrl = row.attachment_url || '';
					const uploadedBy = row.attachment_uploaded_by || '';
					let attachmentCell = '';
					if (attachmentUrl) {
						const safeTitle = escapeAttr(title);
						const safeUploader = escapeAttr(uploadedBy);
						attachmentCell = `
							<div class="social-listing-attach">
								<button type="button" class="social-listing-action social-listing-action-view st-attachment-view-btn" data-url="${attachmentUrl}" data-title="${safeTitle}" data-uploader="${safeUploader}" title="View attachment">
									<i class="bi bi-eye"></i>
								</button>
								<a href="${attachmentUrl}" class="social-listing-action" title="Download attachment" target="_blank" download>
									<i class="bi bi-download"></i>
								</a>
							</div>`;
					}

					html += `<tr>
						<td class="social-listing-title-cell" title="${escapeAttr(title)}"><div class="social-listing-title-text">${escapeHtml(title)}</div></td>
						<td title="${escapeAttr(province)}"><div class="social-listing-location-text">${escapeHtml(province)}</div></td>
						<td title="${escapeAttr(municipality)}"><div class="social-listing-location-text">${escapeHtml(municipality)}</div></td>
						<td class="text-center"><div class="social-listing-status-cell">${primaryLabel ? `<span class="${statusClass}">${primaryLabel}</span>` : ''}${extraParts.join('')}</div></td>
						<td class="text-center">${attachmentCell}</td>
					</tr>`;
				});
			}
					if (pageData.length < perPage) {
						for (let i = pageData.length; i < perPage; i++) {
							html += '<tr><td colspan="5" class="social-listing-empty">&nbsp;</td></tr>';
						}
					}
					html += '</tbody></table></div>';
					html += '<div class="social-listing-pagination-wrapper">';
					html += '<div class="social-listing-pagination">';
					html += `<button class="social-listing-pagination-btn" ${page === 1 ? 'disabled' : ''} onclick="changePage(${page - 1})">&#8592; Prev</button>`;
					html += `<span class="social-listing-pagination-indicator">Page ${page} of ${totalPages}</span>`;
					html += `<button class="social-listing-pagination-btn" ${page === totalPages ? 'disabled' : ''} onclick="changePage(${page + 1})">Next &#8594;</button>`;
					html += '</div>';
					html += '</div>';

			document.getElementById('title-listing-table-container').innerHTML = html;
			try {
				const container = document.getElementById('title-listing-table-container');
				const table = container.querySelector('.social-listing-table');
				if (table) {
					const tbody = table.querySelector('tbody');
					if (tbody) {
						const trs = Array.from(tbody.querySelectorAll('tr')).filter(tr => !tr.classList.contains('social-listing-empty'));
						trs.forEach(function(tr, i) {
							const idx = i;
							tr.tabIndex = 0;
							tr.addEventListener('click', function(e) {
								if (e.target.closest('.social-listing-action') || e.target.closest('.st-attachment-view-btn') || e.target.closest('a')) return;
								const row = pageData[idx] || null;
								if (row && window.openStDetailsModal) {
									try { window.openStDetailsModal(row); } catch(err) { console.error('openStDetailsModal error', err); }
								}
							});
							tr.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); } });
						});
					}
				}
			} catch(e) { console.error('bind listing row handlers failed', e); }
		}

		window.changePage = function(page) {
			const totalPages = Math.ceil(data.length / perPage);
			if (page < 1 || page > totalPages) return;
			currentPage = page;
			renderTable(currentPage);
		};

	})();
	</script>
	<script>
	window.regionMap = @json($regionMap);
window.allProvinces = [];
window.allCities = [];
Object.keys(window.regionMap).forEach(function(region) {
    var reg = window.regionMap[region];
    if (reg.provinces) {
        Object.keys(reg.provinces).forEach(function(prov) {
            window.allProvinces.push(prov);
            window.allCities = window.allCities.concat(reg.provinces[prov]);
        });
    }
});
window.allProvinces = Array.from(new Set(window.allProvinces)).sort();
window.allCities = Array.from(new Set(window.allCities)).sort();
window.allYears = @json($allYears ?? $years);
	$(function() {
		function initDashboardSelect2() {
			if (!$.fn || !$.fn.select2) {
				console && console.warn && console.warn('Select2 plugin is missing for .st-select2');
				return;
			}
			$('.st-select2').each(function () {
				var $el = $(this);
				if ($el.data('select2')) {
					return; 
				}
				var opts = {
					width: '100%',
					closeOnSelect: false,
					allowClear: true,
					placeholder: $el.data('placeholder'),
					templateResult: function (data) { return data.text; },
					templateSelection: function (data) { return data.text; },
					escapeMarkup: function (markup) { return markup; }
				};
				if ($el.closest('#guestFilterDock, #guestMobileFilterPanel, #guestFloatingFilter').length) {
					opts.dropdownCssClass = 'guest-filter-select2-dropdown';
				}
				if ($el.closest('#filterModal').length) {
					opts.dropdownParent = $('#filterModal');
				}
				$el.select2(opts);
			});
		}

		initDashboardSelect2();

		$('#filterModal').on('shown.bs.modal', function() {
			initDashboardSelect2();
			try{
				const modal = this;
				const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
				if (firstFocusable) {
					firstFocusable.focus();
				} else {
					modal.setAttribute('tabindex', '-1');
					modal.focus();
				}
			} catch(e) {}
		});

		$('#filterModal').on('hide.bs.modal', function() {
			try{
				const active = document.activeElement;
				if (active && this.contains(active)) {
					const opener = document.getElementById('floatingBtn') || document.querySelector('[data-bs-target="#filterModal"]') || document.body;
					if (opener && typeof opener.focus === 'function') opener.focus();
				}
			} catch(e) {}
		});

		document.addEventListener('click', function(e) {
			const btn = e.target.closest('.st-attachment-view-btn');
			if (!btn) return;
			const url = btn.getAttribute('data-url');
			const title = btn.getAttribute('data-title') || '';
			const uploader = btn.getAttribute('data-uploader') || '';
			if (window.openStAttachmentModal) {
				window.openStAttachmentModal(url, title, uploader);
			}
		});

		function inferRegionCodeFromRegionText(regionText) {
			if (!regionText) return null;
			const s = String(regionText).toLowerCase().trim();
			if (!s) return null;
			if (s.includes('national capital') || s.includes(' ncr') || s.startsWith('ncr')) return 'NCR';
			if (s.includes('ilocos')) return 'Region I';
			if (s.includes('cagayan valley')) return 'Region II';
			if (s.includes('central luzon')) return 'Region III';
			if (s.includes('calabarzon')) return 'Region IV-A';
            if (s.includes('calborazon')) return 'Region IV-A';
			if (s.includes('mimaropa')) return 'Region IV-B';
			if (s.includes('bicol')) return 'Region V';
			if (s.includes('western visayas')) return 'Region VI';
			if (s.includes('central visayas')) return 'Region VII';
			if (s.includes('eastern visayas')) return 'Region VIII';
			if (s.includes('zamboanga peninsula') || s.includes('zamboanga pen')) return 'Region IX';
			if (s.includes('northern mindanao')) return 'Region X';
			if (s.includes('davao region')) return 'Region XI';
			if (s.includes('soccsksargen')) return 'Region XII';
			if (s.includes('caraga')) return 'CARAGA';
			if (s.includes('bangsamoro') || /\bbarmm\b/.test(s)) return 'BARMM';
			if (!s.includes('caraga') && (s === 'car' || s.includes('cordillera') || /\bcar\b/.test(s))) {
				return 'CAR';
			}
			const txt = s;
			const romanPatterns = [
				{ code: 'Region XII', re: /\bxii\b/ },
				{ code: 'Region XI', re: /\bxi\b/ },
				{ code: 'Region X',  re: /\bx\b/ },
				{ code: 'Region IX', re: /\bix\b/ },
				{ code: 'Region VIII', re: /\bviii\b/ },
				{ code: 'Region VII',  re: /\bvii\b/ },
				{ code: 'Region VI',   re: /\bvi\b/ },
				{ code: 'Region V',    re: /\bv\b/ },
				{ code: 'Region IV-B', re: /\biv[\s-]?b\b/ },
				{ code: 'Region IV-A', re: /\biv[\s-]?a\b/ },
				{ code: 'Region III',  re: /\biii\b/ },
				{ code: 'Region II',   re: /\bii\b/ },
				{ code: 'Region I',    re: /\bi\b/ }
			];
			for (let i = 0; i < romanPatterns.length; i++) {
				if (romanPatterns[i].re.test(txt)) return romanPatterns[i].code;
			}
			return null;
		}

			function propagateFilters() {
					var selRegions = $('#region-select-modal').val() || [];
					var selYears = $('#year-select-modal').val() || [];
				selRegions = selRegions.map(function(r){
					return inferRegionCodeFromRegionText(r) || r;
				});
			if (selRegions.length || selYears.length) {
				$('.card-gallery .card').each(function(){
					var $c = $(this);
					var txt = ($c.find('h2').text() + ' ' + $c.find('p').text()).toLowerCase();
					var show = true;
					if (selRegions.length) {
						show = selRegions.some(r => txt.indexOf(r.toLowerCase()) !== -1);
					}
					if (show && selYears.length) {
						show = selYears.some(y => txt.indexOf(y.toString()) !== -1);
					}
					$c.toggle(show);
				});
			}
		}

if (!window._streportIframeExpanded) {
    window._streportIframeExpanded = false;
}
if (typeof window._streportHasInitialized === 'undefined') {
	window._streportHasInitialized = false;
}
window.addEventListener('message', function(e) {
	if (e.data && e.data.type === 'streportToggleHeight') {
		const iframe = document.querySelector('#streportFrame') || document.querySelector('iframe[src*="/streport"]');
		if (!iframe) return;
		const applyStreportHeight = function(nextHeight) {
			iframe.style.setProperty('height', nextHeight, 'important');
			iframe.style.setProperty('min-height', nextHeight, 'important');
			iframe.style.setProperty('max-height', nextHeight, 'important');
		};
		const isMobileViewport = window.matchMedia('(max-width: 767px)').matches;
		const mobileCollapsedHeight = '500px';
		const mobileExpandedHeight = '2700px';
		const wasInitialized = window._streportHasInitialized;
		const shouldAutoScroll = e.data.scrollIntoView === true;
		window._streportHasInitialized = true;
		iframe.style.transition = 'height 0.3s ease, max-height 0.3s ease';
		iframe.style.overflow = 'hidden';
		if (e.data.height) {
			let nextHeight = e.data.height;
			if (isMobileViewport) {
				nextHeight = String(nextHeight) === mobileExpandedHeight ? mobileExpandedHeight : mobileCollapsedHeight;
			}
			applyStreportHeight(nextHeight);
			window._streportIframeExpanded = (String(nextHeight) !== '500px' && String(nextHeight) !== mobileCollapsedHeight);
		} else {
			if (!window._streportIframeExpanded) {
				const expandedHeight = isMobileViewport ? mobileExpandedHeight : '1600px';
				applyStreportHeight(expandedHeight);
			} else {
				const collapsedHeight = isMobileViewport ? mobileCollapsedHeight : '720px';
				applyStreportHeight(collapsedHeight);
			}
			window._streportIframeExpanded = !window._streportIframeExpanded;
		}

		if (!window._streportIframeExpanded) {
			try {
				if (iframe.contentWindow && typeof iframe.contentWindow.resetRsmFilters === 'function') {
					iframe.contentWindow.resetRsmFilters();
					console.log('parent: resetRsmFilters invoked due to collapse');
				}
			} catch(e) { console.warn('parent: failed to reset filters on collapse', e); }
		}
		if (window._streportIframeExpanded && wasInitialized && shouldAutoScroll) {
			setTimeout(() => {
				try {
					iframe.scrollIntoView({ behavior: 'smooth', block: 'start' });
				} catch(err) { console.warn('scrollIntoView failed', err); }
			}, 350);
		}
		return;
	}
});

$('#region-select-modal').on('change', function() {
			var selectedRegions = $(this).val() || [];
			var provinces = [];
			var cities = [];
			var years = [];


			if (selectedRegions.length === 0) {
				var $yearAll = $('#year-select-modal');
				var selectedYearAll = $yearAll.val() || [];
				$yearAll.empty();
				(window.allYears || []).forEach(function(yr) {
					var selected = selectedYearAll.includes(yr) ? 'selected' : '';
					$yearAll.append('<option value="'+yr+'" '+selected+'>'+yr+'</option>');
				});
				$yearAll.trigger('change.select2');
				var $provAll = $('#province-select-modal');
				var selProv = $provAll.val() || [];
				$provAll.empty();
				(window.allProvinces || []).forEach(function(p) {
					var selected = selProv.includes(p) ? 'selected' : '';
					$provAll.append('<option value="'+p+'" '+selected+'>'+p+'</option>');
				});
				$provAll.trigger('change.select2');
				var $cityAll = $('#municipality-select-modal');
				var selCity = $cityAll.val() || [];
				$cityAll.empty();
				(window.allCities || []).forEach(function(c) {
					var selected = selCity.includes(c) ? 'selected' : '';
					$cityAll.append('<option value="'+c+'" '+selected+'>'+c+'</option>');
				});
				$cityAll.trigger('change.select2');
				propagateFilters();
				return;
			}
			var allRows = window.fullListingData || [];
			allRows.forEach(function(row) {
				if (selectedRegions.includes(row.region)) {
					if (row.province) provinces.push(row.province);
					if (row.municipality) cities.push(row.municipality);
					if (row.year_of_moa) years.push(row.year_of_moa);
				}
			});

			provinces = [...new Set(provinces)];
			cities = [...new Set(cities)];
			years = [...new Set(years)];
			years.sort();

			var $province = $('#province-select-modal');
			var selectedProvince = $province.val() || [];
			$province.empty();
			provinces.forEach(function(prov) {
				var selected = selectedProvince.includes(prov) ? 'selected' : '';
				$province.append('<option value="'+prov+'" '+selected+'>'+prov+'</option>');
			});
			$province.trigger('change.select2');

			var $city = $('#municipality-select-modal');
			var selectedCity = $city.val() || [];
			$city.empty();
			cities.forEach(function(city) {
				var selected = selectedCity.includes(city) ? 'selected' : '';
				$city.append('<option value="'+city+'" '+selected+'>'+city+'</option>');
			});
			$city.trigger('change.select2');

			var $year = $('#year-select-modal');
			var selectedYear = $year.val() || [];
			$year.empty();
			years.forEach(function(yr) {
				var selected = selectedYear.includes(yr) ? 'selected' : '';
				$year.append('<option value="'+yr+'" '+selected+'>'+yr+'</option>');
			});
			$year.trigger('change.select2');
			propagateFilters();
		});

		$('#province-select-modal').on('change', function() {
			var selectedRegions = $('#region-select-modal').val() || [];
			var selectedProvinces = $(this).val() || [];
			var cities = [];
			if (selectedRegions.length === 0 && selectedProvinces.length === 0) {
				var $cityAll = $('#municipality-select-modal');
				var selCity = $cityAll.val() || [];
				$cityAll.empty();
				(window.allCities || []).forEach(function(c) {
					var sel = selCity.includes(c) ? 'selected' : '';
					$cityAll.append('<option value="'+c+'" '+sel+'>'+c+'</option>');
				});
				$cityAll.trigger('change.select2');
				propagateFilters();
				return;
			}
			var allRows = window.fullListingData || [];
			allRows.forEach(function(row) {
				if (
					(selectedRegions.length === 0 || selectedRegions.includes(row.region)) &&
					(selectedProvinces.length === 0 || selectedProvinces.includes(row.province))
				) {
					if (row.municipality) cities.push(row.municipality);
				}
			});
			cities = [...new Set(cities)];
			var $city = $('#municipality-select-modal');
			var selectedCity = $city.val() || [];
			$city.empty();
			cities.forEach(function(city) {
				var selected = selectedCity.includes(city) ? 'selected' : '';
				$city.append('<option value="'+city+'" '+selected+'>'+city+'</option>');
			});
			$city.trigger('change.select2');
		});

		$('#year-select-modal').on('change', propagateFilters);

		$('#region-select-modal').trigger('change');
		$('#province-select-modal').trigger('change');
		propagateFilters();
	});

	const stTitleCounts = {};
	@foreach(collect($data)->filter(function($row){ return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']); }) as $row)
		stTitleCounts["{{ addslashes($row['title']) }}"] = (stTitleCounts["{{ addslashes($row['title']) }}"] || 0) + 1;
	@endforeach

	const yearMoaCounts = {};
	@foreach(collect($regionFilteredData ?? $data)->filter(function($row){ return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['year_of_moa']); }) as $row)
		yearMoaCounts["{{ addslashes($row['year_of_moa']) }}"] = (yearMoaCounts["{{ addslashes($row['year_of_moa']) }}"] || 0) + 1;
	@endforeach
	const yearMoaLabels = Object.keys(yearMoaCounts).sort();
	const yearMoaData = yearMoaLabels.map(y => yearMoaCounts[y]);

	const urlParams = new URLSearchParams(window.location.search);
	const selectedYear = urlParams.get('year_of_moa');
	const defaultColors = ['#10aeb5', '#1de9b6', '#b2ebf2', '#ffb74d', '#9575cd', '#f06292'];
	function brighten(hex, percent) {
		let num = parseInt(hex.replace('#',''),16), amt = Math.round(2.55 * percent),
			R = (num >> 16) + amt, G = (num >> 8 & 0x00FF) + amt, B = (num & 0x0000FF) + amt;
		return '#' + (
			0x1000000 +
			(R<255?R<1?0:R:255)*0x10000 +
			(G<255?G<1?0:G:255)*0x100 +
			(B<255?B<1?0:B:255)
		).toString(16).slice(1);
	}
	const yearMoaColors = yearMoaLabels.map((label, i) => (selectedYear && label === selectedYear)
		? brighten(defaultColors[i % defaultColors.length], 30)
		: defaultColors[i % defaultColors.length]
	);
	const yearMoaBorderColors = yearMoaLabels.map((label, i) => (selectedYear && label === selectedYear)
		? 'rgba(255, 214, 0, 0.85)'
		: '#10aeb5'); 
	const yearMoaBorderWidths = yearMoaLabels.map((label) => (selectedYear && label === selectedYear) ? 12 : 2);

	if (document.getElementById('yearMoaBar')) {
		const ctx = document.getElementById('yearMoaBar').getContext('2d');
		const yearMoaChart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: yearMoaLabels,
				datasets: [{
					label: 'STs Count',
					data: yearMoaData,
					backgroundColor: yearMoaColors,
					borderColor: yearMoaBorderColors,
					borderWidth: yearMoaBorderWidths,
				}]
			},
			options: {
				responsive: true,
				plugins: {
					legend: { display: false },
					title: { display: false },
					tooltip: {
						callbacks: {
							label: function(context) {
								return 'Count: ' + context.parsed.y;
							}
						}
					}
				},
				onClick: function(evt, elements) {
					if (elements && elements.length > 0) {
						const idx = elements[0].index;
						const selectedYear = yearMoaLabels[idx];
						const url = new URL(window.location.href);
						const currentYear = url.searchParams.get('year_of_moa');
						if (currentYear === selectedYear) {
							url.searchParams.delete('year_of_moa'); 
						} else {
							url.searchParams.set('year_of_moa', selectedYear);
						}
						window.location.href = url.toString();
					}
				},
				scales: {
					x: {
						title: { display: true, text: 'Year' }
					},
					y: {
						beginAtZero: true,
						title: { display: true, text: 'MOA Count' },
						ticks: { precision: 0 }
					}
				}
			}
		});

		if (selectedYear) {
			const idx = yearMoaLabels.indexOf(selectedYear);
			if (idx !== -1) {
				let alpha = 0.2;
				let direction = 1; 
				function fade() {
					alpha += direction * 0.04;
					if (alpha >= 1) { alpha = 1; direction = -1; }
					if (alpha <= 0.2) { alpha = 0.2; direction = 1; }
					yearMoaChart.data.datasets[0].borderColor[idx] = `rgba(255, 214, 0, ${alpha.toFixed(2)})`;
					yearMoaChart.data.datasets[0].borderWidth[idx] = 4;
					yearMoaChart.update('none');
					requestAnimationFrame(fade);
				}
				fade();
			}
		}
	}

	const stTitleEntries = Object.entries(stTitleCounts).sort((a, b) => b[1] - a[1]); 
	const stTitleLabels = stTitleEntries.map(e => e[0]);
	const stTitleData = stTitleEntries.map(e => e[1]);
	const stTitleColors = [
		'#10aeb5', '#1de9b6', '#b2ebf2', '#ffb74d', '#9575cd', '#f06292', '#4db6ac', '#ffd54f', '#ba68c8', '#81c784', '#e57373', '#64b5f6', '#a1887f', '#90a4ae', '#f8bbd0'
	];
	if (document.getElementById('stTitlesDoughnut') || document.getElementById('stTitlesDoughnutCopy')) {
		const total = stTitleData.reduce((a, b) => a + b, 0);
		const stTitlePercentages = stTitleData.map(v => ((v / total) * 100));

			const insightEl = document.getElementById('stTitlesInsight');
			if (insightEl) {
				if (total > 0) {
					const topLabel = stTitleLabels[0] || '';
					const topPercent = stTitlePercentages[0] ? stTitlePercentages[0].toFixed(1) : '0.0';
					const titleCount = stTitleLabels.length;
					const top3Percent = stTitlePercentages.slice(0,3).reduce((a,b)=>a+b,0).toFixed(1);
					const insightThreshold = 0.5;
					const lowTitles = stTitlePercentages.filter(p => p <= insightThreshold);
					const lowCount = lowTitles.length;
					const lowSum = lowTitles.reduce((a,b)=>a+b,0).toFixed(1);
					insightEl.style.display = '';
					let narrative = '';
					narrative += `<strong>Portfolio Structure</strong>: top 3 titles supply ${top3Percent}% of total adoption (${titleCount} titles).`;
					narrative += `<br>${lowCount} low‑share title(s) (≤ ${insightThreshold}%) together make up ${lowSum}% of STs, indicating a long‑tail innovation profile.`;
					narrative += `<br><br><strong>Scaling Efficiency</strong>: adoption intensity and replication metrics not shown here.`;
					narrative += `<br><br><strong>Risk & Stability</strong>: concentration risk exists with top programs dominating; long tail offers innovation reservoir.`;
					narrative += `<br><br><strong>Performance Signal</strong>: system appears managed for replication and nationwide rollout.`;
					insightEl.innerHTML = narrative;
				} else {
					insightEl.style.display = 'none';
				}
			}
		const thresholdPercent = 0.5;
		const mainLabels = [];
		const mainData = [];
		const mainColors = [];
		const lowLabels = [];
		const lowData = [];
		const lowColors = [];
		const globalToMainIndex = {};
		const globalToLowIndex = {};
		let othersTotal = 0;
		const othersColor = '#e0e0e0';

		stTitleLabels.forEach((label, idx) => {
			const count = stTitleData[idx];
			const percent = stTitlePercentages[idx];
			const baseColor = stTitleColors[idx % stTitleColors.length];
			if (percent <= thresholdPercent) {
				lowLabels.push(label);
				lowData.push(count);
				lowColors.push(baseColor);
				globalToLowIndex[idx] = lowLabels.length - 1;
				othersTotal += count;
			} else {
				mainLabels.push(label);
				mainData.push(count);
				mainColors.push(baseColor);
				globalToMainIndex[idx] = mainLabels.length - 1;
			}
		});

		if (othersTotal > 0) {
			const othersIndex = mainLabels.length;
			mainLabels.push('Others');
			mainData.push(othersTotal);
			mainColors.push(othersColor);
			Object.keys(globalToLowIndex).forEach(k => {
				globalToMainIndex[k] = othersIndex;
			});
		}

		window.stTitleGlobalToMainIndex = globalToMainIndex;
		window.stTitleGlobalToLowIndex = globalToLowIndex;
		window.stTitleLowLabels = lowLabels;


		const itemsPerPage = 10;
		let currentPage = 1;
		let doughnutChart = null;
		const doughnutBlinkState = {
			imer: null,
			mainIdx: null,
			lowIdx: null,
			visible: true
		};
		function stopDoughnutBlink() {
			const mainChart = window.doughnutChartInstance;
			const lowChart = window.doughnutChartLowInstance;
			const copyMainChart = window.doughnutChartCopyInstance;
			const copyLowChart = window.doughnutChartLowCopyInstance;
			if (doughnutBlinkState.timer) {
				clearInterval(doughnutBlinkState.timer);
				doughnutBlinkState.timer = null;
			}
			if (mainChart) {
				const metaMain = mainChart.getDatasetMeta(0);
				metaMain.data.forEach(function(arc) {
					if (arc) {
						arc.options.borderWidth = 2;
						arc.options.borderColor = '#fff';
					}
				});
				mainChart.setActiveElements([]);
				mainChart.update();
			}
			if (lowChart) {
				const metaLow = lowChart.getDatasetMeta(0);
				metaLow.data.forEach(function(arc) {
					if (arc) {
						arc.options.borderWidth = 2;
						arc.options.borderColor = '#fff';
					}
				});
				lowChart.setActiveElements([]);
				lowChart.update();
			}
			if (copyMainChart) {
				const metaCopyMain = copyMainChart.getDatasetMeta(0);
				metaCopyMain.data.forEach(function(arc) {
					if (arc) {
						arc.options.borderWidth = 2;
						arc.options.borderColor = '#fff';
					}
				});
				copyMainChart.setActiveElements([]);
				copyMainChart.update();
			}
			if (copyLowChart) {
				const metaCopyLow = copyLowChart.getDatasetMeta(0);
				metaCopyLow.data.forEach(function(arc) {
					if (arc) {
						arc.options.borderWidth = 2;
						arc.options.borderColor = '#fff';
					}
				});
				copyLowChart.setActiveElements([]);
				copyLowChart.update();
			}
			doughnutBlinkState.mainIdx = null;
			doughnutBlinkState.lowIdx = null;
		}
		function startDoughnutBlink(mainIdx, lowIdx) {
			const mainChart = window.doughnutChartInstance;
			const lowChart = window.doughnutChartLowInstance;
			const copyMainChart = window.doughnutChartCopyInstance;
			const copyLowChart = window.doughnutChartLowCopyInstance;
			if (!mainChart && !lowChart && !copyMainChart && !copyLowChart) return;
			stopDoughnutBlink();
			doughnutBlinkState.mainIdx = (typeof mainIdx === 'number') ? mainIdx : null;
			doughnutBlinkState.lowIdx = (typeof lowIdx === 'number') ? lowIdx : null;
			doughnutBlinkState.visible = true;
			function applyFrame() {
				if (mainChart && doughnutBlinkState.mainIdx !== null) {
					const meta = mainChart.getDatasetMeta(0);
					meta.data.forEach(function(arc, i) {
						if (!arc) return;
						if (i === doughnutBlinkState.mainIdx) {
							arc.options.borderWidth = doughnutBlinkState.visible ? 8 : 2;
							arc.options.borderColor = '#ffeb3b';
						} else {
							arc.options.borderWidth = 2;
							arc.options.borderColor = '#fff';
						}
					});
					mainChart.setActiveElements([{ datasetIndex: 0, index: doughnutBlinkState.mainIdx }]);
					mainChart.update();
				}
				if (copyMainChart && doughnutBlinkState.mainIdx !== null) {
					const metaCopy = copyMainChart.getDatasetMeta(0);
					metaCopy.data.forEach(function(arc, i) {
						if (!arc) return;
						if (i === doughnutBlinkState.mainIdx) {
							arc.options.borderWidth = doughnutBlinkState.visible ? 8 : 2;
							arc.options.borderColor = '#ffeb3b';
						} else {
							arc.options.borderWidth = 2;
							arc.options.borderColor = '#fff';
						}
					});
					copyMainChart.setActiveElements([{ datasetIndex: 0, index: doughnutBlinkState.mainIdx }]);
					copyMainChart.update();
				}
				if (lowChart && doughnutBlinkState.lowIdx !== null) {
					const metaLow = lowChart.getDatasetMeta(0);
					metaLow.data.forEach(function(arc, i) {
						if (!arc) return;
						if (i === doughnutBlinkState.lowIdx) {
							arc.options.borderWidth = doughnutBlinkState.visible ? 8 : 2;
							arc.options.borderColor = '#ffeb3b';
						} else {
							arc.options.borderWidth = 2;
							arc.options.borderColor = '#fff';
						}
					});
					lowChart.setActiveElements([{ datasetIndex: 0, index: doughnutBlinkState.lowIdx }]);
					lowChart.update();
				}
				if (copyLowChart && doughnutBlinkState.lowIdx !== null) {
					const metaCopyLow = copyLowChart.getDatasetMeta(0);
					metaCopyLow.data.forEach(function(arc, i) {
						if (!arc) return;
						if (i === doughnutBlinkState.lowIdx) {
							arc.options.borderWidth = doughnutBlinkState.visible ? 8 : 2;
							arc.options.borderColor = '#ffeb3b';
						} else {
							arc.options.borderWidth = 2;
							arc.options.borderColor = '#fff';
						}
					});
					copyLowChart.setActiveElements([{ datasetIndex: 0, index: doughnutBlinkState.lowIdx }]);
					copyLowChart.update();
				}
			}
			applyFrame();
			doughnutBlinkState.timer = setInterval(function() {
				doughnutBlinkState.visible = !doughnutBlinkState.visible;
				applyFrame();
			}, 180);
		}
		function renderCategoryList(page = 1) {
			const start = (page - 1) * itemsPerPage;
			const end = start + itemsPerPage;
			let listHtml = '<ul id="stCategoryListUl" class="st-cat-list">';
			stTitleLabels.slice(start, end).forEach((label, i) => {
				const idx = start + i;
				const color = stTitleColors[idx % stTitleColors.length];
				const count = stTitleData[idx];
				const percent = stTitlePercentages[idx].toFixed(1);
				listHtml += `<li class=\"st-cat-list-item\" data-idx=\"${idx}\">` +
					`<span class=\"st-cat-list-swatch\" style=\"background:${color};\"></span>` +
					`<span class=\"st-cat-list-metric\">${percent}% (${count})</span>` +
					`<span class=\"st-cat-list-label\">${label}</span>` +
				`</li>`;
			});
			listHtml += '</ul>';
			const totalPages = Math.ceil(stTitleLabels.length / itemsPerPage);
			if (totalPages > 1) {
				listHtml += `<div class=\"st-cat-pagination\">`;
				listHtml += `<button id=\"stCatPrevBtn\" class=\"st-cat-page-btn\" ${page === 1 ? 'disabled' : ''}>Prev</button>`;
				listHtml += `<span class=\"st-cat-page\">Page ${page} of ${totalPages}</span>`;
				listHtml += `<button id=\"stCatNextBtn\" class=\"st-cat-page-btn\" ${page === totalPages ? 'disabled' : ''}>Next</button>`;
				listHtml += `</div>`;
			}
			const mainListEl = document.getElementById('stCategoryList');
			if (mainListEl) {
				mainListEl.innerHTML = listHtml;
			}

			const copyListEl = document.getElementById('stCategoryListCopy');
			if (copyListEl) {
				let copyHtml = '<ul id="stCategoryListUlCopy" class="st-cat-list">';
				stTitleLabels.slice(start, end).forEach((label, i) => {
					const idx = start + i;
					const color = stTitleColors[idx % stTitleColors.length];
					const count = stTitleData[idx];
					const percent = stTitlePercentages[idx].toFixed(1);
					copyHtml += '<li class="st-cat-list-item" data-idx="' + idx + '">' +
						`<span class="st-cat-list-swatch" style="background:${color};"></span>` +
						`<span class="st-cat-list-metric">${percent}% (${count})</span>` +
						`<span class="st-cat-list-label">${label}</span>` +
					'</li>';
				});
				copyHtml += '</ul>';
				if (totalPages > 1) {
					copyHtml += '<div class="st-cat-pagination">';
					copyHtml += `<button id="stCatPrevBtnCopy" class="st-cat-page-btn" ${page === 1 ? 'disabled' : ''}>Prev</button>`;
					copyHtml += `<span class="st-cat-page">Page ${page} of ${totalPages}</span>`;
					copyHtml += `<button id="stCatNextBtnCopy" class="st-cat-page-btn" ${page === totalPages ? 'disabled' : ''}>Next</button>`;
					copyHtml += '</div>';
				}
				copyListEl.innerHTML = copyHtml;
			}
			if (totalPages > 1) {
				const prevMain = document.getElementById('stCatPrevBtn');
				const nextMain = document.getElementById('stCatNextBtn');
				const prevCopy = document.getElementById('stCatPrevBtnCopy');
				const nextCopy = document.getElementById('stCatNextBtnCopy');
				const goPrev = function() {
					if (currentPage > 1) {
						currentPage--;
						renderCategoryList(currentPage);
					}
				};
				const goNext = function() {
					if (currentPage < totalPages) {
						currentPage++;
						renderCategoryList(currentPage);
					}
				};
				if (prevMain) prevMain.onclick = goPrev;
				if (nextMain) nextMain.onclick = goNext;
				if (prevCopy) prevCopy.onclick = goPrev;
				if (nextCopy) nextCopy.onclick = goNext;
			}
			setTimeout(() => {
				const items = document.querySelectorAll('.st-cat-list-item');
				const mainChart = window.doughnutChartInstance;
				const lowChart = window.doughnutChartLowInstance;
				const tooltip = document.getElementById('doughnutTooltip');
				const catListTooltip = document.getElementById('catListTooltip');
				items.forEach(item => {
					item.addEventListener('mouseenter', function(e) {
						const idx = parseInt(this.getAttribute('data-idx'));
						const mainIdx = (window.stTitleGlobalToMainIndex || {})[idx];
						const lowIdx = (window.stTitleGlobalToLowIndex || {})[idx];
						startDoughnutBlink(mainIdx, lowIdx);
						const label = stTitleLabels[idx];
						const percent = stTitlePercentages[idx].toFixed(1);
						catListTooltip.innerHTML = `<strong>${label}</strong><br><span style='color:#1de9b6;font-weight:600;'>${percent}%</span>`;
						catListTooltip.style.display = 'block';
						let placed = false;
						if (mainChart && mainIdx !== undefined) {
							const meta = mainChart.getDatasetMeta(0);
							const arc = meta.data[mainIdx];
							if (arc) {
								const model = arc.getProps(['startAngle', 'endAngle', 'outerRadius', 'innerRadius', 'x', 'y'], true);
								const midAngle = (model.startAngle + model.endAngle) / 2;
								const r = (model.outerRadius + model.innerRadius) / 2;
								const x = model.x + r * Math.cos(midAngle);
								const y = model.y + r * Math.sin(midAngle);
								const canvas = mainChart.canvas;
								const rect = canvas.getBoundingClientRect();
								catListTooltip.style.left = (rect.left + x - catListTooltip.offsetWidth/2) + 'px';
								catListTooltip.style.top = (rect.top + y - catListTooltip.offsetHeight/2) + 'px';
								placed = true;
							}
						}
						if (!placed) {
							catListTooltip.style.left = (window.innerWidth/2 - catListTooltip.offsetWidth/2) + 'px';
							catListTooltip.style.top = (window.innerHeight/2 - catListTooltip.offsetHeight/2) + 'px';
						}
					});
					item.addEventListener('mouseleave', function() {
						stopDoughnutBlink();
						catListTooltip.style.display = 'none';
					});
					item.addEventListener('click', function() {
						const idx = parseInt(this.getAttribute('data-idx'));
						const label = stTitleLabels[idx];
						try {
							const allRows = window.fullListingData || [];
							const normalize = s => (s || '').toString().trim().toLowerCase();
							const normLabel = normalize(label);
							const matches = allRows.filter(r => normalize(r.title) === normLabel);
							if (matches.length === 1 && window.openStDetailsModal) {
								window.openStDetailsModal(matches[0]);
								return;
							}
							if (matches.length > 1 && window.openStSummaryModal) {
								window.openStSummaryModal({ title: label, filter: function(r){ return normalize(r.title) === normLabel; } });
								return;
							}
							const partial = allRows.find(r => normalize(r.title).includes(normLabel) || normLabel.includes(normalize(r.title)));
							if (partial && window.openStDetailsModal) {
								window.openStDetailsModal(partial);
								return;
							}
						} catch (err) {
							console.error('reference list click: match lookup failed', err);
						}
						if (window.openStTitleModal) {
							window.openStTitleModal(label);
						}
					});
				});
			}, 10);
		}

		if (document.getElementById('stTitlesDoughnut')) {
			window.doughnutChartInstance = new Chart(document.getElementById('stTitlesDoughnut').getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: mainLabels,
					datasets: [{
						data: mainData,
						backgroundColor: mainColors,
						borderWidth: 2,
						borderColor: '#fff',
					}]
				},
				options: {
					responsive: true,
					plugins: {
						legend: {
							display: false
						},
						title: {
							display: false
						},
						tooltip: {
							callbacks: {
								label: function(context) {
									const total = context.dataset.data.reduce((a, b) => a + b, 0);
									const value = context.parsed;
									const percent = ((value / total) * 100).toFixed(1);
									const title = context.label || '';
									return title + ': ' + value + ' (' + percent + '%)';
								}
							}
						}
					}
					,onClick: function(evt, elements) {
						if (!elements || !elements.length) return;
						const idx = elements[0].index;
						const label = this.data.labels && this.data.labels[idx];
						if (!label || label === 'Others') return;
						if (window.openStTitleModal) {
							window.openStTitleModal(label);
						}
					}
				}
			});
		}

		if (document.getElementById('stTitlesDoughnutLow') && lowLabels.length > 0) {
			window.doughnutChartLowInstance = new Chart(document.getElementById('stTitlesDoughnutLow').getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: lowLabels,
					datasets: [{
						data: lowData,
						backgroundColor: lowColors,
						borderWidth: 2,
						borderColor: '#fff',
					}]
				},
				options: {
					responsive: true,
					plugins: {
						legend: { display: false },
						title: { display: false },
						tooltip: { enabled: false }
					},
					onHover: function(evt, activeEls) {
						const tooltipDiv = document.getElementById('catListTooltip');
						if (!tooltipDiv) return;
						const mainChart = window.doughnutChartInstance;
						if (activeEls && activeEls.length) {
							const lowIdx = activeEls[0].index;
							const globalToLow = window.stTitleGlobalToLowIndex || {};
							let globalIdx = null;
							Object.keys(globalToLow).forEach(function(k) {
								if (globalToLow[k] === lowIdx) {
									globalIdx = parseInt(k, 10);
								}
							});
							if (globalIdx === null) {
								tooltipDiv.style.display = 'none';
								return;
							}
							const label = stTitleLabels[globalIdx] || '';
							const value = stTitleData[globalIdx] || 0;
							const percent = (stTitlePercentages[globalIdx] || 0).toFixed(1);
							tooltipDiv.innerHTML = `<strong>${label}</strong><br><span style="color:#1de9b6;font-weight:600;">${percent}% (${value})</span>`;
							tooltipDiv.style.display = 'block';
							const mainIdxMap = window.stTitleGlobalToMainIndex || {};
							const mainIdx = mainIdxMap[globalIdx];
							let placed = false;
							if (mainChart && mainIdx !== undefined) {
								const meta = mainChart.getDatasetMeta(0);
								const arc = meta.data[mainIdx];
								if (arc) {
									const model = arc.getProps(['startAngle', 'endAngle', 'outerRadius', 'innerRadius', 'x', 'y'], true);
									const midAngle = (model.startAngle + model.endAngle) / 2;
									const r = (model.outerRadius + model.innerRadius) / 2;
									const x = model.x + r * Math.cos(midAngle);
									const y = model.y + r * Math.sin(midAngle);
									const canvas = mainChart.canvas;
									const rect = canvas.getBoundingClientRect();
									tooltipDiv.style.left = (rect.left + x - tooltipDiv.offsetWidth / 2) + 'px';
									tooltipDiv.style.top = (rect.top + y - tooltipDiv.offsetHeight / 2) + 'px';
									placed = true;
								}
							}
							if (!placed) {
								tooltipDiv.style.left = (evt.clientX + 14) + 'px';
								tooltipDiv.style.top = (evt.clientY + 14) + 'px';
							}
						} else {
							tooltipDiv.style.display = 'none';
						}
					}
					,onClick: function(evt, elements) {
						if (!elements || !elements.length) return;
						const idx = elements[0].index;
						const label = this.data.labels && this.data.labels[idx];
						if (!label) return;
						if (window.openStTitleModal) {
							window.openStTitleModal(label);
						}
					}
				}
			});
		}
		(function() {
			const outerCanvas = document.getElementById('stTitlesDoughnut');
			const innerCanvas = document.getElementById('stTitlesDoughnutLow');
			if (!outerCanvas || !innerCanvas) return;
			['mousemove', 'click', 'mouseleave'].forEach(function(evtName) {
				outerCanvas.addEventListener(evtName, function(e) {
					if (!window.doughnutChartLowInstance) return;
					const simulated = new MouseEvent(evtName, {
						bubbles: true,
						cancelable: true,
						clientX: e.clientX,
						clientY: e.clientY
					});
					innerCanvas.dispatchEvent(simulated);
				});
			});
		})();
		renderCategoryList(currentPage);

		const copyCanvas = document.getElementById('stTitlesDoughnutCopy');
		if (copyCanvas) {
			window.doughnutChartCopyInstance = new Chart(copyCanvas.getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: mainLabels,
					datasets: [{
						data: mainData,
						backgroundColor: mainColors,
						borderWidth: 2,
						borderColor: '#fff',
					}]
				},
				options: {
					responsive: true,
					plugins: {
						legend: { display: false },
						title: { display: false },
						tooltip: {
							callbacks: {
								label: function(context) {
									const total = context.dataset.data.reduce((a, b) => a + b, 0);
									const value = context.parsed;
									const percent = ((value / total) * 100).toFixed(1);
									const title = context.label || '';
									return title + ': ' + value + ' (' + percent + '%)';
								}
							}
						}
					},
					cutout: '52%',

					onClick: function(evt, elements) {
						if (!elements || !elements.length) return;
						const idx = elements[0].index;
						const label = this.data.labels && this.data.labels[idx];
						if (!label || label === 'Others') return;
						if (window.openStTitleModal) {
							window.openStTitleModal(label);
						}
					}
				}
			});
			}
		const copyInnerCanvas = document.getElementById('stTitlesDoughnutLowCopy');
		if (copyInnerCanvas && lowLabels.length > 0) {
			window.doughnutChartLowCopyInstance = new Chart(copyInnerCanvas.getContext('2d'), {
				type: 'doughnut',
				data: {
					labels: lowLabels,
					datasets: [{
						data: lowData,
						backgroundColor: lowColors,
						borderWidth: 2,
						borderColor: '#fff',
					}]
				},
				options: {
					responsive: true,
					plugins: {
						legend: { display: false },
						title: { display: false },
						tooltip: { enabled: false }
					},
					
					onHover: function(evt, activeEls) {
						const tooltipDiv = document.getElementById('catListTooltip');
						if (!tooltipDiv) return;
						const mainChart = window.doughnutChartCopyInstance;
						if (activeEls && activeEls.length) {
							const lowIdx = activeEls[0].index;
							const globalToLow = window.stTitleGlobalToLowIndex || {};
							let globalIdx = null;
							Object.keys(globalToLow).forEach(function(k) {
								if (globalToLow[k] === lowIdx) {
									globalIdx = parseInt(k, 10);
								}
							});
							if (globalIdx === null) {
								tooltipDiv.style.display = 'none';
								return;
							}
							const label = stTitleLabels[globalIdx] || '';
							const value = stTitleData[globalIdx] || 0;
							const percent = (stTitlePercentages[globalIdx] || 0).toFixed(1);
							tooltipDiv.innerHTML = `<strong>${label}</strong><br><span style="color:#1de9b6;font-weight:600;">${percent}% (${value})</span>`;
							tooltipDiv.style.display = 'block';
							const mainIdxMap = window.stTitleGlobalToMainIndex || {};
							const mainIdx = mainIdxMap[globalIdx];
							let placed = false;
							if (mainChart && mainIdx !== undefined) {
								const meta = mainChart.getDatasetMeta(0);
								const arc = meta.data[mainIdx];
								if (arc) {
									const model = arc.getProps(['startAngle', 'endAngle', 'outerRadius', 'innerRadius', 'x', 'y'], true);
									const midAngle = (model.startAngle + model.endAngle) / 2;
									const r = (model.outerRadius + model.innerRadius) / 2;
									const x = model.x + r * Math.cos(midAngle);
									const y = model.y + r * Math.sin(midAngle);
									const canvas = mainChart.canvas;
									const rect = canvas.getBoundingClientRect();
									tooltipDiv.style.left = (rect.left + x - tooltipDiv.offsetWidth / 2) + 'px';
									tooltipDiv.style.top = (rect.top + y - tooltipDiv.offsetHeight / 2) + 'px';
									placed = true;
								}
							}
							if (!placed) {
								tooltipDiv.style.left = (evt.clientX + 14) + 'px';
								tooltipDiv.style.top = (evt.clientY + 14) + 'px';
							}
						} else {
							tooltipDiv.style.display = 'none';
						}
						},
						onClick: function(evt, elements) {
							if (!elements || !elements.length) return;
							const idx = elements[0].index;
							const label = this.data.labels && this.data.labels[idx];
							if (!label) return;
							if (window.openStTitleModal) {
								window.openStTitleModal(label);
							}
						}
				}
			});
		}
		(function() {
			const outerCopyCanvas = document.getElementById('stTitlesDoughnutCopy');
			const innerCopyCanvas = document.getElementById('stTitlesDoughnutLowCopy');
			if (!outerCopyCanvas || !innerCopyCanvas) return;
			['mousemove', 'click', 'mouseleave'].forEach(function(evtName) {
				outerCopyCanvas.addEventListener(evtName, function(e) {
					if (!window.doughnutChartLowCopyInstance) return;
					const simulated = new MouseEvent(evtName, {
						bubbles: true,
						cancelable: true,
						clientX: e.clientX,
						clientY: e.clientY
					});
					innerCopyCanvas.dispatchEvent(simulated);
				});
			});
		})();
		function normalizeStTitle(raw) {
			if (!raw) return '';
			const str = raw.toString();
			const div = document.createElement('div');
			div.innerHTML = str;
			const decoded = (div.textContent || div.innerText || '').trim();
			return decoded.toLowerCase();
		}
		function escapeHtml(value) {
			return String(value == null ? '' : value)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;');
		}
		function openStTitleModal(stTitle) {
			const modal = document.getElementById('st-title-modal');
			if (!modal) return;
			const titleEl = document.getElementById('st-title-modal-title');
			const bodyEl = document.getElementById('st-title-modal-body');
			if (titleEl) {
				const humanTitle = normalizeStTitle(stTitle) || 'st title';
				titleEl.textContent = stTitle || humanTitle;
			}
			const data = window.fullListingData || [];
			const target = normalizeStTitle(stTitle);
			let rows = [];
			if (Array.isArray(data) && target) {
				rows = data.filter(function(row) {
					const rowTitle = normalizeStTitle(row.title || '');
					return rowTitle === target;
				});
			}
			if (!bodyEl) return;
			if (!rows.length) {
				bodyEl.innerHTML = '<p class="st-title-modal-empty">No records found for this ST title based on the current filters.</p>';
			} else {
				let html = '<div class="st-title-modal-toolbar">';
				html += '<div class="st-title-modal-count">' + rows.length + ' matching records</div>';
				html += '<div class="st-title-modal-subtitle">Regional and local adoption coverage for this title</div>';
				html += '</div>';
				html += '<div class="st-title-modal-table-wrap">';
				html += '<table class="st-title-modal-table">';
				html += '<thead><tr><th>Region</th><th>Province</th><th>City/Municipality</th><th>Year of MOA</th><th class="text-center">Attachment</th></tr></thead><tbody>';
				rows.forEach(function(row) {
					const region = escapeHtml(row.region || '');
					const province = escapeHtml(row.province || '');
					const municipality = escapeHtml(row.municipality || '');
					const year = escapeHtml(row.year_of_moa || '');
					const attachmentUrl = row.attachment_url || '';
					const uploadedBy = row.attachment_uploaded_by || '';
					let attachmentCell = '';
					if (attachmentUrl) {
						const safeUrl = escapeHtml(attachmentUrl);
						const safeTitle = escapeHtml(stTitle || '');
						const safeUploader = escapeHtml(uploadedBy || '');
						attachmentCell = '' +
							'<div class="st-title-modal-attach">' +
								'<button type="button" class="st-title-modal-action st-title-modal-view st-attachment-view-btn" data-url="' + safeUrl + '" data-title="' + safeTitle + '" data-uploader="' + safeUploader + '" title="View attachment">' +
									'<i class="bi bi-eye"></i>' +
								'</button>' +
								'<a href="' + safeUrl + '" class="st-title-modal-action" title="Download attachment" target="_blank" download>' +
									'<i class="bi bi-download"></i>' +
								'</a>' +
							'</div>';
					}
					html += '<tr>' +
						'<td>' + region + '</td>' +
						'<td>' + province + '</td>' +
						'<td>' + municipality + '</td>' +
						'<td>' + year + '</td>' +
						'<td class="text-center">' + attachmentCell + '</td>' +
					'</tr>';
				});
				html += '</tbody></table></div>';
				bodyEl.innerHTML = html;
			}
			modal.style.display = 'block';
			if (document.body) {
				document.body.style.overflow = 'hidden';
			}
		}
		function closeStTitleModal() {
			const modal = document.getElementById('st-title-modal');
			if (!modal) return;
			modal.style.display = 'none';
			if (document.body) {
				document.body.style.overflow = '';
			}
		}
		window.openStTitleModal = openStTitleModal;
		window.closeStTitleModal = closeStTitleModal;

		function openStDetailsModal(row) {
			const modal = document.getElementById('st-details-modal');
			const titleEl = document.getElementById('st-details-modal-title');
			const bodyEl = document.getElementById('st-details-modal-body');
			if (!modal || !bodyEl) return;
			try {
				console.log('openStDetailsModal row:', row);
				console.log('openStDetailsModal year_of_resolution:', row ? row.year_of_resolution : undefined);
			} catch(e) {}
			if (titleEl) titleEl.textContent = (row && row.title) ? row.title : 'ST Details';
			if (!row) {
				bodyEl.innerHTML = '<p class="st-title-modal-empty">No details available.</p>';
			} else {
				const status = (row.status || '').toString();
				let statusLabel = '-';
				try {
					const sLower = status.toLowerCase();
					if (sLower && (sLower.includes('dissolved') || sLower.includes('inactive') || sLower.includes('completed'))) {
						statusLabel = 'Inactive';
					} else if (status && status.trim() !== '') {
						statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
					}
				} catch (e) { statusLabel = status ? status : '-'; }
				const adoption = (row.with_adopted ? 'Adopted' : (row.with_replicated ? 'Replicated' : 'None'));
				const indicators = [];
				if (row.with_expr) indicators.push('Expression of Interest');
				if (row.with_moa) indicators.push('With MOA');
				if (row.with_res) indicators.push('With Resolution');
				if (row.included_aip) indicators.push('Included AIP');
				let html = '';
				html += '<div class="masterdata-item-head">';
				html += '<div>';
				html += '<div class="masterdata-item-title">' + escapeHtml(row.title || '-') + '</div>';
				html += '<div class="masterdata-item-meta">';
				if (row.createdby) html += '<span>Created by: ' + escapeHtml(row.createdby) + '</span>';
				if (row.updatedby) html += '<span>Updated by: ' + escapeHtml(row.updatedby) + '</span>';
				if (row.updated_at) html += '<span>Updated at: ' + escapeHtml(row.updated_at) + '</span>';
				html += '</div></div></div>';

				const attUrl = row.attachment_url || '';
				html += '<div class="masterdata-attachment-panel">';
				html += '<div>';
				html += '<div class="masterdata-stat-label">MOA ATTACHMENT</div>';
				html += '<div class="masterdata-item-meta" style="margin-top:8px;">';
				if (attUrl) {
					html += '<span>Uploaded PDF available for this item.</span>';
					if (row.attachment_uploaded_by) html += '<span>Uploaded by: ' + escapeHtml(row.attachment_uploaded_by) + '</span>';
				} else {
					html += '<span>No PDF attachment uploaded yet.</span>';
					if (!row.with_moa || !row.year_of_moa) html += '<span> Enable With MOA and set Year of MOA to upload an attachment.</span>';
				}
				html += '</div>';
				html += '</div>';
				html += '<div class="masterdata-attachment-actions">';
				if (attUrl) {
					html += '<button type="button" class="masterdata-btn masterdata-btn-secondary st-attachment-view-btn" data-url="' + attUrl + '" data-title="' + escapeHtml(row.title || '') + '" data-uploader="' + escapeHtml(row.attachment_uploaded_by || '') + '">View PDF</button>';
					html += '<a href="' + attUrl + '" class="masterdata-btn" target="_blank" download>Download</a>';
				}
				html += '</div>';
				html += '</div>';

				html += '<div class="masterdata-form-grid">';
				html += '<div class="masterdata-field"><label>Regional Office</label><input type="text" value="' + escapeHtml(row.region || '-') + '" readonly></div>';
				html += '<div class="masterdata-field"><label>Status</label><input type="text" value="' + escapeHtml(statusLabel) + '" readonly></div>';
				html += '<div class="masterdata-field"><label>Inactive Status</label><input type="text" value="' + escapeHtml(row.inactive_status || '-') + '" readonly></div>';
				html += '<div class="masterdata-field full"><label>Inactive Remarks</label><textarea readonly style="min-height:64px; padding:8px; border-radius:8px; border:1px solid rgba(14,75,131,0.08); background:#fbfdff; color:#16324f; font-weight:600;">' + escapeHtml(row.inactive_remarks || '-') + '</textarea></div>';
				html += '<div class="masterdata-field full"><label>Social Technology Title</label><input type="text" value="' + escapeHtml(row.title || '-') + '" readonly></div>';
				html += '<div class="masterdata-field"><label>Province</label><input type="text" value="' + escapeHtml(row.province || '-') + '" readonly></div>';
				html += '<div class="masterdata-field"><label>Municipality</label><input type="text" value="' + escapeHtml(row.municipality || '-') + '" readonly></div>';
				html += '<div class="masterdata-field"><label>Adopted / Replicated</label><input type="text" value="' + escapeHtml(adoption) + '" readonly></div>';
				html += '<div class="masterdata-field full"><label>Indicators</label><div class="masterdata-checks">';
				html += '<label class="masterdata-check"><input type="hidden" name="with_expr" value="0"><span class="masterdata-check-control"><input type="checkbox" ' + (row.with_expr ? 'checked' : '') + ' disabled><span class="masterdata-check-indicator" aria-hidden="true"></span></span><span class="masterdata-check-text"><span class="masterdata-check-title">With Expression of Interest</span></span></label>';
				html += '<label class="masterdata-check"><input type="hidden" name="with_moa" value="0"><span class="masterdata-check-control"><input type="checkbox" ' + (row.with_moa ? 'checked' : '') + ' disabled><span class="masterdata-check-indicator" aria-hidden="true"></span></span><span class="masterdata-check-text"><span class="masterdata-check-title">With MOA</span><span class="masterdata-check-note">Enable when a memorandum of agreement exists.</span></span></label>';
				html += '<label class="masterdata-check"><input type="hidden" name="with_res" value="0"><span class="masterdata-check-control"><input type="checkbox" ' + (row.with_res ? 'checked' : '') + ' disabled><span class="masterdata-check-indicator" aria-hidden="true"></span></span><span class="masterdata-check-text"><span class="masterdata-check-title">With Resolution</span><span class="masterdata-check-note">Enable when a formal resolution has been issued.</span></span></label>';
				html += '<label class="masterdata-check"><input type="hidden" name="included_aip" value="0"><span class="masterdata-check-control"><input type="checkbox" ' + (row.included_aip ? 'checked' : '') + ' disabled><span class="masterdata-check-indicator" aria-hidden="true"></span></span><span class="masterdata-check-text"><span class="masterdata-check-title">Included AIP</span><span class="masterdata-check-note">Use when the item is included in the AIP.</span></span></label>';
				html += '</div></div>';
				if (row.with_moa) {
					html += '<div class="masterdata-field"><label>Year of MOA</label><input type="text" value="' + escapeHtml(row.year_of_moa || '-') + '" readonly></div>';
				}
				if (row.with_res) {
					html += '<div class="masterdata-field"><label>Year of Resolution</label><input type="text" value="' + escapeHtml(row.year_of_resolution || '-') + '" readonly></div>';
				}
				html += '</div>';

				bodyEl.innerHTML = html;
			}
			if (document.body) {
				document.body.classList.add('st-details-open');
				document.body.style.overflow = 'hidden';
			}
			modal.style.display = 'block';
		}

		function closeStDetailsModal() {
			const modal = document.getElementById('st-details-modal');
			if (!modal) return;
			modal.style.display = 'none';
			if (document.body) {
				document.body.style.overflow = '';
				document.body.classList.remove('st-details-open');
			}
		}

		window.openStDetailsModal = openStDetailsModal;
		window.closeStDetailsModal = closeStDetailsModal;

		function openStSummaryModal(config) {
			const modal = document.getElementById('st-summary-modal');
			const titleEl = document.getElementById('st-summary-modal-title');
			const bodyEl = document.getElementById('st-summary-modal-body');
			if (!modal || !titleEl || !bodyEl || !config || typeof config.filter !== 'function') return;

			const rows = (window.fullListingData || []).filter(config.filter);
			titleEl.textContent = config.title || 'ST Listing';

			if (!rows.length) {
				bodyEl.innerHTML = '<p class="st-summary-empty">No ST records matched this summary card.</p>';
			} else {
				let html = '<div class="st-summary-modal-toolbar">';
				html += '<div class="st-summary-modal-meta">' + rows.length + ' matching records</div>';
				html += '</div>';
				html += '<div class="st-summary-table-wrap">';
				html += '<table class="st-summary-table">';
				html += '<thead><tr><th>ST Title</th><th>Region</th><th>Province</th><th>City/Municipality</th><th>Year of MOA</th></tr></thead><tbody>';
				rows.forEach(function(row, idx) {
					const safeTitle = escapeHtml(row.title || 'Untitled ST');
					html += '<tr data-idx="' + idx + '">' +
						'<td>' + safeTitle + '</td>' +
						'<td>' + escapeHtml(row.region || '') + '</td>' +
						'<td>' + escapeHtml(row.province || '') + '</td>' +
						'<td>' + escapeHtml(row.municipality || '') + '</td>' +
						'<td>' + escapeHtml(row.year_of_moa || '') + '</td>' +
					'</tr>';
				});
				html += '</tbody></table></div>';
				bodyEl.innerHTML = html;
				modal._rows = rows;
				setTimeout(function(){
					try {
						const table = bodyEl.querySelector('.st-summary-table');
						if (!table) return;
						const tbody = table.querySelector('tbody');
						if (!tbody) return;
						tbody.querySelectorAll('tr[data-idx]').forEach(function(tr){
							tr.addEventListener('click', function(e){
								const idxAttr = this.getAttribute('data-idx');
								const i = parseInt(idxAttr, 10);
								const row = (modal._rows && modal._rows[i]) || null;
								if (row) {
									openStDetailsModal(row);
								}
							});
							tr.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); } });
							tr.tabIndex = 0;
						});
					} catch(e) { console.error('attach summary row handlers failed', e); }
				}, 10);
			}

			modal.style.display = 'block';
			if (document.body) {
				document.body.style.overflow = 'hidden';
			}
		}

		function closeStSummaryModal() {
			const modal = document.getElementById('st-summary-modal');
			if (!modal) return;
			modal.style.display = 'none';
			if (document.body) {
				document.body.style.overflow = '';
			}
		}

		window.openStSummaryModal = openStSummaryModal;
		window.closeStSummaryModal = closeStSummaryModal;

	function initTotalCardsToSummary() {
		const parseFlag = function(v) {
			if (typeof v === 'boolean') return v;
			if (v === null || v === undefined) return false;
			const s = String(v).toLowerCase().trim();
			if (s === '' || s === '0') return false;
			if (!isNaN(s)) return Number(s) !== 0;
			return s === 'true' || s === 'yes' || s === 'y';
		};

		const cardBindings = {
			'card1': { title: 'Ongoing STs', filter: function(r) { return ((r.status||'').toString().toLowerCase().includes('ongoing')) || false; } },
			'card2': { title: 'Inactive STs', filter: function(r) { const s=(r.status||'').toString().toLowerCase(); return s.includes('dissolved') || s.includes('inactive') || s.includes('completed'); } },
			'card3': { title: 'Replicated STs', filter: function(r) { return parseFlag(r.with_replicated); } },
			'card4': { title: 'Adopted STs', filter: function(r) { return parseFlag(r.with_adopted); } },
		};

		Object.keys(cardBindings).forEach(function(id) {
			const el = document.getElementById(id);
			if (!el) return;
			el.addEventListener('click', function() {
				try { openStSummaryModal({ title: cardBindings[id].title, filter: cardBindings[id].filter }); } catch(e) { console.error('openStSummaryModal failed', e); }
			});
			el.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); el.click(); } });
		});

		try {
			const overlayCards = Array.from(document.querySelectorAll('.map-overlay-totals .map-overlay-card'));
			if (overlayCards && overlayCards.length) {
				overlayCards.forEach(function(el, idx) {
					let cfg = null;
					if (idx === 0) cfg = { title: 'All ST Titles', filter: function(r){ return true; } }; 
					if (idx === 1) cfg = { title: 'Expression of Interest', filter: function(r){ return parseFlag(r.with_expr); } };
					if (idx === 2) cfg = { title: 'SB Resolution', filter: function(r){ return parseFlag(r.with_res); } };
					if (idx === 3) cfg = { title: 'MOA', filter: function(r){ return parseFlag(r.with_moa); } };
					if (!cfg) return;
					el.style.cursor = 'pointer';
					el.addEventListener('click', function(){ try { openStSummaryModal({ title: cfg.title, filter: cfg.filter }); } catch(e){ console.error(e); } });
					el.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); el.click(); } });
				});
			}
		} catch(e) { console.error('initTotalCardsToSummary overlay bind failed', e); }
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initTotalCardsToSummary);
	} else {
		setTimeout(initTotalCardsToSummary, 10);
	}

		function openStAttachmentModal(url, stTitle, uploadedBy) {
			if (!url) return;
			const modal = document.getElementById('st-attachment-modal');
			if (!modal) return;
			const frame = document.getElementById('st-attachment-frame');
			const titleEl = document.getElementById('st-attachment-modal-title');
			const uploaderEl = document.getElementById('st-attachment-modal-uploader');
			if (frame) {
				frame.src = url;
			}
			if (titleEl) {
				titleEl.textContent = stTitle || 'Attachment';
			}
			if (uploaderEl) {
				if (uploadedBy) {
					uploaderEl.textContent = 'Uploaded by: ' + uploadedBy;
					uploaderEl.style.display = 'inline-block';
				} else {
					uploaderEl.textContent = '';
					uploaderEl.style.display = 'none';
				}
			}
			try {
				const localBackdrop = modal.querySelector('.st-region-modal-backdrop');
				const localDialog = modal.querySelector('.st-region-modal-dialog');
				if (localBackdrop) localBackdrop.style.zIndex = 1990;
				if (localDialog) localDialog.style.zIndex = 1995;
				modal.style.zIndex = 1995;
			} catch (e) {
			}
			modal.style.display = 'block';
			if (document.body) {
				document.body.style.overflow = 'hidden';
			}
		}
		function closeStAttachmentModal() {
			const modal = document.getElementById('st-attachment-modal');
			if (!modal) return;
			modal.style.display = 'none';
			const frame = document.getElementById('st-attachment-frame');
			if (frame) {
				frame.src = '';
			}
			const uploaderEl = document.getElementById('st-attachment-modal-uploader');
			if (uploaderEl) {
				uploaderEl.textContent = '';
				uploaderEl.style.display = 'none';
			}
			if (document.body) {
				document.body.style.overflow = '';
			}
		}
		window.openStAttachmentModal = openStAttachmentModal;
		window.closeStAttachmentModal = closeStAttachmentModal;

		function openRegionTitlesModal(regionDisplayName, rows) {
			const modal = document.getElementById('region-titles-modal');
			if (!modal) return;
			const titleEl = document.getElementById('region-titles-modal-title');
			const bodyEl = document.getElementById('region-titles-modal-body');
			if (titleEl) {
				titleEl.textContent = regionDisplayName || 'Region';
					if (bodyEl) {
						if (!rows || !rows.length) {
							bodyEl.innerHTML = "<p style=\"margin:0; color:#64748b;\">No Record found.</p>";
						} else {
							let html = '';
							rows.forEach(function(row, idx) {
								const title = row.title || '';
								const province = row.province || '';
								const municipality = row.municipality || '';
								const uploadedBy = row.attachment_uploaded_by || '';
								const attachmentUrl = row.attachment_url || '';
								html += '<div class="st-region-title-item" data-idx="' + idx + '">';
								html += '<div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">';
								html += '<div>'; 
								html += '<div class="st-region-title-item-main">' + title + '</div>';
								html += '<div class="st-region-title-item-sub">' + province;
								if (municipality) {
									html += ' &bull; ' + municipality;
								}
								html += '</div>';
								html += '</div>';
								if (attachmentUrl) {
									const safeTitle = (title || '').toString().replace(/&/g,'&amp;').replace(/"/g,'&quot;');
									const safeUploader = (uploadedBy || '').toString().replace(/&/g,'&amp;').replace(/"/g,'&quot;');
										html += '' +
										'<div class="btn-group" role="group">' +
											'<button type="button" class="btn btn-sm btn-outline-success st-attachment-view-btn" data-url="' + attachmentUrl + '" data-title="' + safeTitle + '" data-uploader="' + safeUploader + '" title="View attachment">' +
												'<i class="bi bi-eye"></i>' +
											'</button>' +
											'<a href="' + attachmentUrl + '" class="btn btn-sm btn-outline-primary" title="Download attachment" target="_blank" download>' +
												'<i class="bi bi-download"></i>' +
											'</a>' +
											'</div>';
									}
								html += '</div>';
								html += '</div>';
							});
							bodyEl.innerHTML = html;
							modal._rows = rows;
							setTimeout(function(){
								const items = bodyEl.querySelectorAll('.st-region-title-item');
								items.forEach(function(it){
									it.addEventListener('click', function(e){
										if (e.target.closest('.st-attachment-view-btn') || e.target.closest('a')) {
											return;
										}
										const idx = parseInt(this.getAttribute('data-idx'), 10);
										const row = (modal._rows && modal._rows[idx]) || null;
										if (row) {
											openStDetailsModal(row);
										}
									});
								});
							}, 10);
						}
					}
			}
			modal.style.display = 'block';
			if (document.body) {
				document.body.style.overflow = 'hidden';
			}
		}

		function closeRegionTitlesModal() {
			const modal = document.getElementById('region-titles-modal');
			if (!modal) return;
			modal.style.display = 'none';
			if (document.body) {
				document.body.style.overflow = '';
			}
		}
		window.closeRegionTitlesModal = closeRegionTitlesModal;

		function initPhilippinesMapHover() {
			const phMapObject = document.getElementById('philippines-map');
			if (!phMapObject) return;
			if (phMapObject.dataset.phRegionsBound === '1') return;
			const mapWrapper = phMapObject.closest('.st-map-figure-wrapper');
			const regionLabelEl = document.getElementById('map-region-label');
			const svgDoc = phMapObject.contentDocument || (phMapObject.getSVGDocument && phMapObject.getSVGDocument());
			if (!svgDoc) return;
			const paths = svgDoc.querySelectorAll('path');
			if (!paths.length) return;

			const provinceToRegion = {
				// Region I – Ilocos Region
				'Ilocos Norte': 'Region I',
				'Ilocos Sur': 'Region I',
				'La Union': 'Region I',
				'Pangasinan': 'Region I',

				// CAR – Cordillera Administrative Region
				'Abra': 'CAR',
				'Apayao': 'CAR',
				'Benguet': 'CAR',
				'Ifugao': 'CAR',
				'Kalinga': 'CAR',
				'Mountain Province': 'CAR',

				// Region II – Cagayan Valley
				'Batanes': 'Region II',
				'Cagayan': 'Region II',
				'Isabela': 'Region II',
				'Nueva Vizcaya': 'Region II',
				'Quirino': 'Region II',

				// Region III – Central Luzon
				'Aurora': 'Region III',
				'Bataan': 'Region III',
				'Bulacan': 'Region III',
				'Nueva Ecija': 'Region III',
				'Pampanga': 'Region III',
				'Tarlac': 'Region III',
				'Zambales': 'Region III',

				// Region IV‑A – CALABARZON
				'Batangas': 'Region IV-A',
				'Cavite': 'Region IV-A',
				'Laguna': 'Region IV-A',
				'Quezon': 'Region IV-A',
				'Rizal': 'Region IV-A',

				// Region IV‑B – MIMAROPA
				'Marinduque': 'Region IV-B',
				'Mindoro Occidental': 'Region IV-B',
				'Mindoro Oriental': 'Region IV-B',
				'Palawan': 'Region IV-B',
				'Romblon': 'Region IV-B',

				// NCR – National Capital Region
				'Metropolitan Manila': 'NCR',

				// Region V – Bicol Region
				'Albay': 'Region V',
				'Camarines Norte': 'Region V',
				'Camarines Sur': 'Region V',
				'Catanduanes': 'Region V',
				'Masbate': 'Region V',
				'Sorsogon': 'Region V',

				// Region VI – Western Visayas
				'Aklan': 'Region VI',
				'Antique': 'Region VI',
				'Capiz': 'Region VI',
				'Guimaras': 'Region VI',
				'Iloilo': 'Region VI',
				'Negros Occidental': 'Region VI',

				// Region VII – Central Visayas
				'Bohol': 'Region VII',
				'Cebu': 'Region VII',
				'Negros Oriental': 'Region VII',
				'Siquijor': 'Region VII',

				// Region VIII – Eastern Visayas
				'Biliran': 'Region VIII',
				'Eastern Samar': 'Region VIII',
				'Leyte': 'Region VIII',
				'Northern Samar': 'Region VIII',
				'Samar': 'Region VIII',
				'Southern Leyte': 'Region VIII',

				// Region IX – Zamboanga Peninsula
				'Zamboanga del Norte': 'Region IX',
				'Zamboanga del Sur': 'Region IX',
				'Zamboanga Sibugay': 'Region IX',

				// Region X – Northern Mindanao
				'Bukidnon': 'Region X',
				'Camiguin': 'Region X',
				'Lanao del Norte': 'Region X',
				'Misamis Occidental': 'Region X',
				'Misamis Oriental': 'Region X',

				// Region XI – Davao Region
				'Compostela Valley': 'Region XI',
				'Davao del Norte': 'Region XI',
				'Davao del Sur': 'Region XI',
				'Davao Oriental': 'Region XI',

				// Region XII – SOCCSKSARGEN
				'Cotabato': 'Region XII',
				'Sarangani': 'Region XII',
				'South Cotabato': 'Region XII',
				'Sultan Kudarat': 'Region XII',

				// Region XIII – CARAGA
				'Agusan del Norte': 'CARAGA',
				'Agusan del Sur': 'CARAGA',
				'Dinagat Islands': 'CARAGA',
				'Surigao del Norte': 'CARAGA',
				'Surigao del Sur': 'CARAGA',

				// ARMM / BARMM – Bangsamoro
				'Basilan': 'BARMM',
				'Lanao del Sur': 'BARMM',
				'Maguindanao': 'BARMM',
				'Sulu': 'BARMM',
				'Tawi-Tawi': 'BARMM'
			};

			const regionToPaths = {};
			const regionToProvinces = {};
			const regionRows = {};
			const provinceRegionIndex = {};
			const pathInfos = [];
			const svgRoot = svgDoc.documentElement;
			const regionLabels = {
				'Region I': 'Region I – Ilocos Region',
				'CAR': 'CAR – Cordillera Administrative Region',
				'Region II': 'Region II – Cagayan Valley',
				'Region III': 'Region III – Central Luzon',
				'Region IV-A': 'Region IV-A – CALABARZON',
				'Region IV-B': 'Region IV-B – MIMAROPA',
				'NCR': 'NCR – National Capital Region',
				'Region V': 'Region V – Bicol Region',
				'Region VI': 'Region VI – Western Visayas',
				'Region VII': 'Region VII – Central Visayas',
				'Region VIII': 'Region VIII – Eastern Visayas',
				'Region IX': 'Region IX – Zamboanga Peninsula',
				'Region X': 'Region X – Northern Mindanao',
				'Region XI': 'Region XI – Davao Region',
				'Region XII': 'Region XII – SOCCSKSARGEN',
				'CARAGA': 'Region XIII – CARAGA',
				'BARMM': 'BARMM – Bangsamoro Autonomous Region'
			};
			const regionColors = {
				'Region I': '#ffb74d',
				'CAR': '#9575cd',
				'Region II': '#4db6ac',
				'Region III': '#81c784',
				'Region IV-A': '#f06292',
				'Region IV-B': '#64b5f6',
				'NCR': '#ff8a65',
				'Region V': '#ba68c8',
				'Region VI': '#aed581',
				'Region VII': '#4fc3f7',
				'Region VIII': '#ffcc80',
				'Region IX': '#ce93d8',
				'Region X': '#80cbc4',
				'Region XI': '#ffab91',
				'Region XII': '#9fa8da',
				'CARAGA': '#a5d6a7',
				'BARMM': '#ffecb3'
			};
			const regionBlinkTimers = {};
			paths.forEach(path => {
				if (path.dataset.phHoverBound === '1') return;
				const computed = window.getComputedStyle(path);
				const provinceName = path.getAttribute('title') || '';
				const regionName = provinceToRegion[provinceName] || null;
				const baseFill = regionName && regionColors[regionName]
					? regionColors[regionName]
					: (path.getAttribute('fill') || computed.fill || '#000000');
				const originalStroke = path.getAttribute('stroke') || computed.stroke || '#ffffff';
				const originalStrokeWidth = path.getAttribute('stroke-width') || computed.strokeWidth || '0.5';

				const info = {
					path: path,
					originalFill: baseFill,
					originalStroke: originalStroke,
					originalStrokeWidth: originalStrokeWidth,
					regionName: regionName
				};
				pathInfos.push(info);

				if (regionName) {
					if (!regionToPaths[regionName]) {
						regionToPaths[regionName] = [];
					}
					regionToPaths[regionName].push(info);
					if (!regionToProvinces[regionName]) {
						regionToProvinces[regionName] = [];
					}
					if (provinceName && regionToProvinces[regionName].indexOf(provinceName) === -1) {
						regionToProvinces[regionName].push(provinceName);
						const normProv = normalizeProvinceName(provinceName);
						if (normProv && !provinceRegionIndex[normProv]) {
							provinceRegionIndex[normProv] = regionName;
						}
					}
				}

				path.dataset.phHoverBound = '1';
				path.style.fill = info.originalFill;
				path.style.transition = 'fill 0.18s ease-out, stroke 0.18s ease-out, stroke-width 0.18s ease-out, opacity 0.18s ease-out, filter 0.18s ease-out';
				path.style.strokeLinecap = 'round';
				path.style.strokeLinejoin = 'round';
				path.style.cursor = 'pointer';
			});

			function getPathAnchorRect(path) {
				try {
					const bbox = path.getBBox();
					const svgEl = path.ownerSVGElement || svgRoot;
					const vb = svgEl && svgEl.viewBox && svgEl.viewBox.baseVal
						? svgEl.viewBox.baseVal
						: { x: 0, y: 0, width: svgEl.clientWidth || 1, height: svgEl.clientHeight || 1 };
					const mapRect = phMapObject.getBoundingClientRect();
					const scaleX = mapRect.width / (vb.width || 1);
					const scaleY = mapRect.height / (vb.height || 1);
					const centerX = mapRect.left + ((bbox.x + bbox.width / 2 - vb.x) * scaleX);
					const centerY = mapRect.top + ((bbox.y + bbox.height / 2 - vb.y) * scaleY);
					const size = 24;
					return {
						left: centerX - size / 2,
						right: centerX + size / 2,
						top: centerY - size / 2,
						bottom: centerY + size / 2,
						width: size,
						height: size
					};
				} catch (e) {
					return phMapObject.getBoundingClientRect();
				}
			}

	
			const dataForCounts = window.fullListingData || [];
			const regionCounts = {};
			if (Array.isArray(dataForCounts) && dataForCounts.length) {
				dataForCounts.forEach(function(row) {
					const rName = inferRegionCodeFromRow(row, provinceRegionIndex);
					if (!rName) return;
					regionCounts[rName] = (regionCounts[rName] || 0) + 1;
				});
			}

			const mapTooltip = (function() {
				let el = document.getElementById('catListTooltip');
				if (!el) {
					el = document.createElement('div');
					el.id = 'catListTooltip';
					el.style.position = 'fixed';
					el.style.zIndex = '9999';
					el.style.display = 'none';
					el.style.pointerEvents = 'none';
					el.style.background = 'rgba(34,34,34,0.97)';
					el.style.color = '#fff';
					el.style.padding = '4px 10px';
					el.style.borderRadius = '6px';
					el.style.fontSize = '12px';
					el.style.boxShadow = '0 2px 8px rgba(16,174,181,0.13)';
					el.style.whiteSpace = 'pre-line';
					el.style.maxWidth = '260px';
					el.style.lineHeight = '1.3';
					document.body.appendChild(el);
				}
				return el;
			})();

			function formatRegionTooltip(regionCode) {
				if (!regionCode) return '';
				const label = regionLabels[regionCode] || regionCode;
				const count = regionCounts[regionCode] || 0;
				const plural = count === 1 ? 'ST' : 'STs';
				return '<strong>' + label + '</strong><br><span style="color:#1de9b6;font-weight:600;">' + count + ' ' + plural + '</span>';
			}

			function showMapTooltip(regionCode, anchorRect) {
				if (!mapTooltip || !regionCode) return;
				const html = formatRegionTooltip(regionCode);
				if (!html) return;
				mapTooltip.innerHTML = html;
				mapTooltip.style.display = 'block';

				const baseRect = anchorRect || phMapObject.getBoundingClientRect();
				const tooltipWidth = mapTooltip.offsetWidth || 0;
				const tooltipHeight = mapTooltip.offsetHeight || 0;
				let left = baseRect.left + (baseRect.width - tooltipWidth) / 2;
				let top = baseRect.top - tooltipHeight - 8; 
				if (top < 8) {
					top = baseRect.bottom + 8;
				}
				left = Math.max(8, Math.min(left, window.innerWidth - tooltipWidth - 8));
				if (top + tooltipHeight + 8 > window.innerHeight) {
					top = Math.max(8, window.innerHeight - tooltipHeight - 8);
				}
				mapTooltip.style.left = left + 'px';
				mapTooltip.style.top = top + 'px';
			}

			function hideMapTooltip() {
				if (!mapTooltip) return;
				mapTooltip.style.display = 'none';
			}

			function clearRegionBlinkTimers() {
				Object.keys(regionBlinkTimers).forEach(function(regionCode) {
					clearInterval(regionBlinkTimers[regionCode]);
					delete regionBlinkTimers[regionCode];
				});
			}

			function clearMapHoverState() {
				clearRegionBlinkTimers();
				highlightGroup(pathInfos[0] || { regionName: null }, false);
				if (regionLabelEl) {
					regionLabelEl.textContent = 'Hover a region on the map';
				}
				hideMapTooltip();
			}

			function isWithinMapBoundary(target) {
				if (!target) return false;
				if (svgRoot && typeof svgRoot.contains === 'function' && svgRoot.contains(target)) {
					return true;
				}
				if (mapWrapper && typeof mapWrapper.contains === 'function' && mapWrapper.contains(target)) {
					return true;
				}
				return false;
			}

			function setActiveRegionRow(regionCode) {
				Object.keys(regionRows).forEach(function(code) {
					regionRows[code].classList.toggle('is-active', !!regionCode && code === regionCode);
				});
			}

			const regionListEl = document.getElementById('map-region-list');
			if (regionListEl) {
				const orderedRegions = [
					'NCR',
					'Region I',
					'CAR',
					'Region II',
					'Region III',
					'Region IV-A',
					'Region IV-B',
					'Region V',
					'Region VI',
					'Region VII',
					'Region VIII',
					'Region IX',
					'Region X',
					'Region XI',
					'Region XII',
					'CARAGA'
				];
				let html = '<div class="st-map-region-list-title">Regions (ST title count)</div>';
				orderedRegions.forEach(function(code) {
					if (!regionLabels[code]) return;
					const count = regionCounts[code] || 0;
					const color = regionColors[code] || '#cbd5e1';
					html += '<div class="st-map-region-row" data-region="' + code + '">';
					html += '<div class="st-map-region-row-main">';
					html += '<span class="st-map-region-color-dot" style="background:' + color + ';"></span>';
					html += '<span class="st-map-region-label">' + (regionLabels[code] || code) + '</span>';
					html += '</div>';
					html += '<span class="st-map-region-count">' + count + '</span>';
					html += '</div>';
				});
				regionListEl.innerHTML = html;
				function getRegionRepresentativeInfo(regionCode) {
					const arr = regionToPaths[regionCode];
					return (arr && arr.length) ? arr[0] : null;
				}
				function getRegionAnchorRectFromCode(regionCode) {
					const info = getRegionRepresentativeInfo(regionCode);
					if (!info) return phMapObject.getBoundingClientRect();
					return getPathAnchorRect(info.path);
				}
				function startRegionBlink(regionCode) {
					if (!regionCode) return;
					const info = getRegionRepresentativeInfo(regionCode);
					if (!info) return;
					if (regionBlinkTimers[regionCode]) {
						clearInterval(regionBlinkTimers[regionCode]);
					}
					let isVisible = true;
					highlightGroup(info, true, { color: '#dff8f4', stroke: '#0b2540' });
					regionBlinkTimers[regionCode] = setInterval(function() {
						isVisible = !isVisible;
						if (isVisible) {
							highlightGroup(info, true, { color: '#dff8f4', stroke: '#0b2540' });
						} else {
							highlightGroup(info, false);
						}
					}, 650);
				}
				function stopRegionBlink(regionCode) {
					if (!regionCode) return;
					if (regionBlinkTimers[regionCode]) {
						clearInterval(regionBlinkTimers[regionCode]);
						delete regionBlinkTimers[regionCode];
					}
					const info = getRegionRepresentativeInfo(regionCode);
					if (info) {
						highlightGroup(info, false);
					}
				}
				const listRows = regionListEl.querySelectorAll('.st-map-region-row');
				listRows.forEach(function(row) {
					const code = row.getAttribute('data-region');
					if (code) {
						regionRows[code] = row;
					}
					row.addEventListener('mouseenter', function() {
						if (regionLabelEl) {
							if (code && regionLabels[code]) {
								regionLabelEl.textContent = regionLabels[code];
							} else if (code) {
								regionLabelEl.textContent = code;
							}
						}
						showMapTooltip(code, getRegionAnchorRectFromCode(code));
						startRegionBlink(code);
					});
					row.addEventListener('mouseleave', function() {
						stopRegionBlink(code);
						if (regionLabelEl) {
							regionLabelEl.textContent = 'Hover a region on the map';
						}
						hideMapTooltip();
					});
					row.addEventListener('click', function() {
						const info = getRegionRepresentativeInfo(code);
						if (info) {
							handleRegionClick(info);
							if (window.updateStatusSummaryCards) {
								window.updateStatusSummaryCards([code]);
							}
						}
					});
				});
			}

			function highlightGroup(targetInfo, isHover, options) {
				const regionName = targetInfo.regionName;
				let targets;
				if (regionName && regionToPaths[regionName] && regionToPaths[regionName].length) {
					targets = regionToPaths[regionName];
				} else {
					targets = [targetInfo];
				}
				if (!isHover) {
					pathInfos.forEach(function(info) {
						info.path.style.fill = info.originalFill;
						info.path.style.stroke = info.originalStroke;
						info.path.style.strokeWidth = info.originalStrokeWidth;
						info.path.style.opacity = '1';
						info.path.style.filter = 'none';
					});
					setActiveRegionRow(null);
					return;
				}

				pathInfos.forEach(function(info) {
					info.path.style.fill = info.originalFill;
					info.path.style.stroke = info.originalStroke;
					info.path.style.strokeWidth = '0.8';
					info.path.style.opacity = '0.22';
					info.path.style.filter = 'saturate(0.75) brightness(1.02)';
				});

				targets.forEach(function(info) {
					if (svgRoot && info.path.parentNode === svgRoot) {
						svgRoot.appendChild(info.path);
					}
					info.path.style.fill = options && options.color ? options.color : info.originalFill;
					info.path.style.stroke = options && options.stroke ? options.stroke : '#0b2540';
					info.path.style.strokeWidth = '2.4';
					info.path.style.opacity = '1';
					info.path.style.filter = 'drop-shadow(0 0 10px rgba(16, 174, 181, 0.38)) brightness(1.08) saturate(1.18)';
				});

				setActiveRegionRow(regionName || null);
			}

			function normalizeProvinceName(name) {
				if (!name) return '';
				let n = String(name).toLowerCase().trim().replace(/\s+/g, ' ');
				if (n.includes('mindoro')) {
					if (n.includes('occidental')) return 'mindoro occidental';
					if (n.includes('oriental')) return 'mindoro oriental';
				}
				return n;
			}

			function inferRegionCodeFromRegionText(regionText) {
				if (!regionText) return null;
				const s = String(regionText).toLowerCase().trim();
				if (!s) return null;
				if (s.includes('national capital') || s.includes(' ncr') || s.startsWith('ncr')) return 'NCR';
				if (s.includes('ilocos')) return 'Region I';
				if (s.includes('cagayan valley')) return 'Region II';
				if (s.includes('central luzon')) return 'Region III';
				if (s.includes('calabarzon') || s.includes('calborazon')) return 'Region IV-A';
				if (s.includes('mimaropa')) return 'Region IV-B';
				if (s.includes('bicol')) return 'Region V';
				if (s.includes('western visayas')) return 'Region VI';
				if (s.includes('central visayas')) return 'Region VII';
				if (s.includes('eastern visayas')) return 'Region VIII';
				if (s.includes('zamboanga peninsula') || s.includes('zamboanga pen')) return 'Region IX';
				if (s.includes('northern mindanao')) return 'Region X';
				if (s.includes('davao region')) return 'Region XI';
				if (s.includes('soccsksargen')) return 'Region XII';
				if (s.includes('caraga')) return 'CARAGA';
				if (s.includes('bangsamoro') || /\bbarmm\b/.test(s)) return 'BARMM';
				if (!s.includes('caraga') && (s === 'car' || s.includes('cordillera') || /\bcar\b/.test(s))) {
					return 'CAR';
				}
				const txt = s;
				const romanPatterns = [
					{ code: 'Region XII', re: /\bxii\b/ },
					{ code: 'Region XI', re: /\bxi\b/ },
					{ code: 'Region X',  re: /\bx\b/ },
					{ code: 'Region IX', re: /\bix\b/ },
					{ code: 'Region VIII', re: /\bviii\b/ },
					{ code: 'Region VII',  re: /\bvii\b/ },
					{ code: 'Region VI',   re: /\bvi\b/ },
					{ code: 'Region V',    re: /\bv\b/ },
					{ code: 'Region IV-B', re: /\biv[\s-]?b\b/ },
					{ code: 'Region IV-A', re: /\biv[\s-]?a\b/ },
					{ code: 'Region III',  re: /\biii\b/ },
					{ code: 'Region II',   re: /\bii\b/ },
					{ code: 'Region I',    re: /\bi\b/ }
				];
				for (let i = 0; i < romanPatterns.length; i++) {
					if (romanPatterns[i].re.test(txt)) return romanPatterns[i].code;
				}
				return null;
			}

			function inferRegionCodeFromRow(row, provinceRegionIndex) {
				if (!row) return null;
				let byProvince = null;
				if (row.province) {
					const normProv = normalizeProvinceName(row.province);
					byProvince = provinceRegionIndex[normProv] || null;
				}
				const byRegionText = inferRegionCodeFromRegionText(row.region);
				if (byProvince === 'BARMM' && byRegionText && byRegionText !== 'BARMM') {
					return byRegionText;
				}
				if (byProvince) return byProvince;
				return byRegionText;
			}

			function handleRegionClick(targetInfo) {
				const regionName = targetInfo.regionName;
				const data = window.fullListingData || [];
				if (!data.length) {
					openRegionTitlesModal(regionName || 'Region', []);
					return;
				}
				let rows = [];
				if (regionName) {
					rows = data.filter(function(row){
						return inferRegionCodeFromRow(row, provinceRegionIndex) === regionName;
					});
				} else {
					const singleProv = targetInfo.path.getAttribute('title') || '';
					if (singleProv) {
						const targetProvNorm = normalizeProvinceName(singleProv);
						rows = data.filter(function(row){
							if (!row || !row.province) return false;
							return normalizeProvinceName(row.province) === targetProvNorm;
						});
					}
				}
				const displayName = (regionName && regionLabels[regionName]) || regionName || (targetInfo.path.getAttribute('title') ? ('Province: ' + targetInfo.path.getAttribute('title')) : 'Region');
				openRegionTitlesModal(displayName, rows);
				if (regionName && window.updateStatusSummaryCards) {
					window.updateStatusSummaryCards([regionName]);
				}
			}

			pathInfos.forEach(info => {
				const p = info.path;
				let lastPointerOpenAt = 0;

				function openRegionFromPath(event) {
					if (event) {
						if (typeof event.preventDefault === 'function') {
							event.preventDefault();
						}
						if (typeof event.stopPropagation === 'function') {
							event.stopPropagation();
						}
					}
					const now = Date.now();
					if (now - lastPointerOpenAt < 200) {
						return;
					}
					lastPointerOpenAt = now;
					handleRegionClick(info);
					hideMapTooltip();
				}

				p.addEventListener('mouseenter', function () {
					highlightGroup(info, true);
					if (regionLabelEl) {
						const labelKey = info.regionName;
						if (labelKey && regionLabels[labelKey]) {
							regionLabelEl.textContent = regionLabels[labelKey];
						} else if (info.regionName) {
							regionLabelEl.textContent = info.regionName;
						} else {
							regionLabelEl.textContent = 'Province: ' + (p.getAttribute('title') || '');
						}
					}
					if (info.regionName) {
						showMapTooltip(info.regionName, getPathAnchorRect(p));
					} else {
						hideMapTooltip();
					}
				});
				p.addEventListener('mouseleave', function (event) {
					if (isWithinMapBoundary(event.relatedTarget)) {
						return;
					}
					clearMapHoverState();
				});
				p.addEventListener('pointerup', openRegionFromPath);
				p.addEventListener('click', openRegionFromPath);
			});

			if (svgRoot && svgRoot.dataset.phMapExitBound !== '1') {
				svgRoot.addEventListener('mouseleave', function(event) {
					if (isWithinMapBoundary(event.relatedTarget)) {
						return;
					}
					clearMapHoverState();
				});
				svgRoot.dataset.phMapExitBound = '1';
			}

			if (mapWrapper && mapWrapper.dataset.phMapExitBound !== '1') {
				mapWrapper.addEventListener('mouseleave', function(event) {
					if (isWithinMapBoundary(event.relatedTarget)) {
						return;
					}
					clearMapHoverState();
				});
				mapWrapper.dataset.phMapExitBound = '1';
			}

			phMapObject.dataset.phRegionsBound = '1';
		}

		const phMapObject = document.getElementById('philippines-map');
		if (phMapObject) {
			phMapObject.addEventListener('load', function () {
				setTimeout(initPhilippinesMapHover, 0);
			});
			setTimeout(initPhilippinesMapHover, 500);
		}
	}

	function adjustOverlayTotalsNumbers() {
		const els = document.querySelectorAll('.map-overlay-card h1');
		if (!els || !els.length) return;
		els.forEach(el => {
			const card = el.closest('.map-overlay-card');
			if (!card) return;
			const cardStyle = window.getComputedStyle(card);
			const rect = card.getBoundingClientRect();
			const paddingX = parseFloat(cardStyle.paddingLeft || 0) + parseFloat(cardStyle.paddingRight || 0);
			const paddingY = parseFloat(cardStyle.paddingTop || 0) + parseFloat(cardStyle.paddingBottom || 0);
			const maxW = Math.max(24, rect.width - paddingX - 12);
			const maxH = Math.max(24, rect.height - paddingY - 12);

			let font = Math.floor(maxH * 0.65);
			const digits = (el.textContent || '').trim().length || 1;
			const approxPerDigit = Math.max(8, Math.floor(maxW / Math.max(1, digits)));
			font = Math.min(font, approxPerDigit * Math.floor(1.05 * Math.max(1, digits) / Math.max(1, digits)));

			if (digits > 4) font = Math.floor(font * Math.max(0.6, 4 / digits));

			el.style.whiteSpace = 'nowrap';
			el.style.lineHeight = '1';
			el.style.fontSize = font + 'px';

			while ((el.scrollWidth > maxW || el.scrollHeight > maxH) && font > 8) {
				font -= 1;
				el.style.fontSize = font + 'px';
			}

			el.style.visibility = 'visible';
		});
	}

	function debounce(fn, wait) {
		let t;
		return function(...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); };
	}

	document.addEventListener('DOMContentLoaded', function(){
		adjustOverlayTotalsNumbers();
		window.adjustOverlayTotalsNumbers = adjustOverlayTotalsNumbers;

		const observer = new MutationObserver(debounce(adjustOverlayTotalsNumbers, 80));
		document.querySelectorAll('.map-overlay-card h1').forEach(h => observer.observe(h, { childList: true, characterData: true, subtree: true }));
	});

if (typeof showReplicateConfirmPopover !== 'function') {
    function showReplicateConfirmPopover(targetEl, stInfo = {}) {
        try {
            const existing = document.body.querySelector('.replicate-popover');
            if (existing) existing.remove();
            const pop = document.createElement('div');
            pop.style.position = 'fixed';
            pop.style.zIndex = '2147483647';
            pop.style.width = '220px';
            pop.style.background = 'linear-gradient(180deg,#0b1220, #0f1724)';
            pop.style.color = '#e6eef1';
            pop.style.padding = '10px';
            pop.style.borderRadius = '8px';
            pop.style.fontSize = '0.92rem';
            pop.style.boxShadow = '0 12px 40px rgba(2,6,23,0.6)';
            pop.style.pointerEvents = 'auto';
            pop.style.opacity = '0';
            pop.style.transform = 'translateY(-6px) scale(0.98)';
            pop.style.transition = 'opacity 160ms ease, transform 160ms ease';
            const title = (stInfo && stInfo.title) ? stInfo.title : '';
            pop.innerHTML = `<div class="rp-msg">Replicate "${(title||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')}"?</div><div class="rp-actions"><button class="rp-confirm">Confirm</button><button class="rp-cancel">Cancel</button></div>`;
            document.body.appendChild(pop);
            try {
                const cb = pop.querySelector('.rp-confirm');
                const xb = pop.querySelector('.rp-cancel');
                if (cb) {
                    cb.style.background = '#10b981';
                    cb.style.color = '#042f2e';
                    cb.style.border = 'none';
                    cb.style.padding = '6px 10px';
                    cb.style.borderRadius = '6px';
                    cb.style.cursor = 'pointer';
                    cb.style.fontWeight = '600';
                }
                if (xb) {
                    xb.style.background = 'transparent';
                    xb.style.color = '#9ca3af';
                    xb.style.border = '1px solid rgba(255,255,255,0.03)';
                    xb.style.padding = '6px 10px';
                    xb.style.borderRadius = '6px';
                    xb.style.cursor = 'pointer';
                }
            } catch(e) {}
            const r = targetEl.getBoundingClientRect();
            const measure = () => {
                const pw = pop.offsetWidth;
                const ph = pop.offsetHeight;
                let left = r.left + (r.width/2) - (pw/2);
                let top = r.top - ph - 8;
                if (top < 8) top = r.bottom + 8;
                left = Math.max(8, Math.min(left, window.innerWidth - pw - 8));
                pop.style.left = left + 'px';
                pop.style.top = top + 'px';
            };
            requestAnimationFrame(() => { measure();
                pop.style.opacity = '1';
                pop.style.transform = 'translateY(0) scale(1)';
            });
            const confirmBtn = pop.querySelector('.rp-confirm');
            const cancelBtn = pop.querySelector('.rp-cancel');
            const cleanup = () => { try {
                    pop.style.pointerEvents = 'none';
                    pop.style.opacity = '0';
                    pop.style.transform = 'translateY(-6px) scale(0.98)';
                    setTimeout(()=> pop.remove(), 180);
                    document.removeEventListener('keydown', onKey);
                } catch(e){} };
            const onKey = (ev) => { if (ev.key === 'Escape') { ev.preventDefault(); cleanup(); } else if (ev.key === 'Enter') { ev.preventDefault(); confirmBtn.click(); } };
            function doConfirm() {
                try {
                    const handler = window.replicateST;
                    const payload = stInfo.row || stInfo;
                    if (typeof handler === 'function') {
                        const result = handler(payload, targetEl);
                        if (result && typeof result.then === 'function') {
                            confirmBtn.disabled = true; cancelBtn.disabled = true;
                            result.then(res => { cleanup(); showTransientPopover(targetEl, 'Replication started'); }).catch(err => { cleanup(); showTransientPopover(targetEl, 'Replication failed'); console.error(err); });
                            return;
                        }
                    }
                    cleanup();
                    showTransientPopover(targetEl, 'Replication started');
                } catch(err) { cleanup(); console.error(err); showTransientPopover(targetEl, 'Replication failed'); }
            }
            confirmBtn.addEventListener('click', doConfirm);
            cancelBtn.addEventListener('click', (e) => { e.stopPropagation(); cleanup(); });
            requestAnimationFrame(()=> confirmBtn.focus());
            document.addEventListener('keydown', onKey);
        } catch(e) {}
    }
}

	window.onload = function() {
	let yearStats = window.initialYearStats || {};
	console.log('server yearStats', yearStats);
	if (!yearStats || Object.keys(yearStats).length === 0) {
		const allData = window.fullListingData || [];
		console.log('fullListingData sample', allData.slice(0,20));
		const headers = window.fullListingHeaders || [];
		const lower = h => (h || '').toString().toLowerCase();
		const idxOngoing = headers.findIndex(h => lower(h).includes('ongoing'));
		const idxDissolved = headers.findIndex(h => {
			const lh = lower(h);
			return lh.includes('dissolved') || lh.includes('inactive');
		});
		console.log('status column indexes', idxOngoing, idxDissolved, headers);
		const hasStatusMark = v => {
			if (typeof v === 'boolean') return v;
			if (v == null) return false;
			const s = String(v).trim();
			if (!s || s === '0') return false;
			if (!isNaN(s)) return Number(s) !== 0;
			return true;
		};

		yearStats = {};
		allData.forEach(r => {
			const yr = r.year_of_moa || 'Unknown';
			if (!yearStats[yr]) {
				yearStats[yr] = { total: 0, ongoing: 0, dissolved: 0 };
			}
			yearStats[yr].total++;
			let st = (r.status || '').toString().toLowerCase();
			if (!st && idxOngoing !== -1) {
				const cell = r.row && r.row[idxOngoing];
				if (hasStatusMark(cell)) {
					st = 'ongoing';
				}
			}
			if (!st && idxDissolved !== -1) {
				const cell = r.row && r.row[idxDissolved];
				if (hasStatusMark(cell)) {
					st = 'dissolved';
				}
			}
			if (st.includes('ongoing') || st === 'on going') {
				yearStats[yr].ongoing++;
			} else if (st.includes('dissolved') || st.includes('inactive') || st.includes('completed')) {
				yearStats[yr].dissolved++;
			}
		});
		console.log('computed yearStats', yearStats);
	}
    const years = Object.keys(yearStats).sort();
    const totalCounts = years.map(y => yearStats[y].total);
    const ongoingCounts = years.map(y => yearStats[y].ongoing);
    const dissolvedCounts = years.map(y => yearStats[y].dissolved);

	const peakYearEl = document.getElementById('yearSummaryPeakYear');
	const peakCountEl = document.getElementById('yearSummaryPeakCount');
	const averageEl = document.getElementById('yearSummaryAverage');
	const latestYearEl = document.getElementById('yearSummaryLatestYear');
	const latestCountEl = document.getElementById('yearSummaryLatestCount');
	const spanEl = document.getElementById('yearSummarySpan');
	if (years.length > 0) {
		const peakCount = Math.max(...totalCounts);
		const peakIndex = totalCounts.indexOf(peakCount);
		const peakYear = years[peakIndex];
		const numericYears = years.filter(year => /^\d{4}$/.test(String(year)));
		const latestYear = numericYears.length > 0 ? numericYears[numericYears.length - 1] : years[years.length - 1];
		const latestCount = yearStats[latestYear] ? yearStats[latestYear].total : totalCounts[totalCounts.length - 1];
		const averageCount = totalCounts.reduce((sum, count) => sum + count, 0) / totalCounts.length;
		const firstYearSource = numericYears.length > 0 ? numericYears[0] : years[0];
		const firstYearNumeric = Number(firstYearSource);
		const latestYearNumeric = Number(latestYear);
		const spanText = Number.isFinite(firstYearNumeric) && Number.isFinite(latestYearNumeric)
			? ((latestYearNumeric - firstYearNumeric) + 1) + ' years'
			: years.length + ' years';

		if (peakYearEl) peakYearEl.textContent = peakYear;
		if (peakCountEl) peakCountEl.textContent = peakCount + ' recorded MOAs';
		if (averageEl) averageEl.textContent = averageCount.toFixed(1);
		if (latestYearEl) latestYearEl.textContent = latestYear;
		if (latestCountEl) latestCountEl.textContent = latestCount + ' recorded MOAs';
		if (spanEl) spanEl.textContent = spanText;
	}

	const totalOngoing = ongoingCounts.reduce((a,b)=>a+b, 0);
	const totalDissolved = dissolvedCounts.reduce((a,b)=>a+b, 0);
	const card1 = document.getElementById('card1');
	const card2 = document.getElementById('card2');
    const card3 = document.getElementById('card3');
    const card4 = document.getElementById('card4');

    const server = window.serverTotals || {};
    const allData = window.fullListingData || [];
    const truthy = v => (typeof v === 'boolean') ? v : (String(v||'').toLowerCase().trim() === 'true');
    const totalReplicated = (typeof server.totalReplicated === 'number') ? server.totalReplicated :
            allData.reduce((a,r)=> a + (truthy(r.with_replicated) ? 1 : 0), 0);
    const totalAdopted = (typeof server.totalAdopted === 'number') ? server.totalAdopted :
            allData.reduce((a,r)=> a + (truthy(r.with_adopted) ? 1 : 0), 0);

    if(card3) {
        const val = card3.querySelector('.card-value');
        if(val) val.textContent = totalReplicated;
    }
    if(card4) {
        const val = card4.querySelector('.card-value');
        if(val) val.textContent = totalAdopted;
    }

	const grandTotal = totalOngoing + totalDissolved;
	const ongoingPercent = grandTotal > 0 ? Math.round((totalOngoing / grandTotal) * 100) : 0;
	const dissolvedPercent = grandTotal > 0 ? Math.round((totalDissolved / grandTotal) * 100) : 0;

	const ongoingCountEl = document.getElementById('ongoingShareCount');
	const dissolvedCountEl = document.getElementById('dissolvedShareCount');
	const ongoingPercentEl = document.getElementById('ongoingSharePercent');
	const dissolvedPercentEl = document.getElementById('dissolvedSharePercent');
	const ongoingLeadEl = document.getElementById('ongoingShareLead');
	if (ongoingCountEl) ongoingCountEl.textContent = totalOngoing;
	if (dissolvedCountEl) dissolvedCountEl.textContent = totalDissolved;
	if (ongoingPercentEl) ongoingPercentEl.textContent = ongoingPercent + '% of status records';
	if (dissolvedPercentEl) dissolvedPercentEl.textContent = dissolvedPercent + '% of status records';
	if (ongoingLeadEl) {
		if (totalOngoing === totalDissolved) {
			ongoingLeadEl.textContent = 'Operational status is evenly split between active and dissolved records.';
		} else if (totalOngoing > totalDissolved) {
			ongoingLeadEl.textContent = 'Ongoing STs lead by ' + (totalOngoing - totalDissolved) + ' records.';
		} else {
			ongoingLeadEl.textContent = 'Inactive STs lead by ' + (totalDissolved - totalOngoing) + ' records.';
		}
	}

	function bindSummaryCard(cardEl, config) {
		if (!cardEl || !config || !window.openStSummaryModal) return;
		const open = function() {
			window.openStSummaryModal(config);
		};
		cardEl.addEventListener('click', open);
		cardEl.addEventListener('keydown', function(event) {
			if (event.key === 'Enter' || event.key === ' ') {
				event.preventDefault();
				open();
			}
		});
	}

	bindSummaryCard(card1, {
		title: 'Ongoing STs',
		filter: function(row) {
			const status = String(row.status || '').toLowerCase();
			return status.includes('ongoing') || status === 'on going';
		}
	});
	bindSummaryCard(card2, {
		title: 'Inactive STs',
		filter: function(row) {
			const status = String(row.status || '').toLowerCase();
			return status.includes('dissolved') || status.includes('inactive') || status.includes('completed');
		}
	});
	bindSummaryCard(card3, {
		title: 'Replicated STs',
		filter: function(row) {
			return truthy(row.with_replicated);
		}
	});
	bindSummaryCard(card4, {
		title: 'Adopted STs',
		filter: function(row) {
			return truthy(row.with_adopted);
		}
	});

	const centerTextPlugin = {
		id: 'centerText',
		afterDraw(chart, args, pluginOptions) {
			const { ctx, chartArea: { left, right, top, bottom } } = chart;
			const text = pluginOptions && pluginOptions.text ? pluginOptions.text : '';
			if (!text) return;
			ctx.save();
			const x = (left + right) / 2;
			const y = (top + bottom) / 2;
			ctx.font = (pluginOptions.font && pluginOptions.font.size ? pluginOptions.font.size : 32) + 'px ' + (pluginOptions.font && pluginOptions.font.family ? pluginOptions.font.family : 'sans-serif');
			ctx.fillStyle = pluginOptions.color || '#06306e';
			ctx.textAlign = 'center';
			ctx.textBaseline = 'middle';
			ctx.fillText(text, x, y);
			ctx.restore();
		}
	};

	if (window.Chart && Chart.register) {
		Chart.register(centerTextPlugin);
	}

	const ongoingDoughnutCanvas = document.getElementById('ongoingDoughnut');
	if (ongoingDoughnutCanvas && ongoingDoughnutCanvas.getContext) {
		const doughnutCtx = ongoingDoughnutCanvas.getContext('2d');
		new Chart(doughnutCtx, {
			type: 'doughnut',
			data: {
				labels: ['Ongoing STs', 'Inactive STs'],
				datasets: [{
					data: [totalOngoing, totalDissolved],
					backgroundColor: [
						'rgb(75, 192, 192)', // Ongoing
						'rgb(255, 99, 132)'  // Dissolved
					],
					borderColor: '#ffffff',
					borderWidth: 2
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				cutout: '70%',
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							boxWidth: 14,
							usePointStyle: true,
							font: { size: 11 }
						}
					},
					tooltip: {
						callbacks: {
							label(context) {
								const value = context.parsed;
								const total = grandTotal || 0;
								const pct = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
								return `${context.label}: ${value} (${pct}%)`;
							}
						}
					},
					centerText: {
						text: ongoingPercent + '%',
						color: '#06306e',
						font: { size: 30, family: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' }
					}
				}
			}
		});
	}

	const repTotal = totalReplicated + totalAdopted;
	const replicatedPercent = repTotal > 0 ? Math.round((totalReplicated / repTotal) * 100) : 0;
	const adoptedPercent = repTotal > 0 ? Math.round((totalAdopted / repTotal) * 100) : 0;

	const replicatedCountEl = document.getElementById('replicatedShareCount');
	const adoptedCountEl = document.getElementById('adoptedShareCount');
	const replicatedPercentEl = document.getElementById('replicatedSharePercent');
	const adoptedPercentEl = document.getElementById('adoptedSharePercent');
	const replicatedLeadEl = document.getElementById('replicatedShareLead');
	if (replicatedCountEl) replicatedCountEl.textContent = totalReplicated;
	if (adoptedCountEl) adoptedCountEl.textContent = totalAdopted;
	if (replicatedPercentEl) replicatedPercentEl.textContent = replicatedPercent + '% of replicated records';
	if (adoptedPercentEl) adoptedPercentEl.textContent = adoptedPercent + '% of adoption records';
	if (replicatedLeadEl) {
		if (totalReplicated === totalAdopted) {
			replicatedLeadEl.textContent = 'Replication and adoption activity are currently balanced.';
		} else if (totalReplicated > totalAdopted) {
			replicatedLeadEl.textContent = 'Replicated STs lead by ' + (totalReplicated - totalAdopted) + ' records.';
		} else {
			replicatedLeadEl.textContent = 'Adopted STs lead by ' + (totalAdopted - totalReplicated) + ' records.';
		}
	}

	const replicatedCanvas = document.getElementById('replicatedDoughnut');
	if (replicatedCanvas && replicatedCanvas.getContext) {
		const repCtx = replicatedCanvas.getContext('2d');
		new Chart(repCtx, {
			type: 'doughnut',
			data: {
				labels: ['Replicated STs', 'Adopted STs'],
				datasets: [{
					data: [totalReplicated, totalAdopted],
					backgroundColor: [
						'rgb(54, 162, 235)', // Replicated
						'rgb(255, 205, 86)'  // Adopted
					],
					borderColor: '#ffffff',
					borderWidth: 2
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				cutout: '70%',
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							boxWidth: 14,
							usePointStyle: true,
							font: { size: 11 }
						}
					},
					tooltip: {
						callbacks: {
							label(context) {
								const value = context.parsed;
								const total = repTotal || 0;
								const pct = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
								return `${context.label}: ${value} (${pct}%)`;
							}
						}
					},
					centerText: {
						text: replicatedPercent + '%',
						color: '#06306e',
						font: { size: 30, family: 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif' }
					}
				}
			}
		});
	}

	const overallTotalsData = [
		{ label: 'Expression of Interest', color: '#2dd4bf', value: allData.reduce((count, row) => count + (truthy(row.with_expr) ? 1 : 0), 0) },
		{ label: 'SB Resolution', color: '#38bdf8', value: allData.reduce((count, row) => count + (truthy(row.with_res) ? 1 : 0), 0) },
		{ label: 'Memorandum of Agreement', color: '#818cf8', value: allData.reduce((count, row) => count + (truthy(row.with_moa) ? 1 : 0), 0) },
		{ label: 'Ongoing STs', color: '#34d399', value: totalOngoing },
		{ label: 'Inactive STs', color: '#fb7185', value: totalDissolved },
		{ label: 'Replicated STs', color: '#f472b6', value: totalReplicated },
		{ label: 'Adopted STs', color: '#fbbf24', value: totalAdopted }
	];

	const documentCoverageCanvas = document.getElementById('documentCoverageChart');
	if (documentCoverageCanvas && documentCoverageCanvas.getContext) {
		const docCtx = documentCoverageCanvas.getContext('2d');
		new Chart(docCtx, {
			type: 'bar',
			data: {
				labels: overallTotalsData.map(item => item.label),
				datasets: [{
					data: overallTotalsData.map(item => item.value),
					backgroundColor: overallTotalsData.map(item => item.color),
					borderRadius: 12,
					maxBarThickness: 56
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				indexAxis: 'y',
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label(context) {
								return context.label + ': ' + context.parsed.y + ' records';
							}
						}
					}
				},
				scales: {
					x: {
						beginAtZero: true,
						ticks: {
							color: '#64748b',
							font: { size: 11, weight: '600' },
						},
						grid: { color: 'rgba(148, 163, 184, 0.18)' }
					},
					y: {
						ticks: {
							color: '#50657a',
							font: { size: 11, weight: '600' }
						},
						grid: { display: false }
					}
				}
			}
		});
	}

	const docLeaderEl = document.getElementById('docCoverageLeader');
	const docLowestEl = document.getElementById('docCoverageLowest');
	const sortedMilestones = overallTotalsData.slice().sort((left, right) => right.value - left.value);
	if (docLeaderEl && sortedMilestones.length) {
		docLeaderEl.textContent = sortedMilestones[0].label + ' (' + sortedMilestones[0].value + ')';
	}
	if (docLowestEl && sortedMilestones.length) {
		const lowest = sortedMilestones[sortedMilestones.length - 1];
		docLowestEl.textContent = lowest.label + ' (' + lowest.value + ')';
	}

	function renderRankingList(elementId, items, noun) {
		const container = document.getElementById(elementId);
		if (!container) return;
		if (!items.length) {
			container.innerHTML = '<div class="formal-ranking-item"><div class="formal-ranking-main"><div class="formal-ranking-label">No records available</div><div class="formal-ranking-meta">No ranking data could be derived.</div></div></div>';
			return;
		}
		container.innerHTML = items.map((item, index) => {
			return '<div class="formal-ranking-item">' +
				'<div class="formal-ranking-rank">#' + (index + 1) + '</div>' +
				'<div class="formal-ranking-main">' +
					'<div class="formal-ranking-label">' + item.name + '</div>' +
					'<div class="formal-ranking-meta">' + item.share + '% of total ' + noun + ' records</div>' +
				'</div>' +
				'<div class="formal-ranking-value">' + item.count + ' records</div>' +
			'</div>';
		}).join('');
	}

	function buildTopCounts(fieldName) {
		const counts = {};
		allData.forEach(row => {
			const rawValue = row[fieldName];
			const value = (rawValue == null ? '' : String(rawValue)).trim();
			if (!value || value.toLowerCase() === 'unknown') return;
			counts[value] = (counts[value] || 0) + 1;
		});
		const total = Object.values(counts).reduce((sum, count) => sum + count, 0);
		return Object.entries(counts)
			.sort((left, right) => right[1] - left[1])
			.slice(0, 5)
			.map(([name, count]) => ({
				name,
				count,
				share: total > 0 ? ((count / total) * 100).toFixed(1) : '0.0'
			}));
	}

	renderRankingList('topRegionsList', buildTopCounts('region'), 'regional');
	renderRankingList('topProvincesList', buildTopCounts('province'), 'provincial');

	function makeLineConfig(label, dataArray, color) {
		return {
			type: 'line',
			data: {
				labels: years,
				datasets: [{
					label: label,
					data: dataArray,
					borderColor: color,
					backgroundColor: color.replace('rgb', 'rgba').replace(')', ',0.2)'),
					fill: false,
					tension: 0.1
				}]
			},
			options: {
				responsive: true,
				scales: { y: { beginAtZero: true } }
			}
		};
	}

	const ongoingCtx = document.getElementById('onGoing').getContext('2d');
	new Chart(ongoingCtx, {
		type: 'line',
		data: {
			labels: years,
			datasets: [
				{
					label: 'Ongoing STs',
					data: ongoingCounts,
					borderColor: 'rgb(75, 192, 192)',
					backgroundColor: 'rgba(75, 192, 192, 0.2)',
					fill: false,
					tension: 0.1
				},
				{
					label: 'Inactive STs',
					data: dissolvedCounts,
					borderColor: 'rgb(255, 99, 132)',
					backgroundColor: 'rgba(255, 99, 132, 0.2)',
					fill: false,
					tension: 0.1
				}
			]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			scales: { y: { beginAtZero: true } }
		}
	});

	const regionYearCanvas = document.getElementById('regionYearLines');
	if (regionYearCanvas && regionYearCanvas.getContext) {
		const regionYearCtx = regionYearCanvas.getContext('2d');
		const allDataForRegions = window.fullListingData || [];
		const regionYearMap = {};
		allDataForRegions.forEach(function(row) {
			const regionRaw = (row.region || '').toString().trim();
			const year = (row.year_of_moa || '').toString().trim();
			if (!regionRaw || !year) return;
			if (!regionYearMap[regionRaw]) {
				regionYearMap[regionRaw] = {};
			}
			if (!regionYearMap[regionRaw][year]) {
				regionYearMap[regionRaw][year] = 0;
			}
			regionYearMap[regionRaw][year]++;
		});
		const regionNames = Object.keys(regionYearMap).sort();
		const matrixData = [];
		let maxVal = 0;
		regionNames.forEach(function(region) {
			const yearMap = regionYearMap[region] || {};
			years.forEach(function(y) {
				const v = yearMap[y] || 0;
				if (v > 0) {
					matrixData.push({ x: y, y: region, v: v });
					if (v > maxVal) maxVal = v;
				}
			});
		});
		if (matrixData.length && maxVal === 0) {
			maxVal = 1;
		}
		new Chart(regionYearCtx, {
			type: 'matrix',
			data: {
				datasets: [{
					label: 'STs by Region and Year',
					data: matrixData,
					backgroundColor: function(context) {
						const value = context.raw.v || 0;
						if (!maxVal) {
							return 'rgba(33, 150, 243, 0)';
						}
						const ratio = Math.min(1, value / maxVal);
						const alpha = 0.15 + 0.75 * ratio;
						return `rgba(33, 150, 243, ${alpha})`;
					},
					borderColor: 'rgba(255,255,255,0.8)',
					borderWidth: 1,
					width: function(context) {
						const chart = context.chart;
						const a = chart.chartArea;
						if (!a) {
							return 0;
						}
						return (a.right - a.left) / years.length - 2;
					},
					height: function(context) {
						const chart = context.chart;
						const a = chart.chartArea;
						if (!a) {
							return 0;
						}
						return (a.bottom - a.top) / Math.max(1, regionNames.length) - 2;
					}
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							title: function(items) {
								if (!items || !items.length) return '';
								const raw = items[0].raw || {};
								return `${raw.y} – ${raw.x}`;
							},
							label: function(context) {
								const raw = context.raw || {};
								return `${raw.v || 0} STs`;
							}
						}
					}
				},
				scales: {
					x: {
						type: 'category',
						labels: years,
						offset: true,
						title: { display: true, text: 'Year of MOA' },
						grid: { display: false }
					},
					y: {
						type: 'category',
						labels: regionNames,
						offset: true,
						reverse: true,
						title: { display: true, text: 'Region' }
					}
				}
			}
		});
	}

	};
        
	window.addEventListener('resize', debounce(adjustOverlayTotalsNumbers, 120));

	</script>

<style>
@media (max-width: 767px) {
	.st-dashboard-container { display: block !important; max-width: 100% !important; width: 100% !important; padding-top: 0 !important; overflow: visible !important; }
	.stb-main-content .st-dashboard-container { width: min(350px, calc(100vw - 24px)) !important; max-width: min(350px, calc(100vw - 24px)) !important; }
	.mobile-dashboard-container { display: block !important; }
	.st-dashboard-header-fullwidth { position: static !important; z-index: auto !important; }

	#ph-map-loading, #loading-overlay, .slider-modal-overlay, .slider-modal { display: none !important; visibility: hidden !important; pointer-events: none !important; }

	.map-overlay-totals .st-dashboard-card h1 { visibility: visible !important; }

	.formal-chart-panel canvas, canvas { display: block !important; width: 100% !important; height: auto !important; max-height: 720px !important; }

	html, body { overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
	setTimeout(function(){ try{ if(window.__stb_resizeAllCharts) window.__stb_resizeAllCharts(); }catch(e){} }, 800);
});
</script>

<style>
@media (max-width: 767px) {
	html, body { overflow-x: hidden !important; }
	.st-center-outer { padding: 0 !important; }
	.st-dashboard-container, .mobile-dashboard-container {
		width: calc(100vw - 24px) !important;
		max-width: calc(100vw - 24px) !important;
		margin: 12px auto !important;
		padding: 12px !important;
		box-sizing: border-box !important;
		transform: none !important;
	}
	.st-dashboard-header-fullwidth { position: static !important; margin-bottom: 12px !important; }
	.st-dashboard-container { padding-top: 0 !important; }

	.formal-st-top-grid, .formal-second-row-wrap, .formal-third-row-grid, .formal-mini-panel-group, .formal-linked-st-panels {
		grid-template-columns: 1fr !important;
		gap: 12px !important;
	}
	.formal-chart-canvas, .formal-chart-canvas-large, .formal-chart-canvas-medium, .formal-chart-canvas-region, .formal-chart-canvas-docflow {
		min-height: auto !important;
		padding: 8px !important;
	}
	.formal-chart-canvas-large { min-height: 300px !important; }
	.formal-chart-canvas-medium, .formal-chart-canvas-docflow { min-height: 240px !important; }
	.formal-chart-canvas-region { min-height: 260px !important; }

	.st-dashboard-card, .map-overlay-card { width: 100% !important; margin: 0 0 12px 0 !important; }

	#floatingBtn { right: 12px !important; top: 92px !important; }
}
</style>

	@if(auth()->check())
	<button id="floatingBtn" class="btn" aria-label="Open filters" data-bs-toggle="modal" data-bs-target="#filterModal">
		<img src="/images/dattachments/filtering%20icon.png" class="floating-btn-icon" alt="Filter" />
        <span class="filter-label">Filter</span>
    </button>
	@endif
	@guest
	<button id="guestFloatingBtn" class="btn" aria-label="Open filters for guests" onclick="event.stopPropagation(); return window.showGuestFilterDock && window.showGuestFilterDock(event)">
		<img src="/images/dattachments/filtering%20icon.png" class="floating-btn-icon" alt="Filter" />
		<span class="filter-label">Filter</span>
	</button>
	@endguest

<script>
window.setGuestFilterDockOpen = function(isOpen){
	var dock = document.getElementById('guestFilterDock');
	if(!dock) return false;
	var isDesktop = window.innerWidth > 767;
	var shouldOpen = isDesktop ? true : !!isOpen;
	dock.classList.remove('guest-filter-initial-open');
	dock.classList.toggle('open', shouldOpen);
	if (!isDesktop) {
		dock.style.display = shouldOpen ? 'flex' : 'none';
	} else {
		dock.style.display = shouldOpen ? 'block' : 'none';
	}
	document.body.classList.toggle('modal-open', !isDesktop && shouldOpen);
	document.body.classList.toggle('guest-filter-open', shouldOpen);
	return true;
};

window.closeGuestFilterDock = function(){
	return window.setGuestFilterDockOpen(false);
};

window.closeGuestMobileFilterPanel = function(){
	try{
		var panel = document.getElementById('guestMobileFilterPanel');
		if(panel && panel.parentNode){
			panel.parentNode.removeChild(panel);
		}
	}catch(e){}
	try{ document.body.classList.remove('modal-open'); }catch(e){}
	return false;
};

window.closeGuestFilterUi = function(ev){
	if(ev){
		try{ ev.preventDefault(); }catch(e){}
		try{ ev.stopPropagation(); }catch(e){}
	}
	window.closeGuestMobileFilterPanel();
	window.closeGuestFilterDock();
	return false;
};

window.isGuestFilterInteractionTarget = function(target){
	if(!target || typeof target.closest !== 'function') return false;
	return !!(
		target.closest('#guestFloatingBtn') ||
		target.closest('.guest-filter-panel') ||
		target.closest('.filter-modal-panel') ||
		target.closest('.guest-filter-close') ||
		target.closest('.select2-container') ||
		target.closest('.select2-dropdown') ||
		target.closest('.select2-results') ||
		target.closest('.select2-selection')
	);
};

window.showGuestFilterDock = function(ev){
	try{
		console.debug('[showGuestFilterDock] invoked');
		if(window.innerWidth <= 767){
			if(document.getElementById('guestMobileFilterPanel')){ console.debug('[showGuestFilterDock] mobile panel already open'); return false; }
			var existingDock = document.getElementById('guestFilterDock');
			if(existingDock){
				var wrapper = document.createElement('div');
				wrapper.id = 'guestMobileFilterPanel';
				wrapper.className = 'guest-mobile-filter-panel open';
				wrapper.style.position = 'fixed'; wrapper.style.inset='0'; wrapper.style.zIndex='2200'; wrapper.style.display='flex'; wrapper.style.alignItems='flex-end'; wrapper.style.justifyContent='center'; wrapper.style.background='rgba(6,48,110,0.12)';
				var inner = document.createElement('div');
				inner.className = 'filter-modal-panel mobile'; inner.style.display='block'; inner.style.width='100%'; inner.style.maxWidth='640px'; inner.style.borderRadius='12px 12px 0 0'; inner.style.margin='0'; inner.style.padding='0.5rem';
				inner.innerHTML = existingDock.innerHTML;
				wrapper.appendChild(inner);
				document.body.appendChild(wrapper);
				document.body.classList.add('modal-open');
				inner.addEventListener('click', function(e){ e.stopPropagation(); });
				wrapper.addEventListener('click', function(e){ if(!window.isGuestFilterInteractionTarget(e.target)){ window.closeGuestMobileFilterPanel(); } });
				document.addEventListener('keydown', function _esc(e){ if(e.key === 'Escape'){ window.closeGuestMobileFilterPanel(); document.removeEventListener('keydown', _esc); } });
				console.debug('[showGuestFilterDock] opened mobile panel by cloning guestFilterDock');
				return false;
			}
			var mobile = document.createElement('div');
			mobile.id = 'guestMobileFilterPanel'; mobile.className='guest-mobile-filter-panel open'; mobile.style.position='fixed'; mobile.style.inset='0'; mobile.style.zIndex='2200'; mobile.style.display='flex'; mobile.style.alignItems='flex-end'; mobile.style.justifyContent='center'; mobile.style.background='rgba(6,48,110,0.12)';
			mobile.innerHTML = '<div class="filter-modal-panel mobile" style="display:block!important;"><div class="card st-dashboard-card guest-filter-card"><div class="guest-filter-header"><div class="guest-filter-header-top"><div><div class="guest-filter-kicker">Dashboard Filters</div><div class="guest-filter-title">Filters (guest)</div></div><button type="button" class="guest-filter-close" aria-label="Close guest filters" onclick="return window.closeGuestFilterUi && window.closeGuestFilterUi(event)">&times;</button></div></div><div class="card-body guest-filter-body"><p>Please <a href="/login">log in</a> to access full filters, or reload the page.</p></div></div></div>';
			document.body.appendChild(mobile); document.body.classList.add('modal-open');
			var mobilePanel = mobile.querySelector('.filter-modal-panel');
			if (mobilePanel) mobilePanel.addEventListener('click', function(e){ e.stopPropagation(); });
			mobile.addEventListener('click', function(e){ if(!window.isGuestFilterInteractionTarget(e.target)){ window.closeGuestMobileFilterPanel(); } });
			document.addEventListener('keydown', function _esc2(e){ if(e.key === 'Escape'){ window.closeGuestMobileFilterPanel(); document.removeEventListener('keydown', _esc2); } });
			console.debug('[showGuestFilterDock] opened simple mobile fallback');
			return false;
		}

		var dock = document.getElementById('guestFilterDock');
		if(dock){
			if(dock.classList.contains('open')){ console.debug('[showGuestFilterDock] dock already open'); return false; }
			window.setGuestFilterDockOpen(true);
			console.debug('[showGuestFilterDock] showed guestFilterDock');
			return false;
		}

		console.debug('[showGuestFilterDock] no dock element found, nothing to show');
		return false;
	}catch(err){ console.error('showGuestFilterDock error', err); return false; }
};
</script>
	
@auth
	
	<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
	  <div class="modal-dialog modal-dialog-centered filter-modal-dialog" style="width:min(1280px, calc(100vw - 1.5rem)); max-width:none;">
        <div class="modal-content">
		  <div class="modal-body" id="filterModalBody" style="background:transparent;">
			<div class="year-filter-wrap filter-modal-wrap" style="width:min(1220px, calc(100vw - 2rem)); max-width:none; min-width:0;">
				<div class="card st-dashboard-card filter-modal-panel">
					<div class="filter-modal-header">
						<div class="filter-modal-heading">
							<div class="filter-modal-kicker">Dashboard Filters</div>
							<div class="filter-modal-title">Filter By Location &amp; Year</div>
							<div class="filter-modal-subtitle">Refine the dashboard by region, year, province, and city or municipality.</div>
						</div>
					</div>
					<div class="card-body filter-modal-body">
						<form method="GET" action="" class="w-100 d-flex flex-column">
							<div class="filter-form-grid">
								<div class="filter-field">
	                                <label for="region-select-modal" class="st-filter-label">Region</label>
	                                <select id="region-select-modal" name="region[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Regions" style="width:100%;">
                                @foreach($regions as $region)
                                @if (stripos($region, 'Data CY 2020-2022') === false)
                                <option value="{{ $region }}" {{ collect(request('region'))->contains($region) ? 'selected' : '' }}>{{ $region }}</option>
                                @endif
                                @endforeach
								</select>
								</div>

								<div class="filter-field">
	                                <label for="year-select-modal" class="st-filter-label">Year</label>
	                                <select id="year-select-modal" name="year_of_moa[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Years" style="width:100%;">
                                @foreach($years as $year)
                                <option value="{{ $year }}" {{ collect(request('year_of_moa'))->contains($year) ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
								</select>
								</div>

								<div class="filter-field">
	                                <label for="province-select-modal" class="st-filter-label">Province</label>
	                                <select id="province-select-modal" name="province[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Provinces" style="width:100%;">
                                @foreach($provinces as $province)
                                <option value="{{ $province }}" {{ collect(request('province'))->contains($province) ? 'selected' : '' }}>{{ $province }}</option>
                                @endforeach
								</select>
								</div>

								<div class="filter-field">
	                                <label for="municipality-select-modal" class="st-filter-label">City/Municipality</label>
	                                <select id="municipality-select-modal" name="municipality[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Cities/Municipalities" style="width:100%;">
                                @foreach($municipalities as $municipality)
                                <option value="{{ $municipality }}" {{ collect(request('municipality'))->contains($municipality) ? 'selected' : '' }}>{{ $municipality }}</option>
                                @endforeach
								</select>
								</div>
							</div>

	                            <div class="filter-modal-actions">
	                                <button type="button" class="btn filter-modal-secondary" data-bs-dismiss="modal">Close</button>
	                                <button type="submit" class="btn filter-modal-submit">Apply Filters</button>
	                            </div>
                        </form>
					  </div>
					</div>
					@endauth
            </div>
          </div>
        </div>
      </div>
    </div>
    <style>
        #floatingBtn {
            position: fixed;
			top: 112px;
            right: 22px;
			z-index: 920;
            min-width: 132px;
            min-height: 72px;
            background: linear-gradient(135deg, #ffffff 0%, #eef6ff 100%) !important;
            border: 3px solid #06306e;
            border-radius: 18px;
            color: #06306e;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 18px;
			box-shadow: none;
			transition: transform 0.25s ease, background 0.25s ease, border-color 0.25s ease;
            will-change: transform;
        }
        #floatingBtn .filter-label {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1;
        }
		#floatingBtn .floating-btn-icon {
			width: 30px;
			height: 30px;
			object-fit: contain;
			filter: none;
		}

		button#floatingBtn img.floating-btn-icon,
		button#guestFloatingBtn img.floating-btn-icon,
		#floatingBtn .floating-btn-icon,
		#guestFloatingBtn .floating-btn-icon {
			width: 28px !important;
			height: 28px !important;
			max-width: 28px !important;
			max-height: 28px !important;
			display: inline-block !important;
			object-fit: contain !important;
		}

		@media (max-width: 480px) {
			#guestFloatingBtn, #floatingBtn {
				padding: 8px 10px !important;
				min-width: 46px !important;
				min-height: 46px !important;
				border-radius: 12px !important;
			}
			#guestFloatingBtn .filter-label, #floatingBtn .filter-label {
				display: none !important;
			}
		}
		#floatingBtn:hover,
		#floatingBtn:focus-visible,
		#guestFloatingBtn:hover,
		#guestFloatingBtn:focus-visible {
			transform: translateY(-4px) scale(1.03);
			background: linear-gradient(135deg, #ffffff 0%, #dff6f7 100%) !important;
			border-color: #10aeb5;
			outline: none;
		}
		#floatingBtn:active,
		#guestFloatingBtn:active {
			transform: translateY(-1px) scale(0.99);
		}
#floatingBtn {
    display: none;
}

@auth
@media (min-width: 768px) {
    #floatingBtn {
        display: flex;
        position: fixed;
        top: 96px;
        right: 20px;
        z-index: 100;
    }
}
@endauth

@media (max-width: 767px) {
    #floatingBtn {
        display: flex;
        position: fixed;
        top: 96px;
        right: 12px;
        z-index: 9999;
    }
}
		#guestFloatingFilter {
			position: relative;
			top: auto;
			right: auto;
			z-index: auto;
			max-width: none;
			width: min(388px, calc(100vw - 1rem));
			padding-right: 20px;
		}

		#guestFloatingBtn {
			display: none !important;
			position: fixed;
			top: 96px;
			right: 20px;
			z-index: 9999;
			min-width: 60px;
			width:60px !important;
			min-height: 72px;
			background: linear-gradient(135deg, #ffffff 0%, #eef6ff 100%) !important;
			border: 3px solid #06306e;
			border-radius: 18px;
			color: #06306e;
			align-items: center;
			justify-content: center;
			gap: 10px;
			padding: 14px 18px;
			box-shadow: none;
			transition: transform 0.25s ease, background 0.25s ease, border-color 0.25s ease;
			will-change: transform;
		}

		body.guest-filter-open #guestFloatingBtn {
			opacity: 0;
			pointer-events: none;
		}

		#guestFloatingBtn .floating-btn-icon {
			width: 30px;
			height: 30px;
			object-fit: contain;
			display: block;
		}

		@media (max-width: 420px) {
			#guestFloatingBtn .filter-label { display: none; }
		}

		@media (max-width: 767px) {
			#guestFloatingBtn { display: flex !important; }
			.guest-filter-close { display: inline-flex; }
			#guestFilterDock { display: none; }
			#guestFilterDock.guest-filter-initial-open { display: none !important; }
			#guestFilterDock.open {
				display: flex;
				position: fixed;
				inset: 0;
				background: rgba(6,48,110,0.28);
				align-items: center;
				justify-content: center;
				padding: 1rem;
			}
			#guestFilterDock.open .guest-filter-panel {
				width: min(1220px, calc(100vw - 2rem));
				max-width: none;
				min-width: 0;
			}

			#guestFilterDock.open .guest-filter-card { display: block !important; }
		}
		#guestFilterDock {
			position: fixed;
			top: 112px;
			right: 18px;
			z-index: 890;
			display: block;
			width: min(388px, calc(100vw - 1rem));
			padding: 0;
			background: transparent;
			transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.28s ease;
			will-change: transform;
		}
		#guestFilterDock.guest-filter-initial-open {
			display: block;
		}
		#guestFilterDock.open {
			display: block;
		}
		.guest-filter-panel {
			width: 100%;
			position: relative;
		}
		.guest-filter-card {
			border: 1px solid rgba(16, 174, 181, 0.14) !important;
			border-radius: 24px !important;
			overflow: hidden;
			background:
				radial-gradient(circle at top left, rgba(16, 174, 181, 0.12), transparent 38%),
				linear-gradient(180deg, #ffffff 0%, #f7fbfd 100%) !important;
			box-shadow: 0 24px 56px rgba(6, 48, 110, 0.18) !important;
			box-shadow: 0 24px 56px rgba(6, 48, 110, 0.18) !important;
		}
		.guest-filter-header {
			padding: 1.15rem 1.2rem 0.95rem;
			border-bottom: 1px solid rgba(6, 48, 110, 0.08);
		}
		.guest-filter-header-top {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 0.75rem;
		}
		.guest-filter-close {
			display: none;
			border: 0;
			background: rgba(6, 48, 110, 0.08);
			color: #062c67;
			width: 38px;
			height: 38px;
			min-width: 38px;
			border-radius: 999px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 1.5rem;
			line-height: 1;
			cursor: pointer;
			transition: background 0.2s ease, transform 0.2s ease;
		}
		.guest-filter-close:hover,
		.guest-filter-close:focus-visible {
			background: rgba(6, 48, 110, 0.14);
			transform: translateY(-1px);
			outline: none;
		}
		.guest-filter-kicker {
			font-size: 0.7rem;
			font-weight: 800;
			letter-spacing: 0.14em;
			text-transform: uppercase;
			color: #10aeb5;
			margin-bottom: 0.45rem;
		}
		.guest-filter-title {
			font-size: 1.2rem;
			font-weight: 800;
			line-height: 1.15;
			color: #062c67;
		}
		.guest-filter-subtitle {
			margin-top: 0.45rem;
			font-size: 0.88rem;
			line-height: 1.55;
			color: #5f7891;
		}
		.guest-filter-body {
			padding: 1rem 1.2rem 1.2rem !important;
		}
		.guest-filter-grid {
			display: grid;
			grid-template-columns: minmax(0, 1fr);
			gap: 0.95rem;
			align-items: start;
		}
		.guest-filter-field {
			padding: 0.95rem;
			background: rgba(255, 255, 255, 0.82);
			border: 1px solid rgba(6, 48, 110, 0.08);
			border-radius: 18px;
			min-height: 122px;
		}
		.guest-filter-field-wide {
			grid-column: 1 / -1;
			min-height: 132px;
		}
		#guestFloatingFilter .st-filter-label {
			display: block;
			margin-bottom: 0.55rem;
			font-size: 0.76rem;
			font-weight: 800;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: #0a548a;
			padding-right: 20px;
		}
		#guestFloatingFilter .select2-container {
			width: 100% !important;
		}
		#guestFloatingFilter .select2-container--default .select2-selection--multiple {
			min-height: 58px;
			padding: 0.45rem 0.55rem;
			border-radius: 14px;
			border: 1px solid rgba(6, 48, 110, 0.12);
			background: #ffffff;
			box-shadow: 0 8px 18px rgba(6, 48, 110, 0.04);
		}
		#guestFloatingFilter .select2-container--default .select2-selection--multiple .select2-selection__rendered {
			display: flex;
			flex-wrap: wrap;
			gap: 0.4rem;
			align-items: flex-start;
			padding: 0;
		}
		#guestFloatingFilter .select2-container--default .select2-selection--multiple .select2-search--inline {
			flex: 1 1 100%;
			margin-top: 0.2rem;
		}
		#guestFloatingFilter .select2-container--default .select2-selection--multiple .select2-search__field {
			width: 100% !important;
			min-width: 8ch;
			margin-top: 0 !important;
			padding-left: 0.1rem;
		}
		#guestFloatingFilter .select2-container--default .select2-selection--multiple .select2-selection__choice {
			display: inline-flex;
			align-items: flex-start;
			max-width: 100%;
			margin-top: 0;
			border: none;
			border-radius: 14px;
			padding: 0.42rem 0.7rem;
			background: rgba(16, 174, 181, 0.14);
			color: #084a70;
			font-size: 0.8rem;
			font-weight: 700;
			line-height: 1.3;
			white-space: normal;
			word-break: break-word;
		}
		#guestFloatingFilter .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
			white-space: normal;
			overflow: visible;
			text-overflow: clip;
		}
		#guestFloatingFilter .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
			position: static;
			margin-right: 0.45rem;
			padding: 0;
			border: none;
			background: transparent;
			color: #0a548a;
			font-weight: 800;
		}
		.guest-filter-select2-dropdown .select2-results__option,
		.guest-filter-select2-dropdown.select2-dropdown .select2-results__option {
			height: auto !important;
			line-height: 1.35 !important;
			white-space: normal !important;
			word-break: break-word;
			padding: 0.7rem 0.9rem !important;
		}
		.guest-filter-select2-dropdown .select2-results__options,
		.guest-filter-select2-dropdown.select2-dropdown .select2-results__options {
			max-height: min(320px, 48vh) !important;
		}
		.guest-filter-actions {
			display: flex;
			gap: 0.75rem;
			margin-top: 1rem;
		}
		.guest-filter-secondary,
		.guest-filter-submit {
			flex: 1 1 0;
			min-height: 46px;
			border-radius: 999px;
			font-weight: 700;
		}
		.guest-filter-secondary {
			border: 1px solid rgba(6, 48, 110, 0.14);
			background: rgba(255, 255, 255, 0.9);
			color: #0a4f83;
		}
		.guest-filter-submit {
			border: none;
			color: #ffffff;
			background: linear-gradient(135deg, #06306e 0%, #0a5f96 52%, #10aeb5 100%);
			box-shadow: 0 12px 26px rgba(6, 48, 110, 0.2);
		}
		.guest-filter-submit:hover,
		.guest-filter-submit:focus-visible {
			transform: translateY(-1px);
		}
		@media (max-width: 991px) {
			#guestFilterDock {
				top: auto;
				bottom: 14px;
				right: 12px;
				width: min(100vw - 1rem, 420px);
			}

			.guest-filter-panel {
				width: 100%;
			}
		}
		@media (max-width: 767px) {
			.guest-filter-panel {
				width: min(100vw - 1rem, 360px);
			}

			.guest-filter-grid {
				grid-template-columns: 1fr;
			}

			.guest-filter-field,
			.guest-filter-field-wide {
				min-height: auto;
			}

			.guest-filter-actions {
				flex-direction: column-reverse;
			}
		}
		body.modal-open #floatingBtn {
			opacity: 0;
			pointer-events: none;
		}
		#filterModal .modal-dialog.filter-modal-dialog {
			width: min(1280px, calc(100vw - 1.5rem));
			max-width: none;
			margin: min(3vh, 1.5rem) auto;
			min-height: calc(100vh - 1.5rem);
			display: flex;
			align-items: center;
		}
		#filterModal .modal-content {
			background: transparent !important;
			border: none !important;
			box-shadow: none !important;
			width: 100%;
		}
		#filterModal .modal-dialog {
			background: transparent !important;
		}
		#filterModalBody {
			padding: 1rem;
			width: 100%;
			display: flex !important;
			justify-content: center;
			align-items: center;
			min-height: calc(100vh - 1.5rem);
			padding-left: 100px;
		}
		.filter-modal-wrap {
			flex: 1 1 100%;
			width: 100% !important;
			max-width: none !important;
			min-width: 0;
			margin: 0 auto !important;
			align-self: stretch;
			order: initial;
			display: block;
		}
		#filterModal .year-filter-wrap {
			position: static !important;
			right: auto !important;
			top: auto !important;
			transform: none !important;
		}
		#filterModalBody .filter-modal-panel {
			background:
				radial-gradient(circle at top left, rgba(16, 174, 181, 0.12), transparent 32%),
				linear-gradient(180deg, #ffffff 0%, #f7fbfd 100%) !important;
			box-shadow: 0 26px 60px rgba(6, 48, 110, 0.18) !important;
			border: 1px solid rgba(16, 174, 181, 0.14) !important;
			border-radius: 28px !important;
			overflow: hidden;
			width: 100%;
			position: relative;
		}
		#filterModal .filter-modal-body > form {
			width: 100%;
		}
		.filter-modal-header {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 1rem;
			padding: 1.75rem 5.5rem 1.25rem;
			border-bottom: 1px solid rgba(6, 48, 110, 0.08);
			text-align: center;
			position: relative;
		}
		.filter-modal-heading {
			max-width: 720px;
			margin: 0 auto;
		}
		.filter-modal-kicker {
			font-size: 0.72rem;
			font-weight: 800;
			letter-spacing: 0.18em;
			text-transform: uppercase;
			color: #10aeb5;
			margin-bottom: 0.45rem;
		}
		.filter-modal-title {
			font-size: 1.55rem;
			line-height: 1.15;
			font-weight: 800;
			color: #062c67;
		}
		.filter-modal-subtitle {
			margin-top: 0.45rem;
			font-size: 0.94rem;
			line-height: 1.6;
			color: #53718c;
			max-width: 620px;
			margin-left: auto;
			margin-right: auto;
		}
		.filter-modal-close {
		}
		.filter-modal-body {
			padding: 1.55rem 1.75rem 1.75rem;
		}
		.filter-form-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 1.15rem 1.2rem;
			width: 100%;
		}
		.filter-field {
			padding: 1rem 1.05rem 0.8rem;
			background: rgba(255, 255, 255, 0.82);
			border: 1px solid rgba(6, 48, 110, 0.08);
			border-radius: 18px;
			box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
			min-height: 148px;
		}
		.filter-form-grid .filter-field:first-child {
			grid-column: 1 / -1;
			min-height: 170px;
		}
		@media (min-width: 992px) {
			#filterModal .modal-dialog.filter-modal-dialog {
				width: min(1380px, calc(100vw - 1rem)) !important;
			}

			#filterModalBody .filter-modal-panel {
				min-width: 1180px;
			}

			.filter-modal-body {
				min-width: 1180px;
			}
		}
		#filterModal .st-filter-label {
			display: block;
			margin-bottom: 0.55rem;
			font-size: 0.78rem;
			font-weight: 800;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: #0a548a;
		}
		#filterModal .select2-container {
			width: 100% !important;
		}
		#filterModal .select2-container--default .select2-selection--multiple {
			min-height: 52px;
			padding: 0.35rem 0.55rem;
			border-radius: 14px;
			border: 1px solid rgba(6, 48, 110, 0.12);
			background: #ffffff;
			box-shadow: 0 8px 18px rgba(6, 48, 110, 0.04);
		}
		#filterModal .select2-container--default.select2-container--focus .select2-selection--multiple {
			border-color: rgba(16, 174, 181, 0.75);
			box-shadow: 0 0 0 4px rgba(16, 174, 181, 0.12);
		}
		#filterModal .select2-container--default .select2-search--inline .select2-search__field {
			margin-top: 4px;
			color: #33516b;
		}
		#filterModal .select2-container--default .select2-selection--multiple .select2-selection__choice {
			margin-top: 4px;
			border: none;
			border-radius: 999px;
			padding: 0.28rem 0.62rem;
			background: rgba(16, 174, 181, 0.14);
			color: #084a70;
			font-size: 0.82rem;
			font-weight: 700;
		}
		#filterModal .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
			color: #0a548a;
			margin-right: 0.35rem;
		}
		.filter-modal-actions {
			display: flex;
			justify-content: center;
			gap: 0.8rem;
			margin-top: 1.5rem;
			padding-top: 1.25rem;
			border-top: 1px solid rgba(6, 48, 110, 0.08);
		}
		.filter-modal-secondary,
		.filter-modal-submit {
			min-width: 152px;
			min-height: 48px;
			border-radius: 999px;
			font-weight: 700;
			letter-spacing: 0.01em;
		}
		.filter-modal-secondary {
			border: 1px solid rgba(6, 48, 110, 0.14);
			background: rgba(255, 255, 255, 0.85);
			color: #0a4f83;
		}
		.filter-modal-secondary:hover {
			background: #ffffff;
			color: #06306e;
		}
		.filter-modal-submit {
			border: none;
			color: #ffffff;
			background: linear-gradient(135deg, #06306e 0%, #0a5f96 52%, #10aeb5 100%);
			box-shadow: 0 14px 30px rgba(6, 48, 110, 0.22);
		}
		.filter-modal-submit:hover {
			color: #ffffff;
			transform: translateY(-1px);
			box-shadow: 0 18px 34px rgba(6, 48, 110, 0.28);
		}
		@media (max-width: 767px) {
			#filterModal .modal-dialog.filter-modal-dialog {
				width: calc(100vw - 1rem);
				margin: 0.5rem auto;
				min-height: calc(100vh - 1rem);
			}

			#filterModalBody {
				padding: 0.75rem;
				min-height: calc(100vh - 1rem);
			}

			.filter-modal-header {
				padding: 1.2rem 1.1rem 0.95rem;
				text-align: left;
			}

			.filter-modal-heading {
				max-width: none;
				margin: 0;
			}

			.filter-modal-subtitle {
				margin-left: 0;
				margin-right: 0;
			}

			.filter-modal-title {
				font-size: 1.28rem;
			}

			.filter-modal-body {
				padding: 1rem 1.1rem 1.15rem;
			}

			.filter-form-grid {
				grid-template-columns: 1fr;
			}

			.filter-field,
			.filter-form-grid .filter-field:first-child {
				min-height: auto;
			}

			.filter-modal-actions {
				flex-direction: column-reverse;
			}

			.filter-modal-secondary,
			.filter-modal-submit {
				width: 100%;
			}
		}
    </style>

<script>
document.addEventListener('DOMContentLoaded', function () {

	const movers = [];
	const btn = document.getElementById('floatingBtn');
	const guestDock = document.getElementById('guestFilterDock');
	const guestBtn = document.getElementById('guestFloatingBtn');
	if (btn && !btn.dataset.initialized) {
		btn.dataset.initialized = "true";
		movers.push(btn);
	}
	if (guestDock && !guestDock.dataset.initialized) {
		guestDock.dataset.initialized = "true";
		guestDock.addEventListener('click', function (ev) {
			if (window.innerWidth <= 767 && !window.isGuestFilterInteractionTarget(ev.target)) {
				window.closeGuestFilterDock();
			}
		});
		movers.push(guestDock);
	}
	if (guestBtn && !guestBtn.dataset.initialized) {
		guestBtn.dataset.initialized = "true";
		movers.push(guestBtn);
	}

	if (guestDock && window.innerWidth > 767) {
		try {
			document.body.appendChild(guestDock);
		} catch (e) {}
		guestDock.style.position = 'fixed';
		guestDock.style.top = '112px';
		guestDock.style.right = '18px';
		guestDock.style.left = 'auto';
		guestDock.style.bottom = 'auto';
		guestDock.style.zIndex = '890';
		guestDock.style.display = 'block';
		guestDock.style.width = 'min(388px, calc(100vw - 1rem))';
		guestDock.style.padding = '0';
		guestDock.style.background = 'transparent';
	}

	if (guestDock) {
		window.setGuestFilterDockOpen(window.innerWidth > 767);
	}

	if (!movers.length) return; 

    let lastScrollTop = window.scrollY;
    let timeout;

    const maxLag = 1000;     
    const scrollFactor = 0.2;
    const delayBeforeReturn = 400;
    let cumulative = 0;

	const states = new WeakMap();
	movers.forEach(function(el){
		states.set(el, { scrollOffset: 0, targetX: 0, targetY: 0, currentX: 0, currentY: 0 });
	});

	function applyTransforms(el) {
		const s = states.get(el) || { scrollOffset:0, currentX:0, currentY:0 };
		el.style.transform = `translate(${s.currentX}px, ${s.scrollOffset + s.currentY}px)`;
	}

	window.addEventListener('scroll', () => {
		let currentScroll = window.scrollY;
		let delta = currentScroll - lastScrollTop;

		cumulative += Math.abs(delta);
		let lag = Math.min(cumulative * scrollFactor, maxLag);

		movers.forEach(function(el) {
			const s = states.get(el);
			if (!s) return;
			if (delta > 0) {
				s.scrollOffset = -lag; 
			} else if (delta < 0) {
				s.scrollOffset = lag;  
			}
		});

		clearTimeout(timeout);
		timeout = setTimeout(() => {
			movers.forEach(function(el) {
				const s = states.get(el);
				if (s) s.scrollOffset = 0;
			});
			cumulative = 0; 
		}, delayBeforeReturn);

		lastScrollTop = currentScroll;
	});

	let pointerX = window.innerWidth / 2;
	let pointerY = window.innerHeight / 2;

	function onPointerMove(e) {
		const point = e.touches && e.touches[0] ? e.touches[0] : e;
		pointerX = point.clientX;
		pointerY = point.clientY;
		movers.forEach(function(el){
			if (!el.id || (el.id !== 'floatingBtn' && el.id !== 'guestFloatingBtn' && el.id !== 'guestFilterDock')) return;
			const rect = el.getBoundingClientRect();
			const cx = rect.left + rect.width / 2;
			const cy = rect.top + rect.height / 2;
			const dx = pointerX - cx;
			const dy = pointerY - cy;
			const isGuestDock = el.id === 'guestFilterDock';
			const limit = isGuestDock ? 10 : 18;
			const factor = isGuestDock ? 0.025 : 0.06;
			const targetX = Math.max(-limit, Math.min(limit, dx * factor));
			const targetY = Math.max(-limit, Math.min(limit, dy * factor));
			const s = states.get(el);
			if (s) { s.targetX = targetX; s.targetY = targetY; }
		});
	}

	window.addEventListener('mousemove', onPointerMove, { passive: true });
	window.addEventListener('touchmove', onPointerMove, { passive: true });

	function animate() {
		movers.forEach(function(el){
			const s = states.get(el);
			if (!s) return;
			s.currentX += (s.targetX - s.currentX) * 0.12;
			s.currentY += (s.targetY - s.currentY) * 0.12;
			if (Math.abs(s.scrollOffset) < 0.5) s.scrollOffset = 0;
			applyTransforms(el);
		});
		requestAnimationFrame(animate);
	}
	requestAnimationFrame(animate);

	document.addEventListener('pointerdown', function (ev) {
		const target = ev.target;
		const mobilePanel = document.getElementById('guestMobileFilterPanel');
		if (mobilePanel) {
			if (!window.isGuestFilterInteractionTarget(target)) {
				window.closeGuestMobileFilterPanel();
			}
			return;
		}

		const openDock = document.getElementById('guestFilterDock');
		if (window.innerWidth <= 767 && openDock && openDock.classList.contains('open')) {
			if (!window.isGuestFilterInteractionTarget(target)) {
				window.closeGuestFilterDock();
			}
		}
	}, true);

	document.addEventListener('click', function (ev) {
		const btnEl = (ev.target && typeof ev.target.closest === 'function') ? ev.target.closest('#guestFloatingBtn') : null;
		if (btnEl) {
			ev.stopPropagation();
			console.debug('[guestFloatingBtn] click detected on', btnEl);
			const dock = document.getElementById('guestFilterDock');
			if (!dock) {
				console.debug('[guestFloatingBtn] guestFilterDock not found');
				const authModal = document.getElementById('filterModal');
				if (authModal && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
					try {
						const m = window.bootstrap.Modal.getOrCreateInstance(authModal);
						m.show();
						console.debug('[guestFloatingBtn] fallback: showed auth filterModal via bootstrap');
					} catch (e) {
						console.warn('Could not show auth filter modal', e);
					}
					return;
				}
				console.warn('guestFilterDock not found and no auth modal fallback');
				return;
			}
			if (!movers.includes(btnEl)) {
				movers.push(btnEl);
				states.set(btnEl, { scrollOffset: 0, targetX: 0, targetY: 0, currentX: 0, currentY: 0 });
			}
			const opened = !dock.classList.contains('open');
			window.setGuestFilterDockOpen(opened);
			console.debug('[guestFloatingBtn] toggled guestFilterDock, opened=', opened);
			return;
		}

		const openDock = document.getElementById('guestFilterDock');
		if (window.innerWidth <= 767 && openDock && openDock.classList.contains('open')) {
			const target = ev.target;
			const clickedInsideDock = !!(target && openDock.contains(target));
			const clickedGuestButton = !!(target && typeof target.closest === 'function' && target.closest('#guestFloatingBtn'));
			if (!clickedInsideDock && !clickedGuestButton) {
				window.closeGuestFilterDock();
			}
		}
	});

	document.addEventListener('keydown', function (ev) {
		if (ev.key === 'Escape') {
			const openDock = document.getElementById('guestFilterDock');
			if (window.innerWidth <= 767 && openDock && openDock.classList.contains('open')) {
				window.closeGuestFilterDock();
			}
		}
	});





});
</script>
@endsection



