<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya admin yang bisa mengelola user
        return auth::check() && auth::user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user'); // Untuk update, ambil ID dari route parameter
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                'min:10',
                'max:20',
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['peminjam', 'admin', 'kepala_sekolah', 'cleaning_service']),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];

        // Password rules berbeda untuk create dan update
        if (!$isUpdate) {
            // Untuk create, password wajib
            $rules['password'] = [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ];
            $rules['password_confirmation'] = [
                'required',
                'string',
            ];
        } else {
            // Untuk update, password optional
            $rules['password'] = [
                'nullable',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ];
            $rules['password_confirmation'] = [
                'nullable',
                'required_with:password',
                'string',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap harus diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'phone.min' => 'Nomor telepon minimal 10 digit.',
            'phone.max' => 'Nomor telepon maksimal 20 digit.',
            'role.required' => 'Role pengguna harus dipilih.',
            'role.in' => 'Role tidak valid. Pilih salah satu: peminjam, admin, kepala_sekolah, cleaning_service.',
            'password.required' => 'Password harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'password_confirmation.required' => 'Konfirmasi password harus diisi.',
            'password_confirmation.required_with' => 'Konfirmasi password harus diisi jika mengubah password.',
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
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'role' => 'role',
            'password' => 'password',
            'password_confirmation' => 'konfirmasi password',
            'is_active' => 'status aktif',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }

        // Set default untuk is_active jika tidak ada (true untuk create)
        if (!$this->has('is_active') && $this->isMethod('POST')) {
            $this->merge([
                'is_active' => true,
            ]);
        }

        // Hapus password jika kosong pada update
        if (($this->isMethod('PUT') || $this->isMethod('PATCH')) && empty($this->password)) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
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
            // Validasi khusus: tidak bisa mengubah role admin terakhir
            if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                $userId = $this->route('user');
                $user = \App\Models\User::find($userId);

                if ($user && $user->role === 'admin' && $this->role !== 'admin') {
                    // Cek apakah ini admin terakhir
                    $adminCount = \App\Models\User::where('role', 'admin')
                        ->where('is_active', true)
                        ->count();

                    if ($adminCount <= 1) {
                        $validator->errors()->add(
                            'role',
                            'Tidak dapat mengubah role admin terakhir. Sistem harus memiliki minimal satu admin aktif.'
                        );
                    }
                }

                // Validasi: tidak bisa menonaktifkan admin terakhir
                if ($user && $user->role === 'admin' && $this->has('is_active') && $this->is_active === false) {
                    $activeAdminCount = \App\Models\User::where('role', 'admin')
                        ->where('is_active', true)
                        ->where('id', '!=', $userId)
                        ->count();

                    if ($activeAdminCount < 1) {
                        $validator->errors()->add(
                            'is_active',
                            'Tidak dapat menonaktifkan admin terakhir. Sistem harus memiliki minimal satu admin aktif.'
                        );
                    }
                }
            }

            // Validasi email domain sekolah (opsional, bisa disesuaikan)
            if ($this->has('email')) {
                $allowedDomains = ['palembangharapan.sch.id', 'gmail.com', 'yahoo.com'];
                $emailDomain = substr(strrchr($this->email, "@"), 1);

                // Uncomment jika ingin enforce domain tertentu
                // if (!in_array($emailDomain, $allowedDomains)) {
                //     $validator->errors()->add(
                //         'email',
                //         'Email harus menggunakan domain: ' . implode(', ', $allowedDomains)
                //     );
                // }
            }
        });
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Anda tidak memiliki izin untuk mengelola data pengguna. Hanya admin yang dapat melakukan tindakan ini.'
        );
    }

    /**
     * Get the validated data from the request.
     * Override untuk hash password sebelum return
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Hash password jika ada
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        // Hapus password_confirmation karena tidak perlu disimpan
        unset($validated['password_confirmation']);

        return $validated;
    }
}
