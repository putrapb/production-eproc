@extends('layouts.app')

@section('title', 'Upload Ulang Dokumen — Tiket #' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT))

@section('breadcrumb')
  <a href="{{ route('tickets.index') }}" class="breadcrumb-item">Tiket Pengadaan</a>
  <span class="breadcrumb-sep">/</span>
  <a href="{{ route('tickets.show', $ticket) }}" class="breadcrumb-item">Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-active">Upload Ulang</span>
@endsection

@section('content')
<div class="page-header">
  <div class="page-header-left">
    <h1>Upload Ulang Dokumen</h1>
    <p>Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }} — {{ $ticket->title }}</p>
  </div>
  <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali ke Detail
  </a>
</div>

<div class="page-content">

  {{-- Revision notes from Team Leader --}}
  @if($ticket->approvalLogs->where('action', 'rejected_document')->isNotEmpty())
    @php $lastReject = $ticket->approvalLogs->where('action', 'rejected_document')->sortByDesc('created_at')->first(); @endphp
    @if($lastReject?->notes)
    <div class="alert alert-warning mb-lg" style="align-items:flex-start;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <div>
        <strong style="display:block; margin-bottom:4px;">Catatan dari Team Leader:</strong>
        <div style="white-space:pre-line; font-size:13.5px; line-height:1.5; color:var(--color-warning-text);">{{ $lastReject->notes }}</div>
        <div class="caption" style="margin-top:8px; opacity:0.85;">{{ $lastReject->created_at->format('d M Y, H:i') }}</div>
      </div>
    </div>
    @endif
  @endif

  <div style="display:grid; grid-template-columns:1fr 320px; gap:var(--space-lg); align-items:start;">

    {{-- LEFT: Document upload form --}}
    <div>
      <div class="card">
        <div class="card-body">
          <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Existing documents review --}}
            <div class="detail-section-title">Dokumen Pendukung Saat Ini</div>
            <div style="display:flex; flex-direction:column; gap:var(--space-md); margin-bottom:var(--space-xl);">
              @foreach($ticket->documents as $doc)
                <div style="background:var(--color-surface-soft); padding:var(--space-md); border-radius:var(--radius-md); border:1px solid var(--color-border);">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-xs);">
                    <span style="font-weight:600; font-size:14px; color:var(--color-text);">{{ $doc->description }}</span>
                    @if($doc->isAccepted())
                      <span class="badge" style="background:var(--color-success-soft); color:var(--color-success); font-weight:600;">✓ Disetujui</span>
                    @elseif($doc->isRejected())
                      <span class="badge" style="background:var(--color-error-soft); color:var(--color-error); font-weight:600;">✗ Perlu Revisi</span>
                    @else
                      <span class="badge" style="background:var(--color-info-soft); color:var(--color-info); font-weight:600;">Pending</span>
                    @endif
                  </div>

                  @if($doc->isRejected() && $doc->feedback)
                    <div style="font-size:13px; color:var(--color-error); margin:6px 0; font-style:italic; background:rgba(239,68,68,0.05); padding:8px 12px; border-radius:4px; border-left:3px solid var(--color-error); white-space:pre-line;">
                      <strong>Catatan Team Leader:</strong> "{{ $doc->feedback }}"
                    </div>
                  @endif

                  @if($doc->isRejected() || $doc->isPending())
                    <div style="margin-top:var(--space-sm);">
                      <label class="form-label" style="font-size:12px; margin-bottom:4px; color:var(--color-muted);">Unggah File Baru (PDF, Maks. 10MB) <span class="required">*</span></label>
                      <input type="file" name="document_files[{{ $doc->id }}]" accept=".pdf" class="form-control" style="padding:6px 12px;" required onchange="validatePdfFile(this)">
                    </div>
                  @else
                    <div style="margin-top:6px; display:flex; align-items:center; justify-content:space-between;">
                      <span class="caption text-muted">Tidak memerlukan revisi.</span>
                      <a href="{{ route('tickets.document', ['ticketDocument' => $doc->id]) }}" target="_blank" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:11px;">Lihat Dokumen</a>
                    </div>
                  @endif
                </div>
              @endforeach
            </div>

            <hr class="divider">

            {{-- New documents --}}
            <div class="detail-section-title">Tambah Dokumen Baru <span style="font-weight:400; font-size:12px; color:var(--color-muted);">(Opsional)</span></div>
            <div class="form-group">
              <div id="new-documents-container" style="display:flex; flex-direction:column; gap:var(--space-md);"></div>
              <button type="button" onclick="addNewDocumentRow()" class="btn btn-secondary btn-sm" style="margin-top:var(--space-md); display:inline-flex; align-items:center; gap:6px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
                Tambah Dokumen Lainnya
              </button>
            </div>

            <hr class="divider">

            <div style="display:flex; justify-content:flex-end; gap:var(--space-sm);">
              <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-danger">Batalkan</a>
              <button type="submit" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Kirim Ulang Dokumen
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- RIGHT: Ticket info summary (read-only) --}}
    <div>
      <div class="card" style="position:sticky; top:calc(var(--topbar-height) + var(--space-lg));">
        <div class="card-header">
          <div class="heading-sm">Ringkasan Pengajuan</div>
        </div>
        <div class="card-body">

          <div class="detail-field" style="margin-bottom:var(--space-sm);">
            <div class="detail-field-label">Judul</div>
            <div class="detail-field-value" style="font-size:13px;">{{ $ticket->title }}</div>
          </div>

          <div class="detail-field" style="margin-bottom:var(--space-sm);">
            <div class="detail-field-label">Jenis Pengeluaran</div>
            <div class="detail-field-value">
              @if($ticket->expenditure_type)
                <span class="badge badge-{{ strtolower($ticket->expenditure_type) }}">{{ $ticket->expenditure_type }}</span>
              @else
                <span class="text-muted" style="font-size:12px;">Belum diklasifikasi</span>
              @endif
            </div>
          </div>

          <div class="detail-field" style="margin-bottom:var(--space-sm);">
            <div class="detail-field-label">Kategori</div>
            <div class="detail-field-value" style="font-size:13px;">
              <span class="badge badge-category">{{ config('eprocurement.categories.'.$ticket->category, $ticket->category) }}</span>
            </div>
          </div>

          <div class="detail-field" style="margin-bottom:var(--space-md);">
            <div class="detail-field-label">Vendor</div>
            <div class="detail-field-value" style="font-size:13px;">{{ $ticket->vendor_name }}</div>
          </div>

          {{-- Item list read-only --}}
          @if($ticket->items->isNotEmpty())
          <div style="border-top:1px solid var(--color-hairline); padding-top:var(--space-sm); margin-top:var(--space-sm);">
            <div class="detail-field-label" style="margin-bottom:var(--space-xs);">Daftar Item</div>
            @foreach($ticket->items as $i => $item)
              <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:6px 0; border-bottom:1px solid var(--color-hairline); font-size:12px;">
                <div style="flex:1; color:var(--color-text); font-weight:500;">
                  {{ $i + 1 }}. {{ $item->item_name }}
                  <div style="color:var(--color-muted); font-weight:400; font-size:11px; margin-top:2px;">{{ number_format($item->quantity) }} × {{ $item->formatted_unit_price }}</div>
                </div>
                <div style="font-weight:600; color:var(--color-text); margin-left:8px; white-space:nowrap;">{{ $item->formatted_subtotal }}</div>
              </div>
            @endforeach
            <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13px; font-weight:700; color:var(--color-primary);">
              <span>Total</span>
              <span>{{ $ticket->formatted_total_amount }}</span>
            </div>
          </div>
          @endif

        </div>
      </div>
    </div>

  </div>
