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
            // Header
            'title'                    => ['required', 'string', 'max:255'],
            'expenditure_type'         => ['required', 'in:CAPEX,OPEX'],
            'category'                 => ['required', 'in:infrastruktur_utama,lisensi_sistem,layanan_pemeliharaan,perlengkapan_operasional'],
            'description'              => ['nullable', 'string'],
            'vendor_name'              => ['required', 'string', 'max:255'],
            'pic_name'                 => ['required', 'array', 'max:2'],
            'pic_name.*'               => ['required', 'string', 'max:255'],

            // Multi-item (maks 9)
            'items'                    => ['required', 'array', 'min:1', 'max:9'],
            'items.*.item_name'        => ['required', 'string', 'max:255'],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'items.*.unit_price'       => ['required', 'numeric', 'min:1'],

            // Documents (all nullable during edit, because user might not change them)
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
            'expenditure_type.required'        => 'Jenis pengeluaran (CAPEX/OPEX) wajib dipilih.',
            'expenditure_type.in'              => 'Pilihan jenis pengeluaran tidak valid.',
            'category.required'                => 'Kategori pengadaan wajib dipilih.',
            'category.in'                      => 'Kategori tidak valid untuk jenis pengeluaran yang dipilih.',
            'vendor_name.required'             => 'Nama vendor wajib diisi.',
            'items.required'                   => 'Minimal 1 item pengadaan wajib diisi.',
            'items.min'                        => 'Minimal 1 item pengadaan wajib diisi.',
            'items.max'                        => 'Maksimal 9 item pengadaan per tiket.',
            'items.*.item_name.required'       => 'Nama item wajib diisi.',
            'items.*.quantity.required'        => 'Jumlah unit wajib diisi.',
            'items.*.quantity.min'             => 'Jumlah unit minimal 1.',
            'items.*.unit_price.required'      => 'Harga satuan wajib diisi.',
            'items.*.unit_price.min'           => 'Harga satuan harus lebih dari 0.',
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
