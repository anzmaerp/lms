<?php
namespace App\Livewire\Pages\Admin\Taxonomy;

use App\Models\Language;
use App\Models\Scopes\ActiveScope;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExcelExport implements FromQuery, WithHeadings
{
    use Exportable;

    private $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function headings(): array
    {
        return [
            __('#'),
            __('Name'),
            __('general.description'),
            __('general.status'),
        ];
    }

    public function query()
    {
        $languages = Language::withoutGlobalScope(ActiveScope::class)
            ->select('id', 'name', 'description', 'status');

        if (!empty($this->search)) {
            $languages->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $languages;
    }
}
