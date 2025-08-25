<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\CurrencyRepositoryInterface;

class CurrencyService extends BaseService
{
    protected $currencyRepository;

    public function __construct(CurrencyRepositoryInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function getAllCurrencies()
    {
        return $this->currencyRepository->all();
    }
    public function getAllCurrenciesPaginated($perPage = 10, $search = null)
    {
        if ($search) {
            return $this->currencyRepository->searchAndPaginate($search, $perPage);
        }

        return $this->currencyRepository->paginate($perPage);
    }

    public function getAllCurrenciesForExport($search = null)
    {
        $query = Currency::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function findCurrency(int $id)
    {
        return $this->currencyRepository->find($id);
    }

    public function createCurrency(array $data)
    {
        return $this->currencyRepository->create($data);
    }
}
