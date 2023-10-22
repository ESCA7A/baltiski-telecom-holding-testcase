<?php

namespace Domain\Products\Requests;

use Support\BaseRequest;

class ReadRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() ? $this->user()->can('product.read') : false;
    }
}