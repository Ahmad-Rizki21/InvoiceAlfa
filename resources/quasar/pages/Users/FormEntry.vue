<template>
  <q-card class="form-entry q-pa-sm" :class="{ readonly }">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div v-if="!readonly" class="text-h6 q-px-md q-pt-sm">
        {{ $t(`${isCreate ? 'Create' : 'Update'} {entity}`, { entity: $t('User') }) }}
      </div>
      <q-card-section class="q-mb-md scroll" :style="{ maxHeight: isCreate ? '60vh' : '' }">
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.name"
              :label="$t('Name') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :placeholder="readonly ? '-' : ''"
              stack-label
              :rules="rules.name"
              :error="!!errors.name"
              :error-message="errors.name"
            />
          </div>
          <div class="col-xs-12">
            <autocomplete-role
              v-model="formEntry.role_id"
              :label="$t('Role') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :placeholder="readonly ? '-' : ''"
              stack-label
              :rules="rules.role_id"
              :error="!!errors.role_id"
              :error-message="errors.role_id"
            />
          </div>
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.email"
              type="email"
              :label="$t('Email') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :placeholder="readonly ? '-' : ''"
              stack-label
              :rules="rules.email"
              :error="!!errors.email"
              :error-message="errors.email"
            />
          </div>
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.username"
              type="username"
              :label="$t('Username')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :placeholder="readonly ? '-' : ''"
              stack-label
              :rules="rules.username"
              :error="!!errors.username"
              :error-message="errors.username"
            />
          </div>
          <template v-if="isCreate && !readonly">
            <div class="col-xs-12">
              <q-input
                v-model="formEntry.password"
                type="password"
                :label="$t('Password') + '*'"
                :filled="!readonly"
                :borderless="readonly"
                :readonly="readonly"
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
                :filled="!readonly"
                :borderless="readonly"
                :readonly="readonly"
                stack-label
                :rules="rules.password_confirmation"
                :error="!!errors.password_confirmation"
                :error-message="errors.password_confirmation"
              />
            </div>
          </template>
          <template v-if="readonly">
            <div class="col-xs-12">
              <q-input
                :value="formEntry.created_at"
                :label="$t('Created At')"
                borderless
                readonly
              />
            </div>
            <div class="col-xs-12">
              <q-input
                :value="formEntry.updated_at"
                :label="$t('Updated At')"
                borderless
                readonly
              />
            </div>
          </template>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions v-if="readonly" align="right">
        <q-btn
          :label="$t('Close')"
          flat
          :disable="loading || isLoading"
          @click="cancel"
        />
      </q-card-actions>
      <q-card-actions v-else align="right">
        <q-btn
          :label="$t('Cancel')"
          flat
          :disable="loading || isLoading"
          @click="cancel"
        />
        <q-btn
          type="submit"
          :label="$t(`${isCreate ? 'Create' : 'Save'}`)"
          :color="isCreate ? 'positive' : 'primary'"
          :loading="loading || isLoading"
          unelevated
          class="q-px-lg"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script>
import { date } from 'quasar'

const DEFAULT_FORM_ENTRY = {
  name: null,
  role_id: null,
  username: null,
  email: null,
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
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      formEntry: DEFAULT_FORM_ENTRY,
      isLoading: false,
      rules: {
        name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Name') })
        ],
        role_id: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Role') })
        ],
        username: [
          v => !v || (!!v && /^[a-zA-Z0-9_\.]+$/.test(v)) || this.$t('{field} can only contain alphanumeric characters, underscores, and periods', { field: this.$t('Username') })
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

      if (form.created_at) {
        form.created_at = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
      }
      if (form.updated_at) {
        form.updated_at = date.formatDate(form.updated_at, 'DD MMM YYYY HH:mm')
      }

      this.formEntry = form;

      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
    },
    async onSubmit() {
      if (this.loading || this.isLoading) {
        return;
      }

      if (this.readonly) {
        return
      }

      const isValid = await this.$refs.form.validate();

      if (!isValid) {
        return;
      }

      const entry = { ...this.formEntry }

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/users/${entry.id}` : '/v1/users';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.$emit('success');
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('User') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('user') })
          }
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
