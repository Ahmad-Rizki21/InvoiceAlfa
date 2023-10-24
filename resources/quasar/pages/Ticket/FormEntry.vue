<template>
  <q-card class="form-entry q-pa-sm" :class="{ readonly }">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div v-if="!readonly" class="text-h6 q-px-md q-pt-sm">
        {{ $t(`${isCreate ? 'Create' : 'Update'} {entity}`, { entity: $t('Ticket') }) }}
      </div>
      <q-card-section class="q-mb-md">
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.name"
              :label="$t('Name')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              :rules="rules.name"
              :error="!!errors.name"
              :error-message="errors.name"
            />
          </div>
          <div class="col-xs-12">
            <q-input
              v-model="formEntry.location"
              :label="$t('Location')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              :rules="rules.location"
              :error="!!errors.location"
              :error-message="errors.location"
            />
          </div>
          <div v-if="!readonly" class="col-xs-12">
            <q-toggle
              v-model="formEntry.status"
              :label="formEntry.status ? $t('Active') : $t('Inactive')"
              name="status"
            />
          </div>
          <template v-if="readonly">
            <div class="col-xs-12">
              <q-input
                :value="formEntry.status ? $t('Active') : $t('Inactive')"
                :label="$t('Status')"
                borderless
                readonly
                dense
              />
            </div>
            <div class="col-xs-12">
              <q-input
                :value="formEntry.created_at"
                :label="$t('Created At')"
                borderless
                readonly
                dense
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
  location: null,
  status: 1
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
        location: [
          //
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

      form.status = Boolean(parseInt(form.status, 10))

      this.formEntry = form;
      this.$refs.form.resetValidation();
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
      entry.status = entry.status ? 1 : 0

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/tickets/${entry.id}` : '/v1/tickets';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.$emit('success');
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Ticket') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('ticket') })
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
