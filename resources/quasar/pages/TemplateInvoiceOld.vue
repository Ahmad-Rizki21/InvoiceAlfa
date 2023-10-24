<template>
  <q-page class="page-template page-template-invoice">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        {{ $t('Invoice') }}
      </q-toolbar-title>
    </portal>

    <div class="page-header">
      <q-toolbar-title>{{ $t('Template Invoice') }}</q-toolbar-title>
    </div>

    <div class="page-body">
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
              <div class="invoice-header-bottom">
                INVOICE
              </div>
            </div>

            <div class="invoice-recipient">
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Kepada Yth/To
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  <div class="invoice-recipient-company-name">
                    PT. SUMBER ALFARIA TRIJAYA, Tbk. Branch Balaraja
                  </div>
                  <div class="invoice-recepient-company-address">
                    Jl. Arya Jaya Santika No. 19, Kp. Seglok Desa Pasir Bolang Kec. Tigaraksa Kab. Tangerang
                  </div>
                </div>

              </div>
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Nomor Invoice/Invoice Number
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  1086/INV-AJNusa/09/2023
                </div>
              </div>
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Tanggal/Date
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  04-September-2023
                </div>
              </div>
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Surat Penawaran/Our Ref.
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  013/ARTACOM-SAT/SALES/2023
                </div>
              </div>
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Surat Penawaran FO/Our Ref.
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  030/ARTACOM-SAT/SALES/IV/2021
                </div>
              </div>
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Tanggal Persetujuan SAT-HO
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  05-April-2021
                </div>
              </div>
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Tanggal Persetujuan SAT-HO untuk FO
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  22-November-2022
                </div>
              </div>
              <div class="invoice-recipient-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Dasar untuk menerbitkan Invoice Tagihan
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  -
                </div>
              </div>
              <div class="invoice-recipient-row last-row">
                <div class="invoice-recipient-key">
                  <div class="invoice-recipient-label">
                    Dasar untuk menerbitkan Invoice Tagihan FO
                  </div>
                  <div class="invoice-recipient-colon">:</div>
                </div>
                <div class="invoice-recipient-value">
                  SAT-ARTACOM/IT/KONEKSI/X1/2022/CMII-339 tanggal 22
                </div>
              </div>
            </div>

            <div class="invoice-table">
              <div class="invoice-table-row invoice-table-header">
                <div class="invoice-table-left">
                  <div class="invoice-table-no">
                    No.
                  </div>
                  <div class="invoice-table-description">
                    Description
                  </div>
                </div>
                <div class="invoice-table-right">
                  <div class="invoice-table-qty">
                    Qty
                  </div>
                  <div class="invoice-table-unit-price">
                    Unit Price
                  </div>
                  <div class="invoice-table-sub-total">
                    Sub Total
                  </div>
                </div>
              </div>
              <div class="invoice-table-row">
                <div class="invoice-table-left">
                  <div class="invoice-table-no">
                    1
                  </div>
                  <div class="invoice-table-description">
                    Jasa Layanan Komunikasi SD WAN, bulan Agustus 2023 (nama-nama store dan periode bulan layanan terlampir)
                  </div>
                </div>
                <div class="invoice-table-right">
                  <div class="invoice-table-qty">
                    35
                  </div>
                  <div class="invoice-table-unit-price">
                    925,000
                  </div>
                  <div class="invoice-table-sub-total">
                    32,357,000
                  </div>
                </div>
              </div>
              <div class="invoice-table-row">
                <div class="invoice-table-left">
                  <div class="invoice-table-no">
                    2
                  </div>
                  <div class="invoice-table-description">
                    Jasa Layanan Komunikasi SD WAN, bulan Agustus 2023 (nama-nama store dan periode bulan layanan terlampir)
                  </div>
                </div>
                <div class="invoice-table-right">
                  <div class="invoice-table-qty">
                    6
                  </div>
                  <div class="invoice-table-unit-price">
                    900,000
                  </div>
                  <div class="invoice-table-sub-total">
                    5,400,000
                  </div>
                </div>
              </div>
              <div class="invoice-table-row last-row">
                <div class="invoice-table-left">
                  <div class="invoice-table-no">
                    3
                  </div>
                  <div class="invoice-table-description">
                    Penyesuaian nilai tagihan periode Maret - Agustus 2023
                  </div>
                </div>
                <div class="invoice-table-right">
                  <div class="invoice-table-qty">
                    1
                  </div>
                  <div class="invoice-table-unit-price">
                    10,536,250
                  </div>
                  <div class="invoice-table-sub-total">
                    10,536,250
                  </div>
                </div>
              </div>
            </div>

            <div class="invoice-payment">
              <div class="invoice-payment-left">
                Pembayaran dapat ditransfer ke rekening:<br>
                BANK CENTRAL ASIA - KCU KALIMALANG JAKARTA<br>
                A/C 2306079899<br>
                A/N PT.ARTACOMINDO JEJARING NUSA
              </div>
              <div class="invoice-payment-right">
                <div class="invoice-payment-right-row">
                  <div class="invoice-payment-right-key">
                    Sub Total
                  </div>
                  <div class="invoice-payment-right-value">
                    48,311,250
                  </div>
                </div>
                <div class="invoice-payment-right-row">
                  <div class="invoice-payment-right-key">
                    VAT (PPN 11%)
                  </div>
                  <div class="invoice-payment-right-value">
                    5,314,238
                  </div>
                </div>
                <div class="invoice-payment-right-row">
                  <div class="invoice-payment-right-key">
                    {{ $t('Stamp Duty') }}
                  </div>
                  <div class="invoice-payment-right-value">
                    -
                  </div>
                </div>
                <div class="invoice-payment-right-row last-row">
                  <div class="invoice-payment-right-key">
                    Total
                  </div>
                  <div class="invoice-payment-right-value">
                    53,625,488
                  </div>
                </div>
              </div>
            </div>

            <div class="invoice-note">
              <div class="invoice-note-row">
                &nbsp;
              </div>
              <div class="invoice-note-row">
                Terbilang: <em class="transfer-text"># lima puluh tiga juta enam ratus dua puluh lima ribu empat ratus delapan puluh delapan rupiah #</em>
              </div>
              <div class="invoice-note-row bold">
                <div class="invoice-note-row-split">
                  <div class="invoice-note-row-split-left">
                    Term Of Payment
                  </div>
                  <div class="invoice-note-row-split-right">
                    : 30-September-2023
                  </div>
                </div>
              </div>
              <div class="invoice-note-row">
                Catatan: Keterlambatan Pembayaran menyebabkan ketidaknyamanan Pelayanan Jaringan ke Pelanggan
              </div>
              <div class="invoice-note-row">
                <strong>Note:</strong> Bukti transfer mohon dikirim via: <em class="transfer-note-to">(nazirin.nawawi@ajnusa.com)</em>
              </div>

            </div>

            <div class="invoice-signature">
              <div class="invoice-signature-inner">
                <div class="invoice-signature-date">
                  Jakarta, 04-September-2023
                </div>
                <div class="invoice-signature-person">
                  <div class="invoice-signature-person-name">
                    (Nazirin Nawawi)
                  </div>
                  <div class="invoice-signature-person-role">
                    Head Finance & Administrasi
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </q-page>
</template>

