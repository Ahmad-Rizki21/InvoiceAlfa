<template>
  <q-page class="page-cust page-cust-pay container">
    <div class="page-cust-header">
      <q-btn flat round dense icon="arrow_back" @click="onGoBack" />

      <q-toolbar-title>
        {{ $t('Bill Payment') }}
      </q-toolbar-title>
    </div>

  </q-page>
</template>

<script>
export default {
  name: 'PageCustomerPay',
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

    }
  },
  async mounted() {
    // this.$nextTick(() => {
    //   const store = this.$store
    //   const finishedGroups = store.getters['tourGuide/finishedGroups']

    //   if (!finishedGroups.default) {
    //     this.$tourGuide.open('default')
    //   } else if (!finishedGroups.index) {
    //     this.$tourGuide.open('index')
    //   }
    // })
  },
  methods: {
    onGoBack() {
      this.$router.back()
    }
  }
}
</script>

<style lang="scss">
.page-cust-pay {
  padding-bottom: 3rem;

  > .page-body {
    padding-top: 2rem;
  }
}
</style>
