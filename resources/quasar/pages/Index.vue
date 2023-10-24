<template>
  <q-page class="page-index">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <template v-if="$auth.authType === 'u'">

    </template>
    <template v-else-if="['d', 'f'].includes($auth.authType)">
      <content-customer />
    </template>
  </q-page>
</template>

<script>
import ContentCustomer from './Index/ContentCustomer'

export default {
  name: 'PageIndex',
  components: {
    ContentCustomer
  },
  meta() {
    return {
      title: this.$t('Dashboard') + ' - ' + this.$t('Web Console')
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
  }
}
</script>

<style lang="scss">
.page-index {
  padding-bottom: 3rem;

  > .page-body {
    padding-top: 2rem;

    .card-mini-summary-time-taken-button {
      .q-btn__wrapper {
        padding-top: 1px !important;
        padding-bottom: 0 !important;
      }
    }
  }
}
</style>
