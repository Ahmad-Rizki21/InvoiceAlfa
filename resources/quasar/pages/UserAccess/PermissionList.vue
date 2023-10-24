<template>
  <div class="permission-list">
    <q-toolbar
      flat
    >
      <q-toolbar-title>{{ $t('Permission') }}</q-toolbar-title>

      <q-space />

      <q-btn
        v-if="!!role"
        :loading="isLoading"
        color="primary"
        class="btn-permission-save"
        :class="{ 'q-btn-lg': $q.screen.gt.md }"
        @click="onPermissionSave"
      >
        {{ $t('Save') }}
      </q-btn>
    </q-toolbar>

    <div v-if="!!role" class="q-pt-lg q-pl-lg">
      <div
        class="row"
        v-for="(perms, group) in permissions"
        :key="group"
      >
        <div
          class="col-xs-12"
        >
          <q-card
            max-width="475"
            class="mx-auto"
            flat
          >
            <q-card-section>
              <q-list flat>
                <q-item-label class="group-label">{{ $t(group) }}</q-item-label>

                <q-item
                  v-for="permission in perms"
                  :key="permission.id"
                  clickable
                  :class="`btn-permission-item-${permission.id}`"
                  @click="onPermissionClick(permission.id)"
                  :disable="role == $constant.role.TYPE_SUPER_ADMIN && ['access.dashboard', 'manage.permission', 'edit.permission'].includes(permission.key)"
                >
                  <q-item-section>
                    <q-item-label>{{ permission.name }}</q-item-label>
                  </q-item-section>

                  <q-item-section side>
                    <q-checkbox
                      v-model="selectedPermissionsId"
                      :val="permission.id"
                      :disable="role == $constant.role.TYPE_SUPER_ADMIN && ['access.dashboard', 'manage.permission', 'edit.permission'].includes(permission.key)"
                    />
                  </q-item-section>

                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
    <q-card-section
      v-else
      class="empty-permission"
    >
      <em>{{ $t('Please select role on the left side') }}</em>
  </q-card-section>
  </div>
</template>

<script>
export default {
  name: 'PermissionList',
	props: {
		role: {
			type: Number,
			default: null
		}
	},
	data() {
		return {
			permissions: [],
			roles: [],
			isDialogRoleVisible: false,
			isLoading: false,
      selectedPermissionsId: []
		};
	},
	watch: {
		role: {
			deep: true,
			immediate: true,
			async handler(n, o) {
				if (n !== o) {
					await this.fetchRolePermission();

          // this.$nextTick(() => {
          //   if (n == this.$constant.role.TYPE_SUPER_ADMIN) {
          //     const selectedRoles = []

          //     for (const moduleName in this.permissions) {
          //       this.permissions[moduleName].forEach(v => {
          //         selectedRoles.push(v.id)
          //       })
          //     }

          //     this.roles = selectedRoles
          //   }
          // })
				}
			}
		}
	},
	mounted() {
		this.fetchPermissions();
	},
	methods: {
		async fetchPermissions() {
			try {
				let { data } = await this.$api.get('/v1/user-access/permissions');

				if (data.status === 'success') {
          // let permissions = data.data.permissions || {};

          // const arraySorter = [
          //   'Dashboard',
          //   'Report',
          //   'Ticket',
          //   'Customer',
          //   'Remote Location',
          //   'Users'
          // ];

          // const sortedObject = {};

          // Object.keys(permissions).sort((a, b) => {
          //   const indexA = arraySorter.indexOf(a);
          //   const indexB = arraySorter.indexOf(b);

          //   if (indexA === -1 && indexB === -1) {
          //     return a.localeCompare(b);
          //   }

          //   if (indexA === -1) {
          //     return 1;
          //   }

          //   if (indexB === -1) {
          //     return -1;
          //   }

          //   return indexA - indexB;
          // })
          // .forEach(key => {
          //   sortedObject[key] = permissions[key];
          // });

					this.permissions = data.data.permissions;

          this.$emit('permissions:fetched', data.data.permissions)
				} else {
					this.$q.notify({ message: data.message || 'Failed to load permissions' });
				}
			} catch (err) {
        console.error(err)
				this.$q.notify(err);
			}
		},
		async fetchRolePermission() {
			const role = this.role;

			if (!role) {
				return;
			}

			try {
				let { data } = await this.$api.get(`/v1/user-access/permissions/${role}/role`);

				if (data.status === 'success') {
					this.selectedPermissionsId = data.data.permission_ids;
          const permissions = {...this.permissions}
          this.permissions = {}
          await this.$nextTick()
          this.permissions = permissions
				} else {
					this.$q.notify({ message: data.message || 'Failed to fetch roles' });
				}
			} catch (err) {
				this.$q.notify(err);
			}
		},
    onPermissionClick(permissionId) {
      if (this.selectedPermissionsId.includes(permissionId)) {
        this.selectedPermissionsId = this.selectedPermissionsId.filter(v => v !== permissionId)
      } else {
        this.selectedPermissionsId = [...this.selectedPermissionsId, permissionId]
      }
    },
		async onPermissionSave() {
      if (this.isLoading) {
        return
      }

			this.isLoading = true;

			const role = this.role;

			try {
				let { data } = await this.$api.patch(`/v1/user-access/permissions/${role}/role`, {
					permissions: this.selectedPermissionsId
				});

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
				  this.$q.notify({ message: this.$t(data.status === 'success' ? 'Permissions successfully updated' : 'Failed to update permissions') });
        }

				this.$store.dispatch('auth/fetch');
			} catch (err) {
				this.$q.notify(err);
			}

			this.isLoading = false;
		}
	}
};
</script>

<style lang="scss">
.permission-list {
  .q-toolbar__title {
    font-size: 0.9em;
    font-weight: 600;
  }

  .empty-permission {
    width: 100%;
    min-height: 35vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #aaa;
  }

  .btn-permission-save {
    position: absolute;
    top: 1rem;
    right: 1rem;
  }

  .group-label {
    font-weight: bold;
  }

  .q-hoverable {
    > .q-focus-helper {
      opacity: 0 !important;
    }
  }

  // $x: 2;

  // @for $i from 1 through ($x - 1) {
  //   .masonry > div:nth-child(#{$x}n + #{$i}) {
  //     order: #{$i};
  //   }
  // }

  // .masonry > div:nth-child(#{$x}n) {
  //   order: #{$x}
  // }

  // .masonry {
  //   .col-item {
  //     width: 50%;
  //     padding: 1px;

  //     > div {
  //       padding: 4px 8px;
  //     }
  //   }
  // }
}
</style>
