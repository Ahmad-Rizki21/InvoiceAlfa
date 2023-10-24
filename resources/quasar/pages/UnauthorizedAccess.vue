<template>
  <q-page class="page-unauthorized-access">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        &nbsp;
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
      <q-toolbar-title>&nbsp;</q-toolbar-title>
    </div>

    <div class="page-body">
      <q-card>
        <q-dialog
          :value="true"
          no-esc-dismiss
          persistent
          no-route-dismiss
          no-focus
          no-refocus
          no-shake
          :allow-focus-outside="false"
        >
          <div class="column" style="box-shadow: none;">
            <div class="text-h6 q-mb-md">
              {{ $t('You are not authorized to access this site.') }}
            </div>
            <div class="text-center q-mt-md">
              <q-btn color="primary" unelevated @click="onLogoutClick">{{ $t('click here to logout instead') }}</q-btn>
            </div>
          </div>
        </q-dialog>
      </q-card>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'PageUnauthorizedAccess',
  meta() {
    return {
      title: this.$t('Unauthorized Access') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return []
  },
  mounted() {
    document.querySelector('#q-app').style.filter = 'blur(5px)'
  },
  beforeDestroy() {
    document.querySelector('#q-app').style.filter = ''
  },
  methods: {
    async onLogoutClick() {
      await this.$store.dispatch('auth/logout');

      this.$router.replace('/login');
    }
  }
}
</script>

<style lang="scss">
.page-unauthorized-access {
  padding-bottom: 3rem;
}
</style>
