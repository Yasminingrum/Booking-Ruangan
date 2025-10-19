<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * @method mixed input(string $key = null, $default = null)
 * @method bool has(string $key)
 * @method void merge(array $input)
 * @method bool isMethod(string $method)
 * @method mixed route(string $name = null, $default = null)
 */
class RoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya admin yang bisa mengelola ruangan
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roomId = $this->route('room'); // Untuk update, ambil ID dari route parameter

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                // Unique, tapi ignore ID sendiri saat update
                Rule::unique('rooms', 'name')->ignore($roomId),
            ],
            'type' => [
                'required',
                'string',
                Rule::in([
                    'laboratorium',
                    'ruang_musik',
                    'audio_visual',
                    'lapangan_basket',
                    'kolam_renang'
                ]),
            ],
            'capacity' => [
                'required',
                'integer',
                'min:1',
                'max:500',
            ],
            'location' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'facilities' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama ruangan harus diisi.',
            'name.unique' => 'Nama ruangan sudah digunakan.',
            'name.max' => 'Nama ruangan maksimal 100 karakter.',
            'type.required' => 'Jenis ruangan harus dipilih.',
            'type.in' => 'Jenis ruangan tidak valid. Pilih salah satu: laboratorium, ruang_musik, audio_visual, lapangan_basket, kolam_renang.',
            'capacity.required' => 'Kapasitas ruangan harus diisi.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min' => 'Kapasitas minimal 1 orang.',
            'capacity.max' => 'Kapasitas maksimal 500 orang.',
            'location.required' => 'Lokasi ruangan harus diisi.',
            'location.max' => 'Lokasi maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'facilities.max' => 'Fasilitas maksimal 1000 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama ruangan',
            'type' => 'jenis ruangan',
            'capacity' => 'kapasitas',
            'location' => 'lokasi',
            'description' => 'deskripsi',
            'facilities' => 'fasilitas',
            'is_active' => 'status aktif',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default untuk is_active jika tidak ada (true untuk create)
        if (!$this->has('is_active') && $this->isMethod('POST')) {
            $this->merge([
                'is_active' => true,
            ]);
        }

        // Trim whitespace dari nama
        if ($this->has('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validasi khusus: jika ruangan mau di-nonaktifkan,
            // cek apakah ada booking approved yang akan datang
            if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                if ($this->has('is_active') && (bool) $this->input('is_active') === false) {
                    $roomId = $this->route('room');
                    $hasUpcomingBookings = \App\Models\Booking::where('room_id', $roomId)
                        ->where('status', 'approved')
                        ->where('booking_date', '>=', now()->toDateString())
                        ->exists();

                    if ($hasUpcomingBookings) {
                        $validator->errors()->add(
                            'is_active',
                            'Ruangan tidak dapat dinonaktifkan karena masih memiliki peminjaman yang sudah disetujui.'
                        );
                    }
                }
            }
        });
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Anda tidak memiliki izin untuk mengelola data ruangan. Hanya admin yang dapat melakukan tindakan ini.'
        );
    }
}
