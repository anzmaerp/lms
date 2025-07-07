<?php

namespace Modules\Assignments\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AssignmentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id'                                => $this->whenHas('id'),
            'instructor_id'                     => $this->whenHas('instructor_id'),
            'related_type'                      => match ($this->whenHas('related_type')) {
                                                        'App\Models\UserSubjectGroupSubject'    => __('assignments::assignments.subject'),
                                                        'Modules\Courses\Models\Course'         => __('assignments::assignments.course'),
                                                    },
            'related_id'                        => $this->whenHas('related_id'),
            'title'                             => $this->whenHas('title'),
            'description'                       => html_entity_decode($this->whenHas('description')),
            'attachments'                       => $this->thumbnail ? url(Storage::url($this->thumbnail)) : url(Storage::url('placeholder.png')),
            'submissions_assignments_count'     => $this->whenHas('submissions_assignments_count'),
        ];
    }
}