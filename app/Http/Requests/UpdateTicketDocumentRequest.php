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
            'izin_prinsip' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10 MB
        ];
    }

    public function messages(): array
    {
        return [
            'izin_prinsip.required' => 'Dokumen izin prinsip wajib diunggah.',
            'izin_prinsip.mimes'    => 'Dokumen harus dalam format PDF.',
            'izin_prinsip.max'      => 'Ukuran dokumen maksimal 10 MB.',
        ];
    }
}
