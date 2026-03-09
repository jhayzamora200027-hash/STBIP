<div class="masterdata-card" style="margin-bottom: 22px;">
	<div class="masterdata-card-body">
		<div class="masterdata-toolbar">
			<form method="GET" action="{{ route('masterdata.index') }}" class="d-flex align-items-end gap-3 flex-wrap" data-masterdata-updates-form="region">
				<input type="hidden" name="tab" value="updates">
				<div class="masterdata-field">
					<label for="region-filter">Regional Office</label>
					<select id="region-filter" name="region_filter" data-masterdata-region-filter="1">
						@foreach($regions as $region)
							<option value="{{ $region->name }}" @selected($selectedRegionName === $region->name)>{{ $region->name }}</option>
						@endforeach
					</select>
				</div>
			</form>
			<div>
				<div class="masterdata-stat-label">Selected Office</div>
				<div class="masterdata-item-title">{{ $selectedRegionName ?: 'No region selected' }}</div>
			</div>
		</div>
	</div>
</div>

<section class="masterdata-card" style="margin-bottom: 22px;">
	<div class="masterdata-card-header">
		<h2>Add New Item to {{ $selectedRegionName }}</h2>
	</div>
	<div class="masterdata-card-body">
		<form method="POST" action="{{ route('masterdata.region-items.store') }}" data-masterdata-updates-form="create">
			@csrf
			<div class="masterdata-form-grid">
				<div class="masterdata-field">
					<label for="new-region-name">Regional Office</label>
					<input id="new-region-name" type="text" value="{{ $selectedRegionName }}" readonly>
					<input type="hidden" name="region_id" value="{{ old('region_id', $selectedRegion?->id) }}">
				</div>
				<div class="masterdata-field">
					<label for="new-status">Status</label>
					<select id="new-status" name="status">
						<option value="">Select status</option>
						<option value="ongoing" @selected(old('status') === 'ongoing')>Ongoing</option>
						<option value="dissolved" @selected(old('status') === 'dissolved')>Dissolved</option>
					</select>
				</div>
				<div class="masterdata-field full">
					<label for="new-title">Social Technology Title</label>
					<input id="new-title" type="text" name="title" value="{{ old('title') }}" required>
				</div>
				<div class="masterdata-field">
					<label for="new-province">Province</label>
					<input id="new-province" type="text" name="province" value="{{ old('province') }}">
				</div>
				<div class="masterdata-field">
					<label for="new-municipality">Municipality</label>
					<input id="new-municipality" type="text" name="municipality" value="{{ old('municipality') }}">
				</div>
				<div class="masterdata-field">
					<label for="new-adoption-status">Adopted / Replicated</label>
					<select id="new-adoption-status" name="adoption_status">
						<option value="none" @selected(old('adoption_status', 'none') === 'none')>None</option>
						<option value="adopted" @selected(old('adoption_status') === 'adopted')>Adopted</option>
						<option value="replicated" @selected(old('adoption_status') === 'replicated')>Replicated</option>
					</select>
				</div>
				<div class="masterdata-field full">
					<label>Indicators</label>
					<div class="masterdata-checks">
						<label class="masterdata-check"><input type="hidden" name="with_expr" value="0"><input type="checkbox" name="with_expr" value="1" @checked(old('with_expr'))><span class="masterdata-check-text"><span class="masterdata-check-title">With Expression of Interest</span><span class="masterdata-check-note">Mark items with a recorded expression of interest.</span></span></label>
						<label class="masterdata-check"><input type="hidden" name="with_moa" value="0"><input type="checkbox" id="new-with-moa" name="with_moa" value="1" data-toggle-target="new-year-of-moa-field" @checked(old('with_moa'))><span class="masterdata-check-text"><span class="masterdata-check-title">With MOA</span><span class="masterdata-check-note">Enable when a memorandum of agreement exists.</span></span></label>
						<label class="masterdata-check"><input type="hidden" name="with_res" value="0"><input type="checkbox" id="new-with-res" name="with_res" value="1" data-toggle-target="new-year-of-resolution-field" @checked(old('with_res'))><span class="masterdata-check-text"><span class="masterdata-check-title">With Resolution</span><span class="masterdata-check-note">Enable when a formal resolution has been issued.</span></span></label>
						<label class="masterdata-check"><input type="hidden" name="included_aip" value="0"><input type="checkbox" name="included_aip" value="1" @checked(old('included_aip'))><span class="masterdata-check-text"><span class="masterdata-check-title">Included AIP</span><span class="masterdata-check-note">Use when the item is included in the AIP.</span></span></label>
					</div>
				</div>
				<div class="masterdata-field is-hidden" id="new-year-of-moa-field">
					<label for="new-year">Year of MOA</label>
					<input id="new-year" type="number" min="1900" max="2100" name="year_of_moa" value="{{ old('year_of_moa') }}">
				</div>
				<div class="masterdata-field is-hidden" id="new-year-of-resolution-field">
					<label for="new-year-of-resolution">Year of Resolution</label>
					<input id="new-year-of-resolution" type="number" min="1900" max="2100" name="year_of_resolution" value="{{ old('year_of_resolution') }}">
				</div>
			</div>
			<div class="masterdata-item-actions" style="justify-content:flex-start;">
				<button type="submit" class="masterdata-btn masterdata-btn-primary">Save New Region Item</button>
			</div>
		</form>
	</div>
