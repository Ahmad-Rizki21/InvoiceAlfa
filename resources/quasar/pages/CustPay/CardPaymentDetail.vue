<template>
  <q-card
    class="card-payment-detail"
  >
    <q-card-section>
      <p v-if="isTransferToVa" class="notice">
        {{ $t('Payment can be transferred to following virtual account:') }}
      </p>
      <p v-else class="notice">
        {{ $t('Payment can be transferred to the following bank account:') }}
        <!-- {{ $t('Please make your payment promptly through the following account:') }} -->
      </p>

      <div class="bank-detail" :class="{ 'va-detail': isTransferToVa }">
        <template v-if="isTransferToVa">
          <span class="bank-name">{{ customer.transfer_to_virtual_account_bank_name }} Virtual Account</span><br />
          <span class="account-number">
            {{ customer.transfer_to_virtual_account_number }}

            <q-btn
              size="sm"
              flat
              rounded
              padding="sm"
              icon="content_copy"
              class="btn-copy"
              @click="onAccountNumberCopy(customer.transfer_to_virtual_account_number)"
            />
          </span><br />
        </template>
        <template v-else>
          <span class="bank-name">{{ settings[constant.BankTransferName] }}</span><br />
          <span class="account-number">
            {{ settings[constant.BankTransferAccountNumber] }}

            <q-btn
              size="sm"
              flat
              rounded
              padding="sm"
              icon="content_copy"
              class="btn-copy"
              @click="onAccountNumberCopy(settings[constant.BankTransferAccountNumber])"
            />
          </span><br />
          <span clas="account-name">A/N {{ settings[constant.BankTransferAccountName] }}</span>
        </template>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
import { date } from 'quasar'

export default {
  name: 'CardPaymentDetail',
  props: {
    invoice: {
      type: Object,
      default() {
        return {}
      }
    },
    fetching: Boolean,
    customer: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      isFetching: false,
      isLoading: false,
      settings: {}
    }
  },
  computed: {
    constant() {
      return this.$constant.setting_key
    },
    isTransferToVa() {
      return this.invoice.transfer_to_type == this.$constant.transfer_to_type.VirtualAccount &&
        this.customer.transfer_to_virtual_account_bank_name && this.customer.transfer_to_virtual_account_number
    }
  },
  mounted() {
    this.onRequest()
  },
  methods: {
    async onRequest() {
      if (this.isLoading) {
        return
      }

      this.isFetching = true
      this.isLoading = true

      try {
        const { data } = await this.$api.get('/v1/settings')

        if (data.status === 'success') {
          this.settings = data.data.settings
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isFetching = false
      this.isLoading = false
    },
    formatMonth(entry) {
      const d = date.extractDate(entry.due_at || entry.published_at, 'YYYY-MM-DD')

      return date.formatDate(d, 'MMMM')
    },
    formatDate(value) {
      const d = date.extractDate(value, 'YYYY-MM-DD')

      return date.formatDate(d, 'DD MMMM YYYY')
    },
    formatMoney(value) {
      return value ? this.$utils.currency(value || 0, {
        decimal: '.',
        thousand: ',',
        symbol: 'Rp. '
      }) : 'Rp. -'
    },
    formatTotal(entry) {
      return this.$utils.currency(entry.total, {
        decimal: '.',
        thousand: ',',
      }) || '-'
    },
    onAccountNumberCopy(num) {
      this.$utils.copyToClipboard(num)
      this.$q.notify({ message: this.$t('{entity} copied to clipboard', { entity: this.$t('Account number') }) })
    }
  }
}
</script>

<style lang="scss">
.card-payment-detail {
  > .q-card__section {

    .notice {
      font-size: 0.9em;
    }


    .bank-detail {
      font-weight: 500;
      text-align: center;
      padding: 1rem;
      border: 1px solid #ebebeb;
      box-shadow: 0px 0px 8px rgba(148, 139, 139, 0.16);
      border-radius: 0.3rem;

      span {
        line-height: 1.8;
      }

      .bank-name {
        color: #5d5d5d;
      }

      .account-number {
        font-size: 1.5em;
      }

      .btn-copy {
        margin-right: -2.5rem;

        .q-icon {
          color: #5d5d5d;
        }
      }
    }
  }
}
</style>
