<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MainReportController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\StsMoaListingwithUploadingController;
use App\Http\Controllers\StsAttachmentController;
use App\Http\Controllers\STsReportController;
use App\Http\Controllers\GalleryCardController;



// ==================== AUTHENTICATION ROUTES ====================



// Home / Login entry (dashboard front page) — render `dashboard.main` but reuse MainReportController data
Route::get('/', function () {
    $controller = app(\App\Http\Controllers\MainReportController::class);
    $view = $controller->index(request());
    return view('dashboard.main', $view->getData());
})->name('main')->middleware('guest');



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


// ==================== DASHBOARD ROUTES ====================

// Main dashboard page (dashboard front page)
Route::get('/main', function () {
    $controller = app(\App\Http\Controllers\MainReportController::class);
    $view = $controller->index(request());
    return view('dashboard.main', $view->getData());
});
Route::get('/stbmain', function () {
    $controller = app(\App\Http\Controllers\MainReportController::class);
    $view = $controller->index(request());
    return view('dashboard.stbmain', $view->getData());
})->name('stbmain');
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
    ->where('attachment', '[0-9]+')
    ->name('sts.attachments.show');

// ==================== PROTECTED ROUTES (Require Authentication) ====================

Route::middleware(['auth'])->group(function () {
    Route::get('/upload', [ExcelController::class, 'uploadLogs'])->name('upload');
    Route::get('/masterdata', [MasterDataController::class, 'index'])
        ->name('masterdata.index');
    Route::get('/masterdata/updates-panel', [MasterDataController::class, 'updatesPanel'])
        ->name('masterdata.updates-panel');
    Route::post('/masterdata/regions', [MasterDataController::class, 'storeRegion'])
        ->name('masterdata.regions.store')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::delete('/masterdata/regions/{region}', [MasterDataController::class, 'destroyRegion'])
        ->name('masterdata.regions.destroy')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::post('/masterdata/region-items', [MasterDataController::class, 'storeRegionItem'])
        ->name('masterdata.region-items.store')
        ->middleware(\App\Http\Middleware\MasterDataWriteAccess::class);
    Route::patch('/masterdata/region-items/{regionItem}', [MasterDataController::class, 'updateRegionItem'])
        ->name('masterdata.region-items.update')
        ->middleware(\App\Http\Middleware\MasterDataWriteAccess::class);
    Route::post('/masterdata/import-google-sheet', [MasterDataController::class, 'importGoogleSheet'])
        ->name('masterdata.import-google-sheet')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::get('/masterdata/region-items/export', [MasterDataController::class, 'exportRegionItems'])
        ->name('masterdata.region-items.export')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::post('/masterdata/region-items/import-excel', [MasterDataController::class, 'importRegionItemsExcel'])
        ->name('masterdata.region-items.import-excel')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::post('/masterdata/region-items/import-excel-force', [MasterDataController::class, 'importRegionItemsExcelForce'])
        ->name('masterdata.region-items.import-excel-force')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::delete('/masterdata/region-items/{regionItem}', [MasterDataController::class, 'destroyRegionItem'])
        ->name('masterdata.region-items.destroy')
        ->middleware(\App\Http\Middleware\MasterDataDeleteAccess::class);

    // Social technologies titles upload and listing
    Route::get('/social-technologies', [\App\Http\Controllers\SocialTechnologyController::class, 'index'])
        ->name('socialtech.index');
    Route::post('/social-technologies/import', [\App\Http\Controllers\SocialTechnologyController::class, 'import'])
        ->name('socialtech.import');
    Route::post('/social-technologies/add', [\App\Http\Controllers\SocialTechnologyController::class, 'add'])
        ->name('socialtech.add');
    Route::get('/social-technologies/export', [\App\Http\Controllers\SocialTechnologyController::class, 'export'])
        ->name('socialtech.export');
    Route::patch('/social-technologies/{id}', [\App\Http\Controllers\SocialTechnologyController::class, 'update'])
        ->name('socialtech.update');
    Route::delete('/social-technologies/{id}', [\App\Http\Controllers\SocialTechnologyController::class, 'destroy'])
        ->name('socialtech.destroy');
    
    // STs MOA Attachment listing (only rows with Year of MOA and With MOA = true)
    Route::get('/uploadmoasts', [StsMoaListingwithUploadingController::class, 'index'])->name('uploadmoasts');

    Route::get('/sts-attachments/logs', [StsAttachmentController::class, 'logs'])
        ->name('sts.attachments.logs')
        ->withoutMiddleware('auth');

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

    Route::post('/admin/gallery-cards/{galleryCard}/children', [\App\Http\Controllers\GalleryChildController::class, 'store'])
        ->name('admin.gallery.children.store');
    Route::get('/admin/gallery-cards/{galleryCard}/row', [GalleryCardController::class, 'rowPartial']);
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
Route::get('/sts-report/ajax-region-titles', [STsReportController::class, 'ajaxRegionTitles'])->name('stsreport.ajaxRegionTitles');
// Hierarchical JSON for modal dropdowns: provinces -> cities -> ST rows
Route::get('/sts-report/ajax-region-hierarchy', [STsReportController::class, 'ajaxRegionHierarchy'])->name('stsreport.ajaxRegionHierarchy');


