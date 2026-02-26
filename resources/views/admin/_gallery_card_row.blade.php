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
            <form action="{{ route('admin.gallery.destroy', $card->id) }}" method="POST" class="m-0 ajax-form">
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
                        @foreach(($card->children ?? collect())->sortBy('id') as $child)
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
                                            $subChildrenJson = $child->children->sortBy('docno')->map(function($c){
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

                                    <form action="{{ route('admin.gallery.children.destroy', $child->id) }}" method="POST" class="m-0 ajax-form">
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