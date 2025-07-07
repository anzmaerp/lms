<?php

namespace Modules\Assignments\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Arr;
use Modules\Assignments\Casts\AssignmentTypeCast;
use Modules\Assignments\Services\AssignemntsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class ApiSubmitAssignmentRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        
        $rules['assignment_id'] = 'required|exists:assignments_assignment_submissions,id';
        
        if ($this->assignment_id) {
            $assignmentDetail = app(AssignemntsService::class)->getStudentAssignment(
                submissionId: $this->assignment_id,
                studentId: Auth::id(),
                relations: ['assignment']
            );

            if ($assignmentDetail && $assignmentDetail->assignment) {
                $assignmentType     = $assignmentDetail->assignment->type;
                $max_file_count     = $assignmentDetail->assignment->max_file_count;
                $charactersCount    = $assignmentDetail->assignment->characters_count;

                $attachment_ext     = setting('_general.allowed_file_extensions') ?? 'pdf,doc,docx';
                $attachment_ext     .= "," . (setting('_general.allowed_video_extensions') ?? 'mp4,webm,ogg');    
                $allowedExtensions  = !empty($attachment_ext) ? explode(',', $attachment_ext) : [];

                $maxFileSize        = setting('max_file_size', 10);
                $maxFileSize        = is_array($maxFileSize) ? 10 : (float)$maxFileSize;
                $maxFileSizeKB      = $maxFileSize * 1024;

                if ($assignmentType === 'text') {
                    $rules['submission_text'] = 'required|string|max:' . $charactersCount;
                } elseif ($assignmentType === 'document') {
                    $rules['attachments'] = 'required|array|max:' . $max_file_count;
                    $rules['attachments.*'] = 'file|mimes:'.join(',', $allowedExtensions).'|max:' . $maxFileSizeKB;
                } elseif ($assignmentType === 'both') {
                    $rules['submission_text'] = 'required|string|max:' . $charactersCount;
                    $rules['attachments'] = 'required|array|max:' . $max_file_count;
                    $rules['attachments.*'] = 'file|mimes:'.join(',', $allowedExtensions).'|max:' . $maxFileSizeKB;
                }
            }
        }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'attachments.required'      => 'The attachments is required',
            'attachments.max'           => 'The attachments may not be greater than :max.',
            'attachments.*.mimes'       => 'The attachments must be a file of type: :values.',
            'submission_text.required'  => 'The submission text is required',
            'submission_text.max'       => 'The submission text may not be greater than :max.',
            'assignment_id.required'    => 'The assignment id is required',
            'assignment_id.exists'      => 'The assignment id is invalid or does not exist.',
        ];
    }
}
