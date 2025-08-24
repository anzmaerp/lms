<?php


namespace Modules\Upcertify\Livewire;


use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Modules\Upcertify\Models\Template;
use Modules\Upcertify\Models\Certificate;

class CertificateList extends Component
{
    use WithPagination;

    public $tab;
    public $id;
    public $certificate;
    public $categories;
    public $tabs;
    public $title;
    public $isLoading = true;

    public $selectedTutors = [];

    #[Layout('upcertify::layouts.app')]

    private function resolveUserId()
    {
        if ($this->isAdmin()) {
            return !empty($this->selectedTutors) ? $this->selectedTutors : [];
        }
        return [Auth::id()];
    }
    private function isAdmin()
    {
        return method_exists(Auth::user(), 'hasRole')
            ? Auth::user()->hasRole('admin')
            : (property_exists(Auth::user(), 'is_admin') && Auth::user()->is_admin);
    }


    public function render()
    {
        if ($this->isAdmin()) {
            $templates = Template::orderBy('id', 'desc')->paginate(15);
        } else {
$userId = Auth::id(); // integer
$templates = Template::whereJsonContains('user_id', $userId)
    ->orderBy('id', 'desc')
    ->paginate(15);
        }


        $tutors = User::whereHas('roles', function ($q) {
            $q->where('name', 'tutor');
        })->get();

        return view('upcertify::livewire.certificate-list.certificate-list', compact('templates', 'tutors'));
    }

    public function mount() {}

    public function createNow()
    {

        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        $response = isDemoSite();
        if ($response) {
            $this->dispatch(
                'showToast',
                type: 'error',
                message: __('general.demosite_res_txt')
            );
            $this->dispatch('closeModal');
            return;
        }
        $createdBy = Auth::id();
        $certificate = Template::updateOrCreate(
            ['title' => $this->title],
            [
'user_id' => $this->isAdmin()
    ? array_map('intval', $this->selectedTutors) // ensure numeric IDs
    : [(int) Auth::id()],       

                'created_by' => $createdBy,
                'status'     => 'draft',
            ]
        );

        $this->dispatch('showToast', type: 'success', message: __('upcertify::upcertify.certificate_created'));
        sleep(1);
        $this->dispatch('closeModal');
        return redirect()->route('upcertify.update', [
            'id' => $certificate->id,
            'tab' => 'media'
        ]);
    }

    public function loadData()
    {
        $this->isLoading = false;
    }

    public function deleteTemplate($id)
    {

        $response = isDemoSite();
        if ($response) {
            $this->dispatch(
                'showToast',
                type: 'error',
                message: __('general.demosite_res_txt')
            );
            $this->dispatch('closeModal');
            return;
        }
        $uc_certificate = Certificate::where('template_id', $id)->first();
        $certificate = Template::find($id);
        if ($certificate && empty($uc_certificate)) {
            $certificate->delete();
            $this->dispatch('showToast', type: 'success', message: 'Template deleted successfully');
        } else {
            $this->dispatch('showToast', type: 'error', message: 'Template cannot be deleted');
        }
    }
}
