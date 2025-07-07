<?php

namespace Modules\Assignments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;


class Media extends Model
{
    use HasFactory;

    protected $table;

    public function __construct()
    {
        $this->table = (config('assignments.db_prefix') ?? 'assignments_') . 'media';
    }

    protected $fillable = [
        'mediable_id',
        'mediable_type',
        'name',
        'type',
        'path',
    ];
}
