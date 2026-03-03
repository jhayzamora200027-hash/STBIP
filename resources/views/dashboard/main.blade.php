@extends('layouts.app')

@section('content')
@guest
<style>
    /* guest only: make parent layout full-width and left-aligned */
    .stb-main-content {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 50px !important;
        width: 100vw !important;
        max-width: 100vw !important;
    }
</style>
@endguest
<style>
	/* mobile mini layout adjustments removed for new mobile view */
</style>
<link href="/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<style>
/* Beautify Select2 dropdowns */
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

/* fixed height for each result and scrollable dropdown */
.select2-results__option,
.select2-dropdown .select2-results__option {
    height: 40px !important;
    line-height: 40px !important;
    padding: 0 14px !important; /* keep horizontal padding but remove vertical */
    box-sizing: border-box !important;
}
/* constrain the results container to eight rows */
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
		justify-content: flex-start; /* left align container */
		background: none;
		margin-left: 0 !important;
		margin-right: auto;   /* optional */
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
		font-size: 2.7rem;
		font-weight: bold;
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
			font-size: 1.5rem;
		}
		.st-dashboard-select-card select {
			width: 100%;
		}
		.st-dashboard-container {
			max-width: 99vw;
			padding: 8px 2vw 8px 2vw;
		}
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
		/* Force totals + filter to stay in columns on one row when printing */
		.st-totals-row {
			display: grid !important;
			grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
			grid-auto-rows: auto;
			column-gap: 6mm !important;
			row-gap: 3mm !important;
			max-width: 90% !important;
			margin-left: auto !important;
			margin-right: auto !important;
			align-items: stretch;
		}
		/* Place the two totals columns stacked on the left,
		   and the filter column spanning both rows on the right */
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
		/* Make totals + filter row more compact in print */
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
		/* Compact card paddings so header + map fit on first page */
		.st-dashboard-card .card-header {
			padding: 4px 0 !important;
			font-size: 0.9rem !important;
		}
		.st-dashboard-card .card-body {
			padding: 8px 8px 6px 8px !important;
		}
		/* Let the title listing table expand across pages cleanly */
		.st-title-listing-scroll {
			max-height: none !important;
			overflow: visible !important;
		}
		/* Keep Philippines map and region list side-by-side, like on screen */
		.st-map-card-body {
			display: flex !important;
			flex-wrap: nowrap !important;
			justify-content: flex-end !important;
			padding-right: 8px !important; /* allow group to reach right edge */
		}

		.st-map-figure-wrapper { margin-right: 8px !important; }

		@media (max-width: 991.98px) {
			.st-map-card-body { justify-content: center !important; padding-right: 24px !important; }
			.st-map-figure-wrapper, .st-map-region-list { margin-right: 0 !important; }
		}
		.st-map-figure-wrapper {
			position: relative !important;
			z-index: 1102 !important; /* keep map above the fixed totals */
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
			z-index: 1103 !important; /* ensure SVG sits above totals */
			pointer-events: auto !important;
			max-width: 105% !important;
			max-height: 105mm !important;
			display: block !important;
			margin-left: 0 !important;
			margin-right: auto !important;
			
		}
		/* Make region list text more compact in print */
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
		/* Smaller logos in print so header + map fit on first page */
		.st-header-logo {
			height: 70px !important;
			max-width: none !important;
		}
		/* Hide interactive map hint text when printing */
		#map-region-label {
			display: none !important;
		}
		/* Keep header + first map row together on page 1 if possible */
		.st-dashboard-header-fullwidth {
			page-break-after: avoid !important;
			break-after: avoid !important;
		}
		.st-first-map-row {
			margin-top: 2mm !important;
			page-break-before: avoid !important;
		}
		/* Avoid printing modals/tooltips */
		#region-titles-modal,
		#st-title-modal,
		#doughnutTooltip,
		#catListTooltip {
			display: none !important;
		}
	}
</style>

<style>
/* Force header logo width (override inline) */
		.st-dashboard-header .st-header-logo { width:600px !important; max-width:600px !important; height:140px !important; }
		/* Map overlay totals (desktop) + responsive adjustments */
