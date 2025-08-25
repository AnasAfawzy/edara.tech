<?php

namespace App\Http\Controllers;

use App\Services\CostCenterService;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{


    protected $costCenterService;

    public function __construct(CostCenterService $costCenterService)
    {
        $this->costCenterService = $costCenterService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $costCenters = $this->costCenterService->getCostCenterTree();
        dd($costCenters->map->only(['id', 'name', 'code', 'position', 'level']));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
