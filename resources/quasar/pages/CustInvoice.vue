<template>
  <div class="page-cust page-cust-invoice container">
    <div class="page-cust-header">
      <!-- <q-btn flat round dense icon="arrow_back" @click="onGoBack" /> -->

      <q-toolbar-title>
        {{ $t('Invoice') }}
      </q-toolbar-title>

      <q-space />

      <q-btn
        flat
        icon="print"
        round
        padding="sm"
        @click="onPrint"
      />
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
    <div v-else class="row q-col-gutter-sm row-bill-detail">
      <div class="col-xs-12">
        <form-print-preview
          :invoice="entry"
          :receipt="entry"
          :stores="stores"
          :template-settings="formTemplateSettings"
        />
      </div>
    </div>

    <iframe v-if="printUrl" ref="printIframe" :src="printUrl" style="opacity: 0; visibility: hidden; width: 0; height: 0;"></iframe>
  </div>
</template>

<script>
import FormPrintPreview from './Invoice/FormPrintPreview'

export default {
  name: 'PageCustomerInvoice',
  components: {
    FormPrintPreview
  },
  meta() {
    try {
      document.head.querySelector('meta[name="viewport"]').remove()
    } catch (err) {}

    return {
      title: this.$t('Invoice'),
      meta: {
        viewport: {
          name: 'viewport',
          content: 'width=210mm'
        }
      }
    }
  },
  breadcrumbs() {
    return ['Dashboard']
  },
  data() {
    return {
      entry: {},
      stores: [],
      formTemplateSettings: {},
      isFetching: true,
      isLoading: false,
      pageErrorCode: null,
      printUrl: null
    }
  },
  computed: {
    emptyInvoice() {
      return !this.isFetching && !this.entry
    }
  },
  async mounted() {
    try {
      document.body.style.backgroundColor = '#525659';
    } catch (err) {}

    await this.requestSettings()
    this.onRequest()
  },
  beforeDestroy() {
    try {
      document.body.style.backgroundColor = '';
    } catch (err) { }
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

          this.requestStores()
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
    async requestSettings() {
      try {
        const { data } = await this.$api.get(`/v1/settings`)

        if (data.status === 'success') {
          this.formTemplateSettings = data.data.settings
        }
      } catch (err) {
        this.$q.notify(err)
      }
    },
    async requestStores() {
      if (!this.entry.distribution_center_id) {
        return
      }

      const params = {
        distribution_center_id: this.entry.distribution_center_id
      }

      try {
        const { data } = await this.$api.get(`/v1/stores`, { params })

        if (data.status === 'success') {
          this.stores = data.data.stores
        }
      } catch (err) {
        this.$q.notify(err)
      }
    },
    onPrint() {
      this.printUrl = this.$router.resolve(`/invoices/${this.entry.id}/print`).href
      this.$nextTick(() => {
        if (this.$refs.printIframe) {
          const $refs = this.$refs.printIframe
          const contentWindow = $refs.contentWindow

          contentWindow.doPrinting = true
          contentWindow.doPrint = () => {
            this.printUrl = null
          }
        }
      })
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
      this.$router.back()
    }
  }
}
</script>

<style lang="scss">
.page-cust-invoice {
  padding-bottom: 3rem;
  background-color: #525659;

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

  > .page-cust-header {
    background-color: #323639;
    display: flex;
    align-items: center;
    color: #fff;
    padding: 0.5rem 1rem 0;
    box-shadow: 0px 2px 13px #323639;
    position: fixed;
    z-index: 3;
    width: 100%;
    height: 4.15rem;

    @media (min-width: 1140px) {
      padding-left: 2rem;
      padding-right: 2rem;
    }

    @media (min-width: 1440px) {
      padding-left: 3rem;
      padding-right: 3rem;
      height: 4.5rem;
    }

    .q-toolbar__title {
      font-size: 1em;
      font-weight: 900;
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

    padding-top: 4.5rem;

    @media (min-width: 1440px) {
      padding-top: 4.85;
    }
  }

  .font-weight-bold {
    font-weight: 500;
  }
}
</style>
