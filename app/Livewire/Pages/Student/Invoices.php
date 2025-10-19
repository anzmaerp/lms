<?php

namespace App\Livewire\Pages\Student;

use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Invoices extends Component
{
    use WithPagination;

    public $sortby = 'desc';
    public $isLoading = true;
    public $search = '';
    public $status = '';
    public $user;
    public $subTotal;
    public $discountAmount;
    public $invoice;
    public $company_name;
    public $company_logo;
    public $company_email;
    public $company_address;

    private ?OrderService $orderService = null;
    public function boot()
    {
        $this->user = Auth::user();
        $this->orderService = new OrderService();
    }

    public function mount()
    {
        $this->company_name = setting('_general.company_name');
        $this->company_logo = setting('_general.invoice_logo');
        $this->company_email = setting('_general.company_email');
        $this->company_address = setting('_general.company_address');
        $this->company_logo = !empty($this->company_logo[0]['path']) ? url(Storage::url($this->company_logo[0]['path'])) : asset('demo-content/logo-default.svg');
        $this->isLoading = false;
        $this->dispatch('initSelect2', target: '.am-select2');
    }

    public function loadData()
    {
        $this->isLoading = false;
        $this->dispatch('loadPageJs');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $orders = $this->orderService->getOrders($this->status, null, 'Desc', null, null, $this->user->id);
        return view('livewire.pages.student.invoices', compact('orders'));
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