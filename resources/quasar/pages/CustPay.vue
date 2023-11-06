<template>
  <q-page class="page-cust page-cust-bill container">
    <div class="page-cust-header">
      <q-btn flat round dense icon="arrow_back" @click="onGoBack" />

      <q-toolbar-title>
        {{ $t('Bill Payment') }}
      </q-toolbar-title>
    </div>

    <div v-if="entry.status == $constant.invoice_status.Rejected && entry.reject_reason" class="row q-col-gutter-lg q-mb-md">
      <div class="col-xs-12 col-md-10">
        <q-banner dense class="text-white bg-red">
          <template v-slot:avatar>
            <q-icon name="warning" color="white" />
          </template>
          Bukti pembayaran Anda ditolak dengan alasan: <strong>{{ entry.reject_reason }}</strong>
        </q-banner>
      </div>
    </div>

    <div v-if="pageErrorCode" class="row q-col-gutter-sm row-bill-detail">
      <div v-if="pageErrorCode === 404" class="error-box">
        <q-icon name="fmd_bad" />
        <div class="error-message">
          {{ $t('Invoice could not be found') }}
        </div>
        <q-btn color="secondary" outline @click.prevent="$router.replace('/')">
          {{ $t('Back to home') }}
        </q-btn>
      </div>
      <div v-else class="error-box">
        <q-icon name="sentiment_very_dissatisfied" />
        <div class="error-message">
          {{ $t('An error occurred while trying to get bill data') }}
        </div>
        <q-btn color="secondary" outline @click.prevent="onRequest()">
          {{ $t('Refresh') }}
        </q-btn>
      </div>
    </div>
    <div v-else class="row q-col-gutter-lg row-bill-detail">
      <div class="col-xs-12 col-md-10">
        <card-bill-detail
          :invoice="entry"
          :fetching="isFetching"
        />

        <card-payment-detail
          v-if="entry.status != $constant.invoice_status.Paid"
          :invoice="entry"
        />

        <card-payment-proof
          :invoice="entry"
          :editable="isPaymentProofsEditable"
          :addable="entry.status != $constant.invoice_status.Paid"
          :uploadable="entry.status != $constant.invoice_status.Paid"
          @uploaded="onRequest()"
        />
      </div>

      <div class="col-md-2">
        <q-btn
          :to="`/c/invoice/${entry.uid}`"
          target="_blank"
          flat
          color="primary"
          class="font-weight-bold q-mb-sm btn-print-invoice"
        >
          {{ $t('View {entity}', { entity: $t('Invoice') + ' & ' + $t('Kwitansi') }) }}
        </q-btn>
      </div>
    </div>
  </q-page>
</template>

<script>
import CardBillDetail from './CustPay/CardBillDetail'
import CardPaymentDetail from './CustPay/CardPaymentDetail'
import CardPaymentProof from './CustPay/CardPaymentProof'

export default {
  name: 'PageCustomerCust',
  components: {
    CardBillDetail,
    CardPaymentDetail,
    CardPaymentProof
  },
  meta() {
    return {
      title: this.$t('Bill Payment')
    }
  },
  breadcrumbs() {
    return ['Dashboard']
  },
  data() {
    return {
      entry: {},
      isFetching: true,
      isLoading: false,
      pageErrorCode: null
    }
  },
  computed: {
    emptyInvoice() {
      return !this.isFetching && !this.entry
    },
    isPaymentProofsEditable() {
      const constant = this.$constant.invoice_status;

      return ![
        constant.Paid, constant.PendingReview
      ].includes(this.entry.status)
    }
  },
  created() {
    if (this.$auth.authType === 'u') {
      this.$router.replace('/')
    }
  },
  async mounted() {
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

      this.pageErrorCode = null
      this.isFetching = true
      this.isLoading = true

      const params = {
        table_pagination: { ...(props.pagination || {}) },
        includes: 'invoiceServices|invoicePaymentProofs'
      }

      try {
        const { data } = await this.$api.get(`/v1/invoices/${this.$route.params.id}`, { params })

        if (data.status === 'success') {
          this.entry = data.data.invoice

          if (!this.entry) {
            this.$router.replace('/')
            return
          }
        }
      } catch (err) {
        const errRes = err.response || {}
        if (errRes.status === 404) {
          this.pageErrorCode = 404
        } else {
          this.pageErrorCode = 500
        }
      }

      this.isFetching = false
      this.isLoading = false
    },
    formatMonth(entry) {
      const d = date.extractDate(entry.due_at || entry.published_at, 'YYYY-MM-DD')

      return date.formatDate(d, 'MMMM')
    },
    formatDueAt(entry) {
      const d = date.extractDate(entry.due_at, 'YYYY-MM-DD')

      return date.formatDate(d, 'DD MMM YYYY')
    },
    formatTotal(entry) {
      return this.$utils.currency(entry.total, {
        decimal: '.',
        thousand: ',',
        symbol: 'Rp. '
      }) || '-'
    },
    onGoBack() {
      this.$router.push('/')
    }
  }
}
</script>

<style lang="scss">
.page-cust-bill {
  padding-bottom: 3rem;

  .error-box {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    min-height: 20rem;
    min-height: 43vh;
    width: 100%;
    font-weight: 300;
    font-size: 1.5rem;

    .q-icon {
      font-size: 7rem;
      text-align: center;
      margin-left: auto;
      margin-right: auto;
      color: #996b6b;
      opacity: 0.9;
    }

    .error-message {
      margin-bottom: 1.5rem;
    }
  }

  .btn-print-invoice {
    background-color: #3355dd1a;
    .q-focus-helper {
      opacity: 0.15;

      &:before {
        opacity: 0.1;
      }

      &:after {
        opacity: 0.4;
      }
    }
  }

  > .page-body {
    padding-top: 2rem;
  }

  .card-bill-detail {
    margin-bottom: 1.5rem;
  }
  .card-payment-detail {
    margin-bottom: 1.5rem;
  }

  .row-bill-detail {
    margin-bottom: 1.5rem;
  }

  .font-weight-bold {
    font-weight: 500;
  }
}
</style>
