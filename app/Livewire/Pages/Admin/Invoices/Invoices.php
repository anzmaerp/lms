<?php

namespace App\Livewire\Pages\Admin\Invoices;

use App\Jobs\SendDbNotificationJob;
use App\Livewire\Pages\Admin\Invoices\InvoicesExport;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

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
    if (empty($id) || empty($st)) {
        return;
    }

    $st = strtoupper(trim($st)); 

    DB::transaction(function () use ($id, $st) {
        $isPaid = $st === 'Y' ? 'Y' : 'N';

        DB::table('orders')
            ->where('id', $id)
            ->update(['payment_acceptnce' => $isPaid]);

        DB::table('courses_enrollments')
            ->where('order_id', $id)
            ->update(['is_paid' => $isPaid]);

        $enrollment = DB::table('courses_enrollments')
            ->where('order_id', $id)
            ->first();

        $order = DB::table('orders')->where('id', $id)->first();

        if ($enrollment) {
            $student = User::find($enrollment->student_id);
            $course = DB::table('courses_courses')->where('id', $enrollment->course_id)->first();

            if ($student) {
                $notificationData = [
                    'userName' => $student->email ?? 'User',
                    'orderId' => $order->id ?? '',
                    'courseTitle' => $course->title ?? '',
                ];

                if ($isPaid === 'Y') {
                    dispatch(new SendDbNotificationJob('paymentAccepted', $student, $notificationData));
                } else {
                    dispatch(new SendDbNotificationJob('paymentRejected', $student, $notificationData));
                }
            }
        }
    });

    $this->dispatch('showAlertMessage',
        type: 'success',
        title: __('general.success_title'),
        message: __('settings.updated_record_success'));
}

}
