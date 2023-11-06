<template>
  <q-card
    class="card-payment-detail"
  >
    <q-card-section>
      <p class="notice">
        {{ $t('Payment can be transferred to the following bank account:') }}
        <!-- {{ $t('Please make your payment promptly through the following account:') }} -->
      </p>

      <div class="bank-detail">
        <span>{{ settings[constant.BankTransferName] }}</span><br />
        <span>A/C {{ settings[constant.BankTransferAccountNumber] }}</span><br />
        <span>A/N {{ settings[constant.BankTransferAccountName] }}</span>
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
    fetching: Boolean
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

      span {
        line-height: 1.8;
      }
    }
  }
}
</style>
