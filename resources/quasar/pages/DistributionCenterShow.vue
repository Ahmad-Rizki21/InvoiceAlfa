<template>
  <q-page class="page-distribution-centers page-distribution-center-single page-distribution-center-show">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" @click="onGoBack" />
      <q-toolbar-title>
        {{ $t('Distribution Center') }}
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
        {{ $t('Distribution Center') }}
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
import FormPage from './DistributionCenter/FormPage'

export default {
  name: 'PageDistributionCenterShow',
  components: {
    FormPage
  },
  meta() {
    return {
      title: this.$t('Distribution Center') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    const breadcrumbs = [
      'home',
      { text: this.$t('Distribution Centers'), to: '/distribution-centers' },
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
      entry: {},
      scrollToBottom: false
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
  beforeRouteEnter(to, from, next) {
    if (from.name && (from.name.includes('stores.') || from.name.includes('franchises.'))) {
      next(vm => {
        vm.$nextTick(() => {
          vm.scrollToBottom = true
        })
      })

      return
    }
    next()
  },
  scrollBehavior(to, from, savedPosition) {
    console.log({to, from, savedPosition})
    if (savedPosition) {
      return savedPosition
    } else {
      return { x: 0, y: 0 }
    }
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
        const { data } = await this.$api.get(`/v1/distribution-centers/${props.id}`, { params })

        if (data.status === 'success') {
          this.entry = data.data.distribution_center
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
      this.isFetching = false

      if (this.scrollToBottom) {
        this.$nextTick(() => {
          setTimeout(() => {
            window.scrollTo(0, document.body.scrollHeight)
          }, 300)
        })
        this.scrollToBottom = false
      }
    },
    onSuccess(entry) {
      this.entry = { ...entry }
    },
    onDeleted() {
      this.$router.replace('/distribution-centers')
    },
    onGoBack() {
      if (this.$route.query.customer_id) {
        this.$router.push(`/customers/${this.$route.query.customer_id}`)
      } else {
        this.$router.back()
      }
    }
  }
}
</script>

<style lang="scss">
.page-distribution-center-show {
  padding-bottom: 3rem;
}
</style>
