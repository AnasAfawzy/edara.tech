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
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AccountingSettingsController;
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
        Route::get('banks/search', [BankController::class, 'search'])->name('banks.search');
        Route::get('cash-vaults/search', [CashVaultController::class, 'search'])->name('cash-vaults.search');

        Route::resource('users', UserController::class);
        Route::get('accounts/tree/data', [AccountController::class, 'treeData'])->name('accounts.tree.data');
        Route::get('/accounts/{account}/delete-info', [AccountController::class, 'getAccountDeleteInfo'])->name('accounts.delete-info');
        Route::resource('accounts', AccountController::class);

        Route::get('cost-centers/tree/data', [CostCenterController::class, 'treeData'])->name('cost-centers.tree.data');

        Route::resource('cost-centers', CostCenterController::class);
        Route::resource('journal-entries', JournalEntryController::class);
        Route::resource('cash-vaults', CashVaultController::class);
        Route::resource('banks', BankController::class);
        Route::get('/accounting-settings', [AccountingSettingsController::class, 'index'])->name('accounting-settings.index');
        Route::post('/accounting-settings', [AccountingSettingsController::class, 'store'])->name('accounting-settings.store');


        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

        Route::prefix('currencies')->name('currencies.')->group(function () {
            Route::get('/', [CurrencyController::class, 'index'])->name('index');
            Route::get('/create', [CurrencyController::class, 'create'])->name('create');
            Route::post('/', [CurrencyController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [CurrencyController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
            Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');

            // routes للتصدير
            Route::get('/export/excel', [CurrencyController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/csv', [CurrencyController::class, 'exportCsv'])->name('export.csv');
            Route::get('/export/pdf', [CurrencyController::class, 'exportPdf'])->name('export.pdf');
        });
        Route::get('users/search', [UserManagementController::class, 'search'])->name('users.search');
        Route::resource('users', UserManagementController::class)->except(['show']);
        Route::get('roles/search', [RoleController::class, 'search'])->name('roles.search');

        Route::resource('roles', RoleController::class);



        Route::resource('financial-years', FinancialYearController::class);
        Route::post('financial-years/{financialYear}/activate', [FinancialYearController::class, 'activate'])->name('financial-years.activate');
        Route::post('financial-years/{financialYear}/close', [FinancialYearController::class, 'close'])->name('financial-years.close');



        Route::get('/dashboard', function () {
            return view('dashboard');
        })->middleware(['auth', 'verified'])->name('dashboard');

        Route::middleware('auth')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        });

        require __DIR__ . '/auth.php';
    }
);
