<?php

namespace Application\Admin\Products\Requests;

use Support\BaseRequest;

class UpdateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('product.update');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:10'],
            'article' => ['required', 'string', 'alpha_dash:ascii', 'unique:products'],
        ];
    }

    /**
     * Get the validation attributes
     */
    public function attributes(): array
    {
        return [
            'name' => __('Название'),
            'article' => __('Артикул'),
        ];
    }

    public function messages(): array
    {
        return array_merge(
            $this->nameMessages(),
            $this->articleMessages(),
        );
    }

    private function nameMessages(): array
    {
        return [
            'name.required' => 'Поле :attribute обязательно к заполнению.',
            'name.string' => 'Поле :attribute не должно быть числом.',
            'name.min:10' => 'Поле :attribute должно быть длиннее 10 символов.',
        ];
    }

    private function articleMessages(): array
    {
        return [
            'name.required' => 'Поле :attribute обязательно к заполнению.',
            'name.string' => 'Поле :attribute не должно быть числом.',
            'name.min:10' => 'Поле :attribute должно быть длиннее 10 символов.',
        ];
    }
}
