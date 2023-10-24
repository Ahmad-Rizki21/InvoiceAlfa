<template>
  <q-page class="page-franchises page-franchise-single page-franchise-create">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" @click="onGoBack" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Franchise') }) }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.franchise')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-btn flat round dense icon="arrow_back" @click="onGoBack" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Franchise') }) }}
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
import FormPage from './Franchise/FormPage'

export default {
  name: 'PageFranchiseCreate',
  components: {
    FormPage
  },
  meta() {
    return {
      title: this.$t('Create New {entity}', { entity: this.$t('Franchise') }) + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Franchises'),  to: '/franchises' },
      { text: this.$t('New {entity}', { entity: this.$t('Franchise') }) },
    ]
  },
  data() {
    return {
      isLoading: false,
      entry: {}
    }
  },
  computed: {
    parentDistributionCenterId() {
      return parseInt(this.$route.query.distribution_center_id, 10) || null
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
      if (this.parentDistributionCenterId) {
        this.$router.replace({
          path: `/franchises/${entry.id}`,
          query: {
            distribution_center_id: this.parentDistributionCenterId
          }
        })
      } else {
        this.$router.push(`/franchises/${entry.id}`)
      }
    },
    onGoBack() {
      if (this.parentDistributionCenterId) {
        this.$router.push(`/distribution-centers/${this.parentDistributionCenterId}`)
      } else {
        this.$router.back()
      }
    }
  }
}
</script>

<style lang="scss">
.page-franchise-create {
  padding-bottom: 3rem;
}
</style>
