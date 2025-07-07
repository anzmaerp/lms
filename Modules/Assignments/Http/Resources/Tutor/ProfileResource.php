<?php

namespace Modules\Assignments\Http\Resources\Tutor;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ProfileResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->whenHas('id'),
            'full_name'          => $this?->full_name,
            'image'              => $this?->image ? url(Storage::url($this->image)) : url(Storage::url('placeholder.png')),
        ];
    }
}