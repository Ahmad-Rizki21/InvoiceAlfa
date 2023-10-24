<template>
  <q-page class="page-login row">
    <div class="col-xs-12 col-md-4 offset-md-4 col-form">
      <div class="col-card">
        <h1 class="text-h5 text-center">{{ $t('Web Console') }}</h1>
      </div>

      <!-- <tabs-login-register /> -->

      <q-form @submit.prevent="onSubmit" ref="form" class="col-form-inner" greedy>
        <q-card class="elevation-2">
          <q-toolbar flat>
            <q-toolbar-title>
              <h2>{{ $t('Login') }}</h2>
              <p class="mb-0">{{ $t('Login to continue') }}&hellip;</p>
            </q-toolbar-title>
          </q-toolbar>
          <q-card-section>
            <q-input
              v-model="formAuth.username"
              :label="$t('Username') + '/' + $t('Email')"
              name="username"
              class="q-mb-md"
              autofocus
              :rules="rules.username"
              stack-label
            >
              <template v-slot:prepend>
                <q-icon name="person" />
              </template>
            </q-input>

            <q-input
              id="password"
              v-model="formAuth.password"
              :label="$t('Password')"
              name="password"
              :type="showPassword ? 'text' : 'password'"
              stack-label
              :rules="rules.password"
            >
              <template v-slot:prepend>
                <q-icon name="lock" />
              </template>
              <template v-slot:append>
                <q-btn
                  round
                  dense
                  flat
                  :icon="showPassword ? 'visibility_off' : 'visibility'"
                  @click="showPassword = !showPassword"
                />
              </template>
            </q-input>
          </q-card-section>
          <q-card-section class="card-action">
            <q-btn
              type="submit"
              color="primary"
              class="q-btn-lg"
              :loading="isLoading"
            >
              {{ $t('Login') }}
            </q-btn>

            <!-- <q-btn
              color="primary"
              size="md"
              flat
              class="forgot"
              to="/forgot-password"
            >
              {{ $t('Forgot password?') }}
            </q-btn> -->
          </q-card-section>
        </q-card>
      </q-form>
    </div>

  </q-page>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'PageLogin',
  meta() {
    return {
      title: this.$t('Login') + ' - ' + this.$t('Web Console')
    }
  },
  data() {
    return {
      isLoading: false,
      formAuth: {
        username: null,
        password: null,
      },
      showPassword: false,
      rules: {
        username: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Username') + '/' + this.$t('Email') })
        ],
        password: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password') })
        ],
      }
    }
  },
	methods: {
    ...mapActions({
      login: 'auth/login'
    }),
    async onSubmit() {
      if (this.isLoading) {
        return;
      }

      const isValid = await this.$refs.form.validate()

      if (!isValid) {
        return
      }

      this.isLoading = true;

      try {
        let { data } = await this.$api.post('/v1/login', {
          username: this.formAuth.username,
          password: this.formAuth.password,
          client_code: this.$constant.user_access_token.CLIENT_CONSOLE
        });

        const user = data.data.user
        const token = data.data.token

        await this.login({ user, token });

        window.location.reload();
      } catch (err) {
        this.$q.notify(err);
      }

      this.isLoading = false;
    },
  },
}
</script>

<style lang="scss">
.page-login {
  padding: 0;
  border: 1px !important;
  height: 100%;

  .col-form {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;

    .col-card {
      width: 100%;

      h1 {
        margin-bottom: 3rem;
        font-weight: 900;
      }
    }

    .col-form-inner {
      width: 100%;
      height: 500px;

      > .q-card {
        width: 100%;
      }
    }
  }

  .col-background {
    align-items: baseline;
    justify-content: center;
    padding-top: 5%;

    .img {
      max-width: 571px;
    }
  }

  .q-toolbar {
    padding: 1rem 0 0 2rem;

    > .q-toolbar__title {
      color: #5d5d5d;

      h2 {
        font-weight: 500;
        font-size: 1em;
        // margin-bottom: 0.25rem;
        margin: 0;
        line-height: 1.4;
      }

      p {
        font-weight: 200;
        font-size: 0.8em;
      }
    }
  }

  .card-action {
    padding: 2rem;
    display: flex;
    align-items: center;

    button[type="submit"] {
      @media (min-width: $breakpoint-lg-min) {
        height: 52px;
        min-width: 92px;
        padding: 0 23.1111111111px;
      }
    }

    .forgot {
      margin-left: auto;
    }
  }

  input:-webkit-autofill,
  input:-webkit-autofill:hover,
  input:-webkit-autofill:focus,
  input:-webkit-autofill:active,
  input:-internal-autofill-selected  {
    -webkit-box-shadow: yellow !important;
    color: #2a2a2a !important;
    background-color: #7d0400 !important;
    -webkit-text-fill-color: #333 !important;
  }
}
</style>