.map-overlay-totals { position: absolute; bottom: 12px; left: -10px; right: auto; transform: none; width: auto; display: grid; grid-template-columns: 1fr; gap: 10px; background: transparent; border-radius: 12px; padding: 8px 12px 8px 26px; box-shadow: none; border: none; z-index: 5; justify-items: center; pointer-events: none; top: 70px; }
/* decorative vertical bar to the left of the stacked Philippines totals */
	.map-overlay-totals .st-dashboard-card { margin: 0; box-shadow: none; background: transparent; width: 300px; }
	.map-overlay-card { width: 150px; min-width: 150px; background: #ffffff; border: 2px solid #1e90ff; box-shadow: 0 6px 18px rgba(16,174,181,0.06), 0 0 0 6px rgba(30,144,255,0.04); border-radius: 10px; padding: 8px 6px; margin: 0; overflow: hidden; box-sizing: border-box; }
	/* disable hover lift/scale for totals cards */
	.map-overlay-card:hover { transform: none !important; box-shadow: none !important; }
	.map-overlay-card .card-body { padding: 6px 0; background: transparent; display: flex; align-items: center; justify-content: center; padding-top: 6px; padding-bottom: 6px; min-height: 140px; }
	.map-overlay-totals .st-dashboard-card .card-header {
		display: inline-block;
		width: 150px; /* pill width */
		max-width: 100%;
		margin: 0 auto -6px;
		font-size: 0.7rem;
		padding: 1px 4px;
		background: transparent; /* remove header background */
		background-image: none !important;
		color: #1e90ff; /* keep blue text */
		border: none;
		box-shadow: none;
		text-align: center;
		white-space: normal; /* allow wrapping */
		line-height: 1;
	}
	.map-overlay-totals .st-dashboard-card h1 { font-size: 3.2rem; margin: 8px 0 0 0; color: #1e90ff; line-height:1; font-weight:700; white-space:nowrap; display:block; transition: font-size 140ms ease; max-width: 100%; box-sizing: border-box; padding: 0 6px; overflow: hidden; text-overflow: clip; visibility: hidden; }

	/* Highlighted outer frame for the main Philippines total card (applies via .ph-frame class) */
	.ph-frame { position: relative; border-radius: 16px; }
	.ph-frame::before { content: ""; position: absolute; inset: -12px; border-radius: 18px; background: linear-gradient(180deg, rgba(122,235,226,0.08), rgba(16,174,181,0.02)); border: 4px solid rgba(16,174,181,0.22); box-shadow: 0 12px 36px rgba(16,174,181,0.10); pointer-events: none; z-index: -1; }

	/* Mobile: let overlay stack below the map instead of absolute overlay */
	@media (max-width: 991.98px) {
	  .map-overlay-totals { position: static; width: 100%; grid-template-columns: repeat(2, 1fr); margin-top: 12px; box-shadow: none; border: none; background: transparent; padding-left:12px; }}
/* Hide the duplicate totals (the original two columns) on wide screens */
@media (min-width: 992px) {
  .st-totals-row > .col-lg-4:nth-child(1),
  .st-totals-row > .col-lg-4:nth-child(2) { display: none !important; }
  .st-totals-row > .col-lg-4:nth-child(3) { margin-left: auto !important; }
}

/* Mobile: let overlay stack below the map instead of absolute overlay */
@media (max-width: 991.98px) {
  .map-overlay-totals { position: static; grid-template-columns: repeat(2, 1fr); margin-top: 12px; box-shadow: none; border: none; background: transparent; }
  .map-overlay-totals .st-dashboard-card .card-header { color: inherit; }
}
</style>

<style>
	/* Mobile dashboard container styles */
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
		.st-center-outer, .st-dashboard-container { display: none !important; }
	}
</style>

<!-- Desktop/Tablet Dashboard -->
<div class="st-center-outer" style="justify-content:flex-start;">
	 <div class="st-dashboard-container" style="padding-top:0; position:relative; overflow:hidden; width:1500px; min-width:300px; max-width:1500px !important; flex-shrink:0; margin:40px 0 40px 0 !important;">
	<!-- Mobile Dashboard Container (visible only on mobile) -->
	<div class="mobile-dashboard-container">
		<div class="st-dashboard-header" style="font-size:1.1rem; padding:12px 0; text-align:center; border-radius:14px 14px 0 0; margin-bottom:12px;">
			<img class="st-header-logo" src="{{ asset('images/dattachments/DSWD STB Bagong Pil logo white.png') }}" alt="DSWD Logo" style="height:80px; max-width:120px; background:transparent; display:block; margin:0 auto 8px auto;">
			Adopted & Replicated Social Technologies
		</div>
		<div style="padding:0 2vw;">
			<!-- Example: Show summary cards in a vertical stack for mobile -->
			<div class="card st-dashboard-card text-center" style="margin-bottom:12px;">
				<div class="card-header">TOTAL ADOPTED AND REPLICATED</div>
				<div class="card-body">
					<h1 class="js-total-count" style="font-size:2rem;">{{ collect($data)->filter(function($row){
						return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
					})->count() }}</h1>
				</div>
			</div>
			<div class="card st-dashboard-card text-center" style="margin-bottom:12px;">
				<div class="card-header">TOTAL EXPRESSION OF INTEREST</div>
				<div class="card-body">
					<h1 class="js-total-count" style="font-size:2rem;">{{ $totalExpr ?? 0 }}</h1>
				</div>
			</div>
			<div class="card st-dashboard-card text-center" style="margin-bottom:12px;">
				<div class="card-header">TOTAL SB RESOLUTION</div>
				<div class="card-body">
					<h1 class="js-total-count" style="font-size:2rem;">{{ $totalRes ?? 0 }}</h1>
				</div>
			</div>
			<div class="card st-dashboard-card text-center" style="margin-bottom:12px;">
				<div class="card-header">TOTAL MEMORANDUM OF AGREEMENT</div>
				<div class="card-body">
					<h1 class="js-total-count" style="font-size:2rem;">{{ $totalMoa ?? 0 }}</h1>
				</div>
			</div>
			<!-- Add more mobile-friendly dashboard content as needed -->
		</div>
	</div>
    <!-- mobile mini version visible only on narrow screens -->
	<!-- mobile mini-summary removed for new mobile view -->
	   {{-- <div class="no-print" style="position:absolute; top:12px; right:24px; z-index:5;">
		   <button type="button" class="btn btn-sm btn-primary" onclick="window.print()" style="background: linear-gradient(90deg, #10aeb5 60%, #1de9b6 100%); border: none; border-radius: 999px; padding: 6px 18px; font-weight: 600; box-shadow: 0 2px 6px rgba(16,174,181,0.35);">
			   Print / Save as PDF
		   </button>
	   </div> --}}
	   	<div class="st-dashboard-header st-dashboard-header-fullwidth">
	   		<div style="display:flex; flex-direction:row; align-items:center; justify-content:space-between; width:100%; min-height:100px; padding:10px 10px 10px 10px;">
	   			<div style="display:flex; align-items:flex-end; gap:24px; flex-wrap:wrap;">
					<img class="st-header-logo" src="{{ asset('images/dattachments/DSWD STB Bagong Pil logo white.png') }}" alt="DSWD Logo" style="height:200px; max-width:200px; background:transparent;">
	   			</div>
	   			<div style="text-align:right; font-size:1.6rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; flex:1; margin-left:32px;">
	   				Adopted and Replicated Social Technologies
	   			</div>
	   		</div>
	   	</div>
		<div class="container-fluid" style="max-width: 100%;">
				<!-- Philippines Map & Regions Section (moved to top as first chart) -->
				<div class="row mt-4 st-first-map-row">
					<div class="col-12 p-0">
						<div class="card st-dashboard-card flex-fill" style="width:100%;max-width:none;margin:0 auto;">
							<div class="card-header text-center">PHILIPPINES MAP & REGIONS</div>
								<div class="card-body st-map-card-body" style="padding: 24px 24px; display:flex; flex-wrap:wrap; gap:150px; justify-content:flex-end; align-items:flex-start; padding-left: 435px;">
									<div class="st-map-figure-wrapper" style="position:relative; flex: 0 0 420px; max-width:420px; width:420px; display:flex; flex-direction:column; align-items:center;">
										<object id="philippines-map" data="{{ asset('images/philippines.svg') }}" type="image/svg+xml" style="width:100%; max-width:420px; height:auto; display:block;"></object>
										<div id="map-region-label" style="margin-top:10px; font-size:0.95rem; font-weight:600; color:#10aeb5; text-align:center; min-height:22px;">
											Hover a region on the map
										</div>
									</div>
									<!-- totals overlay inserted inside the map -->
							<div class="map-overlay-totals" aria-hidden="false">
								<!-- single column, reordered as requested -->
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

							<div id="map-region-list" class="st-map-region-list" style="flex: 0 0 360px; max-width:360px; width:360px; align-self:stretch;"></div>
								</div>
							</div>
						</div>
					</div>

			
		</div>


			{{-- embed STsReport under map using iframe (isolated) --}}
			<div class="row mt-4">
			    <div class="col-12">
			        <iframe id="streportFrame" src="{{ route('streport') }}?embed=1" style="width:100%; height:600px; max-height:600px; border:none; transition: height 0.3s ease;" title="STsReport"></iframe>
			    </div>
			</div>
<!-- Doughnut Chart for ST Titles (inside main dashboard container, after totals/filtering) -->
			<div class="row mt-4">
				<div class="col-12 p-0">
					<div class="card st-dashboard-card flex-fill" style="width:100%;max-width:none;margin:0 auto;">
						<div class="card-header text-center">SUMMARY OF ST TITLES</div>
						<div class="card-body" style="padding: 32px 16px;">
							<div style="display: flex; flex-direction: row; align-items: flex-start; justify-content: center; gap: 32px; width: 100%;">
								 <!-- Additional Text Box --><div id="stTitlesInsight" style="
										bottom: 10px; 
										padding: 10px; 
										width: 500px;
										height:500px;
										left: 10px;
										border: 2px solid #333; 
										background-color: rgba(255,255,255,0.8);
										border-radius: 5px;
										font-weight: bold;
										position:absolute;
										
									;height:auto;font-size:1.1rem;line-height:1.4;">
										Insight
									</div>
								<div style="flex: 0 0 460px; width: 460px; max-width: 460px; min-width: 460px; height: 480px; display: flex; align-items: center; justify-content: center; position: relative; left:500px"> {{-- JR --}}
									<canvas id="stTitlesDoughnut" style="position:relative; z-index:2; width: 440px; height: 440px; max-width: 440px; min-width: 440px;"></canvas>
									<!-- Inner doughnut for titles at or below 0.5% -->
									<canvas id="stTitlesDoughnutLow" style="position:absolute; z-index:1; width: 220px; height: 220px; max-width: 240px; min-width: 200px; top:50%; left:50%; transform:translate(-50%, -50%);"></canvas>
									<div id="doughnutTooltip" style="position:absolute;pointer-events:none;z-index:20;display:none;"></div>
								</div>
								<div id="stCategoryList" style="flex: 0 0 370px; width: 370px; max-width: 370px; min-width: 370px; height: 440px; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; background: #fafdff; border-radius: 12px; box-shadow: 0 2px 8px rgba(16,174,181,0.07); padding: 12px 8px; margin-left: 500px;"></div> {{-- JR --}}
								<!-- Custom tooltip for category list -->

								<script>
								// Ensure tooltip is appended to body for global positioning
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
							</div>
					</div>
				</div>
			</div>

	<!-- Bar Chart for Year of MOA -->


	<div class="row mt-4">
		<div class="col-12 p-0">
			<div class="card st-dashboard-card year-of-moa-card flex-fill" style="width:100%;max-width:none;margin:0 auto;">
				<div class="card-header text-center">Total Social Technologies</div>
				<div class="card-body" style="padding: 32px 16px;">
<div style="display: flex; flex-direction: row; align-items: flex-start; justify-content: space-between; width: 100%; flex-wrap:nowrap; gap:24px; overflow-x:auto;">
    <!-- left block: cards and small charts -->
    <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: nowrap; overflow-x:auto;">
        <div class="small-cards-grid" style="flex: 0 0 420px;">
            <div id="card1" class="small-card">
                <div class="card-value">0</div>
                <div class="card-label">Ongoing STs</div>
            </div>
            <div id="card2" class="small-card">
                <div class="card-value">0</div>
                <div class="card-label">Dissolved STs</div>
            </div>
            <div id="card3" class="small-card">
                <div class="card-value">0</div>
                <div class="card-label">Replicated STs</div>
            </div>
            <div id="card4" class="small-card">
                <div class="card-value">0</div>
                <div class="card-label">Adopted STs</div>
            </div>
        </div>

        <!-- My Chart Container -->
        <div style="flex: 0 0 300px; display: flex; flex-direction: column; gap: 20px;">
            <!-- Top My Chart -->
            <div style="height: 200px; display: flex; align-items: center; justify-content: center;">
                <canvas id="onGoing" style="width: 300px; height: 200px;"></canvas>
            </div>

            <!-- Duplicate Line Chart -->
            <div style="width: 300px; height: 200px; display: flex; align-items: center; justify-content: center;">
                <canvas id="dissolved" style="width: 300px; height: 200px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Year MOA Bar on right side -->
    <div class="year-chart-wrap" style="flex: 0 0 400px; min-width:500px; height: 450px; display: flex; align-items: center; justify-content: center;">
        <canvas id="yearMoaBar" style="width: 580px; height: 500px;"></canvas>
    </div>
</div>

<style>
.small-cards-grid {
    display: grid;
    grid-template-columns: repeat(2, 200px);
    grid-template-rows: repeat(2, 200px);
    gap: 10px;
}
.small-card {
    width: 200px;
    height: 200px;
    background: #f8fafc;
    border: 1px solid #ccc;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px;
    box-shadow: 0 2px 8px rgba(16,174,181,0.05);
    transition: background 0.2s;
}
.small-card:hover {
    background: #eaf4fa;
}
.small-card .card-value {
    font-size: 2rem;
    font-weight: 700;
    color: #06306e;
}
.small-card .card-label {
    margin-top: 6px;
    font-size: 1rem;
    color: #222;
    text-align: center;
}
/* make dissolved count red */
#card2 .card-value {
    color: #ff4d4f;
}

