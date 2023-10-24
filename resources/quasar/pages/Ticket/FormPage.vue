<template>
  <div class="form-page q-pa-sm q-gutter-md">
    <q-card class="q-pa-sm">
      <q-form ref="form" class="form-entry" :class="{ readonly }" greedy @submit.prevent="onSubmit">
        <q-card-section>
          <div class="row">
            <div :class="{ 'col-xs-12': !readonly, 'col-xs-11': readonly }">
              <div class="row q-col-gutter-sm">
                <div v-if="readonly" class="col-xs-12">
                  <q-input
                    v-show="!fetching"
                    :value="ticket.no"
                    :label="$t('No')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    class="input-form-page-ticket-no"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="no"
                    autocomplete="off"
                  />
                </div>
                <div class="col-xs-12 col-md-5 col-lg-5">
                  <autocomplete-customer
                    v-show="!fetching"
                    v-model="formEntry.customer_id"
                    :label="$t('Customer') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    class="input-form-page-ticket-customer"
                    :placeholder="readonly ? '-' : ''"
                    name="customer_id"
                    autocomplete="off"
                    autofocus
                    :dense="!readonly"
                    :rules="rules.customer_id"
                    :error="!!errors.customer_id"
                    :error-message="errors.customer_id"
                  />
                </div>
                <div class="col-xs-12 col-md-7 col-lg-5" :class="{ 'col-md-7': readonly }">
                  <autocomplete-remote-location
                    v-show="!fetching"
                    v-model="formEntry.remote_location_id"
                    :label="$t('Remote') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    class="input-form-page-ticket-remote-location"
                    :placeholder="readonly ? '-' : ''"
                    name="remote_location_id"
                    :customer-id="formEntry.customer_id"
                    customer-required
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.remote_location_id"
                    :error="!!errors.remote_location_id"
                    :error-message="errors.remote_location_id"
                  />
                </div>
                <div class="col-xs-12 col-md-12 col-lg-10">
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.title"
                    :label="$t('Title')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    class="input-form-page-ticket-title"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="title"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.title"
                    :error="!!errors.title"
                    :error-message="errors.title"
                  />
                </div>
                <div class="col-xs-12">
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.content"
                    :label="$t('Content') + '*'"
                    type="textarea"
                    :rows="readonly ? undefined : 10"
                    :autogrow="readonly"
                    :filled="!readonly"
                    class="input-form-page-ticket-content"
                    name="content"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.content"
                    :error="!!errors.content"
                    :error-message="errors.content"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-5">
                  <select-ticket-down-time
                    v-show="!fetching"
                    v-model="formEntry.down_time_caused_by"
                    :label="$t('Down Time Caused by') + '*'"
                    :filled="!readonly"
                    name="down_time_caused_by"
                    autocomplete="off"
                    class="input-form-page-ticket-down-time-caused-by"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.down_time_caused_by"
                    :error="!!errors.down_time_caused_by"
                    :error-message="errors.down_time_caused_by"
                  />
                </div>
                <div v-if="isCreate" class="col-xs-12">
                  <q-checkbox
                    v-model="autoStartTimer"
                    :label="$t('Automatically start the timer')"
                    dense
                    class="input-form-page-auto-start-timer"
                  />
                </div>
                <div v-if="!readonly && !isCreate" class="col-xs-12">
                  {{ $t('Status') }}
                  <q-toggle
                    v-model="formEntry.status"
                    :label="formEntry.status ? $t('OPEN') : $t('CLOSED')"
                    name="status"
                  />
                </div>
                <template v-if="readonly">
                  <div class="col-xs-12 col-md-4 col-lg-4 ">
                    <q-input
                      :value="formEntry.status ? $t('OPEN') : $t('CLOSED')"
                      :label="$t('Status')"
                      borderless
                      readonly
                    />
                  </div>

                  <div class="col-xs-12 col-md-5">
                    <q-input
                      :value="formEntry.created_at"
                      :label="$t('Created At')"
                      borderless
                      readonly
                    />
                  </div>

                  <div class="col-xs-12 col-md-4">
                    <q-input
                      :value="formEntry.updated_at"
                      :label="$t('Updated At')"
                      borderless
                      readonly
                    />
                  </div>
                </template>
              </div>
            </div>
            <div v-if="$auth.can(['edit.ticket', 'delete.ticket'])" v-show="readonly" class="col-xs-1 text-right form-page-actions">
              <q-btn icon="more_vert" padding="xs" size="md" flat class="btn-form-page-action">
                <q-menu auto-close anchor="bottom right" self="top right">
                  <q-list style="min-width: 100px">
                    <q-item v-if="$auth.can('edit.ticket')" clickable @click="onEdit">
                      <q-item-section>{{ $t('Edit') }}</q-item-section>
                    </q-item>
                    <q-item v-if="$auth.can('delete.ticket')" clickable @click="onDelete">
                      <q-item-section>{{ $t('Delete') }}</q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-btn>
            </div>
          </div>
        </q-card-section>

        <q-card-actions v-if="$auth.can(['create.ticket', 'edit.ticket']) && !readonly" align="right">
          <q-btn
            v-if="!isCreate"
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
            class="q-px-lg btn-form-page-ticket-submit"
          />
        </q-card-actions>
      </q-form>
    </q-card>

    <!-- <q-separator /> -->
  </div>
