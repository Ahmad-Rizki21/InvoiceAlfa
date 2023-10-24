<template>
  <div class="q-pb-md role-list">
    <q-toolbar
      flat
      dense
    >
      <q-toolbar-title>{{ $t('Role') }}</q-toolbar-title>

      <q-space />

      <q-btn
        v-if="$auth.can('create.role')"
        flat
        class="btn-role-list-create"
        padding="xs"
        @click="onRoleCreate"
      >
        <q-icon name="add" />
      </q-btn>
    </q-toolbar>

    <q-list>
      <q-item
        v-for="item in roles"
        :key="item.id"
        clickable
        :disable="!$auth.can('edit.permission')"
        :active="selectedRoleId && selectedRoleId === item.id"
        @click="selectedRoleId = item.id"
      >
        <q-item-section>
          <q-item-label>{{ item.name }}</q-item-label>
          <q-item-label caption>{{ item.description }}</q-item-label>
        </q-item-section>


        <q-item-section v-if="$auth.can('edit.role')" side>
          <q-btn
            class="gt-xs btn-role-list-edit"
            size="12px"
            flat
            dense
            round
            icon="edit"
            @click.prevent="onRoleEdit(item)"
          />
        </q-item-section>
      </q-item>
    </q-list>


    <q-dialog
      v-model="isDialogRoleVisible"
      hide-overlay
      transition="dialog-bottom-transition"
      scrollable
      persistent
      max-width="600px"
    >
      <form-role
        :entry="formRole"
        :loading="isLoading"
        @cancel="onRoleCancel"
        @success="onRoleSuccess"
        @deleted="onRoleDeleted"
      />
    </q-dialog>
  </div>
</template>

<script>
import FormRole from './FormRole';

export default {
  name: 'RoleList',
	components: {
		FormRole
	},
	data() {
		return {
			selectedRoleId: null,
			roles: [],
      formRole: {},
			isDialogRoleVisible: false,
			isLoading: false
		};
	},
	watch: {
		selectedRoleId(n, o) {
			if (n !== o) {
				this.$emit('selected', this.roles.find((v) => v.id === n));
			}
		}
	},
	async mounted() {
		await this.onRequest();

    this.$nextTick(() => {
      for (let i = 0; i < this.roles.length; i++) {
        this.selectedRoleId = this.roles[i].id

        break
      }
    })
	},
	methods: {
		async onRequest() {
			try {
				let { data } = await this.$api.get('/v1/user-access/roles');

				if (data.status === 'success') {
          const superAdminId = this.$constant.role.TYPE_SUPER_ADMIN;
          // const isSuperAdmin = this.$auth.hasRole(superAdminId);

					// this.roles = data.data.roles.filter(v => {
          //   if (!isSuperAdmin && superAdminId == v.id) {
          //     return false;
          //   }

          //   return true
          // });
          this.roles = data.data.roles
          this.$emit('roles:fetched', data.data.roles)
				} else {
					this.$q.notify({ message: data.message || 'Failed to fetch roles' });
				}
			} catch (err) {
				let errMsg = err.message;

				if (err.response && err.response.data && err.response.data.message) {
					errMsg = err.response.data.message;
				}

				this.$q.notify({ message: errMsg });
			}
		},
		onRoleCreate() {
      this.formRole = {}
			this.isDialogRoleVisible = true;
		},
		onRoleEdit(role) {
      this.formRole = role
			this.isDialogRoleVisible = true;
		},
    onRoleCancel() {
      this.formRole = {}
      this.isDialogRoleVisible = false
    },
    onRoleSuccess() {
      this.formRole = {}
      this.isDialogRoleVisible = false
      this.onRequest()
    },
    onRoleDeleted() {
      this.formRole = {}
      this.isDialogRoleVisible = false
      this.onRequest()
    },
    async onRoleSubmit(role) {
      if (this.isLoading) {
        return;
      }

      this.isLoading = true;

      try {
        let { data } = await this.$api[(role.id ? 'patch' : 'post')](`user-access/role${role.id ? '/' + role.id : ''}`, role);

        if (data.status === 'success') {
          this.isDialogRoleVisible = false;
          await this.fetchRoles();
        }

        this.$snackbar({ text: (data.status === 'success' ? 'Role successfully updated' : 'Failed to update role') });
      } catch (err) {
        let errMsg = err.message;

        if (err.response && err.response.data && err.response.data.message) {
          errMsg = err.response.data.message;
        }

        this.$snackbar({ text: errMsg });
      }

      this.isLoading = false;
    },
		async onRoleDelete(role) {
      if (this.isLoading) {
        return;
      }

      this.isLoading = true;

      try {
        const { data } = await this.$axios.delete(`/user-access/role/${role.id}`)

        if (data.status === 'success') {
          this.isDialogRoleVisible = false;
          await this.fetchRoles();
        }

        if (data.message) {
          this.$snackbar({ text: data.message });
        } else {
          this.$snackbar({ text: (data.status === 'success' ? 'Role successfully deleted' : 'Failed to delete role') });
        }
      } catch (err) {
        this.$snackbar(err);
      }

      this.isLoading = false;
    }
	}
};
</script>

<style lang="scss">
.role-list {
  .q-toolbar__title {
    font-size: 0.9em;
    font-weight: 600;
  }
  .q-list {
    .q-item--active {
      background-color: rgba(0, 0, 0, 0.08);

      .q-item__label {
        font-weight: bold;
        color: var(--q-color-primary);
      }
    }
  }
}
</style>
