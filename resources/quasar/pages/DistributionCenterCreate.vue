<template>
  <q-page class="page-distribution-centers page-distribution-center-single page-distribution-center-create">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/distribution-centers" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Distribution Center') }) }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.distribution_center')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-btn flat round dense icon="arrow_back" to="/distribution-centers" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Distribution Center') }) }}
      </q-toolbar-title>

      <div class="sep" />
    </div>

    <div class="page-body">
      <div class="row">
        <div class="col-xs-12">
          <form-page ref="formPage" :entry="entry" @success="onSuccess" />
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import FormPage from './DistributionCenter/FormPage'

export default {
  name: 'PageDistributionCenterCreate',
  components: {
    FormPage
  },
  meta() {
    return {
      title: this.$t('Create New {entity}', { entity: this.$t('Distribution Center') }) + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Distribution Centers'),  to: '/distribution-centers' },
      { text: this.$t('New {entity}', { entity: this.$t('Distribution Center') }) },
    ]
  },
  data() {
    return {
      isLoading: false,
      entry: {}
    }
  },
  beforeRouteLeave(to, from, next) {
    if (this.$refs.formPage.isDirty()) {
      const answer = window.confirm(this.$t('There are unsaved changes. Do you want to discard them?'))

      if (answer) {
        next()
      } else {
        next(false)
      }
    } else {
      next()
    }
  },
  methods: {
    onSuccess(entry) {
      if (this.$route.query.customer_id) {
        this.$router.replace(`/customers/${this.$route.query.customer_id}`)
      } else {
        this.$router.replace(`/distribution-centers/${entry.id}`)
      }
    },
  }
}
</script>

<style lang="scss">
.page-distribution-center-create {
  padding-bottom: 3rem;
}
</style>
