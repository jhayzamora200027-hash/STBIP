@extends('layouts.app')


@section('content')
<div class="container py-4">
    <style>
      .gallery-row.expanded > td {
        border-top: none;
        border-left: none;
        border-right: none;
        background: #ffffff;
        box-shadow: 0 6px 18px rgba(16,24,32,0.04);
      }
      .gallery-row.expanded > td:first-child { border-left: 3px solid #9aa0a6; border-top-left-radius: .25rem; }
      .gallery-row.expanded > td:last-child  { border-right: 3px solid #9aa0a6; border-top-right-radius: .25rem; }
      .children-row > td {
        border: none;
        padding: 0;
        background: transparent;
      }
      .children-panel {
        display: block;
        padding: 0;
        background: transparent;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: height .36s cubic-bezier(.2,.9,.2,1), max-height .36s cubic-bezier(.2,.9,.2,1), opacity .22s ease, border-color .18s ease;
      }
      .children-panel .card {
        transform: translateY(-8px);
        opacity: 0;
        transition: transform .32s cubic-bezier(.2,.9,.2,1), opacity .22s ease;
        will-change: transform, opacity;
      }
      .children-panel.show {
        border-top: 3px solid #9aa0a6;
        border-left: 3px solid #9aa0a6;
        border-right: 3px solid #9aa0a6;
        border-bottom: 3px solid #9aa0a6;
        background: #fbfcfd;
        border-radius: .35rem;
        box-sizing: border-box;
        width: 100%;
        margin-top: -3px;

        /* allow the panel to expand via max-height (large enough for content) */
        max-height: 1400px;
        opacity: 1;
      }

      .children-panel.show .card {
        transform: translateY(0);
        opacity: 1;
        transition-delay: .02s;
      }

      .children-row > td .card { border: none; box-shadow: none; margin-bottom: 0; }
      .children-row > td .table { margin-bottom: 0; }
      .expand-icon { transition: transform .22s ease; }
      .children-panel.collapse .card {
        transform: translateY(-8px);
        opacity: 0;
        transition: transform .32s cubic-bezier(.2,.9,.2,1), opacity .22s ease;
        will-change: transform, opacity;
      }
      .children-panel.collapse.show .card {
        transform: translateY(0);
        opacity: 1;
        transition-delay: .03s;
      }
      .gallery-row.expanded > td { transition: border-color .22s ease, box-shadow .28s ease, background-color .18s ease; }
      .gallery-row.animating > td { box-shadow: 0 8px 26px rgba(16,24,32,0.06); }
      .gallery-row td { transition: background-color .12s ease; }
      .table { border-collapse: collapse; }
      .table td, .table th { border-spacing: 0; }
      .gallery-row.expanded > td { border-bottom: 0 !important; }
      .gallery-row.expanded + .children-row > td { border-top: 0 !important; }
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
                        <label class="form-label">Description</label>
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
                            @include('admin._gallery_card_row', ['card' => $card])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

 
<div class="modal fade" id="editChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editChildForm" method="POST" action="" class="ajax-form">
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

 
<div class="modal fade" id="addChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addChildForm" method="POST" action="" class="ajax-form">
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

 
<div class="modal fade" id="addSubChildModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add sub-child</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addSubChildForm" method="POST" action="" class="ajax-form">
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
          <form id="manageSub_inlineAddForm" method="POST" action="" class="ajax-form">
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
// utility: replace an existing card row (gallery-row + children-row)
function replaceCardRow(cardId, html){
    var parser = new DOMParser();
    var doc = parser.parseFromString('<table>' + html + '</table>', 'text/html');
    var newGalleryRow = doc.querySelector('tr.gallery-row');
    var newChildrenRow = doc.querySelector('tr.children-row');
    if(!newGalleryRow || !newChildrenRow) return;
    var galleryRow = document.querySelector('tr.gallery-row[data-bs-target="#children-panel-' + cardId + '"]');
    var childrenRow = document.getElementById('children-row-' + cardId);
    var wasOpen = false;
    if(childrenRow){
        var panelEl = childrenRow.querySelector('.children-panel');
        wasOpen = panelEl && panelEl.classList.contains('show');
    }
    if(galleryRow && childrenRow) {
        galleryRow.parentNode.replaceChild(newGalleryRow, galleryRow);
        childrenRow.parentNode.replaceChild(newChildrenRow, childrenRow);
    }
    // re-bind events on newly inserted rows and forms
    initGalleryRowEvents();
    initChildControlListeners();
    initAjaxForms();
    // restore collapse state if needed
    if(wasOpen){
        var newPanel = document.querySelector('#children-panel-' + cardId);
        if(newPanel){
            var inst = bootstrap.Collapse.getInstance(newPanel);
            if(!inst) inst = new bootstrap.Collapse(newPanel, {toggle:false});
            inst.show();
        }
    }
}

// generic ajax submit helper
function ajaxSubmit(form, successCb, errorCb){
    // determine method and ensure Laravel-friendly override behaviour
    var override = (form.querySelector('input[name="_method"]') || {}).value;
    var method = override || form.method || 'POST';
    var url = form.action;
    var data = new FormData(form);
    // if we have an override, always submit as POST; Laravel will respect the hidden _method
    var fetchMethod = method.toUpperCase();
    if (override) fetchMethod = 'POST';

    // include CSRF token header if available (Laravel expects X-CSRF-TOKEN)
    var headers = {
        'X-Requested-With': 'XMLHttpRequest'
    };
    var meta = document.querySelector('meta[name="csrf-token"]');
    if(meta){ headers['X-CSRF-TOKEN'] = meta.getAttribute('content'); }
    // debug: log submitted data for troubleshooting
    console.debug('ajaxSubmit', fetchMethod, url);
    data.forEach(function(v,k){ console.debug('  ', k, v); });
    fetch(url, {
        method: fetchMethod,
        headers: headers,
        body: data
    }).then(function(resp){
        if(resp.ok) return resp.json();
        return resp.json().then(function(j){ throw j; });
    }).then(function(json){
        if(json.success){
            successCb && successCb(json);
        } else {
            errorCb && errorCb(json);
        }
        if(typeof hideLoader === 'function') hideLoader();
    }).catch(function(err){
        console.error('ajax error', err);
        if(typeof hideLoader === 'function') hideLoader();
        errorCb && errorCb(err);
    });
}

// Safely hide a modal and remove any leftover backdrops that can block the UI
function safeHideModal(mod){
  try {
    var bs = bootstrap.Modal.getInstance(mod);
    if(bs) bs.hide();
  } catch(e){ console.error('safeHideModal hide error', e); }

  // small delay to allow Bootstrap to remove its backdrop; if it didn't, clean up manually
  setTimeout(function(){
    try {
      // remove any stray backdrops
      document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
      // ensure body no longer has modal-open
      document.body.classList.remove('modal-open');
    } catch(e){}
  }, 120);
}

// display validation errors on a form (expects Laravel-style errors object)
function showFormErrors(form, errors){
    // clear previous state
    form.querySelectorAll('.is-invalid').forEach(function(el){ el.classList.remove('is-invalid'); });
    form.querySelectorAll('.invalid-feedback').forEach(function(el){ el.remove(); });
    if(!errors) return;
    Object.entries(errors).forEach(function([field,msgs]){
        var input = form.querySelector('[name="'+field+'"]');
        if(!input) return;
        input.classList.add('is-invalid');
        var fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        fb.textContent = msgs[0] || '';
        if(input.parentNode) input.parentNode.appendChild(fb);
    });
}

function initAjaxForms(){
    document.querySelectorAll('form.ajax-form').forEach(function(f){
        if (f.dataset.ajaxBound) return;
        f.dataset.ajaxBound = '1';
        f.addEventListener('submit', function(e){
            e.preventDefault();
            ajaxSubmit(f, function(json){
                if(json.rowHtml){ replaceCardRow(json.card_id, json.rowHtml); }
            });
        });
    });
}

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
    } catch (e) {  }

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
        } catch (e) {  }
    }, 0);

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


