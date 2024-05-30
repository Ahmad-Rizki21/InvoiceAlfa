<template>
  <form-print-preview
    :invoice="invoice"
    :receipt="receipt"
    :stores="stores"
    :template-settings="templateSettings"
  />
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

      const params = {
        edit: 1,
        includes: 'invoiceServices'
      }

      try {
        const { data } = await this.$api.get(`/v1/invoices/${this.$route.params.id}`, { params })

        if (data.status === 'success') {
          this.invoice = data.data.invoice
          this.receipt = data.data.invoice

          if (this.$route.query.store !== 'n') {
            this.requestStores()
          }
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
