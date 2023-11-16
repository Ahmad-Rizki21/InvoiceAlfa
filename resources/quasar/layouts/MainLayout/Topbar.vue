<template>
  <q-header
    :height-hint="48"
    :bordered="false"
    class="topbar"
  >
    <q-toolbar>
      <portal-target v-if="$q.screen.lt.md && hasAppToolbarPortal" name="app-toolbar" class="app-toolbar" />
      <template v-else>

        <!-- <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="onToggleSidebar"
        /> -->

        <q-toolbar-title v-if="$root.__currentMeta && $root.__currentMeta.title && !hasAppBreadcrumbsPortal">
          {{ $root.__currentMeta ? $root.__currentMeta.title : '' }}
        </q-toolbar-title>

        <portal-target v-if="$q.screen.gt.sm" name="app-breadcrumbs" class="app-breadcrumbs" />

        <div class="sep" />

        <q-select
          v-model="selectedLocale"
          borderless
          :options="localeOption"
          emit-value
          dense
        >
          <template #selected-item>
            {{ selectedLocaleDisplay }}
          </template>
        </q-select>

        <q-btn
          flat
          class="q-ml-md btn-topbar-menu-item-user"
          :label="$auth.user.name || $auth.user.email || 'Hello, user!'"
          icon-right="arrow_drop_down"
        >
          <q-menu auto-close anchor="bottom left" self="top left" :offset="[0, 6]">
            <q-list class="topbar-menu">
              <q-item clickable class="menu-profile" :to="`/@${$auth.user.username || $auth.user.hash}`">
                <q-item-section side class="q-pr-sm">
                  <avatar-field
                    :value="$auth.user.avatar"
                    :initial="$utils.initials($auth.user.name || $auth.user.username || $auth.user.email)"
                    size="36px"
                    readonly
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ $auth.user.name || '-' }}</q-item-label>
                  <q-item-label caption>{{ $auth.user.email }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable to="/account/profile">
                <q-item-section avatar>
                  <q-avatar icon="settings" />
                </q-item-section>
                  <q-item-section>{{ $t('Account Settings') }}</q-item-section>
              </q-item>
              <q-item clickable to="/account/password">
                <q-item-section avatar>
                  <q-avatar icon="lock_open" />
                </q-item-section>
                  <q-item-section>{{ $t('Change Password') }}</q-item-section>
              </q-item>
              <q-item clickable @click="onLogoutClick">
                <q-item-section avatar>
                  <q-avatar icon="logout" />
                </q-item-section>
                  <q-item-section>{{ $t('Logout') }}</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </template>
    </q-toolbar>
  </q-header>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import { Wormhole } from 'portal-vue'

export default {
  name: 'Topbar',
  data() {
    return {
      selectedLocale: null,
      localeOption: [
        { label: 'Bahasa Indonesia', value: 'id', display: 'ID' },
        { label: 'English', value: 'en', display: 'EN' }
      ],
      syncLocale: this.$utils.debounce(async (locale) => {
        this.$i18n.locale = (locale === 'en-us' ? 'en' : locale)

        import(
          /* webpackInclude: /(id|en-us)\.js$/ */
          'quasar/lang/' + (locale === 'en' ? 'en-us' : locale)
        ).then(lang => {
          this.$q.lang.set(lang.default)
        })

        if (locale !== this.$store.getters['auth/locale']) {
          await this.authUpdate({ locale: locale === 'en-us' ? 'en' : locale })
        }
      }, 1000)
    }
  },
  computed: {
    ...mapGetters({
      appConfig: 'app/settings'
    }),
    hasAppToolbarPortal() {
      return Wormhole.hasContentFor('app-toolbar')
    },
    hasAppBreadcrumbsPortal() {
      return Wormhole.hasContentFor('app-breadcrumbs')
    },
    selectedLocaleDisplay() {
      if (this.selectedLocale) {
        const value = this.localeOption.find(v => v.value === this.selectedLocale)

        if (value) {
          return value.display
        }
      }

      return this.$i18n.locale
    }
  },
  watch: {
    '$i18n.locale': {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.selectedLocale) {
          this.selectedLocale = n === 'en-us' ? 'en' : n
        }
      }
    },
    selectedLocale(n, o) {
      if (n !== o && n !== this.appConfig.locale) {
        this.syncLocale(n)
      }
    }
  },
  methods: {
    ...mapActions({
      appConfigSet: 'app/set',
      authUpdate: 'auth/update'
    }),
    onToggleSidebar() {
      this.appConfigSet({
        sidebar: !this.appConfig.sidebar
      })
    },
    async onLogoutClick() {
      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to logout?'),
        cancel: true,
        ok: this.$t('Yes'),
        cancel: this.$t('Nope')
      }).onOk(async () => {
        await this.$store.dispatch('auth/logout');

        this.isUserMenuVisible = false;

        this.$router.replace('/login');
      })
    }
  }
}
</script>

<style lang="scss">
.topbar {
  &.q-layout__section--marginal {
    color: #333;
    background-color: #fff;
  }

  .q-toolbar__title {
    flex: none;
  }

  .sep {
    margin-right: auto;
  }

  .app-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    line-height: 1.715em;
    width: 100%;
  }

  .app-breadcrumbs {
    @media (min-width: 1024px) {
      margin-left: 1.5rem;
    }
  }
}

.topbar-menu {
  min-width: 175px;

  @media (max-width: $breakpoint-md-max) {
    font-size: 0.9em;
  }

  .q-item__section--avatar {
    min-width: 0;
    padding-right: 8px;

    .q-avatar {
      width: auto;
      height: auto;
    }
  }

  .menu-profile {
    .q-item__label {
      font-size: 1.15em;

      &.q-item__label--caption {
        font-size: 0.95em;
      }
    }
  }
}

.body--dark {
  .topbar {
    &.q-layout__section--marginal {
      background-color: $dark;
      color: #fff;
    }
  }
}
</style>
