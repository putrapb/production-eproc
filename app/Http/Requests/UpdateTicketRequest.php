<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $this->user()?->isRequester()
            && $ticket
            && in_array($ticket->status, [\App\Models\Ticket::STATUS_REVISION, \App\Models\Ticket::STATUS_PENDING_REVIEW]);
    }

    public function rules(): array
    {
        return [
            // Data utama
            'title'                    => ['required', 'string', 'max:255'],
            'category'                 => ['required', 'in:infrastruktur_utama,lisensi_sistem,layanan_pemeliharaan,perlengkapan_operasional'],
            'description'              => ['nullable', 'string'],
            'vendor_name'              => ['required', 'string', 'max:255'],
            'pic_name'                 => ['required', 'array', 'max:2'],
            'pic_name.*'               => ['required', 'string', 'max:255'],

            // Detail item (maksimal 9) -> diganti kembali jadi single item
            'item_name'                => ['required', 'string', 'max:255'],
            'quantity'                 => ['required', 'integer', 'min:1'],
            'amount'                   => ['required', 'numeric', 'min:1'],

            // Dokumen pendukung (opsional saat edit jika tidak ada perubahan)
            'document_files'               => ['nullable', 'array'],
            'document_files.*'             => ['file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
            'new_document_files'           => ['nullable', 'array'],
            'new_document_files.*'         => ['file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
            'new_document_descriptions'     => ['nullable', 'array'],
            'new_document_descriptions.*'   => ['required_with:new_document_files.*', 'string', 'max:255'],
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
            'document_files.*.mimes'       => 'Semua dokumen harus dalam format PDF.',
            'document_files.*.mimetypes'   => 'Tipe file tidak valid. Hanya file PDF yang diperbolehkan.',
            'document_files.*.max'         => 'Ukuran dokumen maksimal 10 MB.',
            'new_document_files.*.mimes'   => 'Semua dokumen baru harus dalam format PDF.',
            'new_document_files.*.mimetypes' => 'Tipe file tidak valid. Hanya file PDF yang diperbolehkan.',
            'new_document_files.*.max'     => 'Ukuran dokumen baru maksimal 10 MB.',
            'new_document_descriptions.*.required_with' => 'Nama/Deskripsi untuk dokumen baru wajib diisi.',
        ];
    }
}