<script>
export default {
  name: 'PageTemplateInvoice',
  meta() {
    return {
      title: this.$t('Invoice') + '-' + this.$t('Template') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Template') },
      { text: this.$t('Invoice') }
    ]
  },
	data() {
		return {

		};
	},
};
</script>

<style lang="scss">
.page-template-invoice {
  padding-bottom: 3rem;

  .paper-content {
    font-family: Arial, sans-serif;
  }

  .invoice-header {
    border-bottom: 1px solid #000;

    &-inner {
      display: flex;
    }

    &-bottom {
      width: 50%;
      margin-top: -12px;
      margin-left: auto;
      text-align: center;
      font-weight: 900;
    }

    .company-logo-wrapper {
      width: 120px;
    }

    .company-logo {
      width: 100%;
    }

    &-company {
      // margin-left: 60px;
      padding: 10px 30px 0 35px;

      > .company-name {
        font-size: 20px;
        font-weight: 400;
        margin: 0;
        line-height: 1.2;
      }

      > .company-address {
        font-size: 11px;
        margin: 0;
      }
    }
  }

  .invoice-recipient {
    font-size: 12px;

    &-row {
      display: flex;
      align-items: baseline;
      margin-top: -1px;

      &.last-row {
        .invoice-recipient-value {
          padding-bottom: 5px;
        }
        .invoice-recipient-key {
          padding-bottom: 0px;
        }
      }
    }

    &-key {
      display: flex;
      align-items: center;
      font-weight: 600;
      width: 50%;
      padding: 1px 5px 10px 5px;
    }

    &-colon {
      margin-left: auto;
    }

    &-value {
      padding: 0 1px 0 5px;
      width: 50%;
      border-left: 1px solid #000;
      padding-bottom: 10px;
    }

    &-company-name {
      padding-left: 5px;
      font-weight: 600;
    }
  }

  .invoice-table {
    border-top: 1px solid #000;
    font-size: 11px;

    &-row {
      display: flex;

      &.invoice-table-header {
        font-weight: 600;
        text-align: center;
        background-color: #ddd;
        font-size: 14px;
        border-bottom: 1px solid #000;

        .invoice-table {
          &-no {
            text-align: center;
            padding-bottom: 0;
          }
          &-description {
            text-align: center;
            padding-bottom: 0;
          }
          &-qty {
            text-align: center;
            padding-bottom: 0;
          }
          &-unit-price {
            text-align: center;
            padding-bottom: 0;
          }
          &-sub-total {
            text-align: center;
            padding-bottom: 0;
          }
        }
      }

      &.last-row {
        .invoice-table {
          &-no {
            padding-bottom: 45px;
          }
          &-description {
            padding-bottom: 45px;
          }
          &-qty {
            padding-bottom: 45px;
          }
          &-unit-price {
            padding-bottom: 45px;
          }
          &-sub-total {
            padding-bottom: 45px;
          }
        }
      }
    }

    &-left {
      display: flex;
      width: 50%;
    }

    &-right {
      display: flex;
      width: 50%;
    }

    &-no {
      width: 16mm;
      text-align: center;
      padding-bottom: 27px;
    }

    &-description {
      width: 89mm;
      border-left: 1px solid #000;
      padding-left: 5px;
      padding-bottom: 27px;
    }

    &-qty {
      width: 21mm;
      border-left: 1px solid #000;
      text-align: center;
      padding-bottom: 27px;
    }

    &-unit-price {
      width: 42mm;
      border-left: 1px solid #000;
      padding-right: 10px;
      text-align: right;
      padding-bottom: 27px;
    }

    &-sub-total {
      width: 42mm;
      border-left: 1px solid #000;
      padding-right: 10px;
      text-align: right;
      padding-bottom: 27px;
    }
  }

  .invoice-payment {
    font-size: 11px;
    display: flex;
    border-top: 1px solid #000;

    &-left {
      width: 50%;
      font-weight: 600;
      padding: 10px 5px 5px 15px;
    }

    &-right {
      width: 50%;
      font-size: 11px;

      &-row {
        display: flex;
        align-items: center;
        width: 100%;
        border-left: 1px solid #000;
        border-bottom: 1px solid #000;

        &.last-row {
          border-bottom: 0;
        }
      }

      &-key {
        width: 50%;
        padding: 2px 2px 2px 5px;
      }

      &-value {
        width: 50%;
        border-left: 1px solid #000;
        text-align: right;
        padding: 2px 10px 2px 5px;
      }
    }
  }

  .invoice-note {
    font-size: 11px;
    border-top: 1px solid #000;

    &-row {
      border-bottom: 1px solid #000;
      padding: 2px 2px 5px 5px;

      &.bold {
        font-weight: 600;
      }

      &-split {
        display: flex;
        width: 100%;

        &-left {
          width: 50%;
        }

        &-right {
          width: 50%;
        }
      }

      .transfer-text {
        font-style: italic;
      }

      .transfer-note-to {
        font-style: italic;
        color: #3355dd;
      }
    }
  }

  .invoice-signature {
    &-inner {
      width: 50%;
      margin-left: auto;
      font-size: 12px;
      padding-top: 6mm;
      padding-bottom: 10mm;
    }

    &-date {
      text-align: center;
      margin-left: 7mm;
      height: 25mm;
    }

    &-person {
      text-align: center;

      &-name {
        font-weight: 600;
        text-decoration: underline;
        line-height: 1.7;
      }
    }
  }
}
</style>
