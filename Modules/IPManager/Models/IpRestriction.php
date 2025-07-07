<?php

namespace Modules\IPManager\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\IPManager\Casts\RestrictionTypeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Country;


class IpRestriction  extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    const TYPE_SPECIFIC_IP       = 0;
    const TYPE_IP_RANGE          = 1;
    const TYPE_COUNTRY           = 2;

    const SPECIFIC_IP            = 'specific_ip';
    const IP_RANGE               = 'ip_range';
    const COUNTRY                = 'country';


    protected $casts = [
        'type'                  => RestrictionTypeCast::class, 
    ];

    public function getTable(): string
    {
        return config('ipmanager.db_prefix') . 'ip_restrictions';
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    
}
