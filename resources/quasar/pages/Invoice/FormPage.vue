<template>
  <div class="form-page q-pa-sm q-gutter-md">
    <q-card class="q-pa-sm">
      <q-form ref="form" class="form-entry" greedy :class="{ readonly }" @submit.prevent="onSubmit">
        <q-card-section>
          <div class="row">
            <div :class="{ 'col-xs-12': !readonly, 'col-xs-11': readonly }">

              <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Details') }}</div>

              <div class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-6">
                  <q-skeleton v-if="fetching" type="rect" />
                  <autocomplete-customer
                    v-show="!fetching"
                    v-model="formEntry.customer_id"
                    :label="$t('Customer')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    :disable="!readonly && !!$route.query.customer_id"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="customer_id"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.customer_id"
                    :error="!!errors.customer_id"
                    :error-message="errors.customer_id"
                  />
                </div>
              </div>
              <div class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.code"
                    :label="$t('Code')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="code"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.code"
                    :error="!!errors.code"
                    :error-message="errors.code"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-5">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.name"
                    :label="$t('Name') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="name"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.name"
                    :error="!!errors.name"
                    :error-message="errors.name"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.location"
                    :label="$t('Location')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="location"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.location"
                    :label="$t('Location')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="location"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.location"
                    :error="!!errors.location"
                    :error-message="errors.location"
                  />
                </div>
                <div class="col-xs-12">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.address"
                    :label="$t('Full Address')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    type="textarea"
                    autogrow
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="address"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.address"
                    :label="$t('Full Address')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    type="textarea"
                    rows="2"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="address"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.address"
                    :error="!!errors.address"
                    :error-message="errors.address"
                  />
                </div>

                <div class="col-xs-12 col-sm-6 col-md-4">
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.phone_number"
                    :label="$t('PIC Phone Number')"
                    :filled="!readonly"
                    name="phone_number"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.phone_number"
                    :error="!!errors.phone_number"
                    :error-message="errors.phone_number"
                    @keypress="$globalListeners.onKeypressOnlyUnsignedNumber($event)"
                  />
                </div>

              </div>


              <template v-if="readonly">
                <div class="row">
                  <div class="col-xs-12 col-md-4">
                    <q-input
                      v-show="!fetching"
                      :value="formEntry.created_at"
                      :label="$t('Created At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                  <div class="col-xs-12 col-md-3">
                    <q-input
                      v-show="!fetching"
                      :value="formEntry.updated_at"
                      :label="$t('Updated At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                </div>
              </template>
            </div>
            <div v-if="$auth.can(['edit.customer', 'delete.customer'])" v-show="readonly" class="col-xs-1 text-right">
              <q-btn icon="more_vert" padding="xs" size="md" flat>
                <q-menu auto-close anchor="bottom right" self="top right">
                  <q-list style="min-width: 100px">
                    <q-item v-if="$auth.can('edit.customer')" clickable @click="onEdit">
                      <q-item-section>{{ $t('Edit') }}</q-item-section>
                    </q-item>
                    <q-item v-if="$auth.can('delete.customer')" clickable @click="onDelete">
                      <q-item-section>{{ $t('Delete') }}</q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-btn>
            </div>
          </div>
        </q-card-section>

        <q-card-actions v-if="$auth.can(['create.customer', 'edit.customer']) && !readonly" align="right">
          <q-btn
            v-if="!isCreate || $route.query.customer_id"
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

    <!-- <q-separator /> -->
  </div>
</template>

<script>
import { date } from 'quasar'

const DEFAULT_FORM_ENTRY = {
  name: null,
  phone_number: null,
  customer_id: null,
  address: null,
  location: null
}

export default {
  name: 'FormPage',
  props: {
    entry: {
      type: Object,
      default() {
        return {}
      }
    },
    loading: Boolean,
    fetching: Boolean,
    editable: Boolean,
    closable: {
      type: Boolean,
      default: true
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
      errors: DEFAULT_FORM_ENTRY,
      isEditable: false,
      defaultFormEntry: DEFAULT_FORM_ENTRY
    }
  },
  computed: {
    isCreate() {
      return !this.entry.id
    },
    readonly() {
      if (this.isEditable) {
        return false
      }

      return !this.isCreate
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
    },
    editable: {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.isEditable) {
          this.isEditable = n
        }
      }
    },
    isEditable(n, o) {
      if (n !== o && n !== this.editable) {
        this.$emit('update:editable', n)
      }
    }
  },
  methods: {
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form };

      if (form.sla) {
        form.sla = parseFloat(form.sla, 10)
      }

      if (form.created_at) {
        form.created_at = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
        form.updated_at = date.formatDate(form.updated_at, 'DD MMM YYYY HH:mm')
      } else {
        const defaultFormEntry = {}

        for (const key in DEFAULT_FORM_ENTRY) {
          defaultFormEntry[key] = form[key]
        }

        this.defaultFormEntry = defaultFormEntry
      }

      if (this.$route.query.customer_id) {
        form.customer_id = this.$route.query.customer_id
        this.defaultFormEntry.customer_id = this.$route.query.customer_id
      }

      this.formEntry = form;
      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
    },
    isDirty() {
      return this.$utils.isDirty(this.defaultFormEntry, this.formEntry)
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

      entry.type = this.$constant.customer.TYPE_DISTRIBUTION_CENTER;

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/distribution-centers/${entry.id}` : '/v1/distribution-centers';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.defaultFormEntry = { ...this.formEntry }
          this.$emit('success', data.data.distribution_center);
          this.isEditable = false
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Distribution center') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('distribution center') })
          }
        }
      } catch (err) {
        if (err.validation) {
          const validationErrors = {
            ...DEFAULT_FORM_ENTRY,
            ...err.validation.errors
          }

          this.errors = validationErrors
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

      if (this.$route.query.customer_id) {
        this.$router.push(`/customers/${this.$route.query.customer_id}`)

        return
      }

      this.isEditable = false
    },
    onEdit() {
      this.isEditable = true
    },
    onDelete() {
      if (this.isLoading) {
        return;
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('distribution center') }),
        cancel: {
          label: this.$t('Cancel'),
          color: 'dark',
          flat: true
        },
        ok: {
          label: this.$t('Yes'),
          color: 'primary',
          unelevated: true,
          flat: true,
          class: 'text-weight-bold'
        },
        persistent: true
      }).onOk(async () => {
        try {
          let { data } = await this.$api.delete(`/v1/distribution-centers/${this.entry.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.$emit('deleted', data)
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Distribution center') }) })
              this.$emit('deleted', data)
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('distribution center') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.form-entry {
  @media (min-width: $breakpoint-sm-min) {
    // max-width: 400px !important;
  }

  &.readonly {
    .q-field.q-textarea.q-field--readonly {
      line-height: 1.4;
    }
  }
}
</style>
