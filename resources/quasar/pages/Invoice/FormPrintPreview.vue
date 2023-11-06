<template>
  <div class="form-print-preview" @click="onClick">
    <form-invoice
      :entry="invoice"
      :template-settings="templateSettings"
    />
    <form-receipt
      :entry="receipt"
      :invoice="invoiceUpdated.invoice_no ? invoiceUpdated : invoice"
      :template-settings="templateSettings"
    />
    <form-store
      v-if="stores.length"
      :entries="stores"
      :invoice="invoiceUpdated.invoice_no ? invoiceUpdated : invoice"
      :template-settings="templateSettings"
    />
  </div>
</template>

<script>
import FormInvoice from './FormInvoice'
import FormReceipt from './FormReceipt'
import FormStore from './FormStore'

export default {
  name: 'FormPrintPreview',
  components: {
    FormInvoice,
    FormReceipt,
    FormStore
  },
  props: {
    invoice: {
      type: Object,
      default() {
        return {}
      }
    },
    invoiceUpdated: {
      type: Object,
      default() {
        return {}
      }
    },
    receipt: {
      type: Object,
      default() {
        return {}
      }
    },
    stores: {
      type: Array,
      default() {
        return []
      }
    },
    templateSettings: {
      type: [Array, Object],
      default() {
        return {}
      }
    }
  },
  methods: {
    onClick(e) {
      const target = e ? (e.target || {}) : {}

      if (!(target.closest && target.closest('.print-paper'))) {
        this.$emit('close')
      }
    }
  }
}
</script>
