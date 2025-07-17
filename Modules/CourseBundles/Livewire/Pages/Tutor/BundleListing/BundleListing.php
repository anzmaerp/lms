<?php

namespace Modules\CourseBundles\Livewire\Pages\Tutor\BundleListing;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\CourseBundles\Services\BundleService;
use Modules\CourseBundles\Models\Bundle;
use Modules\CourseBundles\Casts\BundleStatusCast;

class BundleListing extends Component
{
    use WithPagination;

    public $isLoading = true;
    public $filters = [];
    public $bundleId = 0;
    public $statuses = [];
    public $perPage = 10;
    public $parPageList = [10, 20, 30, 40, 50];

    public function mount()
    {
        $this->dispatch('initSelect2', target: '.am-select2');

        $this->statuses =  BundleStatusCast::$statusMap;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $bundles = [];
        $bundles = (new BundleService())->getBundles(
            instructorId: $this->isAdmin() ? null : auth()->id(),
            with: [
                'thumbnail:mediable_id,mediable_type,type,path',
                'createdBy.profile:id,user_id,first_name,last_name'
            ],
            withCount: ['courses'],
            withSum: ['courses' => 'content_length'],
            filters: $this->filters,
            perPage: $this->perPage
        );
        return view('coursebundles::livewire.tutor.bundle-listing.bundle-listing', compact('bundles'));
    }
    protected function isAdmin(): bool
    {
        return auth()->check() && auth()->user()?->role === 'admin';
    }



    public function resetFilters()
    {
        $this->filters = [];
    }

    public function updatedPerPage()
    {

        $this->resetPage();
    }
    public function loadData()
    {
        $this->isLoading = false;
    }

    public function filterStatus($status)
    {
        $this->filters['status'] = $status;
    }

    public function openPublishModal($id)
    {
        $this->bundleId = $id;
        $this->dispatch('toggleModel', id: 'course_completed_popup', action: 'show');
    }

    #[On('archive-bundle')]
    public function archiveBundle($params = [])
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        return $this->updateBundleStatus($params['id'], 'archived');
    }

    public function publishBundle()
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        $this->updateBundleStatus($this->bundleId, 'published');
        $this->dispatch('toggleModel', id: 'course_completed_popup', action: 'hide');
    }



    #[On('delete-bundle')]
    public function deleteBundle($params = [])
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $bundle = (new BundleService())->getBundle($params['id']);

        if (!$bundle) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('coursebundles::bundles.no_bundles_found'), message: __('coursebundles::bundles.no_bundles_found'));
            return;
        }

        if (!$this->isAdmin() && $bundle->created_by != auth()->id()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('coursebundles::bundles.permission_denied'), message: __('coursebundles::bundles.permission_denied'));
            return;
        }

        $deleted = (new BundleService())->deleteBundle($params['id']);

        if ($deleted) {
            $this->resetFilters();
            $this->dispatch('showAlertMessage', type: 'success', title: __('coursebundles::bundles.bundle_deleted_successfully'), message: __('coursebundles::bundles.bundle_deleted_successfully'));
        } else {
            $this->dispatch('showAlertMessage', type: 'error', title: __('coursebundles::bundles.bundle_delete_error'), message: __('coursebundles::bundles.bundle_delete_failed'));
        }
    }

    public function updateBundleStatus($bundleId, $status)
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $bundle = (new BundleService())->getBundle($bundleId);

        if (!$bundle) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('coursebundles::bundles.no_bundles_found'), message: __('coursebundles::bundles.no_bundles_found'));
            return;
        }

        if (!$this->isAdmin() && $bundle->created_by != auth()->id()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('coursebundles::bundles.permission_denied'), message: __('coursebundles::bundles.permission_denied'));
            return;
        }

        $updated = (new BundleService())->updateBundleStatus($bundle, $status);

        if ($updated) {
            $this->dispatch('showAlertMessage', type: 'success', title: __('coursebundles::bundles.bundle_status_success', ['status' => $status]), message: __('coursebundles::bundles.bundle_status_success', ['status' => $status]));
        } else {
            $this->dispatch('showAlertMessage', type: 'error', title: __('courses::courses.error_title'), message: __('courses::courses.course_delete_failed'));
        }
    }
}
