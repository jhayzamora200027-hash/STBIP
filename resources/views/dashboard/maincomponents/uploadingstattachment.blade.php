@extends('layouts.app')

@section('content')
	<div class="container mt-4 d-flex justify-content-start">
		<div class="card shadow w-100" style="max-width: 900px; margin-bottom: 2.5rem; background: #fff; border-radius: 18px;">
			<div class="card-body">
				<h1 class="mb-4">STs MOA Attachment Listing</h1>
				@if(Auth::user() && in_array(Auth::user()->usergroup, ['admin', 'sysadmin']))
					<button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="viewStsLogsBtn">
						<i class="bi bi-card-list"></i> View Logs
					</button>
				@endif

				@if(session('success'))
					<div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3 p-3 rounded-3" role="alert" style="background:linear-gradient(90deg,#22c55e,#16a34a); color:#ecfdf5; box-shadow:0 4px 10px rgba(16,185,129,0.35);">
						<div class="me-3" style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:999px; background:rgba(255,255,255,0.18);">
							<i class="bi bi-check2" style="font-size:1.2rem;"></i>
						</div>
						<div class="flex-grow-1" style="font-size:0.9rem;">
							<strong>Success.</strong> {{ session('success') }}
						</div>
						<button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="alert" aria-label="Close" style="filter:brightness(0) invert(1);"></button>
					</div>
				@endif

				@if($errors->any())
					<div class="alert alert-danger alert-dismissible fade show d-flex align-items-start mb-3 p-3 rounded-3" role="alert" style="background:linear-gradient(90deg,#ef4444,#b91c1c); color:#fef2f2; box-shadow:0 4px 10px rgba(239,68,68,0.35);">
						<div class="me-3" style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:999px; background:rgba(0,0,0,0.12);">
							<i class="bi bi-exclamation-triangle" style="font-size:1.1rem;"></i>
						</div>
						<div class="flex-grow-1" style="font-size:0.9rem;">
							<strong>There were some issues with your upload:</strong>
							<ul class="mb-0 mt-1" style="padding-left:1.2rem;">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
						<button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="alert" aria-label="Close" style="filter:brightness(0) invert(1);"></button>
					</div>
				@endif

				{{-- Filters: Region, Province, City, ST Title --}}
				<form id="sts-filter-form" method="GET" action="{{ route('uploadmoasts') }}" class="row g-3 mb-3">
					<div class="col-md-3">
						<label for="region" class="form-label">Region</label>
						<select name="region" id="region" class="form-select">
							<option value="">All Regions</option>
							@foreach($regions as $region)
								<option value="{{ $region }}" {{ $selectedRegion === $region ? 'selected' : '' }}>{{ $region }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-3">
						<label for="province" class="form-label">Province</label>
						<select name="province" id="province" class="form-select">
							<option value="">All Provinces</option>
							@foreach($provinceOptions ?? [] as $province)
								<option value="{{ $province }}" {{ ($selectedProvince ?? '') === $province ? 'selected' : '' }}>{{ $province }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-3">
						<label for="city" class="form-label">City/Municipality</label>
						<select name="city" id="city" class="form-select">
							<option value="">All Cities/Municipalities</option>
							@foreach($cityOptions ?? [] as $city)
								<option value="{{ $city }}" {{ ($selectedCity ?? '') === $city ? 'selected' : '' }}>{{ $city }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-3 position-relative">
						<label for="title" class="form-label">ST Title</label>
						<input
							type="text"
							name="title"
							id="title"
							class="form-control"
							placeholder="Type part of the ST title"
							value="{{ $searchTitle ?? '' }}"
						>
						@php
							$initialTitles = $titles ?? [];
							if (!empty($selectedRegion) && !empty($regionTitleMap[$selectedRegion] ?? [])) {
								$initialTitles = $regionTitleMap[$selectedRegion];
							}
						@endphp
						<div id="title-suggestions" class="list-group shadow-sm" style="position:absolute; top: 100%; left:0; right:0; z-index: 20; max-height: 220px; overflow-y:auto; font-size:0.8rem; display:none;">
							{{-- suggestions injected via JS --}}
						</div>
						<div class="mt-2 d-flex">
							<button type="submit" class="btn btn-primary me-2">Filter</button>
							@if($selectedRegion || ($selectedProvince ?? '') !== '' || ($selectedCity ?? '') !== '' || ($searchTitle ?? '') !== '')
								<a href="{{ route('uploadmoasts') }}" class="btn btn-outline-secondary">Clear</a>
							@endif
						</div>
					</div>
				</form>

				{{-- STs table with pagination (10 rows per page), AJAX-loaded --}}
				<div id="sts-list-container">
					@include('dashboard.maincomponents.partials.uploadingstattachment_list', ['sts' => $sts])
				</div>

				<script>
				// Load STs logs into modal container via AJAX
				function loadStsLogsPage(url) {
					if (!url) return;
					// normalize URL path
					if (!url.match(/^https?:\/\//) && url.charAt(0) !== '/') {
						url = '/' + url;
					}
					console.log('loadStsLogsPage fetching', url);
					console.log('cookies before fetch', document.cookie);
					var container = document.getElementById('sts-logs-container');
					if (!container) {
						window.location = url;
						return;
					}

					// put a spinner/placeholder while content loads
					container.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div> Loading logs...</div>';

					fetch(url, {
						credentials: 'include',
						headers: {
							'X-Requested-With': 'XMLHttpRequest',
							'Accept': 'application/json'
						}
					})
						.then(function(response) { return response.json(); })
						.then(function(data) {
							if (data && data.html) {
								container.innerHTML = data.html;
							} else if (data && data.redirect) {
								window.location = data.redirect;
							}
						})
						.catch(function() {
							window.location = url;
						});
				}

				// open modal and load logs when button clicked
				// convert the generated URL into a root-relative path
				let stsLogsUrl = "{{ route('sts.attachments.logs') }}";
				try {
					stsLogsUrl = new URL(stsLogsUrl).pathname;
				} catch (e) {
					// if URL constructor fails, leave as-is
				}
				document.addEventListener('click', function(e) {
					var btn = e.target.closest('#viewStsLogsBtn');
					if (!btn) return;
					var modalEl = document.getElementById('stsLogsModal');
					if (!modalEl) return;
					// include current filter values if form exists
					let qs = '';
					var form = document.getElementById('sts-logs-filter-form');
					if (form) {
						let params = new URLSearchParams(new FormData(form));
						qs = params.toString() ? ('?' + params.toString()) : '';
					}
					loadStsLogsPage(stsLogsUrl + qs);
					var modal = new bootstrap.Modal(modalEl);
					modal.show();
				});
				// filter form submission should reload logs via AJAX
				document.addEventListener('submit', function(e) {
					if (e.target && e.target.id === 'sts-logs-filter-form') {
						e.preventDefault();
						let params = new URLSearchParams(new FormData(e.target));
						loadStsLogsPage(stsLogsUrl + (params.toString() ? '?' + params.toString() : ''));
					}
				});

				const regionTitleMap = @json($regionTitleMap ?? []);
					const allTitles = @json($titles ?? []);

					function loadUploadStsPage(url) {
						if (!url) return;
						var container = document.getElementById('sts-list-container');
						if (!container) {
							window.location = url;
							return;
						}

						fetch(url, {
							headers: {
								'X-Requested-With': 'XMLHttpRequest',
								'Accept': 'application/json'
							}
						})
							.then(function(response) { return response.json(); })
							.then(function(data) {
								if (data && data.html) {
									container.innerHTML = data.html;
								} else if (data && data.redirect) {
									window.location = data.redirect;
								}
							})
							.catch(function() {
								window.location = url;
							});
					}

					document.addEventListener('DOMContentLoaded', function () {
						var form = document.getElementById('sts-filter-form');
						var regionSelect = document.getElementById('region');
						var titleInput = document.getElementById('title');
						var suggestions = document.getElementById('title-suggestions');

						function hideSuggestions() {
							if (suggestions) {
								suggestions.style.display = 'none';
							}
						}

						function refreshTitleOptions() {
							if (!suggestions || !titleInput) return;
							var selectedRegion = regionSelect ? regionSelect.value : '';
							var query = titleInput.value.trim().toLowerCase();

							var titlesList = allTitles;
							if (selectedRegion && regionTitleMap[selectedRegion]) {
								titlesList = regionTitleMap[selectedRegion];
							}

							// When user has typed something, filter; otherwise show first few for quick pick
							if (query.length > 0) {
								titlesList = titlesList.filter(function (title) {
									return title.toLowerCase().indexOf(query) !== -1;
								});
							}
							titlesList = titlesList.slice(0, 15);

							suggestions.innerHTML = '';
							if (titlesList.length === 0) {
								hideSuggestions();
								return;
							}

							titlesList.forEach(function (title) {
								var item = document.createElement('button');
								item.type = 'button';
								item.className = 'list-group-item list-group-item-action';
								item.textContent = title;
								item.addEventListener('click', function () {
									titleInput.value = title;
									hideSuggestions();
								});
								suggestions.appendChild(item);
							});

							suggestions.style.display = 'block';
						}

						if (regionSelect) {
							regionSelect.addEventListener('change', function () {
								hideSuggestions();
								refreshTitleOptions();
							});
						}

						if (titleInput) {
							titleInput.addEventListener('input', function () {
								refreshTitleOptions();
							});
							titleInput.addEventListener('focus', function () {
								refreshTitleOptions();
							});
						}

						document.addEventListener('click', function (e) {
							if (!suggestions || !titleInput) return;
							if (!suggestions.contains(e.target) && e.target !== titleInput) {
								hideSuggestions();
							}
						});
					});
				</script>
			</div>
		</div>
	</div>
@endsection