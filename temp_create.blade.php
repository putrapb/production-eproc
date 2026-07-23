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

        {{-- SECTION: Informasi Pengadaan --}}
        <div class="detail-section-title">Informasi Pengadaan</div>

        <div class="form-group">
          <label class="form-label" for="title">Judul Pengajuan <span class="required">*</span></label>
          <input type="text" id="title" name="title"
            class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
            value="{{ old('title') }}"
            placeholder="Contoh: Pengadaan Storage Array untuk DC Pejompongan"
            required maxlength="255">
          @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="expenditure_type">Jenis Pengeluaran <span class="required">*</span></label>
            <select id="expenditure_type" name="expenditure_type"
              class="form-control {{ $errors->has('expenditure_type') ? 'is-invalid' : '' }}"
              onchange="filterCategories(this.value)" required>
              <option value="" disabled {{ old('expenditure_type') ? '' : 'selected' }}>Pilih CAPEX / OPEX...</option>
              <option value="CAPEX" {{ old('expenditure_type') === 'CAPEX' ? 'selected' : '' }}>CAPEX — Aset / Investasi Baru</option>
              <option value="OPEX"  {{ old('expenditure_type') === 'OPEX'  ? 'selected' : '' }}>OPEX — Operasional / Pemeliharaan</option>
            </select>
            <div style="font-size:11px; color:var(--color-muted); margin-top:4px;">CAPEX: barang baru yang menjadi aset. OPEX: maintenance, spare part, managed service.</div>
            @error('expenditure_type') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label class="form-label" for="category">Kategori <span class="required">*</span></label>
            <select id="category" name="category"
              class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}"
              required>
              <option value="" disabled selected>Pilih jenis pengeluaran dulu...</option>
              @php
                $capexCats = ['infrastruktur_utama' => 'Infrastruktur Utama', 'lisensi_sistem' => 'Lisensi Sistem'];
                $opexCats  = ['layanan_pemeliharaan' => 'Layanan Pemeliharaan', 'perlengkapan_operasional' => 'Perlengkapan Operasional'];
              @endphp
              @foreach($capexCats as $key => $label)
                <option value="{{ $key }}" data-type="CAPEX" {{ old('category') === $key ? 'selected' : '' }} style="display:none;">{{ $label }}</option>
              @endforeach
              @foreach($opexCats as $key => $label)
                <option value="{{ $key }}" data-type="OPEX" {{ old('category') === $key ? 'selected' : '' }} style="display:none;">{{ $label }}</option>
              @endforeach
            </select>
            @error('category') <div class="form-error">{{ $message }}</div> @enderror
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
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; height:20px;">
              <label class="form-label" style="margin-bottom:0;">PIC (Person In Charge) <span class="required">*</span></label>
              <button type="button" id="btn-add-pic" onclick="addPicRow()" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:11px; {{ count(old('pic_name', [''])) >= 2 ? 'display:none;' : '' }}">+ Tambah PIC</button>
            </div>
            <div id="pic-container" style="display:flex; flex-direction:column; gap:var(--space-xs);">
              @php $oldPics = old('pic_name', ['']); if (!is_array($oldPics)) $oldPics = [$oldPics]; @endphp
              @foreach($oldPics as $index => $pic)
              <div class="pic-row" style="display:flex; gap:var(--space-sm); align-items:center;">
                <div style="flex:1;">
                  <input type="text" name="pic_name[]" class="form-control" value="{{ $pic }}" placeholder="Nama PIC" maxlength="255" required>
                </div>
                <button type="button" onclick="removePicRow(this)" class="btn btn-danger btn-icon btn-sm" style="display:{{ count($oldPics) > 1 ? 'inline-flex' : 'none' }};" title="Hapus PIC">
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
              @endforeach
            </div>
            @error('pic_name') <div class="form-error" style="margin-top:4px;">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Deskripsi / Justifikasi Pengadaan</label>
          <textarea id="description" name="description"
            class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
            placeholder="Jelaskan kebutuhan, spesifikasi, dan urgensi pengadaan ini...">{{ old('description') }}</textarea>
          @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <hr class="divider">

        {{-- SECTION: Daftar Item --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-md);">
          <div class="detail-section-title" style="margin-bottom:0;">Daftar Item Pengadaan <span class="required">*</span></div>
          <button type="button" id="btn-add-item" onclick="addItemRow()" class="btn btn-outline btn-sm" style="padding:4px 12px; font-size:12px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
            Tambah Item
          </button>
        </div>

        @error('items') <div class="form-error" style="margin-bottom:8px;">{{ $message }}</div> @enderror

        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse;" id="items-table">
            <thead>
              <tr style="background:var(--color-surface-soft); font-size:12px; font-weight:600; color:var(--color-muted);">
                <th style="padding:10px 12px; text-align:left; border-bottom:1px solid var(--color-border); width:40px;">No.</th>
                <th style="padding:10px 12px; text-align:left; border-bottom:1px solid var(--color-border);">Nama Item <span class="required">*</span></th>
                <th style="padding:10px 12px; text-align:center; border-bottom:1px solid var(--color-border); width:90px;">Qty <span class="required">*</span></th>
                <th style="padding:10px 12px; text-align:right; border-bottom:1px solid var(--color-border); width:200px;">Harga Satuan (Rp) <span class="required">*</span></th>
                <th style="padding:10px 12px; text-align:right; border-bottom:1px solid var(--color-border); width:170px;">Subtotal</th>
                <th style="padding:10px 12px; border-bottom:1px solid var(--color-border); width:50px;"></th>
              </tr>
            </thead>
            <tbody id="items-body">
              @php $oldItems = old('items', [[]]); @endphp
              @foreach($oldItems as $i => $oldItem)
              <tr class="item-row">
                <td style="padding:8px 12px; font-size:13px; color:var(--color-muted);" class="item-no">{{ $i + 1 }}</td>
                <td style="padding:6px 8px;">
                  <input type="text" name="items[{{ $i }}][item_name]"
                    class="form-control {{ $errors->has("items.$i.item_name") ? 'is-invalid' : '' }}"
                    value="{{ $oldItem['item_name'] ?? '' }}"
                    placeholder="Nama barang / jasa" required maxlength="255">
                </td>
                <td style="padding:6px 8px;">
                  <input type="number" name="items[{{ $i }}][quantity]"
                    class="form-control item-qty {{ $errors->has("items.$i.quantity") ? 'is-invalid' : '' }}"
                    value="{{ $oldItem['quantity'] ?? 1 }}"
                    min="1" style="text-align:center;" onchange="recalcRow(this)" required>
                </td>
                <td style="padding:6px 8px;">
                  <input type="text" class="form-control item-price-display {{ $errors->has("items.$i.unit_price") ? 'is-invalid' : '' }}"
                    value="{{ isset($oldItem['unit_price']) ? number_format($oldItem['unit_price'], 0, ',', '.') : '' }}"
                    placeholder="0" style="text-align:right;" oninput="formatItemPrice(this)">
                  <input type="hidden" name="items[{{ $i }}][unit_price]" class="item-price-raw" value="{{ $oldItem['unit_price'] ?? '' }}">
                </td>
                <td style="padding:6px 12px; text-align:right; font-size:13px; font-weight:600;" class="item-subtotal">
                  Rp {{ (isset($oldItem['unit_price']) && isset($oldItem['quantity'])) ? number_format($oldItem['unit_price'] * $oldItem['quantity'], 0, ',', '.') : '0' }}
                </td>
                <td style="padding:6px 8px; text-align:center;">
                  <button type="button" onclick="removeItemRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus" style="display:{{ count($oldItems) > 1 ? 'inline-flex' : 'none' }};">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr style="background:var(--color-surface-soft);">
                <td colspan="4" style="padding:12px; text-align:right; font-size:13px; font-weight:700; color:var(--color-muted);">TOTAL KESELURUHAN</td>
                <td style="padding:12px; text-align:right; font-size:15px; font-weight:700; color:var(--color-primary);" id="grand-total">Rp 0</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div style="font-size:11px; color:var(--color-muted); margin-top:6px;">Maksimal 9 item. Total dihitung otomatis dari Qty × Harga Satuan.</div>

        <hr class="divider">

        {{-- SECTION: Dokumen --}}
        <div class="detail-section-title">Dokumen Pendukung</div>

        <div class="form-group">
          <label class="form-label" style="margin-bottom:var(--space-sm); display:block;">Unggah Dokumen (Maks. 10 MB per file, Format: PDF) <span class="required">*</span></label>
          <div id="documents-container" style="display:flex; flex-direction:column; gap:var(--space-md);">
            <div class="document-row" style="display:flex; gap:var(--space-md); align-items:flex-start; background:var(--color-surface-soft); padding:var(--space-md); border-radius:var(--radius-md); border:1px solid var(--color-border);">
              <div style="flex:1;"><label class="form-label" style="font-size:12px; margin-bottom:4px;">Nama / Deskripsi Dokumen <span class="required">*</span></label><input type="text" name="document_descriptions[]" class="form-control" placeholder="Contoh: Izin Prinsip / RKS / HPS" required></div>
              <div style="flex:1;"><label class="form-label" style="font-size:12px; margin-bottom:4px;">File PDF <span class="required">*</span></label><input type="file" name="document_files[]" accept=".pdf" class="form-control" required style="padding:6px 12px;" onchange="validatePdfFile(this)"></div>
              <div style="align-self:flex-end; padding-bottom:2px;"><button type="button" onclick="removeDocumentRow(this)" class="btn btn-danger btn-icon btn-sm" style="display:none;" title="Hapus"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
            </div>
          </div>
          <button type="button" onclick="addDocumentRow()" class="btn btn-secondary btn-sm" style="margin-top:var(--space-md); display:inline-flex; align-items:center; gap:6px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
            Tambah Dokumen Lainnya
          </button>
          @if ($errors->has('document_files') || $errors->has('document_files.*') || $errors->has('document_descriptions') || $errors->has('document_descriptions.*'))
            <div class="form-error" style="margin-top:12px; display:block;">Harap periksa kembali dokumen. Semua deskripsi dan file harus diisi dan berformat PDF.</div>
          @endif
        </div>

        <hr class="divider">


        <div style="display:flex; justify-content:flex-end; gap:var(--space-sm);">
          <a href="{{ route('tickets.index') }}" onclick="localStorage.removeItem('ticket_form_draft')" class="btn btn-danger">Batalkan</a>
          <button type="button" id="btn-preview-val" onclick="runPreviewValidation()" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Cek Validasi Dulu
          </button>
        </div>

      </div>
    </div>
  </form>
</div>

{{-- ═══ Modal: Gate Card Pre-Validation Result (Transkrip 2) ═══ --}}
<div class="modal-overlay" id="modal-prevalidation-result">
  <div class="modal-card" style="max-width:640px; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; padding:0;">
    <div style="padding:24px 24px 0; flex-shrink:0;">
      <div class="modal-icon" id="pv-modal-icon" style="background:var(--color-primary-soft);">
        <svg width="24" height="24" fill="none" stroke="var(--color-primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="modal-title" style="margin-bottom:4px;">Hasil Pra-Validasi Anggaran</div>
      <div style="font-size:13px; color:var(--color-muted); margin-bottom:16px;">Sistem memeriksa 4 gate validasi sebelum Anda mengajukan pengadaan.</div>
    </div>

    {{-- Gate Cards Container --}}
    <div id="pv-gates-container" style="flex:1; overflow-y:auto; padding:0 24px; display:flex; flex-direction:column; gap:10px;">
      {{-- Diisi secara dinamis oleh JS --}}
    </div>

    {{-- Per-Item Classification Table --}}
    <div id="pv-items-container" style="padding:0 24px; margin-top:10px; display:none;">
      <div style="font-size:12px; font-weight:600; color:var(--color-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Klasifikasi Per-Item</div>
      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:13px;" id="pv-items-table">
          <thead>
            <tr style="background:var(--color-surface-soft);">
              <th style="padding:8px 10px; text-align:left; border-bottom:1px solid var(--color-border); font-weight:600; color:var(--color-muted);">Item</th>
              <th style="padding:8px 10px; text-align:center; border-bottom:1px solid var(--color-border); font-weight:600; color:var(--color-muted); width:80px;">Klasifikasi</th>
              <th style="padding:8px 10px; text-align:right; border-bottom:1px solid var(--color-border); font-weight:600; color:var(--color-muted); width:150px;">Subtotal</th>
            </tr>
          </thead>
          <tbody id="pv-items-body"></tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer" style="flex-shrink:0; margin-top:16px; padding:16px 24px; border-top:1px solid var(--color-border); flex-direction:column; gap:12px;">
      <div style="text-align:left; width:100%; display:none;" id="ds-consent-container">
        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; font-size:13px;">
          <input type="checkbox" id="ds-consent-create" onchange="toggleSubmitTicketBtn()" style="margin-top:2px;">
          <span>Saya menyetujui syarat &amp; ketentuan dan menandatangani pengajuan ini secara digital.</span>
        </label>
      </div>
      <div style="display:flex; justify-content:flex-end; width:100%; gap:8px;">
        <button type="button" onclick="closeModal('modal-prevalidation-result')" class="btn btn-secondary">Tutup</button>
        <button type="button" id="btn-submit-ticket" onclick="submitTicketForm()" class="btn btn-primary" style="display:none;" disabled>
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
          Ajukan Pengadaan
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
// ── CAPEX/OPEX → Filter Kategori ─────────────────
function filterCategories(type) {
  const sel = document.getElementById('category');
  const opts = sel.querySelectorAll('option[data-type]');
  let firstVisible = null;
  opts.forEach(opt => {
    const show = opt.dataset.type === type;
    opt.style.display = show ? '' : 'none';
    if (show && !firstVisible) firstVisible = opt;
  });
  const placeholder = sel.querySelector('option:not([data-type])');
  if (placeholder) placeholder.textContent = type ? 'Pilih kategori...' : 'Pilih jenis pengeluaran dulu...';
  if (firstVisible && !sel.querySelector('option[selected]')) sel.value = firstVisible.value;
}

window.addEventListener('DOMContentLoaded', () => {
  const typeEl = document.getElementById('expenditure_type');
  if (typeEl.value) filterCategories(typeEl.value);
  recalcAll();
});

// ── Multi-Item Table ──────────────────────────────
let itemCounter = {{ count(old('items', [[]])) }};

function formatItemPrice(input) {
  const raw = input.value.replace(/\D/g, '');
  input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
  input.closest('td').querySelector('.item-price-raw').value = raw;
  recalcRow(input);
}

function recalcRow(el) {
  const row = el.closest('.item-row');
  const qty = parseInt(row.querySelector('.item-qty').value) || 0;
  const price = parseInt(row.querySelector('.item-price-raw').value) || 0;
  row.querySelector('.item-subtotal').textContent = 'Rp ' + (qty * price).toLocaleString('id-ID');
  recalcTotal();
}

function recalcAll() {
  document.querySelectorAll('.item-row').forEach(row => {
    const qty = parseInt(row.querySelector('.item-qty').value) || 0;
    const price = parseInt(row.querySelector('.item-price-raw').value) || 0;
    row.querySelector('.item-subtotal').textContent = 'Rp ' + (qty * price).toLocaleString('id-ID');
  });
  recalcTotal();
}

function recalcTotal() {
  let total = 0;
  document.querySelectorAll('.item-row').forEach(row => {
    total += (parseInt(row.querySelector('.item-qty').value)||0) * (parseInt(row.querySelector('.item-price-raw').value)||0);
  });
  document.getElementById('grand-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function addItemRow() {
  const tbody = document.getElementById('items-body');
  if (tbody.querySelectorAll('.item-row').length >= 9) { showToast('error', 'Batas Maksimum', 'Maksimal 9 item per tiket.'); return; }
  const idx = itemCounter++;
  const tr = document.createElement('tr');
  tr.className = 'item-row';
  tr.innerHTML = `
    <td style="padding:8px 12px; font-size:13px; color:var(--color-muted);" class="item-no"></td>
    <td style="padding:6px 8px;"><input type="text" name="items[${idx}][item_name]" class="form-control" placeholder="Nama barang / jasa" required maxlength="255"></td>
    <td style="padding:6px 8px;"><input type="number" name="items[${idx}][quantity]" class="form-control item-qty" value="1" min="1" style="text-align:center;" onchange="recalcRow(this)" required></td>
    <td style="padding:6px 8px;"><input type="text" class="form-control item-price-display" placeholder="0" style="text-align:right;" oninput="formatItemPrice(this)"><input type="hidden" name="items[${idx}][unit_price]" class="item-price-raw" value=""></td>
    <td style="padding:6px 12px; text-align:right; font-size:13px; font-weight:600;" class="item-subtotal">Rp 0</td>
    <td style="padding:6px 8px; text-align:center;"><button type="button" onclick="removeItemRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
  `;
  tbody.appendChild(tr);
  updateItemNumbers();
  updateItemDeleteButtons();
}

function removeItemRow(btn) {
  btn.closest('.item-row').remove();
  updateItemNumbers();
  updateItemDeleteButtons();
  recalcTotal();
}

function updateItemNumbers() {
  document.querySelectorAll('#items-body .item-row').forEach((r, i) => { r.querySelector('.item-no').textContent = i + 1; });
}

function updateItemDeleteButtons() {
  const rows = document.querySelectorAll('#items-body .item-row');
  rows.forEach(r => { const b = r.querySelector('button[onclick="removeItemRow(this)"]'); if (b) b.style.display = rows.length > 1 ? 'inline-flex' : 'none'; });
}

// ── Document Rows ─────────────────────────────────
function addDocumentRow() {
  const c = document.getElementById('documents-container');
  const d = document.createElement('div');
  d.className = 'document-row';
  d.style.cssText = 'display:flex; gap:var(--space-md); align-items:flex-start; background:var(--color-surface-soft); padding:var(--space-md); border-radius:var(--radius-md); border:1px solid var(--color-border); margin-top:var(--space-sm);';
  d.innerHTML = `<div style="flex:1;"><label class="form-label" style="font-size:12px; margin-bottom:4px;">Nama / Deskripsi Dokumen <span class="required">*</span></label><input type="text" name="document_descriptions[]" class="form-control" placeholder="Contoh: RKS / HPS" required></div><div style="flex:1;"><label class="form-label" style="font-size:12px; margin-bottom:4px;">File PDF <span class="required">*</span></label><input type="file" name="document_files[]" accept=".pdf" class="form-control" required style="padding:6px 12px;" onchange="validatePdfFile(this)"></div><div style="align-self:flex-end; padding-bottom:2px;"><button type="button" onclick="removeDocumentRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>`;
  c.appendChild(d);
  updateDocDeleteButtons();
}
function removeDocumentRow(btn) { btn.closest('.document-row').remove(); updateDocDeleteButtons(); }
function updateDocDeleteButtons() {
  const rows = document.querySelectorAll('.document-row');
  rows.forEach(r => { const b = r.querySelector('button[onclick="removeDocumentRow(this)"]'); if (b) b.style.display = rows.length > 1 ? 'inline-flex' : 'none'; });
}

// ── PIC Rows ──────────────────────────────────────
function addPicRow() {
  const c = document.getElementById('pic-container');
  if (c.children.length >= 2) return;
  const d = document.createElement('div');
  d.className = 'pic-row';
  d.style.cssText = 'display:flex; gap:var(--space-sm); align-items:center; margin-top:var(--space-xs);';
  d.innerHTML = `<div style="flex:1;"><input type="text" name="pic_name[]" class="form-control" placeholder="Nama PIC" maxlength="255" required></div><button type="button" onclick="removePicRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus PIC"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>`;
  c.appendChild(d);
  updatePicDeleteButtons();
}
function removePicRow(btn) { btn.closest('.pic-row').remove(); updatePicDeleteButtons(); }
function updatePicDeleteButtons() {
  const rows = document.querySelectorAll('.pic-row');
  rows.forEach(r => { const b = r.querySelector('button[onclick="removePicRow(this)"]'); if (b) b.style.display = rows.length > 1 ? 'inline-flex' : 'none'; });
  const ab = document.getElementById('btn-add-pic');
  if (ab) ab.style.display = rows.length >= 2 ? 'none' : 'inline-flex';
}

function validatePdfFile(input) {
  const file = input.files[0];
  if (file && !(file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf'))) {
    showToast('error', 'Format Tidak Valid', 'Hanya file PDF yang diperbolehkan.');
    input.value = '';
  }
}

// ── Upfront Smart Validation Preview — Gate Card Modal (Transkrip 2) ──
function runPreviewValidation() {
  const btn = document.getElementById('btn-preview-val');

  // Kumpulkan data form
  const title    = document.getElementById('title')?.value || '';
  const category = document.getElementById('category')?.value || '';
  const expType  = document.getElementById('expenditure_type')?.value || '';

  // Collect items from table
  const items = [];
  document.querySelectorAll('#items-body .item-row').forEach(row => {
    const nameInput  = row.querySelector('input[name*="[item_name]"]');
    const qtyInput   = row.querySelector('input[name*="[quantity]"]');
    const priceInput = row.querySelector('.item-price-raw');
    if (nameInput) {
      items.push({
        item_name:  nameInput.value,
        quantity:   qtyInput?.value || 1,
        unit_price: priceInput?.value || 0,
      });
    }
  });

  if (!category || !expType) {
    if (typeof showToast === 'function') {
      showToast('warning', 'Data Belum Lengkap', 'Harap pilih Jenis Pengeluaran dan Kategori terlebih dahulu.');
    }
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Mengecek...</span>';

  fetch('{{ route("tickets.preview-validation") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
    },
    body: JSON.stringify({ title, category, expenditure_type: expType, items }),
  })
  .then(r => r.json())
  .then(data => {
    renderGateCardModal(data);
    openModal('modal-prevalidation-result');
  })
  .catch(() => {
    if (typeof showToast === 'function') {
      showToast('error', 'Koneksi Gagal', 'Tidak dapat menghubungi server. Coba beberapa saat lagi.');
    }
    document.getElementById('btn-submit-ticket').style.display = 'none';
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Cek Validasi Dulu';
  });
}

function renderGateCardModal(data) {
  const container = document.getElementById('pv-gates-container');
  const itemsContainer = document.getElementById('pv-items-container');
  const itemsBody = document.getElementById('pv-items-body');
  const submitBtn = document.getElementById('btn-submit-ticket');
  const gates = data.gates || {};

  // Label dan ikon per Gate
  const gateMeta = {
    1: { label: 'Gate 1 — Duplikasi Pengajuan',    icon: '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2"/><rect x="2" y="9" width="6" height="6" rx="1"/></svg>' },
    2: { label: 'Gate 2 — Validasi Nominal',       icon: '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>' },
    3: { label: 'Gate 3 — Klasifikasi CAPEX/OPEX', icon: '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>' },
    4: { label: 'Gate 4 — Ketersediaan Anggaran',  icon: '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>' },
  };

  const statusConfig = {
    pass:    { bg: 'var(--color-success-soft, #d1fae5)', border: 'var(--color-success, #10b981)', badge: '#10b981', label: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg> Lolos' },
    warning: { bg: 'var(--color-warning-soft, #fef3c7)', border: 'var(--color-warning, #f59e0b)', badge: '#f59e0b', label: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg> Peringatan' },
    info:    { bg: 'var(--color-primary-soft, #eff6ff)',  border: 'var(--color-primary, #3b82f6)', badge: '#3b82f6', label: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg> Info' },
    fail:    { bg: 'var(--color-danger-soft, #fee2e2)',  border: 'var(--color-danger, #ef4444)', badge: '#ef4444', label: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg> Gagal' },
    skipped: { bg: 'var(--color-surface-soft)',           border: 'var(--color-border)',           badge: '#9ca3af', label: '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/></svg> Dilewati' },
  };

  let hasFailure = false;
  let html = '';

  [1, 2, 3, 4].forEach(n => {
    const gate = gates[n] || { status: 'skipped', message: '-' };
    const meta = gateMeta[n];
    const cfg  = statusConfig[gate.status] || statusConfig.skipped;
    if (gate.status === 'fail') hasFailure = true;

    html += `
      <div style="background:${cfg.bg}; border:1px solid ${cfg.border}; border-radius:var(--radius-md); padding:12px 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
          <span style="font-weight:700; font-size:13px;">${meta.icon} ${meta.label}</span>
          <span style="font-size:11px; font-weight:600; color:${cfg.badge}; background:rgba(0,0,0,0.05); padding:2px 8px; border-radius:99px;">${cfg.label}</span>
        </div>
        <div style="font-size:13px; color:var(--color-text); line-height:1.5;">${gate.message}</div>
      </div>`;
  });

  container.innerHTML = html;

  // Render per-item classification table
  const perItems = data.per_item_classification || [];
  if (perItems.length > 0) {
    let rowHtml = '';
    perItems.forEach(item => {
      const typeColor = item.suggested_type === 'CAPEX' ? 'var(--color-primary, #3b82f6)' : 'var(--color-warning, #f59e0b)';
      rowHtml += `
        <tr>
          <td style="padding:7px 10px; border-bottom:1px solid var(--color-border);">${item.item_name || '-'}</td>
          <td style="padding:7px 10px; text-align:center; border-bottom:1px solid var(--color-border);">
            <span style="font-size:11px; font-weight:700; color:${typeColor}; background:rgba(0,0,0,0.05); padding:2px 8px; border-radius:99px;">${item.suggested_type || '?'}</span>
          </td>
          <td style="padding:7px 10px; text-align:right; border-bottom:1px solid var(--color-border); font-weight:600;">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</td>
        </tr>`;
    });
    itemsBody.innerHTML = rowHtml;
    itemsContainer.style.display = 'block';
  } else {
    itemsContainer.style.display = 'none';
  }

  // Show/hide submit button based on failures
  if (!hasFailure) {
    submitBtn.style.display = 'inline-flex';
    document.getElementById('ds-consent-container').style.display = 'block';
    document.getElementById('ds-consent-create').checked = false;
    submitBtn.disabled = true;
  } else {
    submitBtn.style.display = 'none';
    document.getElementById('ds-consent-container').style.display = 'none';
  }
}

function toggleSubmitTicketBtn() {
  document.getElementById('btn-submit-ticket').disabled = !document.getElementById('ds-consent-create').checked;
}

// btn-submit-ticket is now inside the modal — submit the form programmatically
function submitTicketForm() {
  localStorage.removeItem('ticket_form_draft');
  document.getElementById('ticket-form').submit();
}

// Reset validation state on form changes to enforce re-checking
document.getElementById('ticket-form').addEventListener('input', function(e) {
  if (e.target.name === 'document_files[]' || e.target.name === 'document_descriptions[]') return; // documents don't affect budget
  document.getElementById('btn-submit-ticket').style.display = 'none';
  saveFormDraft();
});

document.getElementById('ticket-form').addEventListener('change', function(e) {
  if (e.target.name === 'document_files[]' || e.target.name === 'document_descriptions[]') return;
  document.getElementById('btn-submit-ticket').style.display = 'none';
  saveFormDraft();
});
document.getElementById('ticket-form').addEventListener('submit', function() {
  localStorage.removeItem('ticket_form_draft');
});

// ── Auto-save Form Draft Functions (Transkrip 2) ──
function saveFormDraft() {
  const title = document.getElementById('title')?.value || '';
  const expType = document.getElementById('expenditure_type')?.value || '';
  const category = document.getElementById('category')?.value || '';
  const vendor = document.getElementById('vendor_name')?.value || '';
  const desc = document.getElementById('description')?.value || '';

  const pics = [];
  document.querySelectorAll('input[name="pic_name[]"]').forEach(input => {
    if (input.value.trim()) pics.push(input.value.trim());
  });

  const items = [];
  document.querySelectorAll('.item-row').forEach(row => {
    const inputs = row.querySelectorAll('input');
    if (inputs.length >= 3) {
      items.push({
        item_name: inputs[0].value,
        quantity: inputs[1].value,
        unit_price: row.querySelector('.item-price-raw')?.value || ''
      });
    }
  });

  const draft = { title, expenditure_type: expType, category, vendor_name: vendor, description: desc, pics, items };
  localStorage.setItem('ticket_form_draft', JSON.stringify(draft));
}

function restoreFormDraft() {
  const saved = localStorage.getItem('ticket_form_draft');
  if (!saved) return;

  try {
    const draft = JSON.parse(saved);
    if (!draft.title && !draft.expenditure_type && !draft.category && !draft.vendor_name && !draft.description && (!draft.items || draft.items.length === 0)) {
      return;
    }

    if (draft.title) document.getElementById('title').value = draft.title;
    if (draft.expenditure_type) {
      document.getElementById('expenditure_type').value = draft.expenditure_type;
      filterCategories(draft.expenditure_type);
    }
    if (draft.category) document.getElementById('category').value = draft.category;
    if (draft.vendor_name) document.getElementById('vendor_name').value = draft.vendor_name;
    if (draft.description) document.getElementById('description').value = draft.description;

    // Restore PICs
    if (draft.pics && draft.pics.length > 0) {
      const container = document.getElementById('pic-container');
      container.innerHTML = '';
      draft.pics.forEach((pic, i) => {
        const d = document.createElement('div');
        d.className = 'pic-row';
        d.style.cssText = 'display:flex; gap:var(--space-sm); align-items:center; margin-top:var(--space-xs);';
        d.innerHTML = `<div style="flex:1;"><input type="text" name="pic_name[]" class="form-control" value="${pic}" placeholder="Nama PIC" maxlength="255" required></div>` +
                      (i > 0 ? `<button type="button" onclick="removePicRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus PIC"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>` : '');
        container.appendChild(d);
      });
      updatePicDeleteButtons();
    }

    // Restore Items
    if (draft.items && draft.items.length > 0) {
      const tbody = document.getElementById('items-body');
      tbody.innerHTML = '';
      draft.items.forEach((item, idx) => {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        const formattedPrice = item.unit_price ? parseInt(item.unit_price).toLocaleString('id-ID') : '';
        tr.innerHTML = `
          <td style="padding:8px 12px; font-size:13px; color:var(--color-muted);" class="item-no">${idx + 1}</td>
          <td style="padding:6px 8px;"><input type="text" name="items[${idx}][item_name]" class="form-control" value="${item.item_name}" placeholder="Nama barang / jasa" required maxlength="255"></td>
          <td style="padding:6px 8px;"><input type="number" name="items[${idx}][quantity]" class="form-control item-qty" value="${item.quantity}" min="1" style="text-align:center;" onchange="recalcRow(this)" required></td>
          <td style="padding:6px 8px;"><input type="text" class="form-control item-price-display" value="${formattedPrice}" placeholder="0" style="text-align:right;" oninput="formatItemPrice(this)"><input type="hidden" name="items[${idx}][unit_price]" class="item-price-raw" value="${item.unit_price}"></td>
          <td style="padding:6px 12px; text-align:right; font-size:13px; font-weight:600;" class="item-subtotal">Rp 0</td>
          <td style="padding:6px 8px; text-align:center;"><button type="button" onclick="removeItemRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
        `;
        tbody.appendChild(tr);
      });
      itemCounter = draft.items.length;
      updateItemNumbers();
      updateItemDeleteButtons();
      recalcAll();
    }

    if (typeof showToast === 'function') {
      showToast('info', 'Draf Dipulihkan', 'Melanjutkan pengisian formulir sebelumnya...');
    } else {
      console.log('Form draft restored.');
    }
  } catch (e) {
    console.error('Failed to restore form draft', e);
  }
}

// Restore draft on load
window.addEventListener('DOMContentLoaded', () => {
  restoreFormDraft();
});
</script>
@endpush
@endsection

