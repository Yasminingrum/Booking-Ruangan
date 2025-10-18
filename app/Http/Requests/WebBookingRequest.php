<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WebBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'room_id' => [
                'required',
                'integer',
                Rule::exists('rooms', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'booking_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'purpose' => [
                'required',
                'string',
                'min:10',
                'max:500',
            ],
            'participants' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Ruangan wajib dipilih.',
            'room_id.exists' => 'Ruangan tidak ditemukan atau sedang tidak tersedia.',
            'booking_date.required' => 'Tanggal peminjaman wajib diisi.',
            'booking_date.after_or_equal' => 'Tanggal peminjaman tidak boleh kurang dari hari ini.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid.',
            'end_time.after' => 'Jam selesai harus lebih besar dari jam mulai.',
            'purpose.required' => 'Tujuan peminjaman wajib diisi.',
            'purpose.min' => 'Tujuan peminjaman minimal 10 karakter.',
            'purpose.max' => 'Tujuan peminjaman maksimal 500 karakter.',
            'participants.required' => 'Jumlah peserta wajib diisi.',
            'participants.integer' => 'Jumlah peserta harus berupa angka.',
            'participants.min' => 'Jumlah peserta minimal 1 orang.',
        ];
    }

    public function attributes(): array
    {
        return [
            'room_id' => 'ruangan',
            'booking_date' => 'tanggal peminjaman',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'purpose' => 'tujuan peminjaman',
            'participants' => 'jumlah peserta',
        ];
    }

}
