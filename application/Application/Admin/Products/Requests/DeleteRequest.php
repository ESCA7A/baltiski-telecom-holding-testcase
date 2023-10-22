<?php

namespace Application\Admin\Products\Requests;

use Support\BaseRequest;

class DeleteRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('product.delete');
    }

    public function rules()
    {
        return [
            'id' => ['exists:products,id']
        ];
    }

    public function attributes(): array
    {
        return [
            'id' => __("ID продукта"),
        ];
    }

    public function messages(): array
    {
        return [
            'id' => "Поле ':attribute' должно существовать в базе данных",
        ];
    }
}