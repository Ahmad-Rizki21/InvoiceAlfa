<template>
  <q-layout view="LHh lpR fFf" class="main-layout">
    <topbar />

    <sidebar />

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { mapGetters } from 'vuex'
import Topbar from './MainLayout/Topbar'
import Sidebar from './MainLayout/Sidebar'

export default {
  name: 'MainLayout',
  async preFetch({ redirect, store, currentRoute }) {
    if (!store.getters['auth/check']) {
      return redirect('/login')
    }

    const permissions = store.getters['auth/permissions']

    if (permissions && permissions.length && !permissions.includes('access.dashboard') && currentRoute.name !== 'unauthorized-access') {
      return redirect('/unauthorized-access')
    }

    await store.dispatch('datatable/initStore')
    await store.dispatch('imports/initStore')

    // if (!store.getters['tourGuide/finishedGroups'].default) {
    //   if (currentRoute.name !== 'index') {
    //     return redirect('/')
    //   }
    // } else {
    //   store._vm.$tourGuide.setOptions({
    //     closeButton: true,
    //     exitOnEscape: true,
    //     exitOnClickOutside: true
    //   })
    // }
  },
  components: {
    Topbar,
    Sidebar
  },
  data () {
    return {
    }
  },
  computed: {
    ...mapGetters({
      appConfig: 'app/settings',
    }),
  },
  created() {
    this.$q.dark.set(this.appConfig.dark)
    const locale = this.$store.getters['auth/locale']

    this.$i18n.locale = (locale === 'en-us' ? 'en' : locale)

    import(
      /* webpackInclude: /(id|en-us)\.js$/ */
      'quasar/lang/' + (locale === 'en' ? 'en-us' : locale)
    ).then(lang => {
      this.$q.lang.set(lang.default)
    })
  },

  beforeDestroy() {
    this.$ws.close()
  }
}
</script>

<style lang=scss>
.q-drawer,
.q-menu {
  .q-item__section--avatar {
    min-width: 28px;
  }
}

.main-layout {
  .q-page-container {
    box-sizing: border-box;

    .page-header {
      width: 100%;
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
      padding: 0 1rem;
      min-height: 50px;

      @media (max-width: $breakpoint-sm-max) {
        display: none;
      }

      @media (min-width: $breakpoint-md-min) {
        padding-top: 1.5rem;
        padding-bottom: 0.5rem;
      }

      .sep {
        margin-right: auto;
      }
    }

    .page-body {
      box-sizing: border-box;

      > .q-card {
        > .q-toolbar {
          margin-bottom: 1.5rem;
          display: none;

          @media (min-width: $breakpoint-md-min) {
            display: flex;
          }
        }
      }
    }

    .page-header,
    .page-body {
      @media (min-width: $breakpoint-md-min) {
        padding-left: 2.25rem;
        padding-right: 2.25rem;
      }
    }
  }
}

.body--light {
  .main-layout {
    .q-page-container {
      background-color: #f5f5f8;
    }
  }
}
</style>
