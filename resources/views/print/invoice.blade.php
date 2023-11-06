@extends('print')

@section('body')
<div class="form-invoice q-pa-sm q-gutter-md">
  <div class="print-paper-wrapper A4">
    <div class="print-paper">
      <div class="paper-content">
        <div class="invoice-header">
          <div class="invoice-header-inner">
            <div class="company-logo-wrapper">
              <img src="/img/artacom.png" alt="artacom logo" class="company-logo">
            </div>
            <div class="invoice-header-company">
              <h1 class="company-name">Artacomindo Jejaring Nusa</h1>
              <p class="company-address">Menara Palma Lt. 12, Jl. HR. Rasuna Said Blok X2 Kav.6-Jakarta 12950</p>
            </div>
          </div>
          <div class="invoice-header-bottom"> INVOICE </div>
        </div>
        <div class="invoice-recipient">
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Kepada Yth/To </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <div class="invoice-recipient-company-name"> {{ $invoice->customer_name }} </div>
              <div class="invoice-recepient-company-address"> {{ $invoice->customer_address }} </div>
            </div>
          </div>
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Nomor Invoice/Invoice Number </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value"> {{ $invoice->invoice_no }} </div>
          </div>
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Tanggal/Date </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <span>{{ $invoice->published_at->format('d-F-Y') }}&nbsp;</span>
            </div>
          </div>
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Surat Penawaran/Our Ref. </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <span>{{ $invoice->offering_letter_reference_number }}</span>
            </div>
          </div>
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Surat Penawaran FO/Our Ref. </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <span>{{ $invoice->fo_offering_letter_reference_number }}&nbsp;</span>
            </div>
          </div>
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Tanggal Persetujuan SAT-HO </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <span>{{ $invoice->approval_date?->format('d-F-Y') }}&nbsp;</span>
            </div>
          </div>
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Tanggal Persetujuan SAT-HO untuk FO </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <span>{{ $invoice->fo_approval_date?->format('d-F-Y') }}&nbsp;</span>
            </div>
          </div>
          <div class="invoice-recipient-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Dasar untuk menerbitkan Invoice Tagihan </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <span>{{ $invoice->issuance_number }}&nbsp;</span>
            </div>
          </div>
          <div class="invoice-recipient-row last-row">
            <div class="invoice-recipient-key">
              <div class="invoice-recipient-label"> Dasar untuk menerbitkan Invoice Tagihan FO </div>
              <div class="invoice-recipient-colon">:</div>
            </div>
            <div class="invoice-recipient-value">
              <span>{{ $invoice->fo_issuance_number }}&nbsp;</span>
            </div>
          </div>
        </div>
        <div class="invoice-table">
          <div class="invoice-table-row invoice-table-header">
            <div class="invoice-table-left">
              <div class="invoice-table-no"> No. </div>
              <div class="invoice-table-description"> Description </div>
            </div>
            <div class="invoice-table-right">
              <div class="invoice-table-qty"> Qty </div>
              <div class="invoice-table-unit-price"> Unit Price </div>
              <div class="invoice-table-sub-total"> Sub Total </div>
            </div>
          </div>
          @foreach($invoice->invoiceServices as $i => $service)
          <div class="invoice-table-row form-invoice-service">
            <div class="invoice-table-left">
              <div class="invoice-table-no">{{ $i + 1 }}</div>
              <div class="invoice-table-description">
                <span>{{ $service->description }}&nbsp;</span>
              </div>
            </div>
            <div class="invoice-table-right">
              <div class="invoice-table-qty">
                <span>{{ $service->qty }}&nbsp;</span>
              </div>
              <div class="invoice-table-unit-price">
                <span>{{ is_null($service->unit_price) ? '' : number_format((int) $service->unit_price, 0) }}&nbsp;</span>
              </div>
              <div class="invoice-table-sub-total"> {{ is_null($service->sub_total) ? '' : number_format((int) is_null($service->sub_total) ? ($service->qty * $service->unit_price) : $service->sub_total, 0) }}&nbsp; </div>
            </div>
          </div>
          @endforeach

          @if (count($invoice->invoiceServices) < 4)
          <div class="invoice-table-row form-invoice-service {{ in_array(count($invoice->invoiceServices), [2, 3]) ? 'shrink' : '' }}">
            <div class="invoice-table-left">
              <div class="invoice-table-no">&nbsp;</div>
              <div class="invoice-table-description">
                <span>&nbsp;</span>
              </div>
            </div>
            <div class="invoice-table-right">
              <div class="invoice-table-qty">
                <span>&nbsp;</span>
              </div>
              <div class="invoice-table-unit-price">
                <span>&nbsp;</span>
              </div>
              <div class="invoice-table-sub-total"> &nbsp; </div>
            </div>
          </div>
          @endif
        </div>
        <div class="invoice-payment">
          <div class="invoice-payment-left">
            Pembayaran dapat ditransfer ke rekening: <br>
            {{ $bankTransferName }} <br>
            A/C {{ $bankTransferAccountNumber }} <br>
            A/N {{ $bankTransferAccountName }}
          </div>
          <div class="invoice-payment-right">
            <div class="invoice-payment-right-row">
              <div class="invoice-payment-right-key"> Sub Total </div>
              <div class="invoice-payment-right-value"> {{ is_null($invoice->sub_total) ? '' : number_format((int) $invoice->sub_total, 0) }}&nbsp; </div>
            </div>
            <div class="invoice-payment-right-row">
              <div class="invoice-payment-right-key"> VAT (PPN {{ (int) $invoice->ppn_percentage }}%) </div>
              <div class="invoice-payment-right-value"> {{ is_null($invoice->ppn_total) ? '' : number_format((int) $invoice->ppn_total, 0) }}&nbsp; </div>
            </div>
            <div class="invoice-payment-right-row">
              <div class="invoice-payment-right-key"> Bea Meterai </div>
              <div class="invoice-payment-right-value">
                <span>{{ is_null($invoice->stamp_duty) ? '' : number_format((int) $invoice->stamp_duty, 0) }}&nbsp;</span>
              </div>
            </div>
            <div class="invoice-payment-right-row last-row">
              <div class="invoice-payment-right-key"> Total </div>
              <div class="invoice-payment-right-value"> {{ is_null($invoice->total) ? '' : number_format((int) $invoice->total, 0) }}&nbsp; </div>
            </div>
          </div>
        </div>
        <div class="invoice-note">
          <div class="invoice-note-row"> &nbsp; </div>
          <div class="invoice-note-row"> Terbilang: <em class="transfer-text"># {{ terbilang($invoice->total) }} #</em>
          </div>
          <div class="invoice-note-row bold">
            <div class="invoice-note-row-split">
              <div class="invoice-note-row-split-left"> Term Of Payment </div>
              <div class="invoice-note-row-split-right">
                <div class="invoice-due-at-wrapper">
                  <span class="invoice-due-at-sep">:</span>
                  <span>{{ $invoice->due_at?->format('d-F-Y') }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="invoice-note-row">
            <div class="note-wrapper">
              <span class="note-label"> Catatan: </span>
              <div class="note-input-wrapper">
                <span>{!! $invoice->note !!}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="invoice-signature">
          <div class="invoice-signature-inner">
            <div class="invoice-signature-date"> Jakarta, {{ $invoice->published_at?->format('d-F-Y') }} </div>
            <div class="invoice-signature-person">
              <div class="invoice-signature-person-name"> ({{ $signatoryName }}) </div>
              <div class="invoice-signature-person-role"> {{ $signatoryPosition }} </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
