<?php

namespace Modules\KuponDeal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Modules\KuponDeal\Skeleton\SkeletonClass
 */
class KuponDeal extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'kupondeal';
    }
}