</section>

<section class="masterdata-card">
	<div class="masterdata-card-header">
		<h2>Update Existing Items</h2>
	</div>
	<div class="masterdata-card-body">
		<form method="GET" action="{{ route('masterdata.index') }}" class="masterdata-filter-bar" data-masterdata-updates-form="filters">
			<input type="hidden" name="tab" value="updates">
			<input type="hidden" name="region_filter" value="{{ $selectedRegionName }}">
			<div class="masterdata-field">
				<label for="update-province-filter">Province</label>
				<select id="update-province-filter" name="province_filter">
					<option value="">All provinces</option>
					@foreach($provinceOptions as $provinceOption)
						<option value="{{ $provinceOption }}" @selected($selectedProvince === $provinceOption)>{{ $provinceOption }}</option>
					@endforeach
				</select>
			</div>
			<div class="masterdata-field">
				<label for="update-city-filter">City / Municipality</label>
				<select id="update-city-filter" name="municipality_filter">
					<option value="">All cities / municipalities</option>
					@foreach($municipalityOptions as $municipalityOption)
						<option value="{{ $municipalityOption }}" @selected($selectedMunicipality === $municipalityOption)>{{ $municipalityOption }}</option>
					@endforeach
				</select>
			</div>
			<div class="masterdata-item-actions" style="margin-top:0; justify-content:flex-start;">
				<button type="submit" class="masterdata-btn masterdata-btn-primary" data-masterdata-apply-filters="1">Apply Filters</button>
				<a class="masterdata-btn masterdata-btn-secondary" href="{{ route('masterdata.index', ['tab' => 'updates', 'region_filter' => $selectedRegionName]) }}" data-masterdata-updates-clear="1">Clear</a>
			</div>
		</form>

		<div class="masterdata-list-meta">
			<div>
				Showing {{ $regionItems->count() }} of {{ $regionItems->total() }} item{{ $regionItems->total() === 1 ? '' : 's' }} for {{ $selectedRegionName }}.
			</div>
			<div>
				Page {{ $regionItems->currentPage() }} of {{ max(1, $regionItems->lastPage()) }}
			</div>
		</div>

		<div class="masterdata-item-stack">
			@forelse($regionItems as $item)
				@php
					$adoptionStatus = $item->with_adopted ? 'adopted' : ($item->with_replicated ? 'replicated' : 'none');
				@endphp
				@if($loop->first)
					<div class="masterdata-item-list">
						<div class="masterdata-item-list-head">
							<div>ST Title</div>
							<div>Province</div>
							<div>City / Municipality</div>
							<div>Status</div>
							<div>Updated By</div>
							<div>Updated At</div>
							<div></div>
						</div>
				@endif
				<div class="masterdata-item-entry">
					<div class="masterdata-item-row" data-masterdata-item-toggle="item-{{ $item->id }}" role="button" tabindex="0" aria-expanded="false">
						<div>
							<div class="masterdata-item-row-title">{{ $item->title }}</div>
						</div>
						<div class="masterdata-item-row-cell {{ $item->province ? '' : 'masterdata-item-row-cell-muted' }}">{{ $item->province ?: 'No province' }}</div>
						<div class="masterdata-item-row-cell {{ $item->municipality ? '' : 'masterdata-item-row-cell-muted' }}">{{ $item->municipality ?: 'No municipality' }}</div>
						<div>
							@if($item->status === 'ongoing')
								<span class="masterdata-pill masterdata-status-ongoing">Ongoing</span>
							@elseif($item->status === 'dissolved')
								<span class="masterdata-pill masterdata-status-dissolved">Dissolved</span>
							@else
								<span class="masterdata-pill">Unspecified</span>
							@endif
						</div>
						<div>{{ $item->updatedby ?: '-' }}</div>
						<div>{{ $item->updated_at?->format('M d, Y h:i A') ?: '-' }}</div>
						<div><span class="masterdata-row-chevron">▾</span></div>
					</div>

					<div class="masterdata-item-detail" id="item-{{ $item->id }}">
						<div class="masterdata-item-head">
							<div>
								<div class="masterdata-item-title">{{ $item->title }}</div>
								<div class="masterdata-item-meta">
									<span>Created by: {{ $item->createdby ?: '-' }}</span>
									<span>Updated by: {{ $item->updatedby ?: '-' }}</span>
									<span>Updated at: {{ $item->updated_at?->format('M d, Y h:i A') ?: '-' }}</span>
								</div>
							</div>
						</div>

						<form method="POST" action="{{ route('masterdata.region-items.update', $item) }}" data-masterdata-updates-form="update">
							@csrf
							@method('PATCH')
							<input type="hidden" name="return_province_filter" value="{{ $selectedProvince }}">
							<input type="hidden" name="return_municipality_filter" value="{{ $selectedMunicipality }}">
							<input type="hidden" name="return_page" value="{{ $regionItems->currentPage() }}">
							<div class="masterdata-form-grid">
								<div class="masterdata-field">
									<label>Regional Office</label>
									<input type="text" value="{{ $item->region?->name ?: $selectedRegionName }}" readonly>
									<input type="hidden" name="region_id" value="{{ $item->region_id }}">
								</div>
								<div class="masterdata-field">
									<label>Status</label>
									<select name="status">
										<option value="">Select status</option>
										<option value="ongoing" @selected($item->status === 'ongoing')>Ongoing</option>
										<option value="dissolved" @selected($item->status === 'dissolved')>Dissolved</option>
									</select>
								</div>
								<div class="masterdata-field full">
									<label>Social Technology Title</label>
									<input type="text" name="title" value="{{ $item->title }}" required>
								</div>
								<div class="masterdata-field">
									<label>Province</label>
									<input type="text" name="province" value="{{ $item->province }}">
								</div>
								<div class="masterdata-field">
									<label>Municipality</label>
									<input type="text" name="municipality" value="{{ $item->municipality }}">
								</div>
								<div class="masterdata-field">
									<label>Adopted / Replicated</label>
									<select name="adoption_status">
										<option value="none" @selected($adoptionStatus === 'none')>None</option>
										<option value="adopted" @selected($adoptionStatus === 'adopted')>Adopted</option>
										<option value="replicated" @selected($adoptionStatus === 'replicated')>Replicated</option>
									</select>
								</div>
								<div class="masterdata-field full">
									<label>Indicators</label>
									<div class="masterdata-checks">
										<label class="masterdata-check"><input type="hidden" name="with_expr" value="0"><input type="checkbox" name="with_expr" value="1" @checked($item->with_expr)><span class="masterdata-check-text"><span class="masterdata-check-title">With Expression of Interest</span><span class="masterdata-check-note">Mark items with a recorded expression of interest.</span></span></label>
										<label class="masterdata-check"><input type="hidden" name="with_moa" value="0"><input type="checkbox" name="with_moa" value="1" data-toggle-target="item-{{ $item->id }}-year-of-moa-field" @checked($item->with_moa)><span class="masterdata-check-text"><span class="masterdata-check-title">With MOA</span><span class="masterdata-check-note">Enable when a memorandum of agreement exists.</span></span></label>
										<label class="masterdata-check"><input type="hidden" name="with_res" value="0"><input type="checkbox" name="with_res" value="1" data-toggle-target="item-{{ $item->id }}-year-of-resolution-field" @checked($item->with_res)><span class="masterdata-check-text"><span class="masterdata-check-title">With Resolution</span><span class="masterdata-check-note">Enable when a formal resolution has been issued.</span></span></label>
										<label class="masterdata-check"><input type="hidden" name="included_aip" value="0"><input type="checkbox" name="included_aip" value="1" @checked($item->included_aip)><span class="masterdata-check-text"><span class="masterdata-check-title">Included AIP</span><span class="masterdata-check-note">Use when the item is included in the AIP.</span></span></label>
									</div>
								</div>
								<div class="masterdata-field {{ $item->with_moa ? '' : 'is-hidden' }}" id="item-{{ $item->id }}-year-of-moa-field">
									<label>Year of MOA</label>
									<input type="number" min="1900" max="2100" name="year_of_moa" value="{{ $item->year_of_moa }}">
								</div>
								<div class="masterdata-field {{ $item->with_res ? '' : 'is-hidden' }}" id="item-{{ $item->id }}-year-of-resolution-field">
									<label>Year of Resolution</label>
									<input type="number" min="1900" max="2100" name="year_of_resolution" value="{{ $item->year_of_resolution }}">
								</div>
							</div>
							<div class="masterdata-item-actions">
								<button type="submit" class="masterdata-btn masterdata-btn-primary">Save Changes</button>
							</div>
						</form>

						<form method="POST" action="{{ route('masterdata.region-items.destroy', $item) }}" onsubmit="return confirm('Delete this region item?');" class="masterdata-item-actions" style="margin-top: 10px;" data-masterdata-updates-form="delete">
							@csrf
							@method('DELETE')
							<input type="hidden" name="return_province_filter" value="{{ $selectedProvince }}">
							<input type="hidden" name="return_municipality_filter" value="{{ $selectedMunicipality }}">
							<input type="hidden" name="return_page" value="{{ $regionItems->currentPage() }}">
							<button type="submit" class="masterdata-btn masterdata-btn-danger">Delete Item</button>
						</form>
					</div>
				</div>
				@if($loop->last)
					</div>
				@endif
			@empty
				<div class="masterdata-empty">No region items found for {{ $selectedRegionName }} yet. Add the first one using the form above.</div>
			@endforelse
		</div>

		@if($regionItems->hasPages())
			<div class="masterdata-pagination">
				{{ $regionItems->onEachSide(1)->links() }}
			</div>
		@endif
	</div>
</section>