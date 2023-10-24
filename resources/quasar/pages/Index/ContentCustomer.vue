<template>
  <div class="content-customer container">
    <card-active-invoice />

    <card-invoice-history />
  </div>
</template>

<script>
import CardActiveInvoice from './CardActiveInvoice'
import CardInvoiceHistory from './CardInvoiceHistory'

export default {
  name: 'ContentCustomer',
  components: {
    CardActiveInvoice,
    CardInvoiceHistory
  },
  data() {
    return {
      isNotificationAnimationVisible: true
    }
  },
  methods: {
    onCardBillMouseEnter() {
      this.isNotificationAnimationVisible = true
      const $el = this.$refs.notificationAnimation

      console.log($el)
    },
    onCardBillMouseLeave() {
      this.isNotificationAnimationVisible = false
      const $el = this.$refs.notificationAnimation

      console.log($el)
    }
  }
}
</script>

<style lang="scss">
.content-customer {
  padding-top: 2rem;
  padding-bottom: 3rem;

  p {
    margin-bottom: 0;
  }

}
</style>
