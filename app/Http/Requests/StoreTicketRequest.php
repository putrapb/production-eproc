<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRequester() ?? false;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'item_name'   => ['required', 'string', 'max:255'],
            'category'    => ['required', 'in:hardware,software,services,office_supplies,others'],
            'description' => ['nullable', 'string'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'numeric', 'min:1', 'max:99999999999999.99'],
            'document'    => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10 MB
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Judul pengadaan wajib diisi.',
            'item_name.required'   => 'Nama item wajib diisi.',
            'category.required'    => 'Kategori pengadaan wajib dipilih.',
            'category.in'          => 'Kategori pengadaan tidak valid.',
            'quantity.required'    => 'Jumlah unit wajib diisi.',
            'quantity.min'         => 'Jumlah unit minimal 1.',
            'vendor_name.required' => 'Nama vendor wajib diisi.',
            'amount.required'      => 'Nominal harga wajib diisi.',
            'amount.min'           => 'Nominal harga harus lebih dari 0.',
            'document.required'    => 'Dokumen izin prinsip wajib diunggah.',
            'document.mimes'       => 'Dokumen harus dalam format PDF.',
            'document.max'         => 'Ukuran dokumen maksimal 10 MB.',
        ];
    }
}
