<?php

namespace App\Livewire\Pages\Admin\Invoices;

use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Pages\Admin\Invoices\InvoicesExport;

class Invoices extends Component
{
    use WithPagination;

    public $search = '';
    public $sortby = 'desc';
    public $status = '';
    public $subTotal;
    public $discountAmount;
    public $user;
    public $company_name;

    public $company_logo;
    public $company_email;
    public $company_address;
    private ?OrderService $orderService = null;
    public $invoice;
    public function boot()
    {
        $this->user = Auth::user();
        $this->orderService = new OrderService();
    }

    public function mount()
    {
        $this->dispatch('initSelect2', target: '.am-select2');
        $this->company_name = setting('_general.company_name');
        $this->company_logo = setting('_general.invoice_logo');
        $this->company_email = setting('_general.company_email');
        $this->company_address = setting('_general.company_address');
        $this->company_logo = !empty($this->company_logo[0]['path']) ? url(Storage::url($this->company_logo[0]['path'])) : asset('demo-content/logo-default.svg');
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $orders = $this->orderService->getOrdersList($this->status, $this->search, $this->sortby);
        return view('livewire.pages.admin.invoices.invoices', compact('orders'));
    }
    public function printUsersExcel()
    {
        return (new InvoicesExport(
            $this->status,
            $this->search,
            $this->sortby
        ))->download('Invoices.xlsx');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['status', 'search', 'sortby'])) {
            $this->resetPage();
        }
    }

    public function viewInvoice($id)
    {
        $this->invoice = $this->orderService->getOrdeWrWithItem($id, ['items', 'userProfile', 'countryDetails']);
        $this->dispatch('openInvoiceModal', id: 'invoicePreviewModal', action: 'show');
    }

    public function paymentStatus($id, $st)
    {
        if (!empty($id) && !empty($st)) {
            DB::table('orders')->where('id', $id)->update([
                'payment_acceptnce' => $st
            ]);
            DB::table('courses_enrollments')->where('order_id', $id)->update([
                'is_paid' => $st
            ]);
            $this->dispatch('showAlertMessage', type: 'success', title: __('general.success_title'), message: __('settings.updated_record_success'));
        }
    }
}
