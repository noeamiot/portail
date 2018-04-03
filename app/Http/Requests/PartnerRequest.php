<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; //TODO voir qui a le droit de gérer les partenaires.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string|'.validation_between('title').($this->isMethod('put')?'':'|required'),
	        'description' => 'string|'.validation_between('description').($this->isMethod('put')?'':'|required'),
	        'image'=> 'string|'.validation_between('url').($this->isMethod('put')?'':'|required'), //TODO mettre un champ image
        ];
    }
}