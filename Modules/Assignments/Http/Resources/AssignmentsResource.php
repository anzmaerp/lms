<?php

namespace Modules\Assignments\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AssignmentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->whenHas('id'),
            'instructor_id'         => $this->assignment?->instructor_id,
            'related_type'          => match ($this->assignment?->related_type) {
                                            'App\Models\UserSubjectGroupSubject'    => __('assignments::assignments.subject'),
                                            'Modules\Courses\Models\Course'         => __('assignments::assignments.course'),
                                        },
            'related_id'            => $this->assignment?->related_id,
            'title'                 => $this->assignment?->title,
            'total_marks'           => $this->assignment?->total_marks,
            'passing_percentage'    => $this->assignment?->passing_percentage,
            'status'                => $this->assignment?->status,
            'ended_at'              => $this->whenHas('ended_at'),
            'result'                => $this->whenHas('result'),
            'image'                 => $this->assignment?->thumbnail ? url(Storage::url($this->assignment?->thumbnail)) : url(Storage::url('placeholder.png')),
        ];
    }
}