</template>

<script>
import { date } from 'quasar'
import FormNotes from './FormNotes'

const DEFAULT_FORM_ENTRY = {
  customer_id: null,
  remote_location_id: null,
  title: null,
  content: null,
  status: 1,
  down_time_caused_by: null
}

export default {
  name: 'FormPage',
  components: {
    FormNotes
  },
  props: {
    ticket: {
      type: Object,
      default() {
        return {}
      }
    },
    loading: Boolean,
    fetching: Boolean,
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
        customer_id: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Customer') })
        ],
        remote_location_id: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Remote') })
        ],
        down_time_caused_by: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Down time caused by') })
        ],
        content: [
          //
        ],

      },
      errors: DEFAULT_FORM_ENTRY,
      editable: false,
      autoStartTimer: true
    }
  },
  computed: {
    isCreate() {
      return !this.ticket.id
    },
    readonly() {
      if (this.editable) {
        return false
      }

      return !this.isCreate
    }
  },
  watch: {
    ticket: {
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

      if (form.status == this.$constant.ticket.STATUS_CLOSED) {
        form.status = false
      } else {
        form.status = true
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
      entry.status = entry.status ? this.$constant.ticket.STATUS_OPEN : this.$constant.ticket.STATUS_CLOSED

      if (this.autoStartTimer) {
        entry.auto_start_timer = 1
      }

      if (!this.isCreate && entry.status != this.ticket.status) {
        const shouldContinue = await new Promise((resolve) => {
          let message = this.$t('Are you sure want to turn status to be CLOSED?')

          if (entry.status == this.$constant.ticket.STATUS_OPEN) {
            message = this.$t('Are you sure want to reopen the ticket? Start clock will automatically started afterward.')
          }

          this.$q.dialog({
            title: this.$t('Confirm'),
            message,
            cancel: true,
            persistent: true
          }).onOk(() => {
            resolve(true)
          }).onCancel(() => {
            resolve(false)
          }).onDismiss(() => {
            resolve(false)
          })
        })

        if (!shouldContinue) {
          return
        }
      }

      this.$refs.form.resetValidation()

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/tickets/${entry.id}` : '/v1/tickets';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.$emit('success', data.data.ticket);
          this.editable = false
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

      this.editable = false
    },
    onEdit() {
      this.editable = true
    },
    onDelete() {
      if (this.isLoading) {
        return;
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('ticket') }),
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
          let { data } = await this.$api.delete(`/v1/tickets/${this.ticket.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Ticket') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('ticket') })
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
