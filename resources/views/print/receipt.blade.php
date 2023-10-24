@extends('print')

@section('body')
<div class="form-receipt q-pa-sm q-gutter-md">
  <div class="print-paper-wrapper A4">
    <div class="print-paper">
      <div class="paper-content">
        <div class="receipt-header">
          <div class="company-logo-wrapper">
            <img src="/img/artacom.png" alt="artacom logo" class="company-logo">
          </div>
        </div>
        <div class="receipt-number">
          <div class="receipt-number-inner">
            <div class="receipt-number-label"> KWITANSI </div>
            <div class="receipt-number-content"> Nomor: {{ $invoice->receipt_no }} </div>
          </div>
        </div>
        <div class="receipt-detail">
          <div class="receipt-detail-row">
            <div class="receipt-detail-key">
              <div class="receipt-detail-label"> Sudah Terima dari </div>
              <div class="receipt-detail-colon"> : </div>
            </div>
            <div class="receipt-detail-value">
              <div class="receipt-detail-recipient-company-name"> {{ $invoice->customer_name }}&nbsp; </div>
              <div class="receipt-detail-recipient-company-address"> {{ $invoice->customer_address }}&nbsp; </div>
            </div>
          </div>
          <div class="receipt-detail-row">
            <div class="receipt-detail-key">
              <div class="receipt-detail-label"> Pekerjaan </div>
              <div class="receipt-detail-colon"> : </div>
            </div>
            <div class="receipt-detail-value">
              <span>{{ $invoice->receipt_remark }}&nbsp;</span>
            </div>
          </div>
          <div class="receipt-detail-row">
            <div class="receipt-detail-key">
              <div class="receipt-detail-label"> Jumlah </div>
              <div class="receipt-detail-colon"> : </div>
            </div>
            <div class="receipt-detail-value">
              <div class="receipt-detail-grand-total"> {{ is_null($invoice->total) ? '' : number_format((int) $invoice->total, 0) }}&nbsp; </div>
            </div>
          </div>
          <div class="receipt-detail-row">
            <div class="receipt-detail-key">
              <div class="receipt-detail-label"> Terbilang </div>
              <div class="receipt-detail-colon"> : </div>
            </div>
            <div class="receipt-detail-value">
              <div class="receipt-detail-grand-total-text"> # {{ terbilang($invoice->total) }} # </div>
            </div>
          </div>
        </div>
        <div class="receipt-date">
          <div class="receipt-date-inner"> Jakarta, {{ $invoice->published_at?->format('d-F-Y') }} </div>
        </div>
        <div class="receipt-footer">
          <div class="receipt-owner">
            <div class="receipt-owner-name"> Artacomindo Jejaring Nusa </div>
            <div class="receipt-owner-address"> Menara Palma Lt. 12, Jl. HR. Rasuna Said Blok X2 Kav.6-Jakarta 12950 </div>
            <div class="receipt-owner-contact"> Telp: 021-293911111, 894 5212, 894 4821 </div>
          </div>
          <div class="receipt-signature">
            <div class="receipt-signature-name"> ({{ $signatoryName }}) </div>
            <div class="receipt-signature-role"> {{ $signatoryPosition }} </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
