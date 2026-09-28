<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Menentukan hak akses pengguna untuk melakukan request ini.
     * Hanya pengguna dengan peran 'admin' yang diperbolehkan menambahkan kategori.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi yang berlaku untuk request penambahan kategori baru.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Kustomisasi nama label atribut untuk pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'        => 'nama kategori',
            'description' => 'deskripsi',
        ];
    }

    /**
     * Kustomisasi pesan kesalahan validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => ':Attribute wajib diisi.',
            'name.unique'   => ':Attribute sudah terdaftar, silakan gunakan nama lain.',
            'name.max'      => ':Attribute tidak boleh lebih dari 100 karakter.',
            'description.max' => ':Attribute tidak boleh lebih dari 255 karakter.',
        ];
    }
}

