<?php

namespace App\Livewire\Pages\Admin\Blogs;

use App\Models\Blog;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\BlogCategory;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Livewire\Pages\Admin\Blogs\BlogsExport;

class Blogs extends Component
{
    use WithPagination;

    public $title, $description, $category_id, $selectedCategories = [], $tags = [], $media, $status;
    public $editMode = false;
    public $edit_id;
    public $isLoading = true;
    public $blog;
    public $search = '';
    public $sortby = 'desc';
    public $perPage = 10;
    public $selectedBlogs = [];
    public $selectAll = false;
    public $selectedCategory;
    public $content;
    public $editType = 'update';
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->perPage = setting('_general.per_page_record') ?? 10;
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $blogs = $this->blogs;
        $categories = BlogCategory::all();

        return view('livewire.pages.admin.blogs', compact('blogs', 'categories'));
    }

    #[Computed()]
    public function blogs()
    {
        $blogs = Blog::with(['categories']);

        if (!empty($this->search)) {
            $blogs = $blogs->where(function ($query) {
                $query->whereAny(['title', 'description'], 'LIKE', "%$this->search%");
            });
        }

        return $blogs->orderBy('id', $this->sortby)->paginate($this->perPage);
    }
    public function printUsersExcel()
    {
        $fileName = 'blogs_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new BlogsExport($this->search), $fileName);
    }

    public function updatedPerPage($value)
    {
        $this->resetPage();
    }

    public function updatedPage($page)
    {
        if ($this->selectAll) {
            $this->selectedBlogs = $this->blogs->pluck('id')->toArray();
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedBlogs = $this->blogs->pluck('id')->toArray();
        } else {
            $this->selectedBlogs = [];
        }
    }

    public function updatedSelectedBlogs()
    {
        $this->selectAll = false;
    }

    #[On('delete-blog')]
    public function delete($params = [])
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch(
                'showAlertMessage',
                type: 'error',
                title: __('general.demosite_res_title'),
                message: __('general.demosite_res_txt')
            );
            return;
        }

        if (!empty($params['id'])) {
            Blog::findOrFail($params['id'])->delete();
            $message = __('blogs.blog_deleted_successfully');
        } elseif (!empty($this->selectedBlogs)) {
            Blog::whereIn('id', $this->selectedBlogs)->delete();
            $message = __('blogs.blogs_deleted_successfully');
        }

        $this->selectedBlogs = [];

        $this->dispatch('showAlertMessage', type: 'success', message: $message);
    }
}
