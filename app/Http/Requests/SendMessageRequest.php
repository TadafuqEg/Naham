<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'sender_id' => ['required',Rule::in(User::pluck('id'))],
            'receiver_id' => ['required',Rule::in(User::pluck('id'))],
            'message' => 'nullable',
            'location_link'=>'nullable'
            
        ];
    }
}
