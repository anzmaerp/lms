<?php

namespace Modules\Assignments\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Assignments\Http\Resources\Tutor\MediaResource;
use Illuminate\Http\Request;

class AssignmentResource extends JsonResource
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
            'submission_text'       => $this->whenHas('submission_text'),
            'instructor_id'         => $this->assignment?->instructor_id,
            'related_type'          => match ($this->assignment?->related_type) {
                                            'App\Models\UserSubjectGroupSubject'    => __('assignments::assignments.subject'),
                                            'Modules\Courses\Models\Course'         => __('assignments::assignments.course'),
                                        },
            'related_id'            => $this->assignment?->related_id,
            'title'                 => $this->assignment?->title,
            'description'           => html_entity_decode($this->assignment?->description),
            'total_marks'           => $this->assignment?->total_marks,
            'passing_percentage'    => $this->assignment?->passing_percentage,
            'max_file_count'        => $this->assignment?->max_file_count,
            'ended_at'              => $this->whenHas('ended_at'),
            'type'                  => $this->assignment?->type,
            'result'                => $this->whenHas('result'),
            'assignment_attachments' => $this->when(
                optional($this->assignment)->relationLoaded('attachments'),
                fn () => MediaResource::collection($this->assignment->attachments)
            ),
            'instructor'            => [
                                            'name'  => $this->assignment?->instructor?->profile?->full_name,
                                            'image' => $this->assignment?->instructor?->profile?->image ? url(Storage::url($this->assignment->instructor->profile->image)) : url(Storage::url('placeholder.png')),
                                       ],
            'submission_attachments'           => MediaResource::collection($this->whenLoaded('attachments')),                        
        ];
    }
}