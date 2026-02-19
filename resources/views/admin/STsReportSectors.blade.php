@extends('layouts.app')


@section('content')
<div class="container py-4">
    <style>
      /* 🔧 Outer-only visible frame for expanded gallery row + children (no per-column separators) */

      /* horizontal top line across the expanded parent row */
      .gallery-row.expanded > td {
        /* no top border on the parent TD — top edge is drawn by the inner panel for perfect alignment */
        border-top: none;
        border-left: none;
        border-right: none;
        background: #ffffff;
        box-shadow: 0 6px 18px rgba(16,24,32,0.04);
      }

      /* only the outer vertical edges (first and last cell) — prevents per-column separators */
      .gallery-row.expanded > td:first-child { border-left: 3px solid #9aa0a6; border-top-left-radius: .25rem; }
      .gallery-row.expanded > td:last-child  { border-right: 3px solid #9aa0a6; border-top-right-radius: .25rem; }

      /* child-row TD should be borderless when collapsed — the visible frame is on the inner panel */
      .children-row > td {
        border: none;
        padding: 0; /* panel provides the spacing when open */
        background: transparent;
      }

      /* the actual visible container when expanded — animate using max-height + opacity */
      .children-panel {
        display: block; /* collapse JS will toggle classes; bootstrap animates height, we add transitions for smoothness */
        padding: 0; /* spacing provided when open */
        background: transparent;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: height .36s cubic-bezier(.2,.9,.2,1), max-height .36s cubic-bezier(.2,.9,.2,1), opacity .22s ease, border-color .18s ease;
      }

      /* card inside panel: slide + fade (works when panel receives .show) */
      .children-panel .card {
        transform: translateY(-8px);
        opacity: 0;
        transition: transform .32s cubic-bezier(.2,.9,.2,1), opacity .22s ease;
        will-change: transform, opacity;
      }

      .children-panel.show {
        border-top: 3px solid #9aa0a6; /* draw the full frame from here for perfect alignment */
        border-left: 3px solid #9aa0a6;
        border-right: 3px solid #9aa0a6;
        border-bottom: 3px solid #9aa0a6;
        background: #fbfcfd; /* subtle tint */
        border-radius: .35rem;
        box-sizing: border-box;
        width: 100%;
        margin-top: -3px; /* overlap the table separator so the frame is a single straight line */

        /* allow the panel to expand via max-height (large enough for content) */
        max-height: 1400px;
        opacity: 1;
      }

      .children-panel.show .card {
        transform: translateY(0);
        opacity: 1;
        transition-delay: .02s;
      }

      /* keep inner table/card content visually unchanged */
      .children-row > td .card { border: none; box-shadow: none; margin-bottom: 0; }
      .children-row > td .table { margin-bottom: 0; }

      /* ---------------------- ANIMATIONS ---------------------- */
      /* icon rotation should animate smoothly */
      .expand-icon { transition: transform .22s ease; }

      /* fade + slide content when collapse/show toggles (collapse now targets an inner DIV) */
      .children-panel.collapse .card {
        transform: translateY(-8px);
        opacity: 0;
        transition: transform .32s cubic-bezier(.2,.9,.2,1), opacity .22s ease;
        will-change: transform, opacity;
      }
      .children-panel.collapse.show .card {
        transform: translateY(0);
        opacity: 1;
        transition-delay: .03s; /* slight delay so height begins opening first */
      }

      /* subtle frame transition */
      .gallery-row.expanded > td { transition: border-color .22s ease, box-shadow .28s ease, background-color .18s ease; }
      .gallery-row.animating > td { box-shadow: 0 8px 26px rgba(16,24,32,0.06); }

      /* preserve transitions (no effect on inner cell borders) */
      .gallery-row td { transition: background-color .12s ease; }

      /* ensure table cells align precisely with the panel's border */
      .table { border-collapse: collapse; }
      .table td, .table th { border-spacing: 0; }

      /* when a gallery row is expanded, hide its bottom separator so the panel's top border appears as a single continuous line */
      .gallery-row.expanded > td { border-bottom: 0 !important; }
      .gallery-row.expanded + .children-row > td { border-top: 0 !important; }

      /* nudge the panel above the thin table row line and make it sit on top */
      .children-panel { margin-top: -4px; position: relative; z-index: 2; }
      .gallery-row > td { position: relative; z-index: 1; }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>STs Report — Gallery Utilities</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Validation failed — please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">URL</label>
                        <input type="text" name="url" value="{{ old('url') }}" class="form-control @error('url') is-invalid @enderror" placeholder="/category...">
                        @error('url')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Active</label>
                        <div class="form-check mt-1">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="On going" {{ old('status') === 'On going' ? 'selected' : '' }}>On going</option>
                            <option value="Completed" {{ old('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-4 mt-2">
                        <label class="form-label">Image (optional)</label>
                        <input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
                        @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-center mt-2">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-12 mt-3 text-end">
                        <button class="btn btn-primary">Add gallery card</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Existing gallery cards</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width:100px">Status</th>
                            <th style="width:120px">DocNo</th>
                            <th style="width:90px">Preview</th>
                            <th>Title</th>
                            <th>URL</th>
                            <th style="width:140px">Created By</th>
                            <th style="width:140px">Updated By</th>
                            <th style="width:80px">Active</th>
                            <th style="width:220px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($galleryCards ?? [] as $card)
                        <tr class="gallery-row" data-bs-toggle="collapse" data-bs-target="#children-panel-{{ $card->id }}" aria-expanded="false" aria-controls="children-panel-{{ $card->id }}" style="cursor:pointer;">
                            <td class="text-muted small"><span class="badge bg-light text-dark">{{ $card->status ?? 'On going' }}</span></td>
                            <td>
                                <span class="me-2"><i class="bi bi-caret-down-fill text-muted expand-icon" style="transform:rotate(-90deg);"></i></span>
                                <strong>{{ $card->docno ?? $card->order ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                @if($card->image)
                                    @php
                                        $imgSrc = (\Illuminate\Support\Facades\Storage::disk('public')->exists($card->image))
                                            ? asset('storage/' . $card->image)
                                            : (file_exists(public_path($card->image)) ? asset($card->image) : null);
                                    @endphp
                                    @if($imgSrc)
                                        <img src="{{ $imgSrc }}" alt="preview" class="gallery-thumb rounded" style="width:56px;height:38px;object-fit:cover;"> 
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td>{{ $card->title }}</td>

                            <td>{{ $card->url }}</td>
                            <td>{{ $card->creator ? $card->creator->name : ($card->created_by ?? '') }}</td>
                            <td>{{ $card->updater ? $card->updater->name : ($card->updated_by ?? '') }}</td>
                            <td>{{ $card->is_active ? 'Yes' : 'No' }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-sm btn-secondary btn-edit-card"
                                        data-id="{{ $card->id }}"
                                        data-title="{{ e($card->title) }}"
                                        data-url="{{ e($card->url) }}"
                                        data-is-active="{{ $card->is_active ? 1 : 0 }}"
                                        data-status="{{ $card->status ?? 'On going' }}">Edit</button>
                                    <form action="{{ route('admin.gallery.destroy', $card->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this card?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <tr class="children-row" id="children-row-{{ $card->id }}">
                            <td colspan="9">
                                <div id="children-panel-{{ $card->id }}" class="collapse children-panel @if(old('parent_card_id') == $card->id || (old('editing_child_id') && $card->children->pluck('id')->contains(old('editing_child_id')))) show @endif">
                                    <div class="card card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>Children for "{{ $card->title }}"</strong>
                                        <small class="text-muted">{{ $card->children->count() }} child(ren)</small>
                                    </div>

                                    <h6 class="mb-2">Existing children</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-2">
                                            <thead>
                                                <tr><th>Title</th><th>DocNo</th><th>URL</th><th>Active</th><th>Status</th><th>Created By</th><th>Updated By</th><th style="width:260px">Actions</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($card->children ?? [] as $child)
                                                <tr>
                                                    <td>{{ $child->title }}</td>
                                                    <td>{{ $child->docno }}</td>
                                                    <td><small class="text-muted">{{ $child->url ?? '-' }}</small></td>
                                                    <td>{{ $child->is_active ? 'Yes' : 'No' }}</td>
                                                    <td><span class="badge bg-light text-dark">{{ $child->status ?? 'On going' }}</span></td>
                                                    <td>{{ $child->creator ? $child->creator->name : ($child->created_by ?? '') }}</td>
                                                    <td>{{ $child->updater ? $child->updater->name : ($child->updated_by ?? '') }}</td>
                                                    <td>
                                                        @php
                                                            $childHistoriesJson = $child->histories->map(function($h){
                                                                return [
                                                                    'docno' => $h->docno,
                                                                    'previous_docno' => $h->previous_docno,
                                                                    'creator' => $h->creator ? $h->creator->name : ($h->created_by ?? ''),
                                                                    'created_at' => (string) $h->created_at,
                                                                ];
                                                            })->toJson();
                                                        @endphp

                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <button type="button" class="btn btn-sm btn-secondary btn-edit-child"
                                                              data-id="{{ $child->id }}"
                                                              data-title="{{ e($child->title) }}"
                                                              data-url="{{ e($child->url) }}"
                                                              data-is-active="{{ $child->is_active ? 1 : 0 }}"
                                                              data-status="{{ $child->status ?? 'On going' }}"
                                                              data-is-mother="{{ $child->is_mother ? 1 : 0 }}"
                                                              data-docno="{{ e($child->docno) }}"
                                                              data-histories='{{ $childHistoriesJson }}'>Edit</button>

                                                            @if($child->is_mother)
                                                                @php
                                                                    $subChildrenJson = $child->children->map(function($c){
                                                                        return [
                                                                            'id' => $c->id,
                                                                            'title' => $c->title,
                                                                            'docno' => $c->docno,
                                                                            'url' => $c->url,
                                                                            'is_active' => (bool) $c->is_active,
                                                                            'status' => $c->status ?? 'On going',
                                                                            'created_by' => $c->creator ? $c->creator->name : ($c->created_by ?? ''),
                                                                            'updated_by' => $c->updater ? $c->updater->name : ($c->updated_by ?? ''),
                                                                            'created_at' => (string) $c->created_at,
                                                                        ];
                                                                    })->toJson();
                                                                    $childHistoriesJson = $child->histories->map(function($h){
                                                                        return [
                                                                            'docno' => $h->docno,
                                                                            'previous_docno' => $h->previous_docno,
                                                                            'creator' => $h->creator ? $h->creator->name : ($h->created_by ?? ''),
                                                                            'created_at' => (string) $h->created_at,
                                                                        ];
                                                                    })->toJson();
                                                                @endphp
                                                                <button type="button" class="btn btn-sm btn-info btn-manage-subchildren"
                                                                    data-card-id="{{ $card->id }}"
                                                                    data-child-id="{{ $child->id }}"
                                                                    data-child-title="{{ e($child->title) }}"
                                                                    data-child-docno="{{ e($child->docno) }}"
                                                                    data-child-url="{{ e($child->url ?? '') }}"
                                                                    data-child-is-active="{{ $child->is_active ? 1 : 0 }}"
                                                                    data-child-status="{{ $child->status ?? 'On going' }}"
                                                                    data-child-created-by="{{ $child->creator ? $child->creator->name : ($child->created_by ?? '') }}"
                                                                    data-child-histories='{{ $childHistoriesJson }}'
                                                                    data-subchildren='{{ $subChildrenJson }}'>
                                                                    Manage Sub-children
                                                                </button>
                                                            @endif

                                                            <form action="{{ route('admin.gallery.children.destroy', $child->id) }}" method="POST" class="m-0">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this child?')">Delete</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                
                                                {{-- sub-children moved to modal; placeholder left intentionally empty --}}
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <hr>

                                    <h6>Add child for "{{ $card->title }}"</h6>
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-sm btn-success btn-open-add-child" data-card-id="{{ $card->id }}">Add Child</button>
                                    </div>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Child modal (keeps children table compact) -->
<div class="modal fade" id="editChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editChildForm" method="POST" action="">
        @csrf
        @method('PUT')
        <input type="hidden" name="editing_child_id" id="editing_child_id" value="{{ old('editing_child_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="modal_title" type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">URL</label>
              <input id="modal_url" type="text" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}">
              @error('url')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select id="modal_status" name="status" class="form-select">
                <option value="On going">On going</option>
                <option value="Completed">Completed</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Active</label>
              <select id="modal_is_active" name="is_active" class="form-select">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>

            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check mt-3">
                <input type="hidden" name="is_mother" value="0">
                <input id="modal_is_mother" class="form-check-input" type="checkbox" name="is_mother" value="1">
                <label class="form-check-label" for="modal_is_mother">Is mother</label>
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">DocNo</label>
              <div><span id="modal_docno" class="badge bg-secondary">-</span></div>
            </div>

            <div class="col-12">
              <hr>
              <strong>DocNo history</strong>
              <ul id="modal_docno_history" class="list-unstyled small mt-2 mb-0"></ul>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Child modal -->
<div class="modal fade" id="addChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addChildForm" method="POST" action="">
        @csrf
        <input type="hidden" name="parent_card_id" id="add_parent_card_id" value="{{ old('parent_card_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="add_child_title" type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">URL (optional)</label>
              <input id="add_child_url" type="text" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}">
              @error('url')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Active</label>
              <select id="add_child_is_active" name="is_active" class="form-select">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select id="add_child_status" name="status" class="form-select">
                <option value="On going">On going</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Add Child</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Sub-child modal -->
<div class="modal fade" id="addSubChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add sub-child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addSubChildForm" method="POST" action="">
        @csrf
        <input type="hidden" name="parent_card_id" id="add_sub_parent_card_id" value="{{ old('parent_card_id') }}">
        <input type="hidden" name="parent_child_id" id="add_parent_child_id" value="{{ old('parent_child_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="add_sub_title" type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
              @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">URL (optional)</label>
              <input id="add_sub_url" type="text" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}">
              @error('url')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Active</label>
              <select id="add_sub_is_active" name="is_active" class="form-select">
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select id="add_sub_status" name="status" class="form-select">
                <option value="On going">On going</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Add Sub-child</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Manage Sub-children modal -->
<div class="modal fade" id="manageSubChildrenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Sub-children</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <strong id="manageSub_childTitle">Sub-children</strong>
            <div class="small text-muted" id="manageSub_childCount">0 sub-child(ren)</div>
          </div>
          <div>
            <button type="button" class="btn btn-sm btn-success" id="manageSub_addSubchildBtn">Add Sub-child</button>
          </div>
        </div>

        <!-- inline Add Sub-child form (appears inside this modal) -->
        <div id="manageSub_inlineAddWrap" class="mb-3" style="display:none;">
          <form id="manageSub_inlineAddForm" method="POST" action="">
            @csrf
            <input type="hidden" name="parent_card_id" id="manageSub_add_parent_card_id" value="">
            <input type="hidden" name="parent_child_id" id="manageSub_add_parent_child_id" value="">
            <input type="hidden" name="from_manage_modal" id="manageSub_from_manage_modal" value="1">

            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label class="form-label">Title</label>
                <input id="manageSub_add_title" name="title" type="text" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">URL (optional)</label>
                <input id="manageSub_add_url" name="url" type="text" class="form-control form-control-sm">
              </div>
              <div class="col-md-2">
                <label class="form-label">Active</label>
                <select id="manageSub_add_is_active" name="is_active" class="form-select form-select-sm">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Status</label>
                <select id="manageSub_add_status" name="status" class="form-select">
                  <option value="On going">On going</option>
                  <option value="Completed">Completed</option>
                </select>
              </div>
            </div>

            <div class="mt-2 text-end">
              <button type="button" class="btn btn-sm btn-secondary" id="manageSub_inlineCancelBtn">Cancel</button>
              <button type="submit" class="btn btn-sm btn-success">Add Sub-child</button>
            </div>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0" id="manageSub_table">
            <thead>
              <tr><th>Title</th><th>DocNo</th><th>URL</th><th>Active</th><th>Status</th><th>Created By</th><th style="width:160px">Actions</th></tr>
            </thead>
            <tbody id="manageSub_tbody">
              <tr><td colspan="7" class="text-center text-muted">No sub-children</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Gallery Card modal -->
<div class="modal fade" id="editCardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit gallery card</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editCardForm" method="POST" action="" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="editing_gallery_id" id="editing_gallery_id_card" value="{{ old('editing_gallery_id') }}">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Title</label>
              <input id="card_modal_title" type="text" name="title" class="form-control {{ (old('editing_gallery_id') && $errors->has('title')) ? 'is-invalid' : '' }}" value="{{ old('title') }}" required>
              @if(old('editing_gallery_id')) @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
            </div>
            <div class="col-md-3">
              <label class="form-label">Active</label>
              <select id="card_modal_is_active" name="is_active" class="form-select {{ (old('editing_gallery_id') && $errors->has('is_active')) ? 'is-invalid' : '' }}">
                <option value="1" {{ (old('editing_gallery_id') && old('is_active') == '1') ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ (old('editing_gallery_id') && old('is_active') == '0') ? 'selected' : '' }}>No</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select id="card_modal_status" name="status" class="form-select {{ (old('editing_gallery_id') && $errors->has('status')) ? 'is-invalid' : '' }}">
                <option value="On going" {{ (old('editing_gallery_id') && old('status') == 'On going') ? 'selected' : '' }}>On going</option>
                <option value="Completed" {{ (old('editing_gallery_id') && old('status') == 'Completed') ? 'selected' : '' }}>Completed</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">URL</label>
              <input id="card_modal_url" type="text" name="url" class="form-control {{ (old('editing_gallery_id') && $errors->has('url')) ? 'is-invalid' : '' }}" value="{{ old('url') }}">
              @if(old('editing_gallery_id')) @error('url')<div class="text-danger small">{{ $message }}</div>@enderror @endif
            </div>



            <div class="col-md-6">
              <label class="form-label">Image (replace)</label>
              <input id="card_modal_image" type="file" name="image" accept="image/*" class="form-control form-control-sm {{ (old('editing_gallery_id') && $errors->has('image')) ? 'is-invalid' : '' }}">
              @if(old('editing_gallery_id')) @error('image')<div class="text-danger small">{{ $message }}</div>@enderror @endif
            </div>

            <div class="col-md-6">
              <label class="form-label"></label>
              <div id="card_modal_preview" class="mt-1"></div>
              <style>
                /* small thumbnail in table */
                .gallery-thumb { display:inline-block; vertical-align:middle; }
              </style>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Helper: populate and show Edit Child modal
function openEditChildModal(data){
    var id = data.id;
    var modal = document.getElementById('editChildModal');
    var form = document.getElementById('editChildForm');
    form.action = '/admin/gallery-children/' + id; // route for update (PUT)
    document.getElementById('editing_child_id').value = id;
    document.getElementById('modal_title').value = data.title || '';
    document.getElementById('modal_url').value = data.url || '';
    document.getElementById('modal_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('modal_status')) document.getElementById('modal_status').value = data.status || 'On going';
    document.getElementById('modal_is_mother').checked = data.is_mother ? true : false;
    document.getElementById('modal_docno').textContent = data.docno || '-';

    // histories (array)
    var histEl = document.getElementById('modal_docno_history');
    histEl.innerHTML = '';
    (data.histories || []).forEach(function(h){
        var li = document.createElement('li');
        li.innerHTML = '<strong>' + (h.docno || '') + '</strong>' +
                       ' <span class="text-muted">(previous: ' + (h.previous_docno||'-') + ')</span>' +
                       ' — <em>' + (h.creator || '') + '</em> <small class="text-muted">' + (h.created_at || '') + '</small>';
        histEl.appendChild(li);
    });

    // Ensure this modal stacks above any already-open modal (fixes "appears behind" issue)
    try {
        var visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length > 0) {
            var highest = 1050;
            visibleModals.forEach(function(m){
                var z = parseInt(window.getComputedStyle(m).zIndex, 10) || 1050;
                if (z > highest) highest = z;
            });
            // place this modal above the highest visible modal
            modal.style.zIndex = (highest + 20);
        } else {
            modal.style.zIndex = '';
        }
    } catch (e) { /* defensive - ignore stacking calc errors */ }

    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // adjust the newly-created backdrop so it sits beneath this modal
    setTimeout(function(){
        try {
            var backdrops = document.querySelectorAll('.modal-backdrop.show');
            if (backdrops.length) {
                var topBackdrop = backdrops[backdrops.length - 1];
                var modalZ = parseInt(modal.style.zIndex || window.getComputedStyle(modal).zIndex, 10) || 1070;
                topBackdrop.style.zIndex = (modalZ - 10);
            }
        } catch (e) { /* ignore */ }
    }, 0);

    // cleanup inline styles when modal closes
    modal.addEventListener('hidden.bs.modal', function cleanupStacking(){
        modal.style.zIndex = '';
        try {
            var bps = document.querySelectorAll('.modal-backdrop.show');
            if (bps.length) {
                var last = bps[bps.length - 1];
                if (last) last.style.zIndex = '';
            }
        } catch(e){}
        modal.removeEventListener('hidden.bs.modal', cleanupStacking);
    });
}

// Helper: open Add Child modal and populate fields
function openAddChildModal(data){
    var cardId = data.cardId || data.parent_card_id || null;
    var modal = document.getElementById('addChildModal');
    var form = document.getElementById('addChildForm');
    form.action = '/admin/gallery-cards/' + cardId + '/children';
    document.getElementById('add_parent_card_id').value = cardId || '';
    document.getElementById('add_child_title').value = data.title || '';
    document.getElementById('add_child_url').value = data.url || '';
    document.getElementById('add_child_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('add_child_status')) document.getElementById('add_child_status').value = data.status || 'On going';
    var bs = new bootstrap.Modal(modal);
    bs.show();
}

// Helper: open Add Sub-child modal and populate fields
function openAddSubChildModal(data){
    var cardId = data.cardId || data.parent_card_id || null;
    var parentChildId = data.parentChildId || data.parent_child_id || null;
    var modal = document.getElementById('addSubChildModal');
    var form = document.getElementById('addSubChildForm');
    form.action = '/admin/gallery-cards/' + cardId + '/children';
    document.getElementById('add_sub_parent_card_id').value = cardId || '';
    document.getElementById('add_parent_child_id').value = parentChildId || '';
    document.getElementById('add_sub_title').value = data.title || '';
    document.getElementById('add_sub_url').value = data.url || '';
    document.getElementById('add_sub_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('add_sub_status')) document.getElementById('add_sub_status').value = data.status || 'On going';
    var bs = new bootstrap.Modal(modal);
    bs.show();
}

// Helper: populate and show Edit Gallery Card modal
function openEditCardModal(data){
    var id = data.id;
    var modal = document.getElementById('editCardModal');
    var form = document.getElementById('editCardForm');
    form.action = '/admin/gallery-cards/' + id; // route for update (PUT)
    document.getElementById('editing_gallery_id_card').value = id;
    document.getElementById('card_modal_title').value = data.title || '';
    document.getElementById('card_modal_url').value = data.url || '';
    document.getElementById('card_modal_is_active').value = data.is_active ? '1' : '0';
    if (document.getElementById('card_modal_status')) document.getElementById('card_modal_status').value = data.status || 'On going';

    var prev = document.getElementById('card_modal_preview');
    prev.innerHTML = '';
    if (data.image) {
        var img = document.createElement('img');
        img.src = data.image;
        img.style.maxHeight = '64px';
        img.style.objectFit = 'contain';
        prev.appendChild(img);
    }

    try {
        var visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length > 0) {
            var highest = 1050;
            visibleModals.forEach(function(m){
                var z = parseInt(window.getComputedStyle(m).zIndex, 10) || 1050;
                if (z > highest) highest = z;
            });
            modal.style.zIndex = (highest + 20);
        } else {
            modal.style.zIndex = '';
        }
    } catch (e) {}

    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    setTimeout(function(){
        try {
            var backdrops = document.querySelectorAll('.modal-backdrop.show');
            if (backdrops.length) {
                var topBackdrop = backdrops[backdrops.length - 1];
                var modalZ = parseInt(modal.style.zIndex || window.getComputedStyle(modal).zIndex, 10) || 1070;
                topBackdrop.style.zIndex = (modalZ - 10);
            }
        } catch (e) {}
    }, 0);

    modal.addEventListener('hidden.bs.modal', function cleanupCardStacking(){
        modal.style.zIndex = '';
        try {
            var bps = document.querySelectorAll('.modal-backdrop.show');
            if (bps.length) {
                var last = bps[bps.length - 1];
                if (last) last.style.zIndex = '';
            }
        } catch(e){}
        modal.removeEventListener('hidden.bs.modal', cleanupCardStacking);
    });
}

document.addEventListener('DOMContentLoaded', function(){

    // clickable rows toggle their children panel; ignore clicks on interactive controls
    document.querySelectorAll('.gallery-row').forEach(function(row){
        row.addEventListener('click', function(e){
            if (e.target.closest('button, a, input, select, textarea, form')) return; // don't toggle when interacting with controls
            var sel = row.getAttribute('data-bs-target');
            if (!sel) return;
            var targetEl = document.querySelector(sel);
            if (!targetEl) return;
            var inst = bootstrap.Collapse.getInstance(targetEl);
            if (!inst) inst = new bootstrap.Collapse(targetEl, {toggle: false});
            inst.toggle();
        });

        // hook icon rotation to collapse events (use the actual collapse target like sidebar does)
        var icon = row.querySelector('.expand-icon');
        var sel = row.getAttribute('data-bs-target');
        if (!sel) return;
        var panel = document.querySelector(sel);
        if (!panel || !icon) return;
        if (panel.classList.contains('show')) { icon.style.transform = 'rotate(0deg)'; row.classList.add('expanded'); }

        // show / shown: add expanded + animate state
        panel.addEventListener('show.bs.collapse', function(){
            icon.style.transform = 'rotate(0deg)';
            row.classList.add('expanded');
            row.classList.add('animating');
        });
        panel.addEventListener('shown.bs.collapse', function(){
            // animation finished
            row.classList.remove('animating');
        });

        // hide / hidden: remove expanded after animation finishes
        panel.addEventListener('hide.bs.collapse', function(){
            icon.style.transform = 'rotate(-90deg)';
            row.classList.add('animating');
            row.classList.remove('expanded');
        });
        panel.addEventListener('hidden.bs.collapse', function(){
            row.classList.remove('animating');
        });
    });

    // open edit modal when Edit button is clicked
    document.querySelectorAll('.btn-edit-child').forEach(function(btn){
        btn.addEventListener('click', function(){
            var data = {
                id: btn.getAttribute('data-id'),
                title: btn.getAttribute('data-title'),
                url: btn.getAttribute('data-url'),
                is_active: parseInt(btn.getAttribute('data-is-active') || '1', 10),
                status: btn.getAttribute('data-status') || 'On going',
                is_mother: parseInt(btn.getAttribute('data-is-mother') || '0', 10),
                docno: btn.getAttribute('data-docno') || '-',
                histories: []
            };
            try { data.histories = JSON.parse(btn.getAttribute('data-histories') || '[]'); } catch(e){ data.histories = []; }
            openEditChildModal(data);
        });
    });

    // open edit modal for gallery card (make edit action uniform)
    document.querySelectorAll('.btn-edit-card').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation(); // prevent row toggle when clicking Edit
            var data = {
                id: btn.getAttribute('data-id'),
                title: btn.getAttribute('data-title'),
                url: btn.getAttribute('data-url'),
                is_active: parseInt(btn.getAttribute('data-is-active') || '1', 10)
            };
            openEditCardModal(data);
        });
    });

    // open Add Child modal when Add Child button is clicked
    document.querySelectorAll('.btn-open-add-child').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            openAddChildModal({ cardId: cardId });
        });
    });

    // open Add Sub-child modal when Add Sub-child button is clicked
    document.querySelectorAll('.btn-open-add-subchild').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            var parentChildId = btn.getAttribute('data-parent-child-id');
            openAddSubChildModal({ cardId: cardId, parentChildId: parentChildId });
        });
    });

    // open Manage Sub-children modal (populates list)
    document.querySelectorAll('.btn-manage-subchildren').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            var childId = btn.getAttribute('data-child-id');
            var childTitle = btn.getAttribute('data-child-title') || 'Sub-children';
            var subs = [];
            try { subs = JSON.parse(btn.getAttribute('data-subchildren') || '[]'); } catch(e){ subs = []; }

            var modal = document.getElementById('manageSubChildrenModal');
            document.getElementById('manageSub_childTitle').textContent = 'Sub-children for "' + childTitle + '"';
            document.getElementById('manageSub_childCount').textContent = (subs.length || 0) + ' sub-child(ren)';

            var tbody = document.getElementById('manageSub_tbody');
            tbody.innerHTML = '';

            // read parent histories and build parent node
            var parentHistories = [];
            try { parentHistories = JSON.parse(btn.getAttribute('data-child-histories') || '[]'); } catch(e){ parentHistories = []; }

            var parentNode = {
                id: childId,
                title: childTitle,
                docno: btn.getAttribute('data-child-docno') || '-',
                url: btn.getAttribute('data-child-url') || '',
                is_active: parseInt(btn.getAttribute('data-child-is-active') || '0', 10),
                status: btn.getAttribute('data-child-status') || 'On going',
                created_by: btn.getAttribute('data-child-created-by') || '',
                histories: parentHistories,
                children: subs || []
            };

            function renderSubRows(nodes, level){
                nodes.forEach(function(n){
                    var tr = document.createElement('tr');
                    var indent = '<div style="padding-left:' + (level*18) + 'px;">' + (n.title || '') + '</div>';
                    var titleHtml = indent + (n.children && n.children.length ? ' <span class="badge bg-secondary ms-2">' + n.children.length + '</span>' : '');
                    var histAttr = ' data-histories="' + (n.histories ? (JSON.stringify(n.histories).replace(/'/g,'&#39;').replace(/\"/g,'&quot;')) : '[]') + '"';

                    tr.innerHTML = '<td>' + titleHtml + '</td>' +
                                   '<td>' + (n.docno || '') + '</td>' +
                                   '<td><small class="text-muted">' + (n.url || '-') + '</small></td>' +
                                   '<td>' + (n.is_active ? 'Yes' : 'No') + '</td>' +
                                   '<td>' + (n.status || 'On going') + '</td>' +
                                   '<td>' + (n.created_by || '') + '</td>' +
                                   '<td>' + (n.updated_by || '') + '</td>' +
                                   '<td style="white-space:nowrap; width:170px;">' +
                                     '<div class="d-flex gap-2 flex-wrap" style="display:flex;gap:.5rem;align-items:center;">' +
                                       '<button type="button" class="btn btn-sm btn-secondary btn-edit-subchild" aria-label="Edit sub-child" data-id="'+n.id+'" data-title="'+(n.title||'')+'" data-url="'+(n.url||'')+'" data-docno="'+(n.docno||'')+'" data-is-active="'+(n.is_active?1:0)+'" data-status="'+(n.status||'On going')+'"'+histAttr+'>Edit</button>' +
                                       '<form action="/admin/gallery-children/'+n.id+'" method="POST" class="m-0" style="display:inline-block;margin:0;" onsubmit="return confirm(\'Delete this sub-child?\');">' +
                                          '@csrf'.replace('@csrf','{!! csrf_field() !!}') +
                                          '<input type="hidden" name="_method" value="DELETE">' +
                                          '<button type="submit" class="btn btn-sm btn-danger" aria-label="Delete sub-child">Delete</button>' +
                                       '</form>' +
                                     '</div>' +
                                   '</td>';
                    tbody.appendChild(tr);
                    if (n.children && n.children.length) renderSubRows(n.children, level + 1);
                });
            }

            // render only the parent's children (do NOT show the parent as a table row)
            renderSubRows(parentNode.children || [], 0);

            // attach edit listeners for the dynamically created edit buttons (use data-histories if present)
            tbody.querySelectorAll('.btn-edit-subchild').forEach(function(b){
                b.addEventListener('click', function(){
                    var data = {
                        id: b.getAttribute('data-id'),
                        title: b.getAttribute('data-title'),
                        url: b.getAttribute('data-url'),
                        docno: b.getAttribute('data-docno'),
                        is_active: parseInt(b.getAttribute('data-is-active')||'1',10),
                        status: b.getAttribute('data-status') || 'On going',
                        is_mother: 0,
                        histories: []
                    };
                    try { data.histories = JSON.parse(b.getAttribute('data-histories') || '[]'); } catch(e){ data.histories = []; }
                    openEditChildModal(data);
                });
            });

            // wire Add Sub-child button inside modal (shows inline add form)
            var addBtn = document.getElementById('manageSub_addSubchildBtn');
            addBtn.onclick = function(){ showManageSubInlineAdd(cardId, childId); };

            // function to show inline add form inside Manage modal
            function showManageSubInlineAdd(cardIdParam, parentChildIdParam, prefill){
                var form = document.getElementById('manageSub_inlineAddForm');
                form.action = '/admin/gallery-cards/' + (cardIdParam || cardId) + '/children';
                document.getElementById('manageSub_add_parent_card_id').value = cardIdParam || cardId || '';
                document.getElementById('manageSub_add_parent_child_id').value = parentChildIdParam || '';
                document.getElementById('manageSub_add_title').value = (prefill && prefill.title) ? prefill.title : '';
                document.getElementById('manageSub_add_url').value = (prefill && prefill.url) ? prefill.url : '';
                document.getElementById('manageSub_add_is_active').value = (prefill && typeof prefill.is_active !== 'undefined') ? (prefill.is_active ? '1' : '0') : '1';

                // hide header add button and per-row Add buttons while inline form is visible
                var headerBtn = document.getElementById('manageSub_addSubchildBtn');
                if (headerBtn) headerBtn.style.display = 'none';
                document.querySelectorAll('#manageSub_tbody .btn-add-sub-for, .btn-add-sub-for').forEach(function(b){ b.style.display = 'none'; });

                document.getElementById('manageSub_inlineAddWrap').style.display = 'block';
                document.getElementById('manageSub_add_title').focus();
            }

            // Cancel inline add
            var inlineCancel = document.getElementById('manageSub_inlineCancelBtn');
            if (inlineCancel) inlineCancel.onclick = function(){
                document.getElementById('manageSub_inlineAddWrap').style.display = 'none';
                document.getElementById('manageSub_add_title').value = '';
                document.getElementById('manageSub_add_url').value = '';
                document.getElementById('manageSub_add_parent_card_id').value = '';
                document.getElementById('manageSub_add_parent_child_id').value = '';

                // restore header and per-row Add buttons
                var headerBtn = document.getElementById('manageSub_addSubchildBtn');
                if (headerBtn) headerBtn.style.display = '';
                document.querySelectorAll('#manageSub_tbody .btn-add-sub-for, .btn-add-sub-for').forEach(function(b){ b.style.display = ''; });
            };

            // when modal closes, ensure inline form is hidden and header Add is visible
            var manageModalEl = document.getElementById('manageSubChildrenModal');
            if (manageModalEl) manageModalEl.addEventListener('hidden.bs.modal', function(){
                document.getElementById('manageSub_inlineAddWrap').style.display = 'none';
                document.getElementById('manageSub_add_title').value = '';
                document.getElementById('manageSub_add_url').value = '';
                document.getElementById('manageSub_add_parent_card_id').value = '';
                document.getElementById('manageSub_add_parent_child_id').value = '';
                var headerBtn = document.getElementById('manageSub_addSubchildBtn');
                if (headerBtn) headerBtn.style.display = '';
                document.querySelectorAll('#manageSub_tbody .btn-add-sub-for, .btn-add-sub-for').forEach(function(b){ b.style.display = ''; });
            });

            var bs = new bootstrap.Modal(modal);
            bs.show();
        });

        // helper to expose inline add for JS outside this handler
        window.showManageSubInlineAdd = function(a,b,c){ showManageSubInlineAdd(a,b,c); };
    });

    // If server-side validation failed for adding a child, reopen Add Child modal with old() values
    @if(old('parent_card_id') && !old('parent_child_id'))
        openAddChildModal({
            cardId: {{ (int) old('parent_card_id') }},
            title: {!! json_encode(old('title')) !!},
            url: {!! json_encode(old('url')) !!},
            is_active: {!! json_encode(old('is_active', 1)) !!}
        });
    @endif

    // If server-side validation failed for adding a sub-child, reopen Add Sub-child modal with old() values
    @if(old('parent_card_id') && old('parent_child_id'))
        // If submission came from the Manage modal, re-open Manage modal and show inline add; otherwise open the standalone Add Sub-child modal
        @if(old('from_manage_modal'))
            (function(){
                var cardIdOld = {{ (int) old('parent_card_id') }};
                var parentChildIdOld = {{ (int) old('parent_child_id') }};
                document.querySelectorAll('.btn-manage-subchildren').forEach(function(b){
                    if (parseInt(b.getAttribute('data-child-id')) === parentChildIdOld && parseInt(b.getAttribute('data-card-id')) === cardIdOld) {
                        b.click();
                        setTimeout(function(){
                            // show inline add with old() values
                            if (window.showManageSubInlineAdd) {
                                window.showManageSubInlineAdd(cardIdOld, parentChildIdOld, {
                                    title: {!! json_encode(old('title')) !!},
                                    url: {!! json_encode(old('url')) !!},
                                    is_active: {!! json_encode(old('is_active', 1)) !!}
                                });
                            }
                        }, 250);
                    }
                });
            })();
        @else
            openAddSubChildModal({
                cardId: {{ (int) old('parent_card_id') }},
                parentChildId: {{ (int) old('parent_child_id') }},
                title: {!! json_encode(old('title')) !!},
                url: {!! json_encode(old('url')) !!},
                is_active: {!! json_encode(old('is_active', 1)) !!}
            });
        @endif
    @endif

    // If server-side validation failed for editing child, reopen modal with old() values
    @if(old('editing_child_id'))
        openEditChildModal({
            id: {{ (int) old('editing_child_id') }},
            title: {!! json_encode(old('title')) !!},
            url: {!! json_encode(old('url')) !!},
            is_active: {!! json_encode(old('is_active', 1)) !!},
            is_mother: {!! old('is_mother') ? '1' : '0' !!},
            docno: {!! json_encode(old('docno') ?? '-') !!},
            status: {!! json_encode(old('status', 'On going')) !!},
            histories: []
        });
    @endif

    // If server-side validation failed for editing gallery card, reopen modal with old() values
    @if(old('editing_gallery_id'))
        openEditCardModal({
            id: {{ (int) old('editing_gallery_id') }},
            title: {!! json_encode(old('title')) !!},
            url: {!! json_encode(old('url')) !!},
            is_active: {!! json_encode(old('is_active', 1)) !!},
            status: {!! json_encode(old('status', 'On going')) !!}
        });
    @endif
});
</script>

@endsection