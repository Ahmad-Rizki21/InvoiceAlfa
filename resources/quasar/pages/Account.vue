<template>
  <q-page class="page-account page-account-account">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <div class="row q-col-gutter-md">
      <div class="col-3">
        <q-list bordered separator>
          <q-item clickable to="/account/profile" exact>
            <q-item-section>
              <q-item-label>{{ $t('Account') }}</q-item-label>
            </q-item-section>
          </q-item>
          <q-item clickable to="/account/password" exact>
            <q-item-section>
              <q-item-label>{{ $t('Change Password') }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </div>
      <div class="col-7">
        <transition
          enter-active-class="animated fadeIn"
          leave-active-class="animated fadeOut"
          mode="out-in"
        >
          <q-card v-if="$route.params.tab === 'password'" key="password" :flat="!$q.dark.mode" :borderless="!$q.dark.mode">
            <q-card-section>
              <div class="q-pb-lg">
                <h3 class="text-body1 text-weight-bold q-ma-none">{{ $t('Change Password') }}</h3>
              </div>

              <q-form
                @submit="onPasswordSubmit"
                @reset="onPasswordReset"
                class="q-gutter-md"
              >
                <q-input
                  filled
                  v-model="formPassword.password"
                  :label="$t('New Password')"
                  lazy-rules
                  type="password"
                  :rules="rules.password"
                />

                <q-input
                  filled
                  v-model="formPassword.password_confirmation"
                  :label="$t('New Password Confirmation')"
                  lazy-rules
                  type="password"
                  :rules="rules.password_confirmation"
                  disabled
                />

                <div>
                  <q-btn :label="$t('Submit')" type="submit" color="primary" unelevated/>
                  <q-btn :label="$t('Reset')" type="reset" color="primary" flat class="q-ml-sm" />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
          <q-card v-else key="default" :flat="!$q.dark.mode" :borderless="!$q.dark.mode">
            <q-card-section>
              <div class="q-pb-lg">
                <h3 class="text-body1 text-weight-bold q-ma-none">{{ $t('Update Account') }}</h3>
              </div>


              <q-form
              @submit="onAccountSubmit"
              @reset="onAccountReset"
              class="q-gutter-md"
              >
                <avatar-field
                  v-model="formAvatar.avatar"
                  :initial="$utils.initials($auth.user.name || $auth.user.username || $auth.user.email)"
                  readonly
                />

                <q-input
                  filled
                  v-model="formAccount.name"
                  :label="$t('Name')"
                  lazy-rules
                  :rules="rules.name"
                />

                <q-input
                  filled
                  v-model="formAccount.username"
                  :label="$t('Username')"
                  lazy-rules
                  :rules="rules.username"
                />

                <q-input
                  filled
                  v-model="formAccount.email"
                  :label="$t('Email')"
                  lazy-rules
                  :rules="rules.email"
                />

                <div>
                  <q-btn :label="$t('Save')" type="submit" color="primary" unelevated/>
                  <q-btn :label="$t('Reset')" type="reset" color="primary" flat class="q-ml-sm" />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </transition>
      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'PageAccount',
  meta() {
    let title = this.$t('Update Account');

    if (this.$route.params.tab === 'password') {
      title = this.$t('Change Password')
    }

    return {
      title: title + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    let title = this.$t('Update Account');

    if (this.$route.params.tab === 'password') {
      title = this.$t('Change Password')
    }

    return [
      'home',
      { text: this.$t('My Account') },
      { text: title }
    ]
  },
  data() {
    const $user = this.$auth.user

    return {
      formAccount: {
        name: $user.name,
        username: $user.username,
        email: $user.email
      },
      formPassword: {
        password: null,
        password_confirmation: null
      },
      formAvatar: {
        avatar: $user.avatar
      },
      rules: {
        name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Name') })
        ],
        email: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Email') })
        ],
        username: [
          v => !!v && String(v).length > 3 || this.$t('{field} must be at least {length} characters', { field: this.$t('Username'), length: 3 }),
          v => !!v && String(v).length <= 30 || this.$t('{field} may not be greater than {max}.', { field: this.$t('Username'), max: 30 }),
          v => !!v && /^[a-zA-Z0-9_\.]+$/.test(String(v)) || this.$t('{field} can only contain alphanumeric characters, underscores, and periods', { field: this.$t('Username') })
        ],
        password: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password') }),
          v => v && v.length >= 6 || this.$t('{field} must be at least {length} characters', { field: this.$t('Password'), length: 6 })
        ],
        password_confirmation: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password confirmation') }),
          v => v === this.formPassword.password || this.$t('{field} does not match', { field: this.$t('Password confirmation') })
        ]
      }
    }
  },
  methods: {
    async onAccountSubmit() {
      try {
        const payload = {
          name: this.formAccount.name,
          username: this.formAccount.username,
          email: this.formAccount.email
        }

        const { data } = await this.$api.patch('/v1/auth/me', payload)


        if (data.status === 'success') {
          await this.$utils.delay(500);
          this.$store.dispatch('auth/fetch');
        }

        this.$q.notify({ message: this.$t(data.status === 'success' ? 'Your account successfully updated' : 'Failed to update your account') });
      } catch (err) {
        this.$q.notify(err)
      }
    },
    onAccountReset() {
      const $user = this.$auth.user

      this.formAccount = {
        name: $user.name,
        email: $user.email
      }
    },
    async onPasswordSubmit() {
      try {
        const payload = {
          password: this.formPassword.password,
          password_confirmation: this.formPassword.password_confirmation
        }

        const { data } = await this.$api.patch('/v1/auth/me', payload)


        if (data.status === 'success') {
          await this.$utils.delay(500);
          this.$store.dispatch('auth/fetch');
        }

        this.$q.notify({ message: this.$t(data.status === 'success' ? 'Your password successfully updated' : 'Failed to update your password') });
      } catch (err) {
        this.$q.notify(err);
      }
    },
    onPasswordReset() {
      const $user = this.$auth.user

      this.formPassword = {
        password: null,
        password_confirmation: null
      }
    }
  }
}
</script>

<style lang="scss">
.page-account {
  padding: 3rem;
}
</style>
