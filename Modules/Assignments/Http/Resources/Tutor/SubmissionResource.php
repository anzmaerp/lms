<?php

namespace Modules\Assignments\Http\Resources\Tutor;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class SubmissionResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->whenHas('id'),
            'student_id'            => $this->whenHas('student_id'),
            'assignment_id'         => $this->whenHas('assignment_id'),
            'submission_text'       => $this->whenHas('submission_text'),
            'submitted_at'          => $this->whenHas('submitted_at'),
            'result'                => $this->whenHas('result'),
            'marks_awarded'         => $this->whenHas('marks_awarded'),
            'assignment'            => new AssignmentResource($this->whenLoaded('assignment')),
            'student'               => $this->when(
                $this->relationLoaded('student') && optional($this->student)->relationLoaded('profile'),
                fn () => new ProfileResource($this->student->profile)
                
            ),
            'attachments'           => MediaResource::collection($this->whenLoaded('attachments')),
        ];
    }
}