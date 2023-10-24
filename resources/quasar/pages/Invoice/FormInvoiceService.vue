<template>
  <div class="invoice-table-row form-invoice-service">
    <div class="invoice-table-left">
      <div class="invoice-table-no">
        {{ index + 1 }}
      </div>
      <div class="invoice-table-description">
        <template v-if="!editable">
          <span v-if="inputDescription">{{ inputDescription }}</span>
          <span v-else v-html="'&nbsp;'"></span>
        </template>
        <q-input
          v-else
          v-model="inputDescription"
          filled
          type="textarea"
          rows="1"
          autogrow
          borderless
          :name="`description[${id}]`"
          autocomplete="off"
          dense
          :error="!!errors.description"
          :error-message="errors.description"
        />
      </div>
    </div>
    <div class="invoice-table-right">
      <div class="invoice-table-qty">
        <template v-if="!editable">
          <span v-if="inputQty">{{ formatCurrency(inputQty) }}</span>
          <span v-else v-html="'&nbsp;'"></span>
        </template>
        <q-input
          v-else
          v-model="inputQty"
          filled
          borderless
          :name="`qty[${id}]`"
          autocomplete="off"
          dense
          :error="!!errors.qty"
          :error-message="errors.qty"
          @keypress="$globalListeners.onKeypressOnlyFloat($event)"
        />
      </div>
      <div class="invoice-table-unit-price" :class="{ editable }">
        <template v-if="!editable">
          <span v-if="inputUnitPrice">{{ formatCurrency(inputUnitPrice) }}</span>
          <span v-else v-html="'&nbsp;'"></span>
        </template>
        <q-input
          v-else
          v-model="inputUnitPrice"
          filled
          borderless
          :name="`qty[${id}]`"
          autocomplete="off"
          dense
          :error="!!errors.unit_price"
          :error-message="errors.unit_price"
          @keypress="$globalListeners.onKeypressOnlyFloat($event)"
        />
      </div>
      <div class="invoice-table-sub-total">
        {{ formattedSubTotal }}
      </div>
    </div>

    <q-btn
      v-if="editable && index > 0"
      unelevated
      color="default"
      size="sm"
      padding="xs"
      icon="delete"
      class="btn-form-invoice-service-delete"
      @click.prevent="onDelete"
    >
      <q-tooltip>
        {{ $t('Delete') }}
      </q-tooltip>
    </q-btn>
  </div>
</template>

<script>
export default {
  name: 'FormInvoiceService',
  props: {
    index: [String, Number],
    id: [String, Number],
    description: String,
    qty: [String, Number],
    unitPrice: [String, Number],
    editable: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      inputDescription: null,
      inputQty: null,
      inputUnitPrice: null,
      errors: {
        description: null,
        qty: null,
        unit_price: null
      }
    }
  },
  computed: {
    subTotal() {
      return ((parseFloat(this.inputQty, 10) || 0) * (parseFloat(this.inputUnitPrice, 10) || 0)) || 0
    },
    formattedSubTotal() {
      return this.formatCurrency(this.subTotal)
    }
  },
  watch: {
    description: {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.inputDescription) {
          this.inputDescription = n
        }
      }
    },
    inputDescription(n, o) {
      if (n !== o && n !== this.description) {
        this.$emit('update:description', n)
        this.errors.description = null
      }
    },
    qty: {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.inputQty) {
          this.inputQty = n
        }
      }
    },
    inputQty(n, o) {
      if (n !== o && n !== this.qty) {
        this.$emit('update:qty', n)
        this.errors.qty = null
      }
    },
    unitPrice: {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.inputUnitPrice) {
          this.inputUnitPrice = n
        }
      }
    },
    inputUnitPrice(n, o) {
      if (n !== o && n !== this.unitPrice) {
        this.$emit('update:unit-price', n)
        this.errors.unit_price = null
      }
    },
  },
  methods: {
    formatCurrency(num) {
      return num ? this.$utils.currency(num, {
        decimal: '.',
        thousand: ',',
      }) : 0
    },
    onDelete() {
      this.$emit('delete', this.id)
    },
    validate() {
      const errors = {
        description: null,
        qty: null,
        unit_price: null
      }

      if (!this.inputDescription) {
        errors.description = this.$t('{field} is required', { field: this.$t('description') })
      }

      if (!this.inputQty) {
        errors.qty = this.$t('{field} is required', { field: this.$t('qty') })
      }

      if (this.inputUnitPrice === '' || this.inputUnitPrice === null) {
        errors.unit_price = this.$t('{field} is required', { field: this.$t('unit price') })
      }

      this.errors = errors

      return errors.description || errors.qty || errors.unit_price || true
    }
  }
}
</script>

<style lang="scss">
.form-invoice-service.invoice-table-row {
  position: relative;

  .btn-form-invoice-service-delete {
    position: absolute;
    right: -1.75rem;
    top: 0.75rem;
  }
}
</style>