</style>
						<!-- Filter (moved OUTSIDE the chart wrapper) -->
						{{-- <div class="year-filter-wrap" style="position:absolute; top:50%; transform:translateY(-50%); z-index:6; flex:0 0 320px; max-width:320px; width:320px;">
							<div class="card st-dashboard-card" style="min-height:360px; box-shadow:none; border:1px solid rgba(16,174,181,0.06);">
								<div class="card-header">FILTER BY LOCATION &amp; YEAR</div>
								<div class="card-body" style="padding:12px;">
									<form method="GET" action="" class="w-100 d-flex flex-column">
										<label for="region-select-orig" class="st-filter-label">Region</label>
										<select id="region-select-orig" name="region[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Regions" style="width:100%;">
											@foreach($regions as $region)
											@if (stripos($region, 'Data CY 2020-2022') === false)
											<option value="{{ $region }}" {{ collect(request('region'))->contains($region) ? 'selected' : '' }}>{{ $region }}</option>
											@endif
											@endforeach
										</select>

										<label for="year-select-orig" class="st-filter-label">Year</label>
										<select id="year-select-orig" name="year_of_moa[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Years" style="width:100%;">
											@foreach($years as $year)
											<option value="{{ $year }}" {{ collect(request('year_of_moa'))->contains($year) ? 'selected' : '' }}>{{ $year }}</option>
											@endforeach
										</select>

										<label for="province-select-orig" class="st-filter-label">Province</label>
										<select id="province-select-orig" name="province[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Provinces" style="width:100%;">
											@foreach($provinces as $province)
											<option value="{{ $province }}" {{ collect(request('province'))->contains($province) ? 'selected' : '' }}>{{ $province }}</option>
											@endforeach
										</select>

										<label for="municipality-select-orig" class="st-filter-label">City/Municipality</label>
										<select id="municipality-select-orig" name="municipality[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Cities/Municipalities" style="width:100%;">
											@foreach($municipalities as $municipality)
											<option value="{{ $municipality }}" {{ collect(request('municipality'))->contains($municipality) ? 'selected' : '' }}>{{ $municipality }}</option>
											@endforeach
										</select>

										<button type="submit" class="btn st-btn-gradient w-100 mt-2" style="background: linear-gradient(90deg, #06306e 60%, #06306e 100%); color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; padding: 10px 0; box-shadow: 0 2px 8px rgba(16, 174, 181, 0.08);">Filter</button>
									</form>
								</div>
							</div>
						</div> --}}
					</div>
				</div>
			</div>
		</div>
		</div>
