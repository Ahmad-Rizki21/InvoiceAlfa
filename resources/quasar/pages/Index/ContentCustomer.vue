<template>
  <div class="content-customer container" :class="{ 'has-franchise': franchiseColumnVisible }">
    <q-checkbox
      v-if="$auth.authType === 'd'"
      v-model="showFranchise"
      label="Juga tampilkan tagihan untuk franchise"
      class="q-mb-md"
    />

    <card-active-invoice :show-franchise="franchiseColumnVisible" />

    <card-invoice-history />
  </div>
</template>

<script>
import CardActiveInvoice from './CardActiveInvoice'
import CardInvoiceHistory from './CardInvoiceHistory'
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'ContentCustomer',
  components: {
    CardActiveInvoice,
    CardInvoiceHistory
  },
  data() {
    return {
      isNotificationAnimationVisible: true,
      showFranchise: false,
    }
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/settings'
    }),
    franchiseColumnVisible() {
      return this.$auth.authType == 'f' || this.showFranchise
    }
  },
  watch: {
    'appSettings.isFranchiseBillVisible': {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.showFranchise) {
          this.showFranchise = n
        }
      }
    },
    showFranchise(n, o) {
      if (n !== o && n !== this.appSet.isFranchiseBillVisible) {
        this.appSet({
          isFranchiseBillVisible: n
        })

        this.appSave()
      }
    }
  },
  methods: {
    ...mapActions({
      appSet: 'app/set',
      appSave: 'app/save'
    }),
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

.page-index {
  .content-customer.has-franchise {
    &.container {
      @media (min-width: 576px) {
        max-width: 504px;
      }

      @media (min-width: 768px) {
        max-width: 706px;
      }
      @media (min-width: 992px) {
        max-width: 94%;
      }
      @media (min-width: 1200px) {
        max-width: 1198px;
      }
      @media (min-width: 1360px) {
        max-width: 1278px;
      }
      @media (min-width: 1600px) {
        max-width: 1342px;
      }
    }
  }
}
</style>
