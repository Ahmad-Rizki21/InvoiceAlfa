<template>
  <div class="bulk-print-content">
    <div
      class="bulk-print-inner"
      v-for="(invoice, i) in invoices"
      :key="invoice.id"
    >
      <form-print-preview
        :invoice="invoice"
        :receipt="receipts[invoice.id]"
        :stores="invoice.distribution_center?.stores"
        :template-settings="templateSettings"
      />
    </div>
  </div>
</template>

<script>
import FormPrintPreview from './Invoice/FormPrintPreview'

export default {
  name: 'PageInvoiceShow',
  components: {
    FormPrintPreview
  },
  meta() {
    return {
      title: this.$t('Invoice')
    }
  },
  breadcrumbs() {
    const breadcrumbs = [
      'home',
      { text: this.$t('Invoices'),  to: '/invoices' },
    ]

    if (this.invoice && this.invoice.invoice_no) {
      breadcrumbs.push({ text: this.invoice.invoice_no })
    }

    return breadcrumbs
  },
  data() {
    return {
      isLoading: false,
      invoice: {
        invoice_no: null
      },
      receipt: {
        receipt_no: null
      },
      stores: [],
      invoices: [],
      receipts: {},
      formInvoiceUpdated: {},
      templateSettings: {}
    }
  },
  async created() {
    await this.requestSettings()

    await this.requestInvoice()
  },
  methods: {
    async requestInvoice() {
      if (this.isLoading) {
        return
      }

      this.isLoading = true

      const ids = String(this.$route.query.id)

      const params = {
        edit: 1,
        includes: 'invoiceServices',
        id: 'in:' + ids,
        includeStores: this.$route.query.store !== 'n' ? 'y' : 'n'
      }

      try {
        const { data } = await this.$api.get(`/v1/invoices`, { params })

        if (data.status === 'success') {
          this.invoices = data.data.invoices;
          this.receipts = {}
          const receipts = {}

          data.data.invoices.forEach(v => {
            receipts[v.id] = { ...v }
          })
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false


      if (window.doPrinting) {
        this.$nextTick(() => {
          setTimeout(() => {
            window.print()

            if (window.doPrint) {
              setTimeout(() => {
                window.doPrint()
              }, 500);
            }
          }, 100);
        })
      }
    },
    async requestStores() {
      if (!this.invoice.distribution_center_id) {
        return
      }

      const params = {
        distribution_center_id: this.invoice.distribution_center_id
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
    async requestSettings() {
      try {
        const { data } = await this.$api.get(`/v1/settings`)

        if (data.status === 'success') {
          this.templateSettings = data.data.settings
        }
      } catch (err) {
        this.$q.notify(err)
      }
    },
    onSuccess(entry) {

    },
    onPrint() {
      this.isFormPrintPreviewVisible = false
    },
    onPreview() {
      this.isFormPrintPreviewVisible = true
    }
  }
}
</script>
