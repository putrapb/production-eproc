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
  {{-- Revision notes --}}
  @if($ticket->approvalLogs->where('action', 'rejected')->isNotEmpty())
    @php $lastReject = $ticket->approvalLogs->where('action', 'rejected')->sortByDesc('created_at')->first(); @endphp
    @if($lastReject?->notes)
    <div class="alert alert-warning mb-lg">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <div>
        <strong>Catatan dari PFA:</strong> "{{ $lastReject->notes }}"
        <div class="caption" style="margin-top:4px;">{{ $lastReject->created_at->format('d M Y, H:i') }}</div>
      </div>
    </div>
    @endif
  @endif

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="detail-section-title">Unggah Ulang Dokumen Izin Prinsip</div>

        @if($ticket->izin_prinsip_path)
        <div style="background:var(--color-surface-soft); border-radius:var(--radius-md); padding:var(--space-md); margin-bottom:var(--space-lg); display:flex; align-items:center; gap:var(--space-sm);">
          <div style="color:var(--color-error);">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div class="label-md">Dokumen saat ini tersimpan di sistem.</div>
          <a href="{{ Storage::url($ticket->izin_prinsip_path) }}" target="_blank" class="btn btn-ghost btn-sm" style="margin-left:auto;">Lihat Dokumen Lama</a>
        </div>
        @endif

        <div class="form-group">
          <label class="form-label">Dokumen Baru <span class="required">*</span></label>
          <div class="file-upload-zone" id="upload-zone" onclick="document.getElementById('izin_prinsip').click()"
               ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
            <input type="file" id="izin_prinsip" name="izin_prinsip" accept=".pdf" onchange="handleFileSelect(event)" required>
            <div id="upload-prompt">
              <div class="file-upload-icon">
                <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
              </div>
              <div class="label-md" style="margin-top:var(--space-sm);">Drag & drop file PDF di sini</div>
              <div class="body-sm text-muted" style="margin-top:4px;">atau klik untuk memilih file — Maks. 10 MB</div>
            </div>
            <div id="file-selected" style="display:none;">
              <div style="color:var(--color-error);">
                <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              </div>
              <div id="file-name" class="label-md" style="margin-top:var(--space-sm);"></div>
              <div id="file-size" class="caption text-muted"></div>
              <button type="button" onclick="clearFile(event)" class="btn btn-ghost btn-sm" style="margin-top:var(--space-sm);">Ganti File</button>
            </div>
          </div>
          @error('izin_prinsip') <div class="form-error" style="margin-top:6px;">{{ $message }}</div> @enderror
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

@push('scripts')
<script>
function handleFileSelect(e) {
  const file = e.target.files[0];
  if (file) showFile(file);
}
function showFile(file) {
  document.getElementById('upload-prompt').style.display = 'none';
  document.getElementById('file-selected').style.display = 'block';
  document.getElementById('file-name').textContent = file.name;
  document.getElementById('file-size').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
}
function clearFile(e) {
  e.stopPropagation();
  document.getElementById('izin_prinsip').value = '';
  document.getElementById('upload-prompt').style.display = 'block';
  document.getElementById('file-selected').style.display = 'none';
}
function handleDragOver(e) { e.preventDefault(); document.getElementById('upload-zone').classList.add('dragover'); }
function handleDragLeave(e) { document.getElementById('upload-zone').classList.remove('dragover'); }
function handleDrop(e) {
  e.preventDefault();
  document.getElementById('upload-zone').classList.remove('dragover');
  const file = e.dataTransfer.files[0];
  if (file && file.type === 'application/pdf') {
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('izin_prinsip').files = dt.files;
    showFile(file);
  } else {
    showToast('error', 'Format Tidak Valid', 'Hanya file PDF yang diterima.');
  }
}
</script>
@endpush
@endsection
