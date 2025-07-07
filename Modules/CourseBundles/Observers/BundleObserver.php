<?php

namespace Modules\CourseBundles\Observers;

use Modules\CourseBundles\Models\Bundle;
use Illuminate\Support\Str;

class BundleObserver
{
    /**
     * Handle the Bundle "creating" event.
     *
     * @param  \Modules\CourseBundles\Models\Bundle  $bundle
     * @return void
     */
    public function created(Bundle $bundle)
    {
        $slug            = Str::slug($bundle->title);
        $bundle->slug    = $this->uniqueSlug($slug, $bundle);

        $bundle->save();
    }

    /**
     * Create unique slug automatically
     * @return string unique slug
     */
    protected function uniqueSlug($slug, $bundle)
    {
        if (Bundle::whereSlug($slug)->whereNot('id', $bundle->id)->exists()) {
            $slug = $slug . "-" . $bundle->id;
        }
        return $slug;
    }

    /**
     * Listen to the Bundle updated event.
     *
     * @param  \Modules\CourseBundles\Models\Bundle $bundle
     * @return void
     */
    public function updated(Bundle $bundle): void
    {
        //
    }
}
