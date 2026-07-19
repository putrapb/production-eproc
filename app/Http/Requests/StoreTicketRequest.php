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
            // Data utama
            'title'                    => ['required', 'string', 'max:255'],
            'category'                 => ['required', 'in:infrastruktur_utama,lisensi_sistem,layanan_pemeliharaan,perlengkapan_operasional'],
            'description'              => ['nullable', 'string', 'max:5000'],
            'vendor_name'              => ['required', 'string', 'max:255'],
            'pic_name'                 => ['required', 'array', 'max:2'],
            'pic_name.*'               => ['required', 'string', 'max:255'],

            // Detail item (maksimal 9) -> diganti kembali jadi single item
            'item_name'                => ['required', 'string', 'max:255'],
            'quantity'                 => ['required', 'integer', 'min:1'],
            'amount'                   => ['required', 'numeric', 'min:1'],

            // Dokumen pendukung
            'document_files'           => ['required', 'array', 'min:1'],
            'document_files.*'         => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
            'document_descriptions'    => ['required', 'array', 'min:1'],
            'document_descriptions.*'  => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                   => 'Judul pengadaan wajib diisi.',
            'category.required'                => 'Kategori pengadaan wajib dipilih.',
            'category.in'                      => 'Kategori tidak valid untuk jenis pengeluaran yang dipilih.',
            'vendor_name.required'             => 'Nama vendor wajib diisi.',
            'item_name.required'               => 'Nama item wajib diisi.',
            'quantity.required'                => 'Jumlah unit wajib diisi.',
            'quantity.min'                     => 'Jumlah unit minimal 1.',
            'amount.required'                  => 'Harga satuan wajib diisi.',
            'amount.min'                       => 'Harga satuan harus lebih dari 0.',
            'document_files.required'          => 'Dokumen pendukung wajib diunggah.',
            'document_files.min'               => 'Minimal harus mengunggah 1 dokumen.',
            'document_files.*.mimes'           => 'Semua dokumen harus dalam format PDF.',
            'document_files.*.mimetypes'       => 'Tipe file tidak valid. Hanya PDF yang diperbolehkan.',
            'document_files.*.max'             => 'Ukuran dokumen maksimal 10 MB.',
            'document_descriptions.required'   => 'Deskripsi dokumen wajib diisi.',
            'document_descriptions.*.required' => 'Nama/Deskripsi dokumen wajib diisi.',
        ];
    }
}
