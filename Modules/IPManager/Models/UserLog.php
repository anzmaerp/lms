<?php

namespace Modules\IPManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('ipmanager.db_prefix') . 'user_logs';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
