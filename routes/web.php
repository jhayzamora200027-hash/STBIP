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
})->name('landing')->middleware('guest');



// Profile (authenticated users)
Route::get('/profile', [UserController::class, 'profile'])
    ->name('profile')
    ->middleware('auth');

Route::put('/profile', [UserController::class, 'updateProfile'])
    ->name('profile.update')
    ->middleware('auth');

// Authentication handlers
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register')->middleware('throttle:10,1');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// OTP verification for optional 2FA (email OTP)
Route::get('/otp', [UserController::class, 'showOtpForm'])->name('otp.form');
Route::post('/otp', [UserController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/otp/resend', [UserController::class, 'resendOtp'])->name('otp.resend')->middleware('throttle:6,1');


// ==================== DASHBOARD ROUTES ====================

// Main dashboard page (dashboard front page)
Route::get('/main', function () {
    $controller = app(\App\Http\Controllers\MainReportController::class);
    $view = $controller->index(request());
    return view('dashboard.main', $view->getData());
})->name('main');
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
        ->name('STDashboard')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::post('/social-technologies/import', [\App\Http\Controllers\SocialTechnologyController::class, 'import'])
        ->name('socialtech.import')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::post('/social-technologies/add', [\App\Http\Controllers\SocialTechnologyController::class, 'add'])
        ->name('socialtech.add')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::get('/social-technologies/export', [\App\Http\Controllers\SocialTechnologyController::class, 'export'])
        ->name('socialtech.export')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::patch('/social-technologies/{id}', [\App\Http\Controllers\SocialTechnologyController::class, 'update'])
        ->name('socialtech.update')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::delete('/social-technologies/{id}', [\App\Http\Controllers\SocialTechnologyController::class, 'destroy'])
        ->name('socialtech.destroy')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    // Simple module showing all titles (no pagination)
    Route::get('/st-titles/all', [\App\Http\Controllers\SocialTechnologyTitleModuleController::class, 'index'])
        ->name('sttitles.all');
    // Compatibility redirect for legacy named route references in cached views
    Route::get('/social-technologies/legacy-redirect', function () {
        return redirect()->route('STDashboard');
    })->name('socialtech.index');
    
    // STs MOA Attachment listing (only rows with Year of MOA and With MOA = true)
    Route::get('/uploadmoasts', [StsMoaListingwithUploadingController::class, 'index'])->name('uploadmoasts');

    Route::get('/sts-attachments/logs', [StsAttachmentController::class, 'logs'])
        ->name('sts.attachments.logs')
        ->withoutMiddleware('auth');

    Route::post('/sts-attachments', [StsAttachmentController::class, 'store'])->name('sts.attachments.store');
    Route::delete('/sts-attachments/{attachment}', [StsAttachmentController::class, 'destroy'])
        ->name('sts.attachments.destroy');

    // ==================== USER MANAGEMENT ROUTES ====================
    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::put('/users/{id}', [UserController::class, 'update'])
        ->name('users.update')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);

    // ==================== USER APPROVAL ROUTES ====================
    Route::get('/approvals', [UserApprovalController::class, 'index'])
        ->name('approvals.index')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::put('/users/{id}/approval', [UserApprovalController::class, 'updateApproval'])
        ->name('users.approval.update')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);

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
        ->name('admin.stsreportsectors')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);

    // ==================== TABLE / SCHEMA ADMIN ROUTES (SYSADMIN ONLY) ====================
    Route::get('/admin', [TableController::class, 'index'])
        ->name('admin')
        ->middleware(\App\Http\Middleware\SysAdminMiddleware::class);
    Route::get('/admin/logs', [\App\Http\Controllers\Admin\LogsController::class, 'index'])
        ->name('admin.logs')
        ->middleware(\App\Http\Middleware\RequireAdminOrSysadmin::class);
    Route::get('/admin/table-columns/{tableName}', [TableController::class, 'getColumns']);
    Route::post('/admin/create-table', [TableController::class, 'create']);
    Route::post('/admin/delete-table', [TableController::class, 'delete']);
    Route::post('/admin/add-column', [TableController::class, 'addColumnWithMigration']);
    Route::post('/admin/delete-column', [TableController::class, 'deleteColumnWithMigration']);
});

Route::get('/sts-report/ajax-region-titles', [STsReportController::class, 'ajaxRegionTitles'])->name('stsreport.ajaxRegionTitles');
Route::get('/sts-report/ajax-region-hierarchy', [STsReportController::class, 'ajaxRegionHierarchy'])->name('stsreport.ajaxRegionHierarchy');