function initGalleryRowEvents(){
    document.querySelectorAll('.gallery-row').forEach(function(row){
        row.addEventListener('click', function(e){
            if (e.target.closest('button, a, input, select, textarea, form')) return;
            var sel = row.getAttribute('data-bs-target');
            if (!sel) return;
            var targetEl = document.querySelector(sel);
            if (!targetEl) return;
            var inst = bootstrap.Collapse.getInstance(targetEl);
            if (!inst) inst = new bootstrap.Collapse(targetEl, {toggle: false});
            inst.toggle();
        });

        var icon = row.querySelector('.expand-icon');
        var sel = row.getAttribute('data-bs-target');
        if (!sel) return;
        var panel = document.querySelector(sel);
        if (!panel || !icon) return;
        if (panel.classList.contains('show')) { icon.style.transform = 'rotate(0deg)'; row.classList.add('expanded'); }

        panel.addEventListener('show.bs.collapse', function(){
            icon.style.transform = 'rotate(0deg)';
            row.classList.add('expanded');
            row.classList.add('animating');
        });
        panel.addEventListener('shown.bs.collapse', function(){
            row.classList.remove('animating');
        });
        panel.addEventListener('hide.bs.collapse', function(){
            icon.style.transform = 'rotate(-90deg)';
            row.classList.add('animating');
            row.classList.remove('expanded');
        });
        panel.addEventListener('hidden.bs.collapse', function(){
            row.classList.remove('animating');
        });
    });
}

