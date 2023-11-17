<template>
  <q-drawer
    v-model="isVisible"
    show-if-above
    :bordered="false"
    content-class="sidebar-content"
    :elevated="!$q.dark.mode"
    :width="sidebarWidth"
    :mini="isMini"
  >
    <q-toolbar class="sidebar-toolbar">
      <q-toolbar-title :class="{ 'text-center': isMini }">
        {{ !isMini ? 'Web Console' : 'W' }}
      </q-toolbar-title>
    </q-toolbar>

    <q-list class="sidebar-content-list">
      <template
        v-for="(menu, i) in menuList"
      >
        <q-item-label
          v-if="menu.header"
          header
          class="text-grey-8"
          :key="`header${i}`"
        >
          {{ menu.header }}
        </q-item-label>
        <q-separator
          v-else-if="menu.separator"
          :key="`sep${i}`"
        />
        <q-item
          v-else
          clickable
          :to="menu.to"
          :target="menu.hrefTarget"
          :href="menu.href"
          :class="menu.class"
          exact
          :key="`item${i}`"
        >
          <q-item-section
            v-if="menu.icon"
            avatar
          >
            <q-icon :name="menu.icon" />
          </q-item-section>

          <q-item-section>
            <q-item-label>{{ menu.title }}</q-item-label>
            <q-item-label v-if="menu.caption" caption>
              {{ menu.caption }}
            </q-item-label>
          </q-item-section>
        </q-item>
      </template>
    </q-list>

    <div class="toolbar-bottom">
      <q-btn
        flat
        round
        :icon="isMini ? 'chevron_right' : 'chevron_left'"
        @click.prevent="isMini = !isMini"
      />
      <q-btn
        v-if="!isMini"
        flat
        round
        :icon="appConfig.dark ? 'light_mode' : 'dark_mode'"
        @click.prevent="toggleDarkMode"
      />
      <q-btn
        v-if="!isMini"
        flat
        round
        icon="help"
        class="btn-sidebar-activate-help"
        @click.prevent="onHelpClick"
      />
    </div>
  </q-drawer>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
  name: 'Sidebar',
  data() {
    return {
      isVisible: this.$q.screen.gt.sm,
      isMini: false,
      menuList: this.defaultMenuList()
    }
  },
  computed: {
    ...mapGetters({
      appConfig: 'app/settings',
      permissions: 'auth/permissions'
    }),
    sidebarWidth() {
      if (this.$q.screen.md) {
        return 200
      }

      return 275
    }
  },
  watch: {
    isVisible(n, o) {
      if (n !== o && n !== this.appConfig.sidebar) {
        this.appConfigSet({
          sidebar: n
        })
      }
    },
    'appConfig.sidebar': {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== undefined && n !== this.isVisible) {
          this.isVisible = n
        }
      }
    },
    permissions() {
      this.generateAvailableMenu();
    },
    '$i18n.locale'(n, o) {
      if (n !== o) {
        this.$nextTick(() => {
          this.generateAvailableMenu()
        })
      }
    }
  },
  mounted() {
    this.generateAvailableMenu();
  },
  methods: {
    ...mapActions({
      appConfigSet: 'app/set',
      appConfigSave: 'app/save'
    }),
    defaultMenuList() {
      return [
        {
          header: this.$t('Report'),
          permissions: ['access.dashboard', 'access.report']
        },
        {
          title: this.$t('Dashboard'),
          icon: 'home',
          to: '/',
          class: 'sidebar-menu-item-dashboard',
          permissions: ['access.dashboard']
        },
        // {
        //   title: this.$t('Report'),
        //   icon: 'summarize',
        //   to: '/reports',
        //   class: 'sidebar-menu-item-report',
        //   permissions: ['access.report']
        // },
        {
          header: this.$t('Data'),
          permissions: ['access.dashboard']
        },
        {
          title: this.$t('Invoice'),
          icon: 'receipt_long',
          to: '/invoices',
          class: 'sidebar-menu-item-invoices',
          permissions: ['access.dashboard']
        },
        // {
        //   title: this.$t('Invoice'),
        //   icon: 'web',
        //   to: '/template/invoices',
        //   class: 'sidebar-menu-item-template-invoices',
        //   permissions: ['access.dashboard']
        // },
        {
          title: this.$t('Distribution Center'),
          icon: 'business_center',
          to: '/distribution-centers',
          class: 'sidebar-menu-item-distribution-centers',
          permissions: ['access.dashboard']
        },
        // {
        //   title: this.$t('Receipt'),
        //   icon: 'web_asset',
        //   to: '/template/receipts',
        //   class: 'sidebar-menu-item-template-receipts',
        //   permissions: ['access.dashboard']
        // },
        // {
        //   header: this.$t('Customer'),
        //   permissions: ['access.dashboard']
        // },
        // {
        //   title: this.$t('Stores'),
        //   icon: 'storefront',
        //   to: '/stores',
        //   class: 'sidebar-menu-item-stores',
        //   permissions: ['access.dashboard']
        // },
        {
          header: this.$t('System'),
          permissions: ['manage.users', 'create.role', 'edit.role', 'delete.role', 'edit.permission']
        },
        {
          title: this.$t('Settings'),
          icon: 'settings',
          to: '/settings',
          class: 'sidebar-menu-item-settings',
          permissions: ['manage.users']
        },
        {
          title: this.$t('Users'),
          icon: 'group',
          to: '/users',
          class: 'sidebar-menu-item-users',
          permissions: ['manage.users']
        },
        // {
        //   title: this.$t('User Access'),
        //   icon: 'diversity_3',
        //   to: '/user-access',
        //   class: 'sidebar-menu-item-user-access',
        //   permissions: ['create.role', 'edit.role', 'delete.role', 'edit.permission']
        // },
      ]
    },
    toggleDarkMode() {
      const dark = !this.appConfig.dark

      this.appConfigSet({ dark })

      this.$q.dark.set(dark)

      this.appConfigSave()
    },

    generateAvailableMenu() {
      if (!this.permissions.includes('access.dashboard')) {
        this.menuList = []
      } else {
        this.menuList = this.checkMenuItems(this.defaultMenuList());
      }
    },
    checkMenuItems(items) {
      return items.map(item => {
        if (item.permissions || item.roles) {
          if (item.permissions && this.checkMenuItemPermissionAvailability(item)) {
            return this.checkMenuItemRoleAvailability(item);
          }

          if (item.roles && this.checkMenuItemRoleAvailability(item)) {
            return this.checkMenuItemPermissionAvailability(item);
          }
        } else {
          return this.checkMenuItemChildren(item);
        }
      }).filter(v => !!v);
    },
    checkMenuItemPermissionAvailability(item) {
      if (item.permissions) {
        if (this.permissions.some(v => item.permissions.includes(v))) {
          return this.checkMenuItemChildren(item);
        }

        return null;
      }

      return item;
    },
    checkMenuItemRoleAvailability(item) {
      if (item.roles) {
        if (item.roles.find(r => r == this.$auth.user.role_id)) {
          return this.checkMenuItemChildren(item)
        }

        return null;
      }

      return item;
    },
    checkMenuItemChildren(item) {
      if (!item) {
        return null;
      }

      if (item.items) {
        if (item.items.length) {
          item.items = this.checkMenuItems(item.items);
        }

        if (!item.items.length) {
          return item.noAction ? null : item;
        }
      }

      return item;
    },
    onHelpClick() {
      this.$tourGuide.open(this.$route.name)
    }
  }
}
</script>

