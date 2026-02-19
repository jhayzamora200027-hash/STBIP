{{-- Admin Dashboard Page --}}
{{-- This page allows admin users to manage database tables and columns --}}

@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Check if user has admin privileges --}}
        {{-- @if(Auth::user()->usergroup !== 'sysadmin') --}}
            {{-- Show error message if user is not admin --}}
            {{-- <div>
                You do not have permission to access this page.
                <a href ="{{ route('main') }}" class="btn btn-primary btn-sm ms-3">Go Back</a>
            </div> --}}
        {{-- @else --}}
            {{-- Admin content starts here --}}
            
            {{-- Page Header with Back Button --}}
            {{-- <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>System Define Tables</h1>
            </div> --}}

            {{-- CREATE TABLE SECTION --}}
            {{-- Form to create a new table with default columns (id, created_at, updated_at) --}}
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
                                {{-- Clicking table name opens modal with columns --}}
                                <td style="cursor: pointer;" class="table-row-clickable" data-table="{{ $table->name }}">{{ $table->name }}</td>
                                <td>{{ $table->count }}</td>
                                <td>
                                    {{-- Delete table button --}}
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

    {{-- MODAL: TABLE COLUMNS --}}
    {{-- This modal opens when clicking on a table name --}}
    {{-- Shows all columns in the table and allows adding/deleting columns --}}
    <div class="modal fade" id="columnsModal" tabindex="-1" aria-labelledby="columnsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="columnsModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- ADD COLUMN FORM --}}
                    {{-- Form to add a new column to the selected table --}}
                    <div class="card mb-3">
                        <h6 class="card-title">System Define Fields</h6>
                        <div class="card-body">
                            <h6 class="card-title">Add New Column</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    {{-- Column name input --}}
                                    <input type="text" class="form-control" id="newColumnName" placeholder="Column Name">
                                </div>
                                <div class="col-md-3">
                                    {{-- Column data type selector --}}
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
                                    {{-- Nullable/Not Null selector --}}
                                    <select class="form-select" id="newColumnNullable">
                                        <option value="NULL">Nullable</option>
                                        <option value="NOT NULL">Not Null</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    {{-- Add column button --}}
                                    <button type="button" class="btn btn-primary w-100" id="addColumnBtn">Add</button>
                                </div>
                            </div>
                            {{-- Success/Error message will appear here --}}
                            <div id="addColumnMessage" class="mt-2"></div>
                        </div>
                    </div>
                    
                    {{-- COLUMNS TABLE --}}
                    {{-- Displays all columns in the selected table --}}
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
                        {{-- Columns will be loaded dynamically via JavaScript --}}
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

    {{-- ========================================
         JAVASCRIPT SECTION
         Handles all table and column operations
        ======================================== --}}
    <script>
        // ==============================================================================
        // MAIN INITIALIZATION
        // ==============================================================================
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- VARIABLE DECLARATIONS ---
            const tableRows = document.querySelectorAll('.table-row-clickable');
            const modalElement = document.getElementById('columnsModal');
            let modal = null;
            let currentTableName = ''; // Stores the currently selected table name
            
            // Initialize Bootstrap Modal after DOM is ready
            if (modalElement && typeof bootstrap !== 'undefined') {
                modal = new bootstrap.Modal(modalElement);
            } else {
                console.error('Bootstrap or modal element not found');
            }
            
            
            // ==============================================================================
            // 1. CREATE TABLE FUNCTIONALITY
            // ==============================================================================
            
            // Event: Create Table Button Click
            document.getElementById('createTableBtn').addEventListener('click', function() {
                const tableName = document.getElementById('newTableName').value.trim();
                
                // Validation
                if (!tableName) {
                    document.getElementById('createTableMessage').innerHTML = 
                        '<div class="alert alert-danger">Table name is required</div>';
                    return;
                }
                
                // Show loading message
                document.getElementById('createTableMessage').innerHTML = 
                    '<div class="alert alert-info">Creating table...</div>';
                
                // Send AJAX request
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
                        document.getElementById('createTableMessage').innerHTML = 
                            '<div class="alert alert-success">Table created successfully!</div>';
                        document.getElementById('newTableName').value = '';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        document.getElementById('createTableMessage').innerHTML = 
                            '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('createTableMessage').innerHTML = 
                        '<div class="alert alert-danger">Error creating table</div>';
                    console.error('Error:', error);
                });
            });
            
            
            // ==============================================================================
            // 2. DELETE TABLE FUNCTIONALITY
            // ==============================================================================
            
            // Event: Delete Table Button Click (for all delete buttons)
            document.querySelectorAll('.delete-table-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent modal from opening
                    const tableName = this.getAttribute('data-table');
                    
                    // Confirm before deleting
                    if (confirm(`Are you sure you want to delete table "${tableName}"? This will delete all data!`)) {
                        deleteTable(tableName);
                    }
                });
            });
            
            // Function: Delete Table
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
            
            
            // ==============================================================================
            // 3. MODAL & LOAD COLUMNS FUNCTIONALITY
            // ==============================================================================
            
            // Event: Click on Table Name to Open Modal
            tableRows.forEach(row => {
                row.addEventListener('click', function() {
                    const tableName = this.getAttribute('data-table');
                    currentTableName = tableName; // Store for add/delete column operations
                    
                    // Update modal title
                    document.getElementById('columnsModalLabel').textContent = 'Columns in ' + tableName;
                    
                    // Show loading message
                    document.getElementById('columnsTableBody').innerHTML = 
                        '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
                    
                    // Clear previous messages
                    document.getElementById('addColumnMessage').innerHTML = '';
                    
                    // Open the modal if it exists
                    if (modal) {
                        modal.show();
                        // Load columns for this table
                        loadColumns(tableName);
                    } else {
                        alert('Modal not initialized. Please refresh the page.');
                    }
                });
            });
            
            // Function: Load Columns for a Table
            function loadColumns(tableName) {
                fetch(`/admin/table-columns/${tableName}`)
                    .then(response => response.json())
                    .then(data => {
                        // Build HTML for columns table
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
                        
                        // Insert HTML into table
                        document.getElementById('columnsTableBody').innerHTML = html;
                        
                        // Add click event to each delete column button
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
                        document.getElementById('columnsTableBody').innerHTML = 
                            '<tr><td colspan="6" class="text-center text-danger">Error loading columns</td></tr>';
                        console.error('Error:', error);
                    });
            }
            
            
            // ==============================================================================
            // 4. ADD COLUMN FUNCTIONALITY
            // ==============================================================================
            
            // Event: Add Column Button Click
            document.getElementById('addColumnBtn').addEventListener('click', function() {
                const columnName = document.getElementById('newColumnName').value.trim();
                const columnType = document.getElementById('newColumnType').value;
                const nullable = document.getElementById('newColumnNullable').value;
                
                // Validation
                if (!columnName) {
                    document.getElementById('addColumnMessage').innerHTML = 
                        '<div class="alert alert-danger">Column name is required</div>';
                    return;
                }
                
                // Show loading message
                document.getElementById('addColumnMessage').innerHTML = 
                    '<div class="alert alert-info">Adding column...</div>';
                
                // Send AJAX request
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
                        document.getElementById('addColumnMessage').innerHTML = 
                            '<div class="alert alert-success">Column added successfully!</div>';
                        document.getElementById('newColumnName').value = '';
                        loadColumns(currentTableName); // Refresh columns list
                    } else {
                        document.getElementById('addColumnMessage').innerHTML = 
                            '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('addColumnMessage').innerHTML = 
                        '<div class="alert alert-danger">Error adding column</div>';
                    console.error('Error:', error);
                });
            });
            
            
            // ==============================================================================
            // 5. DELETE COLUMN FUNCTIONALITY
            // ==============================================================================
            
            // Function: Delete Column from Table
            function deleteColumn(tableName, columnName) {
                // Show loading message
                document.getElementById('addColumnMessage').innerHTML = 
                    '<div class="alert alert-info">Deleting column...</div>';

                // Send AJAX request
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
                        // Not JSON, show raw text
                        throw new Error(text || 'Unknown error');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('addColumnMessage').innerHTML = 
                            '<div class="alert alert-success">Column deleted successfully!</div>';
                        loadColumns(tableName); // Refresh columns list
                    } else {
                        document.getElementById('addColumnMessage').innerHTML = 
                            '<div class="alert alert-danger">Error: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('addColumnMessage').innerHTML = 
                        '<div class="alert alert-danger">Error deleting column: ' + error.message + '</div>';
                    console.error('Error:', error);
                });
            }
            
            
        }); // End of DOMContentLoaded
    </script>
        {{-- @endif --}} {{-- End of admin check --}}
    </div>
@endsection