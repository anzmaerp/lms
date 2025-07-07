<?php

namespace Modules\Assignments\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use Modules\Assignments\Casts\AssignmentTypeCast;

class AssignmentRequest extends BaseFormRequest
{

    public $assignment_for;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'                     => 'required|string|max:255',
            'description'               => 'required|string|max:65535',
            'assignment_type'           => 'required|string|in:'. implode(',', array_keys(AssignmentTypeCast::$typeMap)),
            'assignment_for'            => 'required|string|max:255',
            'related_id'                => 'required',
            'existingAttachments'       => 'nullable',
            'total_marks'               => 'required|numeric|min:1|max:100',
            'passing_grade'             => 'required|numeric|min:1|max:100',
            'dueDays'                   => 'required|numeric|min:1|max:1000',
            'dueTime'                   => 'required|string',
            'charactersCount'           => 'required_if:assignment_type,text,both|nullable|min:1|max:100000',
            'max_file_upload'           => 'required_if:assignment_type,document,both|nullable|numeric|min:1|max:10',
            'max_upload_file_size'      => 'required_if:assignment_type,document,both|nullable|numeric|min:1|max:10',
        ];
        if ($this->assignment_for == 'App\Models\UserSubjectGroupSubject') {
            $rules['user_subject_slots'] = 'required';
        } else {
            $rules['user_subject_slots'] = 'nullable';
        }
        return $rules;
    }

    
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'charactersCount.required_if'           => __('validation.required', ['attribute' => 'characters limit']),
            'max_file_upload.required_if'           => __('validation.required', ['attribute' => 'max files to upload']),
            'max_upload_file_size.required_if'      => __('validation.required', ['attribute' => 'max upload file size']),
        ];
    }


    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'charactersCount'    => 'characters limit',
        ];
    }
}
