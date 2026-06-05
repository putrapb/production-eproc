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
            'category'    => ['required', 'in:infrastruktur_utama,lisensi_sistem,layanan_pemeliharaan,perlengkapan_operasional'],
            'description' => ['nullable', 'string'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'numeric', 'min:1', 'max:99999999999999.99'],
            'izin_prinsip' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'], // 10 MB, double-validated MIME
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Judul pengadaan wajib diisi.',
            'item_name.required'   => 'Nama item wajib diisi.',
            'category.required'    => 'Kategori pengadaan wajib dipilih.',
            'category.in'          => 'Kategori pengadaan tidak valid. Pilih salah satu: Infrastruktur Utama, Lisensi Sistem, Layanan Pemeliharaan, atau Perlengkapan Operasional.',
            'quantity.required'    => 'Jumlah unit wajib diisi.',
            'quantity.min'         => 'Jumlah unit minimal 1.',
            'vendor_name.required' => 'Nama vendor wajib diisi.',
            'amount.required'      => 'Nominal harga wajib diisi.',
            'amount.min'           => 'Nominal harga harus lebih dari 0.',
            'izin_prinsip.required' => 'Dokumen izin prinsip wajib diunggah.',
            'izin_prinsip.mimes'    => 'Dokumen harus dalam format PDF.',
            'izin_prinsip.mimetypes' => 'Tipe file tidak valid. Hanya file PDF asli yang diperbolehkan.',
            'izin_prinsip.max'      => 'Ukuran dokumen maksimal 10 MB.',
        ];
    }
}
