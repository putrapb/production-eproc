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

        <div class="form-group">
          <label class="form-label" for="description">Deskripsi <span class="required">*</span></label>
          <textarea id="description" name="description"
            class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
            placeholder="Jelaskan kebutuhan dan spesifikasi item yang diajukan..."
            required>{{ old('description') }}</textarea>
          @error('description') <div class="form-error">{{ $message }}</div> @enderror
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
        <div class="detail-section-title">Dokumen Izin Prinsip</div>

        <div class="form-group">
          <label class="form-label">Unggah Dokumen PDF <span class="required">*</span></label>

          <div class="file-upload-zone" id="upload-zone" onclick="document.getElementById('izin_prinsip').click()"
               ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
            <input type="file" id="izin_prinsip" name="izin_prinsip" accept=".pdf" onchange="handleFileSelect(event)">

            <div id="upload-prompt">
              <div class="file-upload-icon">
                <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
              </div>
              <div class="label-md" style="margin-top:var(--space-sm);">Drag & drop file PDF di sini</div>
              <div class="body-sm text-muted" style="margin-top:4px;">atau klik untuk memilih file</div>
              <div class="caption text-muted" style="margin-top:var(--space-xs);">Maks. 10 MB · Format: PDF</div>
            </div>

            <div id="file-selected" style="display:none;">
              <div class="file-upload-icon" style="color:var(--color-error);">
                <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
              </div>
              <div id="file-name" class="label-md" style="margin-top:var(--space-sm);"></div>
              <div id="file-size" class="caption text-muted" style="margin-top:4px;"></div>
              <button type="button" onclick="clearFile(event)" class="btn btn-ghost btn-sm" style="margin-top:var(--space-sm);">
                Ganti File
              </button>
            </div>
          </div>

          @error('izin_prinsip') <div class="form-error" style="margin-top:6px;">{{ $message }}</div> @enderror
        </div>

        <hr class="divider">

        {{-- Actions --}}
        <div style="display:flex; justify-content:flex-end; gap:var(--space-sm);">
          <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Batal</a>
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

// File upload UX
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

function handleDragOver(e) {
  e.preventDefault();
  document.getElementById('upload-zone').classList.add('dragover');
}
function handleDragLeave(e) {
  document.getElementById('upload-zone').classList.remove('dragover');
}
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
