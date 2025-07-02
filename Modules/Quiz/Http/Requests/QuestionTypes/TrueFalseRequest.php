<?php

namespace Modules\Quiz\Http\Requests\QuestionTypes;

use Illuminate\Foundation\Http\FormRequest;

class TrueFalseRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'question_title'    => 'required|string|max:255', 
            'correct_answer'    => 'required|string|in:true,false',
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
            'question_text' => 'question description',
        ];
    }
}
