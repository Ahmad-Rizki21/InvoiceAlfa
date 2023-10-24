<template>
  <q-page class="page-customers page-customer-single page-customer-show">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/customers" />
      <q-toolbar-title>
        {{ $t('Customer') }}
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
        {{ $t('Customer') }}
      </q-toolbar-title>

      <div class="sep" />

      <small v-if="entry.id" class="entry-id"><span class="user-select-none">ID: #</span>{{ entry.id }}</small>
    </div>

    <div class="page-body">
      <div class="row">
        <div class="col-xs-12 col-md-12">
          <form-page :entry="entry" :fetching="isFetching" :editable.sync="isEditable" @success="onSuccess" @deleted="onDeleted" />
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import FormPage from './Customer/FormPage'

export default {
  name: 'PageCustomerShow',
  components: {
    FormPage
  },
  meta() {
    return {
      title: this.$t('Customer') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    const breadcrumbs = [
      'home',
      { text: this.$t('Customers'), to: '/customers' },
    ]

    if (this.isEditable) {
      breadcrumbs.push({
        text: this.$t('Update Details')
      })
    } else {
      breadcrumbs.push({
        text: this.$t('Details')
      })
    }

    return breadcrumbs
  },
  data() {
    return {
      isLoading: false,
      isFetching: false,
      isEditable: false,
      entry: {}
    }
  },
  computed: {
    isCreate() {
      return !Boolean(this.entry.id)
    }
  },
  mounted() {
    this.onRequest()
  },
  methods: {
    async onRequest(props) {
      if (this.isLoading || this.isFetching) {
        return
      }

      if (!props) {
        props = { id: this.$route.params.id }
      }

      this.isLoading = true
      this.isFetching = true

      const params = {
        table_pagination: { ...(props.pagination || {}) },
      }

      try {
        const { data } = await this.$api.get(`/v1/customers/${props.id}`, { params })

        if (data.status === 'success') {
          this.entry = data.data.customer
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
      this.isFetching = false
    },
    onSuccess(entry) {
      this.entry = { ...entry }
    },
    onDeleted() {
      this.$router.replace('/customers')
    }
  }
}
</script>

<style lang="scss">
.page-customer-show {
  padding-bottom: 3rem;
}
</style>
