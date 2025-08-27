<?php

namespace App\Providers;

use App\Repositories\BankRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\AccountRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\CurrencyRepository;
use App\Repositories\CashVaultRepository;
use App\Repositories\CostCenterRepository;
use App\Repositories\JournalEntryRepository;
use App\Repositories\FinancialYearRepository;
use App\Repositories\Interfaces\BankRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\CurrencyRepositoryInterface;
use App\Repositories\Interfaces\CashVaultRepositoryInterface;
use App\Repositories\Interfaces\CostCenterRepositoryInterface;
use App\Repositories\Interfaces\JournalEntryRepositoryInterface;
use App\Repositories\Interfaces\FinancialYearRepositoryInterface;



class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
        $this->app->bind(CostCenterRepositoryInterface::class, CostCenterRepository::class);
        $this->app->bind(JournalEntryRepositoryInterface::class, JournalEntryRepository::class);
        $this->app->bind(CashVaultRepositoryInterface::class, CashVaultRepository::class);
        $this->app->bind(BankRepositoryInterface::class, BankRepository::class);
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(FinancialYearRepositoryInterface::class, FinancialYearRepository::class);
        // وهكذا لأي Repository جديد
    }

    public function boot(): void
    {
        //
    }
}
