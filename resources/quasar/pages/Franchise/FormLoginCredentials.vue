<template>
  <q-card class="form-entry q-pa-sm">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div class="text-h6 q-px-md q-pt-sm">
        {{ $t('Login Credentials') }}
      </div>
      <small class="q-px-md ">
        {{ entry.name }}
      </small>
      <q-card-section class="q-mb-md">
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.email"
              type="email"
              :label="$t('Email') + '*'"
              filled
              dense
              stack-label
              :rules="rules.email"
              :error="!!errors.email"
              :error-message="errors.email"
            />
          </div>
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.username"
              :label="$t('Username')"
              filled
              dense
              stack-label
              :rules="rules.username"
              :error="!!errors.username"
              :error-message="errors.username"
            />
          </div>
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.password"
              type="password"
              :label="$t('Password') + '*'"
              filled
              dense
              stack-label
              :rules="rules.password"
              :error="!!errors.password"
              :error-message="errors.password"
            />
          </div>
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.password_confirmation"
              type="password"
              :label="$t('Confirm Password') + '*'"
              filled
              dense
              stack-label
              :rules="rules.password_confirmation"
              :error="!!errors.password_confirmation"
              :error-message="errors.password_confirmation"
            />
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn
          :label="$t('Cancel')"
          flat
          :disable="loading || isLoading"
          @click="cancel"
        />
        <q-btn
          type="submit"
          :label="$t('Save')"
          color="primary"
          :loading="loading || isLoading"
          unelevated
          class="q-px-lg"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script>
const DEFAULT_FORM_ENTRY = {
  email: null,
  username: null,
  password: null,
  password_confirmation: null
}

export default {
  name: 'FormEntry',
  props: {
    loading: Boolean,
    closable: {
      type: Boolean,
      default: true
    },
    entry: {
      type: Object,
      default() {
        return DEFAULT_FORM_ENTRY
      }
    },
    asView: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      formEntry: DEFAULT_FORM_ENTRY,
      isLoading: false,
      rules: {
        username: [
          v => !v || (!!v && v.length > 3) || this.$t('{field} must be at least {length} characters', { field: this.$t('Username'), length: 3 }),
          v => !v || (!!v && /^[a-zA-Z0-9_\.]+$/.test(v)) || this.$t('{field} can only contain alphanumeric characters, underscores, and periods', { field: this.$t('Username') }),
          v => !v || (!!v && /^[^0-9][a-zA-Z0-9_\.]+$/.test(v)) || this.$t('{field} must be starts with letter', { field: this.$t('Username') })
        ],
        email: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Email') }),
          (v) =>
            (!!v && this.$utils.isEmail(v)) ||
            this.$t('{field} is invalid', { field: this.$t('Email') }),
        ],
        password: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password') }),
          v => v && v.length >= 6 || this.$t('{field} must be at least {length} characters', { field: this.$t('Password'), length: 6 })
        ],
        password_confirmation: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password confirmation') }),
          v => v === this.formEntry.password || this.$t('{field} does not match', { field: this.$t('Password confirmation') })
        ]
      },
      errors: DEFAULT_FORM_ENTRY
    }
  },
  computed: {
    isCreate() {
      return !this.formEntry.id
    }
  },
  watch: {
    entry: {
      immediate: true,
      handler(n) {
        this.$nextTick(() => {
          this.fill(n)
        })
      }
    }
  },
  methods: {
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form };
      this.formEntry = form;
      this.$refs.form.resetValidation();
    },
    async onSubmit() {
      if (this.loading || this.isLoading) {
        return;
      }

      const isValid = await this.$refs.form.validate();

      if (!isValid) {
        return;
      }

      const entry = {
        email: this.formEntry.email,
        username: this.formEntry.username,
        password: this.formEntry.password,
        password_confirmation: this.formEntry.password_confirmation
      }

      this.isLoading = true;

      try {
        let { data } = await this.$api.patch(`/v1/franchises/${this.entry.id}`, entry);

        if (data.status === 'success') {
          this.$emit('success');
        }
        if (data.status === 'success') {
          this.$q.notify({ message: this.$t('{entity} updated', { entity: this.$t('Login Credentials') }) })
        } else {
          this.$t('Failed to update {entity}', { entity: this.$t('login credentials') })
        }
      } catch (err) {
        if (err.validation) {
          this.errors = {
            ...DEFAULT_FORM_ENTRY,
            ...err.validation.errors
          }
        } else {
          this.$q.notify(err);
        }
      }

      this.isLoading = false;
    },
    cancel() {
      if (this.loading || this.isLoading) {
        return;
      }

      this.$emit('cancel');
    }
  }
}
</script>

<style lang="scss" scoped>
.form-entry {
  @media (min-width: $breakpoint-sm-min) {
    max-width: 400px !important;
  }
}
</style>
