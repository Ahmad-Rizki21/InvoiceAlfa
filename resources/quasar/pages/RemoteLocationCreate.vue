<template>
  <q-page class="page-remote-locations page-remote-location-single page-remote-location-create">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/remote-locations" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Remote Location') }) }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.remote_location')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-btn flat round dense icon="arrow_back" to="/remote-locations" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Remote') }) }}
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
import FormPage from './RemoteLocation/FormPage'

export default {
  name: 'PageRemoteLocationCreate',
  components: {
    FormPage
  },
  meta() {
    return {
      title: this.$t('Create New {entity}', { entity: this.$t('Remote Location') }) + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Remote Locations'),  to: '/remote-locations' },
      { text: this.$t('New {entity}', { entity: this.$t('Remote Location') }) },
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
      this.$router.push(`/remote-locations/${entry.id}`)
    },
  }
}
</script>

<style lang="scss">
.page-remote-location-create {
  padding-bottom: 3rem;
}
</style>
