<?php

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CashVaultController;
use App\Http\Controllers\CostCenterController;

use App\Http\Controllers\JournalEntryController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
    ],
    function () {
        // Livewire::setUpdateRoute(function ($handle) {
        //     return Route::post('/livewire/update', $handle);
        // });
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
        // Route::resource('currency', CurrencyController::class);
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
