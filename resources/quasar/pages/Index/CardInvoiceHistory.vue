<template>
  <div class="invoice-history-wrapper">
    <div class="card-label">
      Riwayat Tagihan
    </div>

    <q-card flat class="card-invoice-history">
      <q-card-section>
        <div class="bill-item" v-for="i in 18" :key="i">
          <div class="date">
            <span>{{ $t('Month') }}</span>
            <div>Oktober 2023</div>
          </div>
          <div class="invoice">
            <span>
              Invoice

              <router-link to="/invoice/1">
                <q-icon
                  name="chevron_right"
                />
              </router-link>
            </span>
            <div>00001/INV-AJNusa/10/2023</div>
          </div>
          <div class="status">
            <span>Tanggal Bayar</span>
            <div>21 Okt 2023</div>
          </div>
          <div class="total-bill">
            <span class="total-bill-label">
              Total Tagihan
            </span>
            <div class="total-bill-amount">
              Rp. 150.000.000
            </div>
          </div>
          <div class="action">
            <q-icon
              name="chevron_right"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
export default {
  name: 'CardInvoiceHistory',
  data() {
    return {
      invoices: [],
      isLoading: true
    }
  },
  computed: {
    invoice() {
      return this.invoices[0]
    }
  }
}
</script>

<style lang="scss">
.invoice-history-wrapper {

  .card-label {
    font-size: 1.2em;
    font-weight: 500;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    color: #333;
    margin-bottom: 0.5rem;
  }

  .card-invoice-history {
    min-height: 24.5rem;
    min-height: 39vh;


    .total-bill {
      // text-align: right;
      // flex: 1;

      &-label {
        color: rgba(49, 53, 60, 0.6);
      }

      &-amount {
        font-weight: 900;
        font-size: 1.72em;
        line-height: 1;
      }
    }

    .bill-item {
      border-bottom: 1px solid rgba(208, 210, 216, 0.36);
      padding: 1.5rem 0;
      font-size: 0.9em;
      display: flex;
      align-items: center;
      width: 100%;
      padding-bottom: 1.75rem;

      .date,
      .invoice,
      .due-at,
      .status {
        padding-left: 1.25rem;
        padding-right: 1.25rem;

        > span {
          color: rgba(49, 53, 60, 0.6);
          font-size: 0.9em;
          line-height: 1;
        }
        > div {
          font-weight: 600;
          font-size: 1.1em;
          line-height: 1;
        }
      }

      .date {
        // width: 9%;
        padding-left: 0;
      }

      .invoice {
        // width: 28%;
      }

      .status {
        // width: 16%;;
      }

      .due-at {
        flex: 1;
      }


      &:first-child {
        padding-top: 0;
      }

      &:last-child {
        border-bottom: 0;
      }

      .date,
      .invoice,
      .due-at,
      .status {
        padding-left: 2rem;
        padding-right: 2rem;

        > div {
          font-weight: 500;
        }
      }

      .date {
        padding-left: 0;
      }

      .invoice,
      .due-at {
        flex: 1;
      }

      .status {
        width: 25%;
      }

      .action {
        width: 7%;
        text-align: right;
      }

      .total-bill {
        // text-align: right;
        // flex: 1;

        &-label {
          color: rgba(49, 53, 60, 0.6);
        }

        &-amount {
          font-weight: 500;
          font-size: 1.1em;
        }
      }
    }
  }
}
</style>
