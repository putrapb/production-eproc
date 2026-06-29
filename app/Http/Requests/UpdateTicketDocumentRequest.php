<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only Requester may re-upload, and only when ticket is in 'revision' status
        $ticket = $this->route('ticket');

        return $this->user()?->isRequester()
            && $ticket
            && $ticket->isRevision();
    }

    public function rules(): array
    {
        return [
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
