<?php

namespace Modules\Assignments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Modules\Assignments\Casts\AssignmentTypeCast;

class SubmitAssignmentRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules($assignmentType, $max_file_count, $charactersCount)
    {
        $rules = [
        ];
        if ($assignmentType == 'text') {
            $rules['description'] = 'required|string|max:'.$charactersCount;
        } elseif ($assignmentType == 'document') {
            $rules['existingAttachments'] = 'required|array|max:'.$max_file_count;
        } elseif ($assignmentType == 'both') {
            $rules['description']               = 'required|string|max:'.$charactersCount;
            $rules['existingAttachments']       = 'required|array|max:'.$max_file_count;
        }
        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function messages(){
        return [
            'existingAttachments.required'      => 'The attachments is required',
            'existingAttachments.max'           => 'The attachments may not be greater than :max.',
        ];
    }
}
