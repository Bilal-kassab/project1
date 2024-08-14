<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class BookStaticTripRequest extends FormRequest
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
           'number_of_friend'=>'required|numeric|min:1',
           'trip_id'=>'required|numeric|exists:bookings,id',
           'rooms_needed'=>'required|numeric|min:1',
           'room_price'=>'decimal:0,1000000|numeric',
           'days'=>'required|numeric',
           'total_price'=>'required|numeric|min:1',
           'price_after_discount'=>'nullable|min:1',
           'discount'=>'boolean',
        //    'payment_type'=>'required|in:stripe,wallet',
        ];
    }
}
