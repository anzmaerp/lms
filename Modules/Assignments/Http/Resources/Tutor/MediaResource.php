<?php

namespace Modules\Assignments\Http\Resources\Tutor;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MediaResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->whenHas('id'),
            'name'                  => $this->whenHas('name'),
            'type'                  => $this->whenHas('type'),  
            'url'                   => $this?->path ? url(Storage::url($this->path)) : url(Storage::url('placeholder.png')),
            'size'                  => humanFilesize(Storage::disk(getStorageDisk())->size($this->path))
        ];
    }
}