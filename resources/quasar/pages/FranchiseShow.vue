<template>
  <q-page class="page-franchises page-franchise-single page-franchise-show">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" @click="onGoBack" />
      <q-toolbar-title>
        {{ $t('Franchise') }}
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
      <q-btn flat round dense icon="arrow_back" @click="onGoBack" />
      <q-toolbar-title>
        {{ $t('Franchise') }}
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
import FormPage from './Franchise/FormPage'

export default {
  name: 'PageFranchiseShow',
  components: {
    FormPage
  },
  meta() {
    return {
      title: this.$t('Franchise') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    const breadcrumbs = [
      'home',
      { text: this.$t('Franchises'), to: '/franchises' },
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
        const { data } = await this.$api.get(`/v1/franchises/${props.id}`, { params })

        if (data.status === 'success') {
          this.entry = data.data.franchise
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
      this.$router.replace('/franchises')
    },
    onGoBack() {
      if (this.$route.query.distribution_center_id) {
        this.$router.push(`/distribution-centers/${this.$route.query.distribution_center_id}`)
      } else if (this.$route.query.customer_id) {
        this.$router.push(`/customers/${this.$route.query.customer_id}`)
      } else {
        this.$router.back()
      }
    }
  }
}
</script>

<style lang="scss">
.page-franchise-show {
  padding-bottom: 3rem;
}
</style>
