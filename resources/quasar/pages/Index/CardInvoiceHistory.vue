<template>
  <div class="invoice-history-wrapper">
    <div class="card-label">
      Riwayat Tagihan
    </div>

    <q-card flat class="card-invoice-history">
      <div v-if="isFetching"></div>
      <q-card-section v-else-if="entries && entries.length">
        <router-link :to="`/c/bill/${entry.uid}`" class="bill-item" v-for="entry in entries" :key="entry.id">
          <div class="date">
            <span class="span">{{ $t('Month') }}</span>
            <span class="span span-invoice">{{ entry.invoice_no }}</span>
            <div>{{ formatMonth(entry) }}</div>
          </div>
          <div class="invoice">
            <span class="span">
              Invoice

              <q-icon
                name="chevron_right"
              />
            </span>
            <div>{{ entry.invoice_no }}</div>
          </div>
          <div class="status">
            <span class="span">Tanggal Bayar</span>
            <div>{{ formatDate(entry.paid_at) }}</div>
          </div>
          <div class="total-bill">
            <span class="span total-bill-label">
              Total Tagihan
            </span>
            <div class="total-bill-amount">
              {{ formatMoney(entry.total) }}
            </div>
          </div>
          <div class="action">
            <q-icon
              name="chevron_right"
            />
          </div>
        </router-link>
      </q-card-section>
      <q-card-section v-else class="empty-invoice">
        Anda belum memiliki riwayat tagihan
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import { date } from 'quasar'

export default {
  name: 'CardInvoiceHistory',
  data() {
    return {
      entries: [],
      isLoading: false,
      isFetching: true
    }
  },
  computed: {
    emptyInvoice() {
      return !this.isFetching && !this.entries.length
    },
    firstInvoice() {
      return this.entries[0]
    }
  },
  mounted() {
    this.onRequest()
  },
  methods: {
    async onRequest(props) {
      if (this.isLoading) {
        return
      }

      if (!props) {
        props = { pagination: 999999999 }
      }

      this.isFetching = true
      this.isLoading = true


      const params = {
        table_pagination: { ...(props.pagination || {}) },
        status: `in:${[
          this.$constant.invoice_status.Paid
        ].join('|')}`,
        me: 'y'
      }

      params.table_pagination.sortBy = 'published_at'
      params.table_pagination.descending = true

      try {
        const { data } = await this.$api.get('/v1/invoices', { params })

        if (data.status === 'success') {
          this.entries = data.data.invoices
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isFetching = false
      this.isLoading = false
    },
    formatMonth(entry) {
      const d = date.extractDate(entry.due_at || entry.published_at, 'YYYY-MM-DD')

      return date.formatDate(d, 'MMMM YYYY')
    },
    formatDate(payload) {
      const d = date.extractDate(payload, 'YYYY-MM-DD HH:mm:ss')

      return date.formatDate(d, 'DD MMM YYYY')
    },
    formatMoney(payload) {
      return this.$utils.currency(payload, {
        decimal: '.',
        thousand: ',',
        symbol: 'Rp. '
      }) || '-'
    }
  }
}
</script>

<style lang="scss">
.invoice-history-wrapper {

  @media (min-width: 1200px) {
    max-width: 992px;
    margin-left: auto;
    margin-right: auto;
  }

  @media (min-width: 1600px) {
    max-width: 1104px;
  }

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
      padding-bottom: 1.5rem;
      text-decoration: none;
      color: #1d1d1d;
      color: var(--q-text-color-default);

      .date,
      .invoice,
      .due-at,
      .status {
        padding-left: 1.25rem;
        padding-right: 1.25rem;

        > .span {
          color: rgba(49, 53, 60, 0.6);
          font-size: 0.9em;
          line-height: 1;
          display: none;
          margin-bottom: 0.25rem;

          @media (min-width: $breakpoint-md-min) {
            display: block;
          }
        }
        > div {
          font-weight: 400;
          font-size: 1.1em;
          line-height: 1;
        }
      }

      .date {
        // width: 9%;
        padding-left: 0;

        @media (min-width: $breakpoint-md-min) {
          width: 17%;
        }

        > .span-invoice {
          display: block;
          font-size: 0.75em;

          @media (min-width: $breakpoint-md-min) {
            display: none;
          }
        }
      }

      .invoice {
        // width: 28%;
        display: none;

        @media (min-width: $breakpoint-md-min) {
          display: block;
        }
      }

      .status {
        // width: 16%;;
        display: none;

        @media (min-width: $breakpoint-md-min) {
          display: block;
        }
      }

      .due-at {
        flex: 1;
        display: none;

        @media (min-width: $breakpoint-md-min) {
          display: block;
        }
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
          font-weight: 400;
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
        width: 5%;
        text-align: right;
      }

      .total-bill {
        // text-align: right;
        flex: 1;
        min-width: 20%;
        display: flex;

        @media (min-width: $breakpoint-md-min) {
          flex: none;
          display: block;
        }

        &-label {
          color: rgba(49, 53, 60, 0.6);
          font-size: 0.9em;
          line-height: 1;
          display: none;
          margin-bottom: 0.25rem;

          @media (min-width: $breakpoint-md-min) {
            display: block;
          }
        }

        &-amount {
          font-weight: 500;
          font-size: 1em;
          flex: 1;
          text-align: right;

          @media (min-width: $breakpoint-md-min) {
            // font-size: 1.1em;
            text-align: left;
            flex: none;
          }
        }
      }
    }
  }

  .empty-invoice {
    min-height: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    font-style: italic;
    color: rgba(49, 53, 60, 0.6);
  }
}
</style>
