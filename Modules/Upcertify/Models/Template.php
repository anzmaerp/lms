<?php

namespace Modules\Upcertify\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;   
use App\Models\User;                                    
use Illuminate\Support\Arr;

class Template extends Model
{
    protected $table;

    public function __construct() {
        $this->table = config('upcertify.db_prefix') . 'templates';
        parent::__construct();
    }

    protected $guarded = [];

    public const STATUSES = [
        'draft'   => 0,
        'publish' => 1,
    ];
    
    protected $casts = [
        'body' => 'array',
        'user_id' => 'array',  
    ];

    protected function status(): Attribute {
        return Attribute::make(
            get: fn($value) => Arr::get(array_flip(self::STATUSES), $value, null),
            set: fn($value) => Arr::get(self::STATUSES, $value, null)
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
