<?php

namespace Modules\Assignments\Http\Resources\Tutor;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AssignmentResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [

                'id'                    => $this->whenHas('id'),
                'instructor_id'         => $this->whenHas('instructor_id'),
                'related_type'          => match ($this->related_type) {
                                            'App\Models\UserSubjectGroupSubject'    => __('assignments::assignments.subject'),
                                            'Modules\Courses\Models\Course'         => __('assignments::assignments.course'),
                                        },
                'title'                 => $this->whenHas('title'),
                'description'           => $this->whenHas('description'),
                'total_marks'           => $this->whenHas('total_marks'),
                'passing_percentage'    => $this->whenHas('passing_percentage'),
                'status'                => $this->whenHas('status'),
                'type'                  => $this->whenHas('type'),
            ];
    }   
}