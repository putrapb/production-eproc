@extends('layouts.app')

@section('title', 'Pengajuan Pengadaan Baru')

@section('breadcrumb')
  <a href="{{ route('tickets.index') }}" class="breadcrumb-item">Tiket Pengadaan</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-active">Pengajuan Baru</span>
@endsection

@section('content')
<div class="page-header">
  <div class="page-header-left">
    <h1>Pengajuan Pengadaan Baru</h1>
    <p>Isi formulir berikut untuk mengajukan permintaan pengadaan.</p>
  </div>
  <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Kembali
  </a>
</div>

<div class="page-content">
  <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" id="ticket-form">
    @csrf

    <div class="card">
      <div class="card-body">

        {{-- SECTION: Informasi Item --}}
        <div class="detail-section-title">Informasi Item</div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="title">Judul Pengajuan <span class="required">*</span></label>
            <input type="text" id="title" name="title"
              class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
              value="{{ old('title') }}"
              placeholder="Contoh: Pengadaan Server Rack Unit 2U"
              required maxlength="255">
            @error('title') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label" for="item_name">Nama Item <span class="required">*</span></label>
            <input type="text" id="item_name" name="item_name"
              class="form-control {{ $errors->has('item_name') ? 'is-invalid' : '' }}"
              value="{{ old('item_name') }}"
              placeholder="Contoh: Dell PowerEdge R740xd"
              required maxlength="255">
            @error('item_name') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="description">Deskripsi <span class="required">*</span></label>
            <textarea id="description" name="description"
              class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
              placeholder="Jelaskan kebutuhan dan spesifikasi item yang diajukan..."
              required>{{ old('description') }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label" for="pic_name">PIC (Person In Charge)</label>
            <input type="text" id="pic_name" name="pic_name"
              class="form-control {{ $errors->has('pic_name') ? 'is-invalid' : '' }}"
              value="{{ old('pic_name') }}"
              placeholder="Nama penanggung jawab pengadaan ini"
              maxlength="255">
            @error('pic_name') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>


        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="category">Kategori <span class="required">*</span></label>
            <select id="category" name="category"
              class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}"
              required>
              <option value="" disabled selected>Pilih kategori...</option>
              @foreach(config('eprocurement.categories') as $key => $label)
                <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('category') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label" for="quantity">Jumlah Unit <span class="required">*</span></label>
            <input type="number" id="quantity" name="quantity"
              class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
              value="{{ old('quantity', 1) }}"
              min="1" max="9999" required>
            @error('quantity') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="vendor_name">Nama Vendor <span class="required">*</span></label>
            <input type="text" id="vendor_name" name="vendor_name"
              class="form-control {{ $errors->has('vendor_name') ? 'is-invalid' : '' }}"
              value="{{ old('vendor_name') }}"
              placeholder="Contoh: PT Astra Graphia Tbk"
              required maxlength="255">
            @error('vendor_name') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label" for="amount">Nominal Harga <span class="required">*</span></label>
            <div style="position:relative;">
              <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--color-muted); font-weight:500;">Rp</span>
              <input type="text" id="amount-display" name="amount_display"
                class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                value="{{ old('amount') ? number_format((float)old('amount'), 0, ',', '.') : '' }}"
                placeholder="0"
                style="padding-left:36px;"
                oninput="formatAmount(this)">
              <input type="hidden" id="amount" name="amount" value="{{ old('amount') }}">
            </div>
            @error('amount') <div class="form-error">{{ $message }}</div> @enderror
          </div>
        </div>

        <hr class="divider">

        {{-- SECTION: Dokumen --}}
        <div class="detail-section-title">Dokumen Pendukung</div>

        <div class="form-group">
          <label class="form-label" style="margin-bottom: var(--space-sm); display: block;">Unggah Dokumen Pendukung (Maks. 10 MB per file, Format: PDF) <span class="required">*</span></label>

          <div id="documents-container" style="display: flex; flex-direction: column; gap: var(--space-md);">
            {{-- Initial row --}}
            <div class="document-row" style="display: flex; gap: var(--space-md); align-items: flex-start; background: var(--color-surface-soft); padding: var(--space-md); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
              <div style="flex: 1;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Nama / Deskripsi Dokumen <span class="required">*</span></label>
                <input type="text" name="document_descriptions[]" class="form-control" placeholder="Contoh: Izin Prinsip / RKS / HPS" required>
              </div>
              <div style="flex: 1;">
                <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">File PDF <span class="required">*</span></label>
                <input type="file" name="document_files[]" accept=".pdf" class="form-control" required style="padding: 6px 12px;">
              </div>
              <div style="align-self: flex-end; padding-bottom: 2px;">
                <button type="button" onclick="removeDocumentRow(this)" class="btn btn-danger btn-icon btn-sm" style="display: none;" title="Hapus Dokumen">
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
          </div>

          <button type="button" onclick="addDocumentRow()" class="btn btn-secondary btn-sm" style="margin-top: var(--space-md); display: inline-flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
            Tambah Dokumen Lainnya
          </button>

          @if ($errors->has('document_files') || $errors->has('document_files.*') || $errors->has('document_descriptions') || $errors->has('document_descriptions.*'))
            <div class="form-error" style="margin-top:12px; display: block;">
              Harap periksa kembali dokumen yang diunggah. Semua deskripsi dan file harus diisi dan berformat PDF.
            </div>
          @endif
        </div>

        <hr class="divider">

        {{-- Actions --}}
        <div style="display:flex; justify-content:flex-end; gap:var(--space-sm);">
          <a href="{{ route('tickets.index') }}" class="btn btn-danger">Batalkan</a>
          <button type="submit" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            Ajukan Pengadaan
          </button>
        </div>

      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
// Amount formatting
function formatAmount(input) {
  let raw = input.value.replace(/\D/g, '');
  input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
  document.getElementById('amount').value = raw;
}

// Dynamic Document Rows
function addDocumentRow() {
  const container = document.getElementById('documents-container');
  const newRow = document.createElement('div');
  newRow.className = 'document-row';
  newRow.style = 'display: flex; gap: var(--space-md); align-items: flex-start; background: var(--color-surface-soft); padding: var(--space-md); border-radius: var(--radius-md); border: 1px solid var(--color-border); margin-top: var(--space-sm);';
  newRow.innerHTML = `
    <div style="flex: 1;">
      <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">Nama / Deskripsi Dokumen <span class="required">*</span></label>
      <input type="text" name="document_descriptions[]" class="form-control" placeholder="Contoh: Izin Prinsip / RKS / HPS" required>
    </div>
    <div style="flex: 1;">
      <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">File PDF <span class="required">*</span></label>
      <input type="file" name="document_files[]" accept=".pdf" class="form-control" required style="padding: 6px 12px;">
    </div>
    <div style="align-self: flex-end; padding-bottom: 2px;">
      <button type="button" onclick="removeDocumentRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus Dokumen">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </button>
    </div>
  `;
  container.appendChild(newRow);
  updateDeleteButtons();
}

function removeDocumentRow(button) {
  const row = button.closest('.document-row');
  row.remove();
  updateDeleteButtons();
}

function updateDeleteButtons() {
  const rows = document.querySelectorAll('.document-row');
  rows.forEach(row => {
    const delBtn = row.querySelector('button[onclick="removeDocumentRow(this)"]');
    if (delBtn) {
      delBtn.style.display = rows.length > 1 ? 'inline-flex' : 'none';
    }
  });
}
</script>
@endpush
@endsection
