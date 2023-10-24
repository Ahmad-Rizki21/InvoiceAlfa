<template>
  <q-layout class="auth-layout" view="lHh lpR fFf">
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
  name: 'AuthLayout',
  preFetch({ redirect, store }) {
    if (store.getters['auth/check']) {
      return redirect('/')
    }
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
  }
}
</script>

<style lang="scss" scoped>
.body--light {
  .auth-layout {
    background-color: #f5f5f5;
  }
}
.body--dark {
  .auth-layout {
    background-color: #7d0400;
  }
}

#layout-login {
  background-color: #f5f5f5;

  &.theme--dark {
    background-color: #7d0400;
  }

  .main-row {
    align-items: center;
    position: relative;

    > .col-form {
      z-index: 1;
      position: relative;
      top: 1vh;
    }
  }
}
</style>
