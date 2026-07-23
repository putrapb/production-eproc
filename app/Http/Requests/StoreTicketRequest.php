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
            'expenditure_type'         => ['required', 'in:CAPEX,OPEX'],
            'category'                 => ['required', 'in:infrastruktur_utama,lisensi_sistem,layanan_pemeliharaan,perlengkapan_operasional'],
            'description'              => ['nullable', 'string', 'max:5000'],
            'vendor_name'              => ['required', 'string', 'max:255'],
            'pic_name'                 => ['required', 'array', 'max:2'],
            'pic_name.*'               => ['required', 'string', 'max:255'],

            // Detail item (maksimal 9)
            'items'                    => ['required', 'array', 'min:1', 'max:9'],
            'items.*.item_name'        => ['required', 'string', 'max:255'],
            'items.*.quantity'         => ['required', 'integer', 'min:1', 'max:10000'],
            'items.*.unit_price'       => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'items.*.expenditure_type' => ['nullable', 'string', 'in:CAPEX,OPEX'],

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
            'expenditure_type.required'        => 'Jenis pengeluaran wajib dipilih.',
            'category.required'                => 'Kategori pengadaan wajib dipilih.',
            'category.in'                      => 'Kategori tidak valid untuk jenis pengeluaran yang dipilih.',
            'vendor_name.required'             => 'Nama vendor wajib diisi.',
            'items.required'                   => 'Daftar item wajib diisi.',
            'items.min'                        => 'Minimal harus ada 1 item pengadaan.',
            'items.max'                        => 'Maksimal 9 item pengadaan.',
            'items.*.item_name.required'       => 'Nama item wajib diisi.',
            'items.*.quantity.required'        => 'Jumlah unit wajib diisi.',
            'items.*.quantity.min'             => 'Jumlah unit minimal 1.',
            'items.*.unit_price.required'      => 'Harga satuan wajib diisi.',
            'items.*.unit_price.min'           => 'Harga satuan tidak boleh negatif.',
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
