<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MainReportController;
use App\Http\Controllers\StsMoaListingwithUploadingController;
use App\Http\Controllers\StsAttachmentController;
use App\Http\Controllers\STsReportController;
use App\Http\Controllers\GalleryCardController;



// ==================== AUTHENTICATION ROUTES ====================

// Home / Login entry (shown to guests)
Route::get('/demo', function () {
    return view('demo');
});

Route::get('/demo1', function () {
    return view('demo1');
});

Route::get('/demo2', function () {
    return view('demo2');
});

// Specified Regions gallery page (copied from demo1)
Route::get('/specified-regions', function () {
    return view('SpecifiedRegions');
})->name('specified.regions');
// Home / Login entry (dashboard front page) — render `dashboard.main` but reuse MainReportController data
Route::get('/', function () {
    $controller = app(\App\Http\Controllers\MainReportController::class);
    $view = $controller->index(request());
    return view('dashboard.main', $view->getData());
})->name('main')->middleware('guest');

// Registration page (guests only)
Route::get('/register', function () {
    return view('login.accounts.register');
})->name('register')->middleware('guest');

// Profile (authenticated users)
Route::get('/profile', [UserController::class, 'profile'])
    ->name('profile')
    ->middleware('auth');

Route::put('/profile', [UserController::class, 'updateProfile'])
    ->name('profile.update')
    ->middleware('auth');

// Authentication handlers
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Demo routes
Route::get('/loading-demo', function () {
    return view('loading-demo');
})->name('loading.demo');

Route::get('/demo-modal', function () {
    return view('demo-modal');
})->name('demo.modal');

// ==================== DASHBOARD ROUTES ====================

// Main dashboard page (dashboard front page)
Route::get('/main', function () {
    $controller = app(\App\Http\Controllers\MainReportController::class);
    $view = $controller->index(request());
    return view('dashboard.main', $view->getData());
});


Route::get('/kreport', function () {
    return view('dashboard.mainreports.KnowledgeReport');
})->name('kreport');

Route::get('/streport', [MainReportController::class, 'index'])->name('streport');
Route::post('/streport/prewarm', [MainReportController::class, 'prewarm'])->name('streport.prewarm');

// ==================== EXCEL / UPLOAD ROUTES ====================

// List all uploaded excels
Route::get('/upload-logs', [ExcelController::class, 'uploadLogs'])->name('upload.logs');

// Chart data endpoints
Route::get('/excel/chart-data', [App\Http\Controllers\ExcelController::class, 'chartData'])
    ->name('excel.chartData');
Route::match(['get', 'post'], '/excel/chart-categories-by-title', [ExcelController::class, 'chartCategoriesByTitle'])
    ->name('excel.chartCategoriesByTitle');
Route::get('/excel/chart-categories', [ExcelController::class, 'chartCategoriesByTitle'])
    ->name('excel.chartCategories');

// Base Excel and upload actions
Route::post('/excel/set-base', [ExcelController::class, 'setBase'])->name('excel.setBase');
Route::post('/excel-upload', [ExcelController::class, 'upload'])->name('excel.upload');
Route::post('/excel/refresh-google-sheet', [ExcelController::class, 'refreshFromGoogleSheet'])
    ->name('excel.refreshGoogleSheet');

// ==================== ADMIN: ADDITIONAL USER ====================

// Add Additional User (Admin)
Route::post('/admin/add-user', [UserController::class, 'addUser'])->name('admin.addUser');

// Public route to view ST attachments (PDFs) even when not logged in
Route::get('/sts-attachments/{attachment}', [StsAttachmentController::class, 'show'])
    ->name('sts.attachments.show');

// ==================== PROTECTED ROUTES (Require Authentication) ====================

Route::middleware(['auth'])->group(function () {
    // Uploading page (with logs)
    Route::get('/upload', [ExcelController::class, 'uploadLogs'])->name('upload');
    
    // STs MOA Attachment listing (only rows with Year of MOA and With MOA = true)
    Route::get('/uploadmoasts', [StsMoaListingwithUploadingController::class, 'index'])->name('uploadmoasts');

    // Per-ST attachment upload & management (upload/delete require auth)
    Route::post('/sts-attachments', [StsAttachmentController::class, 'store'])->name('sts.attachments.store');
    Route::delete('/sts-attachments/{attachment}', [StsAttachmentController::class, 'destroy'])
        ->name('sts.attachments.destroy');

    // ==================== USER MANAGEMENT ROUTES ====================
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

    // ==================== USER APPROVAL ROUTES ====================
    Route::get('/approvals', [UserApprovalController::class, 'index'])->name('approvals.index');
    Route::put('/users/{id}/approval', [UserApprovalController::class, 'updateApproval'])
        ->name('users.approval.update');

    // ==================== GALLERY CARD ADMIN ROUTES ====================
    // Manage STsReport gallery cards (add / edit / delete)
    Route::post('/admin/gallery-cards', [GalleryCardController::class, 'store'])->name('admin.gallery.store');
    Route::put('/admin/gallery-cards/{galleryCard}', [GalleryCardController::class, 'update'])->name('admin.gallery.update');
    Route::delete('/admin/gallery-cards/{galleryCard}', [GalleryCardController::class, 'destroy'])->name('admin.gallery.destroy');

    // Child entries for gallery cards (children of a "mother" card)
    Route::post('/admin/gallery-cards/{galleryCard}/children', [\App\Http\Controllers\GalleryChildController::class, 'store'])
        ->name('admin.gallery.children.store');
    Route::put('/admin/gallery-children/{galleryChild}', [\App\Http\Controllers\GalleryChildController::class, 'update'])
        ->name('admin.gallery.children.update');
    Route::delete('/admin/gallery-children/{galleryChild}', [\App\Http\Controllers\GalleryChildController::class, 'destroy'])
        ->name('admin.gallery.children.destroy');

    // STs Report Sector Utilities (admin view)
    Route::get('/admin/sts-report-sectors', [GalleryCardController::class, 'index'])
        ->name('admin.stsreportsectors');

    // ==================== TABLE / SCHEMA ADMIN ROUTES (SYSADMIN ONLY) ====================
    Route::get('/admin', [TableController::class, 'index'])
        ->name('admin')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::get('/admin/table-columns/{tableName}', [TableController::class, 'getColumns']);
    Route::post('/admin/create-table', [TableController::class, 'create']);
    Route::post('/admin/delete-table', [TableController::class, 'delete']);
    Route::post('/admin/add-column', [TableController::class, 'addColumnWithMigration']);
    Route::post('/admin/delete-column', [TableController::class, 'deleteColumnWithMigration']);
});

// AJAX routes
Route::get('/demo1/ajax-region-titles', [STsReportController::class, 'ajaxRegionTitles'])->name('demo1.ajaxRegionTitles');
// Hierarchical JSON for modal dropdowns: provinces -> cities -> ST rows
Route::get('/demo1/ajax-region-hierarchy', [STsReportController::class, 'ajaxRegionHierarchy'])->name('demo1.ajaxRegionHierarchy');


