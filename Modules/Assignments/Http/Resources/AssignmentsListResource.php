<?php

namespace Modules\Assignments\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AssignmentsListResource extends JsonResource
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
            'ended_at'                          => now()->addDays($this->whenHas('due_days'))->setTimeFromTimeString($this->whenHas('due_time')),
            // 'due_days'                          => $this->whenHas('due_days'),
            // 'due_time'                          => $this->whenHas('due_time'),
            'total_marks'                       => $this->whenHas('total_marks'),
            'passing_percentage'                => $this->passing_percentage,
            // 'type'                              => $this->whenHas('type'),
            'status'                            => $this->whenHas('status'),
            'submissions_assignments_count'     => $this->whenHas('submissions_assignments_count'),
            'image'                             => $this->thumbnail ? url(Storage::url($this->thumbnail)) : url(Storage::url('placeholder.png')),
        ];
    }
}