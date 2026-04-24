

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">Create New Table</h6>
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" class="form-control" id="newTableName" placeholder="Table Name">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success w-100" id="createTableBtn">Create</button>
                    </div>
                </div>
                {{-- Success/Error message will appear here --}}
                <div id="createTableMessage" class="mt-2"></div>
            </div>
        </div>

        {{-- DATABASE TABLES LIST SECTION --}}
        {{-- Displays all tables in the database with record counts --}}
        <div class="card mt-4">
            <div class="card-header">
                <h2>Database Tables</h2>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Table Name</th>
                            <th>Records Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop through all tables passed from controller --}}
                        @forelse($tables as $index => $table)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td style="cursor: pointer;" class="table-row-clickable" data-table="{{ $table->name }}">{{ $table->name }}</td>
                                <td>{{ $table->count }}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm delete-table-btn" data-table="{{ $table->name }}">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No tables found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="columnsModal" tabindex="-1" aria-labelledby="columnsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="columnsModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-3">
                        <h6 class="card-title">System Define Fields</h6>
                        <div class="card-body">
                            <h6 class="card-title">Add New Column</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="newColumnName" placeholder="Column Name">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="newColumnType">
                                        <option value="VARCHAR(255)">VARCHAR(255)</option>
                                        <option value="INT">INT</option>
                                        <option value="BIGINT">BIGINT</option>
                                        <option value="TEXT">TEXT</option>
                                        <option value="BOOLEAN">BOOLEAN</option>
                                        <option value="DATE">DATE</option>
                                        <option value="DATETIME">DATETIME</option>
                                        <option value="DECIMAL(10,2)">DECIMAL(10,2)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="newColumnNullable">
                                        <option value="NULL">Nullable</option>
                                        <option value="NOT NULL">Not Null</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" id="addColumnBtn">Add</button>
                                </div>
                            </div>
                            <div id="addColumnMessage" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Column Name</th>
                                <th>Type</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="columnsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const tableRows = document.querySelectorAll('.table-row-clickable');
            const modalElement = document.getElementById('columnsModal');
            let modal = null;
            let currentTableName = ''; 
            
            if (modalElement && typeof bootstrap !== 'undefined') {
                modal = new bootstrap.Modal(modalElement);
            } else {
                console.error('Bootstrap or modal element not found');
            }
            
            document.getElementById('createTableBtn').addEventListener('click', function() {
                const tableName = document.getElementById('newTableName').value.trim();
                
                function showAlert(containerId, type, text) {
                    const c = document.getElementById(containerId);
                    if (!c) return;
                    try { c.textContent = ''; const d = document.createElement('div'); d.className = 'alert alert-' + type; d.textContent = text; c.appendChild(d); } catch(e) { try { c.innerHTML = sanitizeHtml('<div class="alert alert-' + type + '">' + String(text) + '</div>'); } catch(_){} }
                }

                if (!tableName) {
                    showAlert('createTableMessage', 'danger', 'Table name is required');
                    return;
                }

                showAlert('createTableMessage', 'info', 'Creating table...');
                
                fetch('/admin/create-table', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ table: tableName })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('createTableMessage').innerHTML = sanitizeHtml('<div class="alert alert-success">Table created successfully!</div>');
                        document.getElementById('newTableName').value = '';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('createTableMessage', 'danger', 'Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    document.getElementById('createTableMessage').innerHTML = sanitizeHtml('<div class="alert alert-danger">Error creating table</div>');
                    console.error('Error:', error);
                });
            });
            
            document.querySelectorAll('.delete-table-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); 
                    const tableName = this.getAttribute('data-table');
                    
                    if (confirm(`Are you sure you want to delete table "${tableName}"? This will delete all data!`)) {
                        deleteTable(tableName);
                    }
                });
            });
            
            function deleteTable(tableName) {
                fetch('/admin/delete-table', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ table: tableName })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Table deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting table');
                    console.error('Error:', error);
                });
            }
            
            tableRows.forEach(row => {
                row.addEventListener('click', function() {
                    const tableName = this.getAttribute('data-table');
                    currentTableName = tableName; // Store for add/delete column operations
                    
                    document.getElementById('columnsModalLabel').textContent = 'Columns in ' + tableName;
                    
                    document.getElementById('columnsTableBody').innerHTML = sanitizeHtml('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
                    
                    document.getElementById('addColumnMessage').textContent = '';
                    
                    if (modal) {
                        modal.show();
                        loadColumns(tableName);
                    } else {
                        alert('Modal not initialized. Please refresh the page.');
                    }
                });
            });
            
            function loadColumns(tableName) {
                fetch(`/admin/table-columns/${tableName}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        data.forEach((column, index) => {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${column.Field}</td>
                                    <td>${column.Type}</td>
                                    <td>${column.Null}</td>
                                    <td>${column.Key}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm delete-column-btn" 
                                                data-column="${column.Field}">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        document.getElementById('columnsTableBody').innerHTML = sanitizeHtml(html);
                        
                        document.querySelectorAll('.delete-column-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const columnName = this.getAttribute('data-column');
                                if (confirm(`Are you sure you want to delete column "${columnName}"?`)) {
                                    deleteColumn(tableName, columnName);
                                }
                            });
                        });
                    })
                    .catch(error => {
                            try { document.getElementById('columnsTableBody').innerHTML = sanitizeHtml('<tr><td colspan="6" class="text-center text-danger">Error loading columns</td></tr>'); } catch(_){} 
                        console.error('Error:', error);
                    });
            }
            
            document.getElementById('addColumnBtn').addEventListener('click', function() {
                const columnName = document.getElementById('newColumnName').value.trim();
                const columnType = document.getElementById('newColumnType').value;
                const nullable = document.getElementById('newColumnNullable').value;
                
                if (!columnName) {
                    showAlert('addColumnMessage', 'danger', 'Column name is required');
                    return;
                }

                showAlert('addColumnMessage', 'info', 'Adding column...');
                
                fetch('/admin/add-column', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        table: currentTableName,
                        column: columnName,
                        type: columnType,
                        nullable: nullable
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('addColumnMessage').innerHTML = sanitizeHtml('<div class="alert alert-success">Column added successfully!</div>');
                        document.getElementById('newColumnName').value = '';
                        loadColumns(currentTableName); 
                    } else {
                        showAlert('addColumnMessage', 'danger', 'Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    showAlert('addColumnMessage', 'danger', 'Error adding column');
                    console.error('Error:', error);
                });
            });
            
            
            function deleteColumn(tableName, columnName) {
                document.getElementById('addColumnMessage').innerHTML = sanitizeHtml('<div class="alert alert-info">Deleting column...</div>');

                fetch('/admin/delete-column', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        table: tableName,
                        column: columnName
                    })
                })
                .then(async response => {
                    const text = await response.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error(text || 'Unknown error');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showAlert('addColumnMessage', 'success', 'Column deleted successfully!');
                        loadColumns(tableName); 
                    } else {
                        showAlert('addColumnMessage', 'danger', 'Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    showAlert('addColumnMessage', 'danger', 'Error deleting column: ' + (error && error.message ? error.message : String(error)));
                    console.error('Error:', error);
                });
            }
            
            
        }); 
    </script>
    </div>
@endsection