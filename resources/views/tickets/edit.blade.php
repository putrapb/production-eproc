@extends('layouts.app')

@section('title', 'Edit Tiket — Tiket #' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT))

@section('breadcrumb')
  <a href="{{ route('tickets.index') }}" class="breadcrumb-item">Tiket Pengadaan</a>
  <span class="breadcrumb-sep">/</span>
  <a href="{{ route('tickets.show', $ticket) }}" class="breadcrumb-item">Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-active">Edit Tiket</span>
@endsection

@section('content')
<div class="page-header">
  <div class="page-header-left">
    <h1>Edit Tiket Pengadaan</h1>
    <p>Tiket #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }} — {{ $ticket->title }}</p>
  </div>
  <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Batal & Kembali
  </a>
</div>

<div class="page-content">

  @if($ticket->approvalLogs->where('action', 'rejected_document')->isNotEmpty() || $ticket->approvalLogs->where('action', 'rejected')->isNotEmpty())
    @php 
      $lastLog = $ticket->approvalLogs->whereIn('action', ['rejected_document', 'rejected'])->sortByDesc('created_at')->first(); 
    @endphp
    @if($lastLog?->notes)
    <div class="alert alert-warning mb-lg" style="align-items:flex-start;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path d="M12 9v4M12 16h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <div>
        <strong style="display:block; margin-bottom:4px;">Catatan Penolakan / Revisi:</strong>
        <div style="white-space:pre-line; font-size:13.5px; line-height:1.5; color:var(--color-warning-text);">{{ $lastLog->notes }}</div>
        <div class="caption" style="margin-top:8px; opacity:0.85;">Oleh {{ $lastLog->user->name }} pada {{ $lastLog->created_at->format('d M Y, H:i') }}</div>
      </div>
    </div>
    @endif
  @endif

  <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data" id="ticket-form">
    @csrf
    @method('PUT')

    <div class="card">
      <div class="card-body">

        {{-- SECTION: Informasi Pengadaan --}}
        <div class="detail-section-title">Informasi Pengadaan</div>

        <div class="form-group">
          <label class="form-label" for="title">Judul Pengajuan <span class="required">*</span></label>
          <input type="text" id="title" name="title"
            class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
            value="{{ old('title', $ticket->title) }}"
            placeholder="Contoh: Pengadaan Storage Array untuk DC Pejompongan"
            required maxlength="255" readonly style="background:var(--color-surface-soft); cursor:not-allowed;">
          @error('title') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="expenditure_type">Jenis Pengeluaran <span class="required">*</span></label>
            <select id="expenditure_type" name="expenditure_type"
              class="form-control {{ $errors->has('expenditure_type') ? 'is-invalid' : '' }}"
              onchange="filterCategories(this.value)" required>
              <option value="" disabled {{ old('expenditure_type', $ticket->expenditure_type) ? '' : 'selected' }}>Pilih CAPEX / OPEX...</option>
              <option value="CAPEX" {{ old('expenditure_type', $ticket->expenditure_type) === 'CAPEX' ? 'selected' : '' }}>CAPEX — Aset / Investasi Baru</option>
              <option value="OPEX"  {{ old('expenditure_type', $ticket->expenditure_type) === 'OPEX'  ? 'selected' : '' }}>OPEX — Operasional / Pemeliharaan</option>
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
                $selectedCategory = old('category', $ticket->category);
              @endphp
              @foreach($capexCats as $key => $label)
                <option value="{{ $key }}" data-type="CAPEX" {{ $selectedCategory === $key ? 'selected' : '' }} style="display:none;">{{ $label }}</option>
              @endforeach
              @foreach($opexCats as $key => $label)
                <option value="{{ $key }}" data-type="OPEX" {{ $selectedCategory === $key ? 'selected' : '' }} style="display:none;">{{ $label }}</option>
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
              value="{{ old('vendor_name', $ticket->vendor_name) }}"
              placeholder="Contoh: PT Astra Graphia Tbk"
              required maxlength="255" readonly style="background:var(--color-surface-soft); cursor:not-allowed;">
            @error('vendor_name') <div class="form-error">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; height:20px;">
              <label class="form-label" style="margin-bottom:0;">PIC (Person In Charge) <span class="required">*</span></label>
              @php $oldPics = old('pic_name', is_array($ticket->pic_name) ? $ticket->pic_name : [$ticket->pic_name]); if (!is_array($oldPics)) $oldPics = [$oldPics]; @endphp
              <button type="button" id="btn-add-pic" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:11px; display:none;">+ Tambah PIC</button>
            </div>
            <div id="pic-container" style="display:flex; flex-direction:column; gap:var(--space-xs);">
              @foreach($oldPics as $index => $pic)
              <div class="pic-row" style="display:flex; gap:var(--space-sm); align-items:center;">
                <div style="flex:1;">
                  <input type="text" name="pic_name[]" class="form-control" value="{{ $pic }}" placeholder="Nama PIC" maxlength="255" required readonly style="background:var(--color-surface-soft); cursor:not-allowed;">
                </div>
                <button type="button" class="btn btn-danger btn-icon btn-sm" style="display:none;" title="Hapus PIC">
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
            placeholder="Jelaskan kebutuhan, spesifikasi, dan urgensi pengadaan ini..." readonly style="background:var(--color-surface-soft); cursor:not-allowed;">{{ old('description', $ticket->description) }}</textarea>
          @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <hr class="divider">

        {{-- SECTION: Daftar Item --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-md);">
          <div class="detail-section-title" style="margin-bottom:0;">Daftar Item Pengadaan <span class="required">*</span></div>
          <button type="button" id="btn-add-item" class="btn btn-outline btn-sm" style="display:none;">
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
                <th style="padding:10px 12px; text-align:right; border-bottom:1px solid var(--color-border); width:140px;">Subtotal</th>
                <th style="padding:10px 12px; text-align:center; border-bottom:1px solid var(--color-border); width:105px;" title="Klasifikasi anggaran per item: CAPEX atau OPEX">Klasifikasi <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></th>
                <th style="padding:10px 12px; border-bottom:1px solid var(--color-border); width:50px;"></th>
              </tr>
            </thead>
            <tbody id="items-body">
              @php $oldItems = old('items', $ticket->items->toArray()); if(empty($oldItems)) $oldItems = [[]]; @endphp
              @foreach($oldItems as $i => $oldItem)
                @php
                  // Determine the per-item expenditure_type:
                  // Priority: old() form input > item's own expenditure_type > parent ticket's type
                  $itemExpType = old("items.$i.expenditure_type",
                      ($oldItem['expenditure_type'] ?? null) ?: ($ticket->expenditure_type ?? ''));
                @endphp
                <tr class="item-row">
                  <td style="padding:8px 12px; font-size:13px; color:var(--color-muted);" class="item-no">{{ $i + 1 }}</td>
                  <td style="padding:6px 8px;">
                    <input type="text" name="items[{{ $i }}][item_name]"
                      class="form-control {{ $errors->has("items.$i.item_name") ? 'is-invalid' : '' }}"
                      value="{{ $oldItem['item_name'] ?? '' }}"
                      placeholder="Nama barang / jasa" required maxlength="255" readonly style="background:var(--color-surface-soft); cursor:not-allowed;">
                  </td>
                  <td style="padding:6px 8px;">
                    <input type="number" name="items[{{ $i }}][quantity]"
                      class="form-control item-qty {{ $errors->has("items.$i.quantity") ? 'is-invalid' : '' }}"
                      value="{{ $oldItem['quantity'] ?? 1 }}"
                      min="1" style="text-align:center; background:var(--color-surface-soft); cursor:not-allowed;" required readonly>
                  </td>
                  <td style="padding:6px 8px;">
                    <input type="text" class="form-control item-price-display {{ $errors->has("items.$i.unit_price") ? 'is-invalid' : '' }}"
                      value="{{ isset($oldItem['unit_price']) ? number_format($oldItem['unit_price'], 0, ',', '.') : '' }}"
                      placeholder="0" style="text-align:right; background:var(--color-surface-soft); cursor:not-allowed;" readonly>
                    <input type="hidden" name="items[{{ $i }}][unit_price]" class="item-price-raw" value="{{ $oldItem['unit_price'] ?? '' }}">
                  </td>
                  <td style="padding:6px 12px; text-align:right; font-size:13px; font-weight:600;" class="item-subtotal">
                    Rp {{ (isset($oldItem['unit_price']) && isset($oldItem['quantity'])) ? number_format($oldItem['unit_price'] * $oldItem['quantity'], 0, ',', '.') : '0' }}
                  </td>
                  {{-- Editable: per-item CAPEX/OPEX classification --}}
                  <td style="padding:4px 6px;">
                    <select name="items[{{ $i }}][expenditure_type]" class="form-control" style="font-size:12px; padding:5px 8px; min-width:90px;" required
                      title="Klasifikasi anggaran untuk item ini">
                      <option value="CAPEX" {{ $itemExpType === 'CAPEX' ? 'selected' : '' }}>CAPEX</option>
                      <option value="OPEX"  {{ $itemExpType === 'OPEX'  ? 'selected' : '' }}>OPEX</option>
                    </select>
                  </td>
                  <td style="padding:6px 8px; text-align:center;">
                    <button type="button" class="btn btn-danger btn-icon btn-sm" title="Hapus" style="display:none;">
                      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr style="background:var(--color-surface-soft);">
                <td colspan="5" style="padding:12px; text-align:right; font-size:13px; font-weight:700; color:var(--color-muted);">TOTAL KESELURUHAN</td>
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
        
        <div style="display:flex; flex-direction:column; gap:var(--space-md); margin-bottom:var(--space-xl);">
          @foreach($ticket->documents as $doc)
            <div style="background:var(--color-surface-soft); padding:var(--space-md); border-radius:var(--radius-md); border:1px solid var(--color-border);">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-xs);">
                <span style="font-weight:600; font-size:14px; color:var(--color-text);">{{ $doc->description }}</span>
                @if($doc->isAccepted())
                  <span class="badge" style="background:var(--color-success-soft); color:var(--color-success); font-weight:600; display:inline-flex; align-items:center; gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Disetujui</span>
                @elseif($doc->isRejected())
                  <span class="badge" style="background:var(--color-error-soft); color:var(--color-error); font-weight:600; display:inline-flex; align-items:center; gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Perlu Revisi</span>
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
                  <label class="form-label" style="font-size:12px; margin-bottom:4px; color:var(--color-muted);">Unggah File Baru untuk mengganti file lama (PDF, Maks. 10MB)</label>
                  <input type="file" name="document_files[{{ $doc->id }}]" accept=".pdf" class="form-control" style="padding:6px 12px;" onchange="validatePdfFile(this)">
                  <div style="font-size:11.5px; color:var(--color-muted); margin-top:4px;">Kosongkan jika tidak ingin mengubah dokumen ini.</div>
                </div>
              @else
                <div style="margin-top:6px; display:flex; align-items:center; justify-content:space-between;">
                  <span class="caption text-muted">Tidak memerlukan revisi.</span>
                  <a href="{{ route('tickets.document', ['ticketDocument' => $doc->id]) }}" target="_blank" class="btn btn-ghost btn-sm" style="padding:4px 8px; font-size:11px;">Lihat Dokumen Saat Ini</a>
                </div>
              @endif
            </div>
          @endforeach
        </div>

        <div class="detail-section-title" style="font-size:14px; margin-top:16px;">Tambah Dokumen Baru <span style="font-weight:400; font-size:12px; color:var(--color-muted);">(Opsional)</span></div>
        <div class="form-group">
          <div id="new-documents-container" style="display:flex; flex-direction:column; gap:var(--space-md);"></div>
          <button type="button" onclick="addNewDocumentRow()" class="btn btn-outline btn-sm" style="margin-top:var(--space-md); display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
            Tambah Dokumen Ekstra
          </button>
        </div>

      </div>
      <div class="card-footer" style="display:flex; justify-content:flex-end; gap:var(--space-sm); flex-wrap:wrap;">
        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Simpan Revisi Tiket
        </button>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
  function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID').format(number);
  }

  function parseRupiah(str) {
    return parseInt(str.replace(/[^0-9]/g, '')) || 0;
  }

  function formatItemPrice(input) {
    const rawVal = parseRupiah(input.value);
    input.value = rawVal === 0 ? '' : formatRupiah(rawVal);
    
    // Update hidden field
    const row = input.closest('.item-row');
    row.querySelector('.item-price-raw').value = rawVal === 0 ? '' : rawVal;
    
    recalcRow(input);
  }

  function recalcRow(el) {
    const row = el.closest('.item-row');
    const qty = parseInt(row.querySelector('.item-qty').value) || 0;
    const price = parseInt(row.querySelector('.item-price-raw').value) || 0;
    
    const subtotal = qty * price;
    row.querySelector('.item-subtotal').textContent = 'Rp ' + formatRupiah(subtotal);
    
    recalcGrandTotal();
  }

  function recalcGrandTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
      const qty = parseInt(row.querySelector('.item-qty').value) || 0;
      const price = parseInt(row.querySelector('.item-price-raw').value) || 0;
      total += (qty * price);
    });
    document.getElementById('grand-total').textContent = 'Rp ' + formatRupiah(total);
  }

  function addItemRow() {
    const tbody = document.getElementById('items-body');
    const rows = tbody.querySelectorAll('.item-row');
    if (rows.length >= 9) {
      showToast('error', 'Batas Maksimal', 'Anda hanya dapat menambahkan maksimal 9 item.');
      return;
    }
    
    const nextIdx = rows.length;
    const newRow = document.createElement('tr');
    newRow.className = 'item-row';
    newRow.innerHTML = `
      <td style="padding:8px 12px; font-size:13px; color:var(--color-muted);" class="item-no">${nextIdx + 1}</td>
      <td style="padding:6px 8px;">
        <input type="text" name="items[${nextIdx}][item_name]" class="form-control" placeholder="Nama barang / jasa" required maxlength="255">
      </td>
      <td style="padding:6px 8px;">
        <input type="number" name="items[${nextIdx}][quantity]" class="form-control item-qty" value="1" min="1" style="text-align:center;" onchange="recalcRow(this)" required>
      </td>
      <td style="padding:6px 8px;">
        <input type="text" class="form-control item-price-display" placeholder="0" style="text-align:right;" oninput="formatItemPrice(this)">
        <input type="hidden" name="items[${nextIdx}][unit_price]" class="item-price-raw" value="">
      </td>
      <td style="padding:6px 12px; text-align:right; font-size:13px; font-weight:600;" class="item-subtotal">Rp 0</td>
      <td style="padding:6px 8px; text-align:center;">
        <button type="button" onclick="removeItemRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </td>
    `;
    tbody.appendChild(newRow);
    
    // Tampilkan semua tombol hapus jika row > 1
    updateRemoveButtons();
  }

  function removeItemRow(btn) {
    const tbody = document.getElementById('items-body');
    if (tbody.querySelectorAll('.item-row').length > 1) {
      btn.closest('.item-row').remove();
      updateRowNumbers();
      recalcGrandTotal();
      updateRemoveButtons();
    }
  }

  function updateRowNumbers() {
    document.querySelectorAll('#items-body .item-row').forEach((row, idx) => {
      row.querySelector('.item-no').textContent = idx + 1;
      
      // Update name attributes to keep array contiguous
      row.querySelector('input[name$="[item_name]"]').name = `items[${idx}][item_name]`;
      row.querySelector('input[name$="[quantity]"]').name = `items[${idx}][quantity]`;
      row.querySelector('input[name$="[unit_price]"]').name = `items[${idx}][unit_price]`;
    });
  }

  function updateRemoveButtons() {
    const btns = document.querySelectorAll('#items-body .btn-danger');
    const display = btns.length > 1 ? 'inline-flex' : 'none';
    btns.forEach(btn => btn.style.display = display);
  }

  function filterCategories(expType) {
    const catSelect = document.getElementById('category');
    const options = catSelect.querySelectorAll('option[data-type]');
    let firstMatch = null;
    let foundCurrent = false;
    const currentVal = catSelect.value;
    
    options.forEach(opt => {
      if (opt.getAttribute('data-type') === expType) {
        opt.style.display = 'block';
        if (!firstMatch) firstMatch = opt;
        if (opt.value === currentVal) foundCurrent = true;
      } else {
        opt.style.display = 'none';
      }
    });

    if (!foundCurrent && firstMatch) {
      catSelect.value = firstMatch.value;
    }
  }

  function addPicRow() {
    const container = document.getElementById('pic-container');
    if (container.children.length >= 2) return;
    
    const div = document.createElement('div');
    div.className = 'pic-row';
    div.style.cssText = 'display:flex; gap:var(--space-sm); align-items:center;';
    div.innerHTML = `
      <div style="flex:1;">
        <input type="text" name="pic_name[]" class="form-control" placeholder="Nama PIC Kedua" maxlength="255" required>
      </div>
      <button type="button" onclick="removePicRow(this)" class="btn btn-danger btn-icon btn-sm" title="Hapus PIC">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </button>
    `;
    container.appendChild(div);
    
    document.getElementById('btn-add-pic').style.display = 'none';
    
    const removeBtns = container.querySelectorAll('.btn-danger');
    removeBtns.forEach(btn => btn.style.display = 'inline-flex');
  }

  function removePicRow(btn) {
    const container = document.getElementById('pic-container');
    btn.closest('.pic-row').remove();
    
    document.getElementById('btn-add-pic').style.display = 'inline-flex';
    
    if (container.children.length === 1) {
      container.querySelector('.btn-danger').style.display = 'none';
    }
  }

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
      if(typeof showToast === 'function') {
          showToast('error', 'Format Tidak Valid', 'Hanya file PDF yang diperbolehkan.');
      } else {
          alert('Format Tidak Valid: Hanya file PDF yang diperbolehkan.');
      }
      input.value = '';
    }
  }

  // Initialize
  document.addEventListener('DOMContentLoaded', () => {
    const expType = document.getElementById('expenditure_type').value;
    if (expType) filterCategories(expType);
    recalcGrandTotal();
  });
</script>
@endpush
@endsection