</div>

@push('scripts')
<script>
function addNewDocumentRow() {
  const c = document.getElementById('new-documents-container');
  const d = document.createElement('div');
  d.className = 'new-document-row';
  d.style.cssText = 'display:flex; gap:var(--space-md); align-items:flex-start; background:var(--color-surface-soft); padding:var(--space-md); border-radius:var(--radius-md); border:1px solid var(--color-border);';
  d.innerHTML = `
    <div style="flex:1;">
      <label class="form-label" style="font-size:12px; margin-bottom:4px;">Nama / Deskripsi Dokumen <span class="required">*</span></label>
      <input type="text" name="new_document_descriptions[]" class="form-control" placeholder="Contoh: RKS / HPS" required>
    </div>
    <div style="flex:1;">
      <label class="form-label" style="font-size:12px; margin-bottom:4px;">File PDF <span class="required">*</span></label>
      <input type="file" name="new_document_files[]" accept=".pdf" class="form-control" required style="padding:6px 12px;" onchange="validatePdfFile(this)">
    </div>
    <div style="align-self:flex-end; padding-bottom:2px;">
      <button type="button" onclick="this.closest('.new-document-row').remove()" class="btn btn-danger btn-icon btn-sm" title="Hapus">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </button>
    </div>
  `;
  c.appendChild(d);
}

function validatePdfFile(input) {
  const file = input.files[0];
  if (file && !(file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf'))) {
    showToast('error', 'Format Tidak Valid', 'Hanya file PDF yang diperbolehkan.');
    input.value = '';
  }
}
</script>
@endpush
@endsection
