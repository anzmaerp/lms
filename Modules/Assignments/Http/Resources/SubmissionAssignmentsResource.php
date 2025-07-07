<?php

namespace Modules\Assignments\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class SubmissionAssignmentsResource extends JsonResource
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
            'marks_awarded'         => $this->whenHas('marks_awarded'),
            'total_marks'           => $this->assignment?->total_marks,
            'submitted_at'          => $this->whenHas('submitted_at'),
            'result'                => $this->whenHas('result'),
            'student'               => [
                                            'name'  => $this->student?->profile?->full_name,
                                            'email' => $this->student?->email,
                                            'image' => $this->student?->profile?->image ? url(Storage::url($this->student->profile->image)) : url(Storage::url('placeholder.png')),
                                       ],
        ];
    }
}