<style lang="scss">
.sidebar-content {
  .sidebar-toolbar {
    border-right: 1px solid #e9edf4;
    border-bottom: 1px solid #e9edf4;

    > .q-toolbar__title {
      @media (min-width: $breakpoint-md-min) {
        font-weight: 500;
      }
      @media (min-width: $breakpoint-md-min) and (max-width: $breakpoint-md-max) {
        text-align: center;
      }
      @media (min-width: $breakpoint-lg-min) {
        font-weight: bold;
        padding-left: 2.75rem;
      }
    }
  }
  .toolbar-bottom {
    position: absolute;
    bottom: 0;
    padding-left: 0.5rem;
    padding-bottom: 1.5rem;

    @media (max-height: 589px) {
      background-color: inherit;
      width: 100%;
      padding-top: 2rem;
      padding-bottom: 0.5rem;
      position: relative;
    }
  }

  .q-item__label--header {
    padding-top: 1.5rem;
    padding-bottom: 0.25rem;
  }
}
.q-drawer--mini {
  .sidebar-content {
    .sidebar-toolbar {
      > .q-toolbar__title {
        @media (min-width: $breakpoint-md-min) {
          padding-left: 0.15rem;
        }
      }
    }
  }
}

.body--dark {
  .sidebar-content {
    .sidebar-toolbar {
      border-color: rgba(0, 0, 0, 0.1);
    }
  }
}
</style>
