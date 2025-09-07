<?php

namespace App\Livewire;

use App\Models\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class WarehouseTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    // Modal state
    public $showModal = false;
    public $isEditing = false;

    // Form fields
    public $warehouseId = null;
    public $name = '';
    public $notes = '';

    protected function rules()
    {
        $id = $this->warehouseId ?? 'NULL';
        return [
            'name'  => 'required|string|max:255|unique:warehouses,name,' . $id . ',id',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $w = Warehouse::findOrFail($id);
        $this->warehouseId = $w->id;
        $this->name  = $w->name;
        $this->notes = $w->notes;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing && $this->warehouseId) {
            $w = Warehouse::findOrFail($this->warehouseId);
            $w->update([
                'name'  => $this->name,
                'notes' => $this->notes,
            ]);
            session()->flash('message', __('Warehouse updated successfully'));
        } else {
            Warehouse::create([
                'name'  => $this->name,
                'notes' => $this->notes,
            ]);
            session()->flash('message', __('Warehouse created successfully'));
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['warehouseId', 'name', 'notes']);
        $this->resetValidation();
    }

    public function delete($warehouseId)
    {
        if ($w = Warehouse::find($warehouseId)) {
            $w->delete();
            session()->flash('message', __('Warehouse deleted successfully'));
        }
    }

    public function confirmDelete($warehouseId)
    {
        $this->dispatch('show-delete-confirmation', $warehouseId);
    }

    public function render()
    {
        $query = Warehouse::query();

        if (trim($this->search) !== '') {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('notes', 'like', $s);
            });
        }

        $warehouses = $query->orderByDesc('id')->paginate($this->perPage);

        return view('livewire.warehouse-table', compact('warehouses'));
    }
}
