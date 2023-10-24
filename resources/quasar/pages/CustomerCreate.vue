<template>
  <q-page class="page-customers page-customer-single page-customer-create">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/customers" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Customer') }) }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.customer')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-btn flat round dense icon="arrow_back" to="/customers" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Customer') }) }}
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
import FormPage from './Customer/FormPage'

export default {
  name: 'PageCustomerCreate',
  components: {
    FormPage
  },
  meta() {
    return {
      title: this.$t('Create New {entity}', { entity: this.$t('Customer') }) + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Customers'),  to: '/customers' },
      { text: this.$t('New {entity}', { entity: this.$t('Customer') }) },
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
      this.$router.push(`/customers/${entry.id}`)
    },
  }
}
</script>

<style lang="scss">
.page-customer-create {
  padding-bottom: 3rem;
}
</style>