<script>
								// move Year-of-MOA card above SUMMARY OF ST TITLES on load
								document.addEventListener('DOMContentLoaded', function(){
									try {
										const yearRow = document.querySelector('.year-of-moa-card')?.closest('.row.mt-4');
										const summaryHeader = Array.from(document.querySelectorAll('.card-header')).find(h => h.textContent && h.textContent.trim() === 'SUMMARY OF ST TITLES');
										const summaryRow = summaryHeader ? summaryHeader.closest('.row.mt-4') : null;
										if (yearRow && summaryRow && summaryRow.parentNode) {
											summaryRow.parentNode.insertBefore(yearRow, summaryRow);
										}
									} catch (e) { /* noop */ }
								});
							</script>

							<!-- Title Listing Section: Below Year of MOA -->

		<style>
		/* Responsive and fixed card/table for Title Listing */
		.st-title-listing-card {
			max-width: 98vw;
			width: 100%;
			margin: 0 auto;
			box-shadow: 0 2px 12px rgba(16, 174, 181, 0.10);
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
			/* Let the list expand naturally so all regions are visible
			   without an internal scrollbar; rely on page scroll instead. */
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
		.st-map-region-count {
			min-width: 34px;
			text-align: right;
			font-weight: 600;
			color: #10aeb5;
		}
		@media (max-width: 1400px) {
			.st-title-listing-card { max-width: 99vw; }
			.st-title-listing-table { min-width: 700px; }
		}
		.year-chart-wrap { order: 1; margin-right: auto; }
.year-filter-wrap { order: 2; margin-left: 24px; align-self: flex-start; }

/* Anchor filter inside the Year-of-MOA card on wide screens */
.year-of-moa-card .card-body { position: relative; }
@media (min-width: 992px) {
	/* absolutely position the filter to the card's right interior edge and vertically center it */
	.year-of-moa-card .year-filter-wrap { position: absolute !important; right: 24px; top: 50%; transform: translateY(-50%); width: 320px; z-index: 6; }
	/* reserve room for the filter so the chart never sits underneath */
}

@media (max-width: 991px) {
			.st-title-listing-card { max-width: 100vw; }
			.st-title-listing-table { min-width: 520px; }
			.year-filter-wrap { position: static !important; transform: none !important; right: auto !important; top: auto !important; flex: 0 0 100% !important; max-width: 100% !important; width: 100% !important; margin-top: 16px; order: 2; margin-left: 0; }
			.year-chart-wrap { order: 1; width:100% !important; flex: 1 1 100% !important; min-width: 0;}
			.year-filter-wrap .card { min-height: auto; }
		}
		@media (max-width: 767px) {
			.st-title-listing-card { max-width: 100vw; }
			.st-title-listing-table { min-width: 400px; font-size: 0.95em; }
		}

		</style>

		<style>
		/* Make the dashboard header fill the card's width, ignoring card padding */
		/* Full-width header, with space below for totals/filter */
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
		/* Simple custom modal for region ST titles */
		.st-region-modal-backdrop {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(15, 23, 42, 0.45);
			z-index: 1040;
		}
		.st-region-modal-dialog {
			position: fixed;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			background: #ffffff;
			border-radius: 16px;
			width: 90vw;
			max-width: 840px;
			max-height: 80vh;
			overflow: hidden;
			box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
			z-index: 1050;
			display: flex;
			flex-direction: column;
		}
		.st-region-modal-header {
			padding: 14px 20px;
			border-bottom: 1px solid #e0f2f1;
			display: flex;
			align-items: center;
			justify-content: space-between;
			background: linear-gradient(90deg, #06306e 60%, #06306e 100%);
			color: #ffffff;
		}
		.st-region-modal-header h5 {
			margin: 0;
			font-weight: 600;
			font-size: 1.05rem;
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
			padding: 16px 20px;
			background: #f8fafc;
			overflow-y: auto;
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
		<div class="row mt-4 justify-content-center">
			<div class="col-12">
				<div class="card st-dashboard-card st-title-listing-card flex-fill">
					<div class="card-header text-center">SOCIAL TECHNOLOGIES</div>
					<div class="card-body" style="padding: 20px 4px;">
						<div class="mb-2">
                                                    <input type="text" id="title-listing-search" class="form-control" placeholder="Search ST title" />
                                                </div>
                                                <div id="title-listing-table-container"></div>
						<script>
						// Pass all listing data to JS
						window.fullListingData = @json(collect($data)->filter(function($row){
							return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
						})->values());
					window.fullListingHeaders = @json($headers ?? []);
				// server-supplied totals for adopted/replicated (used by client script)
				window.serverTotals = {
				    totalReplicated: {{ $totalReplicated ?? 0 }},
				    totalAdopted: {{ $totalAdopted ?? 0 }}
				};
						</script>
					</div>
				</div>
			</div>
		</div>
		</div> <!-- end .container-fluid -->

		<!-- Region ST Titles Modal -->
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

		<!-- ST Title Details Modal -->
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

		<!-- ST Attachment View Modal -->
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



	        <!-- Slider image modal moved from STsReport (now in main layout for full-page display) -->
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

</div> <!-- end .st-dashboard-container -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>
	// Client-side pagination for Title Listing, with attachment icon
	(function() {
		const perPage = 10;
		let currentPage = 1;
		// keep a master copy plus filtered result used for paging
		const allData = window.fullListingData || [];
		let data = allData.slice();

		function escapeAttr(str) {
			return (str || '').toString().replace(/&/g, '&amp;').replace(/"/g, '&quot;');
		}

		function filterData(term) {
			term = (term || '').trim().toLowerCase();
			if (!term) {
				data = allData.slice();
			} else {
				data = allData.filter(r => (r.title||'').toLowerCase().includes(term));
			}
			currentPage = 1;
		}

		// wire up search field if present
		document.addEventListener('DOMContentLoaded', () => {
			const inp = document.getElementById('title-listing-search');
			if (inp) {
				inp.addEventListener('input', function() {
					filterData(this.value);
					renderTable(currentPage);
				});
			}
		});

		function renderTable(page) {
			const start = (page - 1) * perPage;
			const end = start + perPage;
			const pageData = data.slice(start, end);

			let html = '<div class="st-title-listing-scroll">';
			html += '<table class="table table-bordered table-striped align-middle mb-0 st-title-listing-table">';
			html += '<thead style="background:linear-gradient(90deg,#10aeb5 60%,#1de9b6 100%);color:#fff;"><tr>' +
				'<th style="width:340px;max-width:340px;">Title</th>' +
				'<th style="width:180px;max-width:180px;">Province</th>' +
				'<th style="width:180px;max-width:180px;">City/Municipality</th>' +
				'<th style="width:80px;max-width:80px; text-align:center;">Attachment</th>' +
			'</tr></thead><tbody>';
			if (pageData.length === 0) {
				html += '<tr><td colspan="4" class="text-center">No data found.</td></tr>';
			} else {
				pageData.forEach(row => {
					const title = row.title || '';
					const province = row.province || '';
					const municipality = row.municipality || '';
					const attachmentUrl = row.attachment_url || '';
					const uploadedBy = row.attachment_uploaded_by || '';
					let attachmentCell = '';
					if (attachmentUrl) {
						const safeTitle = escapeAttr(title);
						const safeUploader = escapeAttr(uploadedBy);
						attachmentCell = `<button type="button" class="btn btn-sm btn-outline-success st-attachment-view-btn" data-url="${attachmentUrl}" data-title="${safeTitle}" data-uploader="${safeUploader}" title="View attachment"><i class="bi bi-filetype-pdf"></i></button>`;
					}

					html += `<tr>
						<td title="${escapeAttr(title)}">${title ? title.substring(0, 60) : ''}</td>
						<td title="${escapeAttr(province)}">${province ? province.substring(0, 30) : ''}</td>
						<td title="${escapeAttr(municipality)}">${municipality ? municipality.substring(0, 30) : ''}</td>
						<td class="text-center">${attachmentCell}</td>
					</tr>`;
				});
				// Fill remaining rows with blanks to always show 10 rows
				for (let i = pageData.length; i < perPage; i++) {
					html += '<tr><td>&nbsp;</td><td></td><td></td><td></td></tr>';
				}
			}
			html += '</tbody></table></div>';

			// Pagination controls (modern, beautiful style)
			const totalPages = Math.ceil(data.length / perPage);
			html += `<style>
				.st-custom-pagination { display: flex; justify-content: center; align-items: center; gap: 14px; background: #f8fafc; border-radius: 12px; box-shadow: 0 2px 8px #b2ebf2; margin-top: 18px; padding: 10px 0 6px 0; }
				.st-custom-pagination-btn { border: none; background: linear-gradient(90deg, #06306e 60%, #06306e 100%); color: #fff; font-weight: 700; border-radius: 8px; padding: 7px 26px; font-size: 1.08em; box-shadow: 0 2px 8px #b2ebf2; outline: none; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; cursor: pointer; position: relative; }
				.st-custom-pagination-btn:disabled { background: #e0f7fa; color: #b0b0b0; box-shadow: none; cursor: not-allowed; }
				.st-custom-pagination-btn:not(:disabled):hover { background: linear-gradient(90deg, #06306e 60%, #06306e 100%); transform: translateY(-2px) scale(1.04); box-shadow: 0 4px 16px #b2ebf2; }
				.st-custom-pagination-indicator { font-weight: 600; color: #06306e; font-size: 1.13em; min-width: 110px; text-align: center; letter-spacing: 0.5px; }
			</style>`;
			html += '<div class="st-custom-pagination">';
			html += `<button class="st-custom-pagination-btn" ${page === 1 ? 'disabled' : ''} onclick="changePage(${page - 1})">&#8592; Prev</button>`;
			html += `<span class="st-custom-pagination-indicator">Page ${page} of ${totalPages}</span>`;
			html += `<button class="st-custom-pagination-btn" ${page === totalPages ? 'disabled' : ''} onclick="changePage(${page + 1})">Next &#8594;</button>`;
			html += '</div>';

			document.getElementById('title-listing-table-container').innerHTML = html;
		}

		window.changePage = function(page) {
			const totalPages = Math.ceil(data.length / perPage);
			if (page < 1 || page > totalPages) return;
			currentPage = page;
			renderTable(currentPage);
		};

		renderTable(currentPage);
	})();
	</script>
	<script>
	window.regionMap = @json($regionMap);
// compute master lists of provinces and cities for resetting filters
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
		$('.st-select2').select2({
			width: '100%',
			closeOnSelect: false,
			allowClear: true,
			placeholder: function(){ return $(this).data('placeholder'); },
			templateResult: function (data) { return data.text; },
			templateSelection: function (data) { return data.text; },
			escapeMarkup: function (markup) { return markup; }
		});

		// when the modal opens, ensure its selects are initialised with proper dropdown parent
		$('#filterModal').on('shown.bs.modal', function() {
			$(this).find('.st-select2').select2({
				width: '100%',
				closeOnSelect: false,
				allowClear: true,
				placeholder: function(){ return $(this).data('placeholder'); },
				templateResult: function (data) { return data.text; },
				templateSelection: function (data) { return data.text; },
				escapeMarkup: function (markup) { return markup; },
				dropdownParent: $('#filterModal')
			});
		});

		// Global click handler for attachment view buttons (map + title listing)
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

		// region text normalization helper (duplicated early for use inside propagateFilters)
		function inferRegionCodeFromRegionText(regionText) {
			if (!regionText) return null;
			const s = String(regionText).toLowerCase().trim();
			if (!s) return null;
			// named aliases
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

		// helper that applies selections to any gallery/iframe on the page
		function propagateFilters() {
			var selRegions = $('#region-select-orig').val() || [];
			var selYears = $('#year-select-orig').val() || [];
			// convert any FO-style text to canonical "Region X" codes for the slider
			selRegions = selRegions.map(function(r){
				return inferRegionCodeFromRegionText(r) || r;
			});
			// notify iframe about the new selections; we intentionally do **not**
			// reload it so the embedded report (totals, ST listing) stays fixed.
			var iframe = document.querySelector('iframe');
			if (iframe) {
				// tell the embedded report about the selections but indicate that
				// this message originates from the outer filter form; the iframe
				// should mirror slide/gallery visibility but not load new totals.
				try { iframe.contentWindow.postMessage({ type:'streportFilters', regions: selRegions, years: selYears, skipTotals: true }, '*'); } catch(e){}
			}			// local filtering of any card-gallery containers on this page
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

		// global listener for expand/collapse messages from iframe
if (!window._streportIframeExpanded) {
    window._streportIframeExpanded = false;
}
window.addEventListener('message', function(e) {
    if (e.data && e.data.type === 'streportToggleHeight') {
        const iframe = document.querySelector('#streportFrame') || document.querySelector('iframe[src*="/streport"]');
        if (!iframe) return;
        // ensure transition is set in case iframe was recreated; always reapply so collapse animates too
        iframe.style.transition = 'height 0.3s ease, max-height 0.3s ease';
        // make sure overflow is hidden so changing height doesn't reveal content abruptly
        iframe.style.overflow = 'hidden';
        if (e.data.height) {
            iframe.style.height = e.data.height;
            iframe.style.maxHeight = e.data.height;
            // remember expansion state based on height (600px is our default collapsed size)
            window._streportIframeExpanded = (e.data.height !== '600px');
        } else {
            // original toggle behaviour for backwards compatibility
            // use fixed 1500px expansion rather than full viewport height
            if (!window._streportIframeExpanded) {
                iframe.style.height = '1500px';
                iframe.style.maxHeight = '1500px';
            } else {
                iframe.style.height = '600px';
                iframe.style.maxHeight = '600px';
            }
            window._streportIframeExpanded = !window._streportIframeExpanded;
        }
        // when the iframe collapses, clear any filters inside it so it can't
        // later trigger a fetch for the wrong region when reopened.
        if (!window._streportIframeExpanded) {
            try {
                if (iframe.contentWindow && typeof iframe.contentWindow.resetRsmFilters === 'function') {
                    iframe.contentWindow.resetRsmFilters();
                    console.log('parent: resetRsmFilters invoked due to collapse');
                }
            } catch(e) { console.warn('parent: failed to reset filters on collapse', e); }
        }
        // if we've just expanded, bring the iframe into view and focus it after the transition completes
        if (window._streportIframeExpanded) {
            // wait until the CSS transition settles (roughly 300ms)
            setTimeout(() => {
                try {
                    iframe.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    iframe.focus && iframe.focus();
                } catch(err) { console.warn('scrollIntoView failed', err); }
            }, 350);
        }
    }
});

$('#region-select-orig').on('change', function() {
			var selectedRegions = $(this).val() || [];
			var provinces = [];
			var cities = [];
			var years = [];

			// When no region is selected, restore the full list of years,
			// provinces, and cities so everything reverts back.
			if (selectedRegions.length === 0) {
				var $yearAll = $('#year-select-orig');
				var selectedYearAll = $yearAll.val() || [];
				$yearAll.empty();
				(window.allYears || []).forEach(function(yr) {
					var selected = selectedYearAll.includes(yr) ? 'selected' : '';
					$yearAll.append('<option value="'+yr+'" '+selected+'>'+yr+'</option>');
				});
				$yearAll.trigger('change.select2');
				// restore provinces and cities as well
				var $provAll = $('#province-select-orig');
				var selProv = $provAll.val() || [];
				$provAll.empty();
				(window.allProvinces || []).forEach(function(p) {
					var selected = selProv.includes(p) ? 'selected' : '';
					$provAll.append('<option value="'+p+'" '+selected+'>'+p+'</option>');
				});
				$provAll.trigger('change.select2');
				var $cityAll = $('#municipality-select-orig');
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
			// use the full listing data for accurate province/city sets
			var allRows = window.fullListingData || [];
			allRows.forEach(function(row) {
				if (selectedRegions.includes(row.region)) {
					if (row.province) provinces.push(row.province);
					if (row.municipality) cities.push(row.municipality);
					if (row.year_of_moa) years.push(row.year_of_moa);
				}
			});

			// Remove duplicates
			provinces = [...new Set(provinces)];
			cities = [...new Set(cities)];
			years = [...new Set(years)];
			years.sort();

			// Update province dropdown
			var $province = $('#province-select-orig');
			var selectedProvince = $province.val() || [];
			$province.empty();
			provinces.forEach(function(prov) {
				var selected = selectedProvince.includes(prov) ? 'selected' : '';
				$province.append('<option value="'+prov+'" '+selected+'>'+prov+'</option>');
			});
			$province.trigger('change.select2');

			// Update city dropdown
			var $city = $('#municipality-select-orig');
			var selectedCity = $city.val() || [];
			$city.empty();
			cities.forEach(function(city) {
				var selected = selectedCity.includes(city) ? 'selected' : '';
				$city.append('<option value="'+city+'" '+selected+'>'+city+'</option>');
			});
			$city.trigger('change.select2');

			// Update year dropdown (based on selected regions)
			var $year = $('#year-select-orig');
			var selectedYear = $year.val() || [];
			$year.empty();
			years.forEach(function(yr) {
				var selected = selectedYear.includes(yr) ? 'selected' : '';
				$year.append('<option value="'+yr+'" '+selected+'>'+yr+'</option>');
			});
			$year.trigger('change.select2');
			propagateFilters();
		});

		// province change cascades to cities
		$('#province-select-orig').on('change', function() {
			var selectedRegions = $('#region-select-orig').val() || [];
			var selectedProvinces = $(this).val() || [];
			var cities = [];
			// if no filters, restore entire city list
			if (selectedRegions.length === 0 && selectedProvinces.length === 0) {
				var $cityAll = $('#municipality-select-orig');
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
			var $city = $('#municipality-select-orig');
			var selectedCity = $city.val() || [];
			$city.empty();
			cities.forEach(function(city) {
				var selected = selectedCity.includes(city) ? 'selected' : '';
				$city.append('<option value="'+city+'" '+selected+'>'+city+'</option>');
			});
			$city.trigger('change.select2');
		});

		// propagate when year selector is changed as well
		$('#year-select-orig').on('change', propagateFilters);

		// if the page loaded with preselected filters, fire change events
		// so dependent dropdowns update immediately
		$('#region-select-orig').trigger('change');
		$('#province-select-orig').trigger('change');
		// update iframe/gallery based on any initial filter values
		propagateFilters();
	});
	// Prepare data for the doughnut chart (ST Titles)
	// Build a map of title => count first
	const stTitleCounts = {};
	@foreach(collect($data)->filter(function($row){ return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']); }) as $row)
		stTitleCounts["{{ addslashes($row['title']) }}"] = (stTitleCounts["{{ addslashes($row['title']) }}"] || 0) + 1;
	@endforeach

	// Prepare data for the Year of MOA bar chart (use regionFilteredData, so it updates with region/province/municipality filters, but NOT year_of_moa)
	const yearMoaCounts = {};
	@foreach(collect($regionFilteredData ?? $data)->filter(function($row){ return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['year_of_moa']); }) as $row)
		yearMoaCounts["{{ addslashes($row['year_of_moa']) }}"] = (yearMoaCounts["{{ addslashes($row['year_of_moa']) }}"] || 0) + 1;
	@endforeach
	const yearMoaLabels = Object.keys(yearMoaCounts).sort();
	const yearMoaData = yearMoaLabels.map(y => yearMoaCounts[y]);

	// Highlight selected year bar
	const urlParams = new URLSearchParams(window.location.search);
	const selectedYear = urlParams.get('year_of_moa');
	const defaultColors = ['#10aeb5', '#1de9b6', '#b2ebf2', '#ffb74d', '#9575cd', '#f06292'];
	// Brighter highlight for selected bar
	function brighten(hex, percent) {
		// Simple hex color brightener
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
		: '#10aeb5'); // Gold glow for selected
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
							url.searchParams.delete('year_of_moa'); // Remove filter if clicking same year
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

		// Fading (smooth blinking) effect for selected year
		if (selectedYear) {
			const idx = yearMoaLabels.indexOf(selectedYear);
			if (idx !== -1) {
				let alpha = 0.2;
				let direction = 1; // 1 = fade in, -1 = fade out
				function fade() {
					alpha += direction * 0.04; // Adjust speed here
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

	// Convert map to sorted arrays so the biggest titles appear first
	const stTitleEntries = Object.entries(stTitleCounts).sort((a, b) => b[1] - a[1]); // [title, count]
	const stTitleLabels = stTitleEntries.map(e => e[0]);
	const stTitleData = stTitleEntries.map(e => e[1]);
	const stTitleColors = [
		'#10aeb5', '#1de9b6', '#b2ebf2', '#ffb74d', '#9575cd', '#f06292', '#4db6ac', '#ffd54f', '#ba68c8', '#81c784', '#e57373', '#64b5f6', '#a1887f', '#90a4ae', '#f8bbd0'
	];

	if (document.getElementById('stTitlesDoughnut')) {
		// Calculate percentages for the list
		const total = stTitleData.reduce((a, b) => a + b, 0);
		const stTitlePercentages = stTitleData.map(v => ((v / total) * 100));

			// update insight box with overall totals and some narrative explanation
			const insightEl = document.getElementById('stTitlesInsight');
			if (insightEl) {
				if (total > 0) {
					const topLabel = stTitleLabels[0] || '';
					const topPercent = stTitlePercentages[0] ? stTitlePercentages[0].toFixed(1) : '0.0';
					const titleCount = stTitleLabels.length;
					// additional narrative metrics
					const top3Percent = stTitlePercentages.slice(0,3).reduce((a,b)=>a+b,0).toFixed(1);
					const insightThreshold = 0.5;
					const lowTitles = stTitlePercentages.filter(p => p <= insightThreshold);
					const lowCount = lowTitles.length;
					const lowSum = lowTitles.reduce((a,b)=>a+b,0).toFixed(1);
					insightEl.style.display = '';
					// build structured narrative
					let narrative = '';
					// Portfolio Structure
					narrative += `<strong>Portfolio Structure</strong>: top 3 titles supply ${top3Percent}% of total adoption (${titleCount} titles).`;
					narrative += `<br>${lowCount} low‑share title(s) (≤ ${insightThreshold}%) together make up ${lowSum}% of STs, indicating a long‑tail innovation profile.`;
					// Scaling Efficiency placeholder (may require external data)
					narrative += `<br><br><strong>Scaling Efficiency</strong>: adoption intensity and replication metrics not shown here.`;
					// Risk & Stability
					narrative += `<br><br><strong>Risk & Stability</strong>: concentration risk exists with top programs dominating; long tail offers innovation reservoir.`;
					// Performance signal
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

		// Add aggregated "Others" slice if needed and map low titles to it
		if (othersTotal > 0) {
			const othersIndex = mainLabels.length;
			mainLabels.push('Others');
			mainData.push(othersTotal);
			mainColors.push(othersColor);
			Object.keys(globalToLowIndex).forEach(k => {
				globalToMainIndex[k] = othersIndex;
			});
		}

		// Expose mappings for hover behavior
		window.stTitleGlobalToMainIndex = globalToMainIndex;
		window.stTitleGlobalToLowIndex = globalToLowIndex;
		window.stTitleLowLabels = lowLabels;


		// Pagination for the list beside the chart (still shows all titles individually)
		const itemsPerPage = 10;
		let currentPage = 1;
		let doughnutChart = null;
		// State for continuous blinking of doughnut segments when hovering list items
		const doughnutBlinkState = {
			imer: null,
			mainIdx: null,
			lowIdx: null,
			visible: true
		};
		function stopDoughnutBlink() {
			const mainChart = window.doughnutChartInstance;
			const lowChart = window.doughnutChartLowInstance;
			if (doughnutBlinkState.timer) {
				clearInterval(doughnutBlinkState.timer);
				doughnutBlinkState.timer = null;
			}
			// Reset all arc borders to default
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
			doughnutBlinkState.mainIdx = null;
			doughnutBlinkState.lowIdx = null;
		}
		function startDoughnutBlink(mainIdx, lowIdx) {
			const mainChart = window.doughnutChartInstance;
			const lowChart = window.doughnutChartLowInstance;
			// If there is nothing to highlight, just exit
			if (!mainChart && !lowChart) return;
			// Clear any existing blink first
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
			}
			// Apply first frame immediately, then toggle visibility on a faster interval
			applyFrame();
			doughnutBlinkState.timer = setInterval(function() {
				doughnutBlinkState.visible = !doughnutBlinkState.visible;
				applyFrame();
			}, 180);
		}
		function renderCategoryList(page = 1) {
			const start = (page - 1) * itemsPerPage;
			const end = start + itemsPerPage;
			let listHtml = '<ul id="stCategoryListUl" style="list-style:none;padding:0 8px 0 0;margin:0;max-height:370px;overflow-y:auto;">';
			stTitleLabels.slice(start, end).forEach((label, i) => {
				const idx = start + i;
				const color = stTitleColors[idx % stTitleColors.length];
				const count = stTitleData[idx];
				const percent = stTitlePercentages[idx].toFixed(1);
				listHtml += `<li class=\"st-cat-list-item\" data-idx=\"${idx}\" style=\"display:flex;align-items:center;margin-bottom:14px;padding:10px 8px;border-radius:8px;background:#fff;box-shadow:0 1px 4px #e0f7fa;transition:box-shadow 0.2s;cursor:pointer;\">\n` +
					`<span style=\"display:inline-block;width:18px;height:18px;background:${color};border-radius:4px;margin-right:10px;box-shadow:0 1px 4px #b2ebf2;\"></span>` +
					`<span style=\"font-weight:700;font-size:1.08em;color:#10aeb5;min-width:72px;display:inline-block;\">${percent}% (${count})</span>` +
					`<span style=\"margin-left:8px;white-space:pre-line;word-break:break-word;\">${label}</span>` +
				`</li>`;
			});
			listHtml += '</ul>';
			// Pagination controls
			const totalPages = Math.ceil(stTitleLabels.length / itemsPerPage);
			if (totalPages > 1) {
				listHtml += `<div style=\"display:flex;justify-content:center;align-items:center;margin-top:8px;gap:8px;\">`;
				listHtml += `<button id=\"stCatPrevBtn\" ${page === 1 ? 'disabled' : ''} style=\"padding:4px 16px;border-radius:6px;border:1.5px solid #10aeb5;background:#fff;color:#10aeb5;font-weight:600;box-shadow:0 1px 4px #b2ebf2;transition:background 0.15s;\">Prev</button>`;
				listHtml += `<span style=\"margin:0 8px;font-weight:500;color:#10aeb5;\">Page ${page} of ${totalPages}</span>`;
				listHtml += `<button id=\"stCatNextBtn\" ${page === totalPages ? 'disabled' : ''} style=\"padding:4px 16px;border-radius:6px;border:1.5px solid #10aeb5;background:#fff;color:#10aeb5;font-weight:600;box-shadow:0 1px 4px #b2ebf2;transition:background 0.15s;\">Next</button>`;
				listHtml += `</div>`;
			}
			document.getElementById('stCategoryList').innerHTML = listHtml;
			// Add event listeners
			if (totalPages > 1) {
				document.getElementById('stCatPrevBtn').onclick = function() {
					if (currentPage > 1) {
						currentPage--;
						renderCategoryList(currentPage);
					}
				};
				document.getElementById('stCatNextBtn').onclick = function() {
					if (currentPage < totalPages) {
						currentPage++;
						renderCategoryList(currentPage);
					}
				};
			}
			// Highlight doughnut segment(s) on hover
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
						// Start continuous blinking highlight for the related segments
						startDoughnutBlink(mainIdx, lowIdx);
						// Show custom tooltip for list
						const label = stTitleLabels[idx];
						const percent = stTitlePercentages[idx].toFixed(1);
						catListTooltip.innerHTML = `<strong>${label}</strong><br><span style='color:#1de9b6;font-weight:600;'>${percent}%</span>`;
						catListTooltip.style.display = 'block';
						// Position tooltip at main doughnut segment centroid (absolute to viewport)
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
								// Get canvas position relative to viewport
								const canvas = mainChart.canvas;
								const rect = canvas.getBoundingClientRect();
								// Offset tooltip to center above the arc centroid
								catListTooltip.style.left = (rect.left + x - catListTooltip.offsetWidth/2) + 'px';
								catListTooltip.style.top = (rect.top + y - catListTooltip.offsetHeight/2) + 'px';
								placed = true;
							}
						}
						if (!placed) {
							// fallback: center of window
							catListTooltip.style.left = (window.innerWidth/2 - catListTooltip.offsetWidth/2) + 'px';
							catListTooltip.style.top = (window.innerHeight/2 - catListTooltip.offsetHeight/2) + 'px';
						}
					});
					item.addEventListener('mouseleave', function() {
						// Stop blinking and reset all segments
						stopDoughnutBlink();
						catListTooltip.style.display = 'none';
					});
					item.addEventListener('click', function() {
						const idx = parseInt(this.getAttribute('data-idx'));
						const label = stTitleLabels[idx];
						if (window.openStTitleModal) {
							window.openStTitleModal(label);
						}
					});
				});
			}, 10);
		}

		// Draw the main chart (grouping low-percentage titles into "Others")
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
						// Ignore aggregated "Others" slice
						if (!label || label === 'Others') return;
						if (window.openStTitleModal) {
							window.openStTitleModal(label);
						}
					}
				}
			});
		}

		// Draw the secondary chart showing only low-percentage titles (<= 0.5%)
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
						// Disable the inner chart's built-in tooltip to avoid overlapping labels; we'll use a custom tooltip instead
						tooltip: { enabled: false }
					},
					// Custom hover handler so inner slices still show their name/percent,
					// using the outer chart's global percentages and positioning over the
					// corresponding (aggregated) slice in the outer doughnut.
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
							// Position tooltip centered over the corresponding outer slice ("Others").
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
							// Fallback: near the mouse pointer if we can't find the outer slice
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
		// Relay hover/click events from the outer doughnut canvas to the inner one
		// so both charts remain interactive even though the outer canvas sits on top.
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

		// Modal helpers for ST title details
		function openStTitleModal(stTitle) {
			const modal = document.getElementById('st-title-modal');
			if (!modal) return;
			const titleEl = document.getElementById('st-title-modal-title');
			const bodyEl = document.getElementById('st-title-modal-body');
			if (titleEl) {
				titleEl.textContent = stTitle || 'ST Title';
			}
			const data = window.fullListingData || [];
			const target = (stTitle || '').toString().trim().toLowerCase();
			let rows = [];
			if (Array.isArray(data) && target) {
				rows = data.filter(function(row) {
					const rowTitle = (row.title || '').toString().trim().toLowerCase();
					return rowTitle === target;
				});
			}
			if (!bodyEl) return;
			if (!rows.length) {
				bodyEl.innerHTML = '<p style="margin:0; color:#64748b;">No records found for this ST title based on the current filters.</p>';
			} else {
				let html = '<div style="max-height:60vh;overflow:auto;">';
				html += '<table class="table table-sm table-striped table-bordered mb-0">';
				html += '<thead><tr><th>Region</th><th>Province</th><th>City/Municipality</th><th>Year of MOA</th><th class="text-center">Attachment</th></tr></thead><tbody>';
				rows.forEach(function(row) {
					const region = row.region || '';
					const province = row.province || '';
					const municipality = row.municipality || '';
					const year = row.year_of_moa || '';
					const attachmentUrl = row.attachment_url || '';
					const uploadedBy = row.attachment_uploaded_by || '';
					let attachmentCell = '';
					if (attachmentUrl) {
						const safeTitle = (stTitle || '').toString().replace(/&/g,'&amp;').replace(/"/g,'&quot;');
						const safeUploader = (uploadedBy || '').toString().replace(/&/g,'&amp;').replace(/"/g,'&quot;');
						attachmentCell = '<button type="button" class="btn btn-sm btn-outline-success st-attachment-view-btn" data-url="' + attachmentUrl + '" data-title="' + safeTitle + '" data-uploader="' + safeUploader + '" title="View attachment"><i class="bi bi-filetype-pdf"></i></button>';
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

		// Attachment modal helpers (shared by map + title listing)
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

		// Simple modal helpers for region ST titles
		function openRegionTitlesModal(regionDisplayName, rows) {
			const modal = document.getElementById('region-titles-modal');
			if (!modal) return;
			const titleEl = document.getElementById('region-titles-modal-title');
			const bodyEl = document.getElementById('region-titles-modal-body');
			if (titleEl) {
				titleEl.textContent = regionDisplayName || 'Region';
			}
			if (bodyEl) {
				if (!rows || !rows.length) {
					bodyEl.innerHTML = '<p style="margin:0; color:#64748b;">No ST titles for this region based on the current filters.</p>';
				} else {
					let html = '';
					rows.forEach(function(row) {
						const title = row.title || '';
						const province = row.province || '';
						const municipality = row.municipality || '';
						const uploadedBy = row.attachment_uploaded_by || '';
						const attachmentUrl = row.attachment_url || '';
						html += '<div class="st-region-title-item">';
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
							html += '<button type="button" class="btn btn-sm btn-outline-success st-attachment-view-btn" data-url="' + attachmentUrl + '" data-title="' + safeTitle + '" data-uploader="' + safeUploader + '" title="View attachment"><i class="bi bi-filetype-pdf"></i></button>';
						}
						html += '</div>';
						html += '</div>';
					});
					bodyEl.innerHTML = html;
				}
			}
			modal.style.display = 'block';
			// Prevent background scroll while modal is open
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

		// Region-level hover + click for Philippines SVG map embedded via <object>
		function initPhilippinesMapHover() {
			const phMapObject = document.getElementById('philippines-map');
			if (!phMapObject) return;
			// Prevent running twice if both the load handler and timeout fire
			if (phMapObject.dataset.phRegionsBound === '1') return;
			const regionLabelEl = document.getElementById('map-region-label');
			const svgDoc = phMapObject.contentDocument || (phMapObject.getSVGDocument && phMapObject.getSVGDocument());
			if (!svgDoc) return;
			const paths = svgDoc.querySelectorAll('path');
			if (!paths.length) return;

			// Map provinces (by their SVG title attribute) to administrative regions
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
			const provinceRegionIndex = {};
			const pathInfos = [];
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
			// Timers used for blinking highlights when hovering region list items
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
				// Set base fill by region so each region has a distinct color
				path.style.fill = info.originalFill;
				path.style.transition = 'fill 0.15s ease-out, stroke 0.15s ease-out, stroke-width 0.15s ease-out';
				path.style.cursor = 'pointer';
			});

			// Build counts per region using fullListingData and generic region inference
			const dataForCounts = window.fullListingData || [];
			const regionCounts = {};
			if (Array.isArray(dataForCounts) && dataForCounts.length) {
				dataForCounts.forEach(function(row) {
					const rName = inferRegionCodeFromRow(row, provinceRegionIndex);
					if (!rName) return;
					regionCounts[rName] = (regionCounts[rName] || 0) + 1;
				});
			}

			// Render region list with counts beside the map
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
				// Helper to get a representative path info for a region
				function getRegionRepresentativeInfo(regionCode) {
					const arr = regionToPaths[regionCode];
					return (arr && arr.length) ? arr[0] : null;
				}
				function startRegionBlink(regionCode) {
					if (!regionCode) return;
					const info = getRegionRepresentativeInfo(regionCode);
					if (!info) return;
					// Ensure any existing timer for this region is cleared first
					if (regionBlinkTimers[regionCode]) {
						clearInterval(regionBlinkTimers[regionCode]);
					}
					let visible = true;
					// Immediately highlight once
					highlightGroup(info, true, { color: '#ffeb3b', stroke: '#f9a825' });
					regionBlinkTimers[regionCode] = setInterval(function() {
						visible = !visible;
						highlightGroup(info, visible, { color: '#ffeb3b', stroke: '#f9a825' });
					}, 450);
				}
				function stopRegionBlink(regionCode) {
					if (!regionCode) return;
					const timer = regionBlinkTimers[regionCode];
					if (timer) {
						clearInterval(timer);
						delete regionBlinkTimers[regionCode];
					}
					const info = getRegionRepresentativeInfo(regionCode);
					if (info) {
						highlightGroup(info, false);
					}
				}
				// Bind hover and click events from list rows to the map regions
				const listRows = regionListEl.querySelectorAll('.st-map-region-row');
				listRows.forEach(function(row) {
					const code = row.getAttribute('data-region');
					row.addEventListener('mouseenter', function() {
						if (regionLabelEl) {
							if (code && regionLabels[code]) {
								regionLabelEl.textContent = regionLabels[code];
							} else if (code) {
								regionLabelEl.textContent = code;
							}
						}
						startRegionBlink(code);
					});
					row.addEventListener('mouseleave', function() {
						stopRegionBlink(code);
						if (regionLabelEl) {
							regionLabelEl.textContent = 'Hover a region on the map';
						}
					});
					row.addEventListener('click', function() {
						const info = getRegionRepresentativeInfo(code);
						if (info) {
							handleRegionClick(info);
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
				const highlightFill = options && options.color ? options.color : '#10aeb5';
				const highlightStroke = options && options.stroke ? options.stroke : '#1de9b6';

				targets.forEach(info => {
					if (isHover) {
						info.path.style.fill = highlightFill;
						info.path.style.stroke = highlightStroke;
						info.path.style.strokeWidth = '1.5';
					} else {
						info.path.style.fill = info.originalFill;
						info.path.style.stroke = info.originalStroke;
						info.path.style.strokeWidth = info.originalStrokeWidth;
					}
				});
			}

			function normalizeProvinceName(name) {
				if (!name) return '';
				let n = String(name).toLowerCase().trim().replace(/\s+/g, ' ');
				// Handle known variants where data and SVG use different word order
				// e.g. "Occidental Mindoro" vs "Mindoro Occidental", "Oriental Mindoro" vs "Mindoro Oriental"
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
				// Named-region aliases
				if (s.includes('national capital') || s.includes(' ncr') || s.startsWith('ncr')) return 'NCR';
				if (s.includes('ilocos')) return 'Region I';
				if (s.includes('cagayan valley')) return 'Region II';
				if (s.includes('central luzon')) return 'Region III';
				// accept a couple of common variants/misspellings
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
				// Only treat explicit BARMM/Bangsamoro labels as BARMM; avoid loose 'armm' matches
				if (s.includes('bangsamoro') || /\bbarmm\b/.test(s)) return 'BARMM';
				// CAR (Cordillera Administrative Region) but not CARAGA
				if (!s.includes('caraga') && (s === 'car' || s.includes('cordillera') || /\bcar\b/.test(s))) {
					return 'CAR';
				}
				// Generic roman-numeral based detection (handles "FO I", "Region VI", etc.)
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
				// Also infer from the region/field-office text
				const byRegionText = inferRegionCodeFromRegionText(row.region);
				// Special case: provinces like Basilan, Sulu, Tawi-Tawi appear in the FO IX sheet.
				// When the map groups them under BARMM but the region text clearly says Region IX,
				// treat them as Region IX so counts match the FO IX tab.
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
					// For region clicks, always use the same inference as the counts
					// so the modal total matches the number in the side list.
					rows = data.filter(function(row){
						return inferRegionCodeFromRow(row, provinceRegionIndex) === regionName;
					});
				} else {
					// Fallback: if this path isn't mapped to a region, filter by its province only.
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
			}

			pathInfos.forEach(info => {
				const p = info.path;
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
				});
				p.addEventListener('mouseleave', function () {
					highlightGroup(info, false);
					if (regionLabelEl) {
						regionLabelEl.textContent = 'Hover a region on the map';
					}
				});
				p.addEventListener('click', function () {
					handleRegionClick(info);
				});
			});

			phMapObject.dataset.phRegionsBound = '1';
		}

		const phMapObject = document.getElementById('philippines-map');
		if (phMapObject) {
			phMapObject.addEventListener('load', function () {
				setTimeout(initPhilippinesMapHover, 0);
			});
			// Fallback in case the load event fired before this script
			setTimeout(initPhilippinesMapHover, 500);
		}
	}

	// Auto-fit overlay totals' numbers to their card area (grow to fill, shrink for long numbers)
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

			// start from a large font based on available height but cap by width-per-digit
			let font = Math.floor(maxH * 0.65);
			const digits = (el.textContent || '').trim().length || 1;
			const approxPerDigit = Math.max(8, Math.floor(maxW / Math.max(1, digits)));
			font = Math.min(font, approxPerDigit * Math.floor(1.05 * Math.max(1, digits) / Math.max(1, digits)));

			// reduce starting font for very long numbers so loop converges quickly
			if (digits > 4) font = Math.floor(font * Math.max(0.6, 4 / digits));

			el.style.whiteSpace = 'nowrap';
			el.style.lineHeight = '1';
			el.style.fontSize = font + 'px';

			// shrink until it fits horizontally and vertically
			while ((el.scrollWidth > maxW || el.scrollHeight > maxH) && font > 8) {
				font -= 1;
				el.style.fontSize = font + 'px';
			}

			// ensure the number is visible only after sizing (prevents flashes/overflow)
			el.style.visibility = 'visible';
		});
	}

	function debounce(fn, wait) {
		let t;
		return function(...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); };
	}

	// watch for window resize and content changes
	document.addEventListener('DOMContentLoaded', function(){
		adjustOverlayTotalsNumbers();
		window.adjustOverlayTotalsNumbers = adjustOverlayTotalsNumbers; // expose for manual calls

		const observer = new MutationObserver(debounce(adjustOverlayTotalsNumbers, 80));
		document.querySelectorAll('.map-overlay-card h1').forEach(h => observer.observe(h, { childList: true, characterData: true, subtree: true }));
	});

// make replicate-popover helper available on main dashboard as well
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
    // prefer server-computed counts if available
    let yearStats = window.initialYearStats || {};
    console.log('server yearStats', yearStats);
    if (!yearStats || Object.keys(yearStats).length === 0) {
        // fall back to client-side computation
        const allData = window.fullListingData || [];
        console.log('fullListingData sample', allData.slice(0,20));
        const headers = window.fullListingHeaders || [];
        const idxOngoing = headers.findIndex(h => h.includes('ongoing'));
        const idxDissolved = headers.findIndex(h => h.includes('dissolved') || h.includes('inactive'));
        console.log('status column indexes', idxOngoing, idxDissolved, headers);
        yearStats = {};
        allData.forEach(r => {
            const yr = r.year_of_moa || 'Unknown';
            if (!yearStats[yr]) {
                yearStats[yr] = { total: 0, ongoing: 0, dissolved: 0 };
            }
            yearStats[yr].total++;
            // status determination as before
            let st = (r.status || '').toString().toLowerCase();
            if (!st && idxOngoing !== -1) {
                const cell = r.row && r.row[idxOngoing];
                if (cell !== null && cell !== undefined && String(cell).trim() !== '') {
                    st = 'ongoing';
                }
            }
            if (!st && idxDissolved !== -1) {
                const cell = r.row && r.row[idxDissolved];
                if (cell !== null && cell !== undefined && String(cell).trim() !== '') {
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

    // update summary cards with overall counts
    const totalOngoing = ongoingCounts.reduce((a,b)=>a+b, 0);
    const totalDissolved = dissolvedCounts.reduce((a,b)=>a+b, 0);
    const card1 = document.getElementById('card1');
    const card2 = document.getElementById('card2');
    const card3 = document.getElementById('card3');
    const card4 = document.getElementById('card4');

    // compute replicated/adopted using available data
    // prefer totals computed by server when available
    const server = window.serverTotals || {};
    const allData = window.fullListingData || [];
    const truthy = v => (typeof v === 'boolean') ? v : (String(v||'').toLowerCase().trim() === 'true');
    const totalReplicated = (typeof server.totalReplicated === 'number') ? server.totalReplicated :
            allData.reduce((a,r)=> a + (truthy(r.with_replicated) ? 1 : 0), 0);
    const totalAdopted = (typeof server.totalAdopted === 'number') ? server.totalAdopted :
            allData.reduce((a,r)=> a + (truthy(r.with_adopted) ? 1 : 0), 0);

    if(card1) {
        const val = card1.querySelector('.card-value');
        if(val) val.textContent = totalOngoing;
    }
    if(card2) {
        const val = card2.querySelector('.card-value');
        if(val) val.textContent = totalDissolved;
    }
    if(card3) {
        const val = card3.querySelector('.card-value');
        if(val) val.textContent = totalReplicated;
    }
    if(card4) {
        const val = card4.querySelector('.card-value');
        if(val) val.textContent = totalAdopted;
    }

    // helper to create a simple line chart config
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

    // draw charts in the two canvases provided
    new Chart(document.getElementById('onGoing').getContext('2d'), makeLineConfig('Ongoing STs', ongoingCounts, 'rgb(75, 192, 192)'));
    new Chart(document.getElementById('dissolved').getContext('2d'), makeLineConfig('Dissolved STs', dissolvedCounts, 'rgb(255, 99, 132)'));
    // (if the extra canvas myLineChart3 is still needed you can also reuse totalCounts)

    // (optional) you can initialise other charts here if needed
};
        
	window.addEventListener('resize', debounce(adjustOverlayTotalsNumbers, 120));

	</script>

    <!-- floating filter button using external icon file -->
    <button id="floatingBtn" class="btn" aria-label="Filter" style="background-color: white" data-bs-toggle="modal" data-bs-target="#filterModal">
        <img src="/images/dattachments/filtering%20icon.png" width="24" height="24" alt="Filter" />
        <span class="filter-label">Filter</span>
    </button>

    <!-- filter modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body d-flex justify-content-center align-items-center" id="filterModalBody" style="background:transparent;">
            <!-- standalone filter form for modal -->
            <div class="year-filter-wrap" style="flex:0 0 320px; max-width:600px; width:600px !important; min-width:320px">
                <div class="card st-dashboard-card" style="min-height:360px; box-shadow:none; border:1px solid rgba(16,174,181,0.06);">
                    <!-- header could be commented out if undesired -->
                    <div class="card-header">FILTER BY LOCATION &amp; YEAR</div>
                    <div class="card-body" style="padding:12px;">
                        <form method="GET" action="" class="w-100 d-flex flex-column">
                            <label for="region-select-modal" class="st-filter-label">Region</label>
                            <select id="region-select-modal" name="region[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Regions" style="width:100%;">
                                @foreach($regions as $region)
                                @if (stripos($region, 'Data CY 2020-2022') === false)
                                <option value="{{ $region }}" {{ collect(request('region'))->contains($region) ? 'selected' : '' }}>{{ $region }}</option>
                                @endif
                                @endforeach
                            </select>

                            <label for="year-select-modal" class="st-filter-label">Year</label>
                            <select id="year-select-modal" name="year_of_moa[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Years" style="width:100%;">
                                @foreach($years as $year)
                                <option value="{{ $year }}" {{ collect(request('year_of_moa'))->contains($year) ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>

                            <label for="province-select-modal" class="st-filter-label">Province</label>
                            <select id="province-select-modal" name="province[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Provinces" style="width:100%;">
                                @foreach($provinces as $province)
                                <option value="{{ $province }}" {{ collect(request('province'))->contains($province) ? 'selected' : '' }}>{{ $province }}</option>
                                @endforeach
                            </select>

                            <label for="municipality-select-modal" class="st-filter-label">City/Municipality</label>
                            <select id="municipality-select-modal" name="municipality[]" class="form-control mb-2 st-select2" multiple data-placeholder="Select Cities/Municipalities" style="width:100%;">
                                @foreach($municipalities as $municipality)
                                <option value="{{ $municipality }}" {{ collect(request('municipality'))->contains($municipality) ? 'selected' : '' }}>{{ $municipality }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn st-btn-gradient w-100 mt-2" style="background: linear-gradient(90deg, #06306e 60%, #06306e 100%); color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; padding: 10px 0; box-shadow: 0 2px 8px rgba(16, 174, 181, 0.08);">Filter</button>
                        </form>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <style>
        #floatingBtn {
            position: fixed;
            top: 80px;      /* starting offset */
            right: 20px;    /* top-right */
            z-index: 2000;
            background: #fff !important;
            border: 1.5px solid rgba(0,0,0,0.2);
            border-radius: 6px;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            /* longer duration and smoother curve for pleasant movement */
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }
        #floatingBtn .filter-label {
            font-size: 0.9rem;
            font-weight: 500;
        }
        /* ensure modal itself is transparent */
        #filterModal .modal-content {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        #filterModal .modal-dialog {
            background: transparent !important;
        }
        /* when card is cloned into modal, give it its original background so it's visible */
        #filterModalBody .card {
            background: #fff !important;
            box-shadow: 0 2px 12px rgba(16,174,181,0.06) !important;
            border: 1px solid rgba(16,174,181,0.06) !important;
        }
    </style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const btn = document.getElementById('floatingBtn');
    if (!btn) return; // prevent errors

    if (btn.dataset.initialized) return; // prevent duplicate binding
    btn.dataset.initialized = "true";

    let lastScrollTop = window.scrollY;
    let timeout;

    const maxLag = 400;      // maximum push distance
    const scrollFactor = 0.2; // px of push per px scrolled cumulatively
    const delayBeforeReturn = 400;
    let cumulative = 0;

    window.addEventListener('scroll', () => {

        let currentScroll = window.scrollY;
        let delta = currentScroll - lastScrollTop;

        cumulative += Math.abs(delta);
        // calculate how far to push based on total scroll since last reset
        let lag = Math.min(cumulative * scrollFactor, maxLag);
        if (delta > 0) {
            btn.style.transform = `translateY(-${lag}px)`; // scrolling down pushes up
        } else if (delta < 0) {
            btn.style.transform = `translateY(${lag}px)`;  // scrolling up pushes down
        }

        clearTimeout(timeout);
        timeout = setTimeout(() => {
            btn.style.transform = 'translateY(0)';
            cumulative = 0; // reset for next round
        }, delayBeforeReturn);

        lastScrollTop = currentScroll;
    });





});
</script>

	@endsection



