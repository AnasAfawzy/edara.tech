<?php

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CashVaultController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AccountStatementController;
use App\Http\Controllers\AccountingSettingsController;
use App\Http\Controllers\OpeningJournalEntryController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
    ],
    function () {

        Route::get('/', function () {
            return view('auth.login');
        });

        // Public routes (غير محمية)
        Route::get('banks/search', [BankController::class, 'search'])->name('banks.search');
        Route::get('cash-vaults/search', [CashVaultController::class, 'search'])->name('cash-vaults.search');

        // Protected routes (محمية بـ auth middleware)
        Route::middleware('auth')->group(function () {

            // Users
            // Route::resource('users', UserController::class);

            // Accounts
            Route::get('accounts/tree/data', [AccountController::class, 'treeData'])->name('accounts.tree.data');
            Route::get('/accounts/{account}/delete-info', [AccountController::class, 'getAccountDeleteInfo'])->name('accounts.delete-info');
            Route::get('accounts/statement', [AccountStatementController::class, 'show'])->name('accounts.statement');
            Route::post('accounts/statement/get', [AccountStatementController::class, 'getStatement'])->name('accounts.statement.get');
            Route::resource('accounts', AccountController::class);

            // Cost Centers
            Route::get('cost-centers/tree/data', [CostCenterController::class, 'treeData'])->name('cost-centers.tree.data');
            Route::resource('cost-centers', CostCenterController::class);

            // Journal Entries
            Route::get('journal-entries/search', [JournalEntryController::class, 'search'])->name('journal-entries.search');
            Route::get('api/accounts/search', [JournalEntryController::class, 'searchAccounts'])->name('api.accounts.search');
            Route::get('api/cost-centers/search', [JournalEntryController::class, 'searchCostCenters'])->name('api.cost-centers.search');
            Route::get('journal-entries/export/excel', [JournalEntryController::class, 'exportExcel'])->name('journal-entries.export.excel');
            Route::get('journal-entries/export/pdf', [JournalEntryController::class, 'exportPdf'])->name('journal-entries.export.pdf');
            Route::get('journal-entries/{id}/duplicate', [JournalEntryController::class, 'duplicate'])->name('journal-entries.duplicate');
            Route::get('journal-entries/{id}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');
            Route::post('journal-entries/{id}/submit', [JournalEntryController::class, 'submit'])->name('journal-entries.submit')->middleware('can:submit journal entry');
            Route::post('journal-entries/{id}/approve', [JournalEntryController::class, 'approve'])->name('journal-entries.approve')->middleware('can:approve journal entry');
            Route::post('journal-entries/{id}/reject', [JournalEntryController::class, 'reject'])->name('journal-entries.reject')->middleware('can:reject journal entry');

            Route::get('journal-entries/attachments/{fileId}/download', [JournalEntryController::class, 'downloadAttachmentFile'])->name('journal-entries.attachments.download');
            Route::delete('journal-entries/attachments/{attachmentId}', [JournalEntryController::class, 'deleteAttachment'])->name('journal-entries.attachments.delete');
            Route::delete('journal-entries/attachments/files/{fileId}', [JournalEntryController::class, 'deleteAttachmentFile'])->name('journal-entries.attachments.files.delete');
            Route::resource('journal-entries', JournalEntryController::class);

            // Opening Journal Entry Routes - مجمعة ومنظمة
            Route::prefix('opening-journal-entry')->name('opening-journal-entry.')->group(function () {
                Route::get('/', [OpeningJournalEntryController::class, 'index'])->name('index');
                Route::post('/', [OpeningJournalEntryController::class, 'store'])->name('store');
                Route::get('/report', [OpeningJournalEntryController::class, 'report'])->name('report');
                Route::get('/export', [OpeningJournalEntryController::class, 'export'])->name('export');
                Route::delete('/clear', [OpeningJournalEntryController::class, 'clear'])->name('clear');
            });

            // Cash Vaults & Banks
            Route::resource('cash-vaults', CashVaultController::class);
            Route::resource('banks', BankController::class);

            // Settings
            Route::get('/accounting-settings', [AccountingSettingsController::class, 'index'])->name('accounting-settings.index');
            Route::post('/accounting-settings', [AccountingSettingsController::class, 'store'])->name('accounting-settings.store');
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

            // Currencies
            Route::prefix('currencies')->name('currencies.')->group(function () {
                Route::get('/', [CurrencyController::class, 'index'])->name('index');
                Route::get('/create', [CurrencyController::class, 'create'])->name('create');
                Route::post('/', [CurrencyController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [CurrencyController::class, 'edit'])->name('edit');
                Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
                Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');
                Route::get('/export/excel', [CurrencyController::class, 'exportExcel'])->name('export.excel');
                Route::get('/export/csv', [CurrencyController::class, 'exportCsv'])->name('export.csv');
                Route::get('/export/pdf', [CurrencyController::class, 'exportPdf'])->name('export.pdf');
            });

            // User Management
            Route::get('users/search', [UserManagementController::class, 'search'])->name('users.search');
            Route::get('users_table/search', [UserManagementController::class, 'UsersTableSearch'])->name('users_table.search');
            Route::resource('users', UserManagementController::class)->except(['show']);
            // Route::resource('inventory', InventoryController::class)->except(['show']);
            Route::get('warehouses/search', [WarehouseController::class, 'search'])->name('warehouses.search');
            Route::get('warehouses/all', [WarehouseController::class, 'all'])->name('warehouses.all');
            Route::post('warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');
            Route::get('warehouses/statistics', [WarehouseController::class, 'statistics'])->name('warehouses.statistics');
            Route::resource('warehouses', WarehouseController::class);
            // Roles
            Route::resource('roles', RoleController::class);
            Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
                Route::get('/ajax/search', [RoleController::class, 'search'])->name('search');
                Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('duplicate');
                Route::get('/users/list', [RoleController::class, 'getUsersWithRoles'])->name('users');
                Route::get('/permissions/matrix', [RoleController::class, 'getPermissionMatrix'])->name('permissions.matrix');
            });

            // Financial Years
            Route::resource('financial-years', FinancialYearController::class)->except(['show', 'create']);
            Route::post('financial-years/{id}/activate', [FinancialYearController::class, 'activate'])->name('financial-years.activate');
            Route::post('financial-years/{id}/close', [FinancialYearController::class, 'close'])->name('financial-years.close');
            Route::get('financial-years/search', [FinancialYearController::class, 'search'])->name('financial-years.search');

            // Dashboard
            Route::get('/dashboard', function () {
                return view('dashboard');
            })->middleware(['verified'])->name('dashboard');

            // Profile
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        require __DIR__ . '/auth.php';
    }
);
