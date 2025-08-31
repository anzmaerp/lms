<?php

namespace App\Livewire\Pages\Admin\OfflinePayments;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\OfflinePayment;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Livewire\Pages\Admin\OfflinePayments\OfflinePaymentsExport;


class OfflinePayments extends Component
{
    use WithPagination;

    public $per_page = '';
    public $search = '';
    public $sortby = 'desc';
    public $selectedPayments = [];
    public $selectAll = false;
    public $isLoading = true;
    public $editableId = null;
    public $per_page_opt = [10, 20, 30, 50, 100];

    // Form fields
    public $name = '';
    public $description = '';
    public $instructions = '';
    public $status = 'active';

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteConfirmRecord' => 'deletePayment'];

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $offline_payments = OfflinePayment::select('id', 'name', 'description', 'instructions', 'status', 'created_at');

        if (!empty($this->search)) {
            $offline_payments = $offline_payments->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $offline_payments = $offline_payments->orderBy('id', $this->sortby)->paginate($this->per_page_opt[$this->per_page] ?? 10);

        return view('livewire.pages.admin.offline-payments.offline-payments', [
            'offline_payments' => $offline_payments,
        ]);
    }

    public function loadData()
    {
        $this->isLoading = false;
    }

    public function mount()
    {
        $this->per_page = setting('_general.per_page_record') ? array_search(setting('_general.per_page_record'), $this->per_page_opt) : 0;
    }
    public function printUsersExcel()
    {
        $fileName = 'offline_payments_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new OfflinePaymentsExport($this->search), $fileName);
    }


    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPayments = OfflinePayment::pluck('id')->map(function ($id) {
                return (string) $id;
            })->toArray();
        } else {
            $this->selectedPayments = [];
        }
    }

    public function updated($name)
    {
        if ($name == 'search' || $name == 'per_page' || $name == 'sortby') {
            $this->resetPage();
        }
    }

    public function edit($id)
    {
        $this->resetErrorBag();
        $this->editableId = $id;
        $payment = OfflinePayment::find($id);
        if ($payment) {
            $this->name = $payment->name;
            $this->description = $payment->description;
            $this->instructions = $payment->instructions;
            $this->status = $payment->status;
        }
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $response = isDemoSite();
        if ($response) {
            $this->dispatchBrowserEvent('showAlertMessage', [
                'type' => 'error',
                'title' => __('general.demosite_res_title'),
                'message' => __('general.demosite_res_txt')
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            OfflinePayment::updateOrCreate(['id' => $this->editableId], [
                'name' => sanitizeTextField($this->name),
                'description' => $this->description,
                'instructions' => $this->instructions,
                'status' => $this->status,
            ]);

            DB::commit();

            $this->editableId = null;
            $this->reset(['name', 'description', 'instructions', 'status']);
            $this->dispatch('showAlertMessage', type: 'success', title: __('general.success_title'), message: __('general.success_message'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.error_title'), message: __('general.error_message'));
        }
    }

    #[On('delete-payment')]
    public function deleteRecord($params = [])
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $ids = [];

        if (!empty($params['id'])) {
            $ids[] = $params['id'];
        } else {
            $ids = $this->selectedPayments;
        }

        if (empty($ids)) {
            return;
        }

        try {
            OfflinePayment::whereIn('id', $ids)->delete();
            $this->selectedPayments = array_diff($this->selectedPayments, $ids);
            $this->dispatch('showAlertMessage', type: 'success', title: __('general.success_title'), message: __('general.delete_record'));
        } catch (\Exception $e) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.error_title'), message: __('general.error_message'));
        }
    }

    public function deleteAllRecord()
    {
        if (!empty($this->selectedPayments)) {
            OfflinePayment::whereIn('id', $this->selectedPayments)->delete();
            $this->selectedPayments = [];
        }
        $this->dispatch('delete-category-confirm');
    }
}
