@extends('layouts.app')

@section('content')
    <h1>Drag &amp; Drop Editor</h1>

    <div id="gjs" style="height:80vh; border:1px solid #ccc;">
        {!! $dashboardHtml ?? '' !!}
    </div>

    <button id="saveBtn" class="btn btn-primary mt-2">Save</button>

    {{-- load grapesjs from CDN to avoid running npm yet --}}
    <link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet"/>
    <script src="https://unpkg.com/grapesjs"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const editor = grapesjs.init({
                container: '#gjs',
                fromElement: true,
                plugins: ['gjs-blocks-basic'],
                storageManager: {
                    type: 'ajax',
                    url: '{{ route('dragdrop.save') }}',
                    autosave: false,
                    params: { _token: '{{ csrf_token() }}' },
                }
            });

            // add main SVG block and preload asset
            editor.BlockManager.add('main-svg', {
                label: 'Main SVG',
                category: 'Images',
                content: `<img src="{{ asset('images/philippines.svg') }}" style="max-width:100%;" />`
            });

            editor.on('load', () => {
                editor.AssetManager.add({
                    src: '{{ asset('images/philippines.svg') }}',
                    type: 'image/svg+xml',
                    name: 'philippines-map'
                });
            });

            document.getElementById('saveBtn').addEventListener('click', function(){
                editor.store(); // POSTs gjs-html/gjs-css to the save route
                alert('Content stored.');
            });
        });
    </script>
@endsection
