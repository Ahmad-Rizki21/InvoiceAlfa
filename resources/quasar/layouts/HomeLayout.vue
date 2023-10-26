<template>
  <q-layout view="LHh lpR fFf" class="main-layout" :class="{ 'customer-layout': $auth.authType !== 'u' }">
    <div v-if="$auth.authType !== 'u'" class="container">
      <div class="customer-layout-header">
        <p class="customer-name">{{ $auth.user.name }}</p>

        <q-space />

        <q-btn
          flat
          icon="logout"
          @click="onLogoutClick"
        >
          <q-tooltip>
            Logout
          </q-tooltip>
        </q-btn>
      </div>
    </div>

    <topbar v-if="$auth.authType === 'u'" />

    <sidebar v-if="$auth.authType === 'u'" />

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import MainLayout from './MainLayout'

export default {
  name: 'HomeLayout',
  extends: MainLayout,
  async preFetch({ redirect, store, currentRoute }) {
    if (!store.getters['auth/check']) {
      return redirect('/login')
    }

    const permissions = store.getters['auth/permissions']

    if (permissions && permissions.length && !permissions.includes('access.dashboard') && currentRoute.name !== 'unauthorized-access') {
      return redirect('/unauthorized-access')
    }

    await store.dispatch('datatable/initStore')
  },
  methods: {
    async onLogoutClick() {
      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to logout?'),
        cancel: true,
        ok: this.$t('Yes'),
        cancel: this.$t('No')
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
.customer-layout {
 &-header {
    padding: 1.5rem 0 0.5rem;
    // margin-bottom: 2rem;
    border-bottom: 1px solid rgba(208, 210, 216, 0.6);
    display: flex;
    align-items: center;

    .customer-name {
      font-weight: 500;
      color: #333;
      margin-bottom: 0;
    }
  }

  .container {
    width: 100%;
    padding-right: 1rem;
    padding-left: 1rem;
    margin-right: auto;
    margin-left: auto;

    @media (min-width: 576px) {
      max-width: 504px;
    }

    @media (min-width: 768px) {
      max-width: 706px;
    }

    @media (min-width: 992px) {
      max-width: 906px;
    }
    @media (min-width: 1200px) {
      max-width: 1040px;
    }
  }

  .page-cust {
    > .page-cust-header {
      padding: 1rem 0;
    }
  }
}

.body--light {
  .customer-layout {
    background-image: linear-gradient(to right top, #f5f5f8, #f2f3f8, #f0f0f8, #edeef8, #ebebf8);

    .q-page-container {
      background-color: transparent;
    }
  }
}
</style>
