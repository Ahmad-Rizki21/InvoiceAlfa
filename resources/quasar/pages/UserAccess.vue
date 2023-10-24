<template>
  <q-page class="page-user-access">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        {{ $t('User Access') }}
      </q-toolbar-title>
    </portal>

    <div class="page-header">
      <q-toolbar-title>{{ $t('User Access') }}</q-toolbar-title>
    </div>

    <div class="page-body">
      <div class="row q-col-gutter-lg">
        <div class="col-xs-12 col-md-3">
          <q-card
            outlined
            class="q-mx-auto"
          >
            <role-list @selected="onRoleSelected" :roles:fetched="rolesFetched = true" />
          </q-card>
        </div>
        <div class="col-xs-12 col-md-7 col-lg-5">
          <q-card
            outlined
            class="mx-auto"
          >
            <permission-list :role="selectedRole" @permissions:fetched="permissionFetched = true" />
          </q-card>
        </div>
      </div>
    </div>

  </q-page>
</template>

<script>
import RoleList from './UserAccess/RoleList';
import PermissionList from './UserAccess/PermissionList';

export default {
  name: 'PageUserAccess',
  meta() {
    return {
      title: this.$t('User Access') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('User Access') }
    ]
  },
	components: {
		RoleList,
		PermissionList
	},
	data() {
		return {
			selectedRole: null,
      permissionFetched: false,
      rolesFetched: false
		};
	},
  watch: {
    rolesFetched(n, o) {
      if (n !== o && n && this.permissionFetched) {
        this.$nextTick(() => {
          if (!this.$store.getters['tourGuide/finishedGroups']['user-access']) {
            setTimeout(() => {
              this.$tourGuide.open('user-access')
            }, 350)
          }
        })
      }
    },
    permissionFetched(n, o) {
      if (n !== o && n && this.rolesFetched) {
        this.$nextTick(() => {
          if (!this.$store.getters['tourGuide/finishedGroups']['user-access']) {
            setTimeout(() => {
              this.$tourGuide.open('user-access')
            }, 350)
          }
        })
      }
    }
  },
	methods: {
		onRoleSelected(role) {
			this.selectedRole = role ? (role.id || null) : null;
		}
	}
};
</script>

<style lang="scss">
.page-user-access {
  padding-bottom: 3rem;
}
</style>