function initChildControlListeners(){
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

    document.querySelectorAll('.btn-edit-card').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var data = {
                id: btn.getAttribute('data-id'),
                title: btn.getAttribute('data-title'),
                url: btn.getAttribute('data-url'),
                is_active: parseInt(btn.getAttribute('data-is-active') || '1', 10)
            };
            openEditCardModal(data);
        });
    });

    document.querySelectorAll('.btn-open-add-child').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            openAddChildModal({ cardId: cardId });
        });
    });

    document.querySelectorAll('.btn-open-add-subchild').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            var parentChildId = btn.getAttribute('data-parent-child-id');
            openAddSubChildModal({ cardId: cardId, parentChildId: parentChildId });
        });
    });
}

document.addEventListener('DOMContentLoaded', function(){
    initGalleryRowEvents();
    initChildControlListeners();


    ['addChildForm','editChildForm','addSubChildForm','manageSub_inlineAddForm'].forEach(function(id){
      var form = document.getElementById(id);
      if(form && !form.dataset.ajaxBound){
        form.dataset.ajaxBound = '1';
        form.addEventListener('submit', function(e){
          e.preventDefault();
          ajaxSubmit(form, function(json){
            if(json.rowHtml){
              replaceCardRow(json.card_id, json.rowHtml);
              if (currentManageState && document.getElementById('manageSubChildrenModal').classList.contains('show')) {
                var btn = document.querySelector('.btn-manage-subchildren[data-card-id="'+currentManageState.cardId+'"][data-child-id="'+currentManageState.childId+'"]');
                if(btn){
                  try { currentManageState.subs = JSON.parse(btn.getAttribute('data-subchildren')||'[]'); } catch(e){currentManageState.subs = [];}                                
                }
                populateManageSubModal(currentManageState);
              }
            }
            var mod = form.closest('.modal');
            if(mod){
              try { safeHideModal(mod); } catch(e){ console.error(e); }
            }
          }, function(err){
            if(err && err.errors){
              showFormErrors(form, err.errors);
            }
          });
        });
      }
    });

    var currentManageState = null;

    function populateManageSubModal(state){
        if(!state) return;
        var cardId = state.cardId;
        var childId = state.childId;
        var childTitle = state.childTitle || 'Sub-children';
        var subs = state.subs || [];
        var parentHistories = state.parentHistories || [];

        var modal = document.getElementById('manageSubChildrenModal');
        document.getElementById('manageSub_childTitle').textContent = 'Sub-children for "' + childTitle + '"';
        document.getElementById('manageSub_childCount').textContent = (subs.length || 0) + ' sub-child(ren)';

        var tbody = document.getElementById('manageSub_tbody');
        tbody.innerHTML = '';

        var parentNode = {
            id: childId,
            title: childTitle,
            docno: state.docno || '-',
            url: state.url || '',
            is_active: state.is_active || 0,
            status: state.status || 'On going',
            created_by: state.created_by || '',
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
                                   '<form action="/admin/gallery-children/'+n.id+'" method="POST" class="m-0 ajax-form" style="display:inline-block;margin:0;" onsubmit="return confirm(\'Delete this sub-child?\');">' +
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

        renderSubRows(parentNode.children || [], 0);

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
        initAjaxForms();

        try {
          var headerBtn = document.getElementById('manageSub_addSubchildBtn');
          if (headerBtn) {
            headerBtn.onclick = function(){
              console.log('manageSub header Add clicked', cardId, childId);
              showManageSubInlineAdd(cardId, childId);
            };
          }
        } catch(e){ console.error('bind header add error', e); }
    }

      window.populateManageSubModal = populateManageSubModal;

      window.triggerManageSubchildren = function(btn){
        try {
          var b = btn;
          var cardId = b.getAttribute('data-card-id');
          var childId = b.getAttribute('data-child-id');
          var childTitle = b.getAttribute('data-child-title') || 'Sub-children';
          var subs = [];
          try { subs = JSON.parse(b.getAttribute('data-subchildren') || '[]'); } catch(e){ subs = []; }
          var parentHistories = [];
          try { parentHistories = JSON.parse(b.getAttribute('data-child-histories') || '[]'); } catch(e){ parentHistories = []; }

          var state = {
            cardId: cardId,
            childId: childId,
            childTitle: childTitle,
            subs: subs,
            parentHistories: parentHistories,
            docno: b.getAttribute('data-child-docno') || '-',
            url: b.getAttribute('data-child-url') || '',
            is_active: parseInt(b.getAttribute('data-child-is-active') || '0',10),
            status: b.getAttribute('data-child-status') || 'On going',
            created_by: b.getAttribute('data-child-created-by') || ''
          };

          if(window.populateManageSubModal) window.populateManageSubModal(state);
          var modal = document.getElementById('manageSubChildrenModal');
          if(modal){ var bs = new bootstrap.Modal(modal); bs.show(); }
        } catch(e) { console.error('triggerManageSubchildren error', e); }
        return false;
      };

    document.querySelectorAll('.btn-manage-subchildren').forEach(function(btn){
        btn.addEventListener('click', function(){
            var cardId = btn.getAttribute('data-card-id');
            var childId = btn.getAttribute('data-child-id');
            var childTitle = btn.getAttribute('data-child-title') || 'Sub-children';
            var subs = [];
            try { subs = JSON.parse(btn.getAttribute('data-subchildren') || '[]'); } catch(e){ subs = []; }

            var parentHistories = [];
            try { parentHistories = JSON.parse(btn.getAttribute('data-child-histories') || '[]'); } catch(e){ parentHistories = []; }

            currentManageState = { cardId, childId, childTitle, subs, parentHistories,
                docno: btn.getAttribute('data-child-docno') || '-',
                url: btn.getAttribute('data-child-url') || '',
                is_active: parseInt(btn.getAttribute('data-child-is-active') || '0',10),
                status: btn.getAttribute('data-child-status') || 'On going',
                created_by: btn.getAttribute('data-child-created-by') || ''
            };

            populateManageSubModal(currentManageState);

            var modal = document.getElementById('manageSubChildrenModal');
            var bs = new bootstrap.Modal(modal);
            bs.show();
        });

        window.showManageSubInlineAdd = function(a,b,c){ showManageSubInlineAdd(a,b,c); };
    });

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
                                       '<form action="/admin/gallery-children/'+n.id+'" method="POST" class="m-0 ajax-form" style="display:inline-block;margin:0;" onsubmit="return confirm(\'Delete this sub-child?\');">' +
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

            renderSubRows(parentNode.children || [], 0);

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
            initAjaxForms();
            initAjaxForms();
            initAjaxForms();

            var addBtn = document.getElementById('manageSub_addSubchildBtn');
            if (addBtn) {
              addBtn.onclick = function(){
                try {
                  if (currentManageState && currentManageState.cardId) {
                    showManageSubInlineAdd(currentManageState.cardId, currentManageState.childId);
                  } else {
                    var fallbackCard = document.getElementById('manageSub_add_parent_card_id') ? document.getElementById('manageSub_add_parent_card_id').value : '';
                    var fallbackChild = document.getElementById('manageSub_add_parent_child_id') ? document.getElementById('manageSub_add_parent_child_id').value : '';
                    showManageSubInlineAdd(fallbackCard, fallbackChild);
                  }
                } catch(e){
                  console.error('manageSub add button error', e);
                }
              };
            }

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

</script>
@endsection