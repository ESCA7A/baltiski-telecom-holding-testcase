<?php

namespace Support;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): ?JsonResponse
    {
        if (!$validator->errors()->count()) return null;

        throw new HttpResponseException(
            response(__('Отправлены невалидные значения. data: :data', [
                'data' => $validator->errors()->toArray(),
            ]), 422)
        );
    }
}
