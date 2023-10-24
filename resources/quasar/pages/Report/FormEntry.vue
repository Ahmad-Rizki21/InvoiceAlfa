<template>
  <q-card class="form-entry q-pa-sm" :class="{ readonly }">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div v-if="!readonly" class="text-h6 q-px-md q-pt-sm">
        {{ $t(`${isCreate ? 'Create' : 'Update'} {entity}`, { entity: $t('Branch') }) }}
      </div>
      <q-card-section class="q-mb-md">
        <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Details') }}</div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-md-8">
            <q-input
              v-model="formEntry.location"
              :label="$t('Location Name') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              name="location"
              autocomplete="off"
              :rules="rules.location"
              :error="!!errors.location"
              :error-message="errors.location"
            />
          </div>
          <div class="col-xs-12 col-md-4">
            <q-input
              v-model="formEntry.terminal_name"
              :label="$t('Terminal Name') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              name="terminal_name"
              autocomplete="off"
              dense
              :rules="rules.terminal_name"
              :error="!!errors.terminal_name"
              :error-message="errors.terminal_name"
            />
          </div>
          <div class="col-xs-12 col-md-4">
            <q-input
              v-model="formEntry.sla"
              :label="$t('SLA')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              name="sla"
              mask="###"
              suffix="%"
              autocomplete="off"
              dense
              :rules="rules.sla"
              :error="!!errors.sla"
              :error-message="errors.sla"
            />
          </div>

          <!-- <div class="col-xs-12 col-sm-5 col-md-4">
            <q-input
              v-model="formEntry.mttr_hour"
              :label="$t('MTTR')"
              :filled="!readonly"
              type="number"
              name="mttr"
              autocomplete="off"
              class="double-input"
              :borderless="readonly"
              :readonly="readonly"
              dense
              stack-label
              :shadow-text="` ${$t('hour')}`"
              :rules="rules.mttr"
              :error="!!errors.mttr"
              :error-message="errors.mttr"
            >
              <q-input
                v-model="formEntry.mttr_minute"
                borderless
                dense
                type="number"
                autocomplete="off"
                :shadow-text="` ${$t('minute')}`"
                @keydown="onDoubleInputChildKeydown"
              />
            </q-input>
          </div> -->
          <!-- <div class="col-xs-12 col-sm-5 col-md-4">
            <q-input
              v-model="formEntry.total_time_hour"
              :label="$t('Total Time')"
              :filled="!readonly"
              type="number"
              name="total_time"
              autocomplete="off"
              class="double-input"
              :borderless="readonly"
              :readonly="readonly"
              dense
              stack-label
              :shadow-text="` ${$t('hour')}`"
              :rules="rules.total_time"
              :error="!!errors.total_time"
              :error-message="errors.total_time"
            >
              <q-input
                v-model="formEntry.total_time_minute"
                borderless
                dense
                type="number"
                autocomplete="off"
                :shadow-text="` ${$t('minute')}`"
                @keydown="onDoubleInputChildKeydown"
              />
            </q-input>
          </div> -->
          <div class="col-xs-12 col-sm-5 col-md-4">
            <q-input
              v-model="formEntry.online_at"
              :label="$t('Online At')"
              :filled="!readonly"
              name="online_at"
              autocomplete="off"
              :borderless="readonly"
              :readonly="readonly"
              dense
              :rules="rules.online_at"
              :error="!!errors.online_at"
              :error-message="errors.online_at"
              mask="##/##/####"
              :hint="$utils.isDateValid(formEntry.online_at) ? undefined : $t('Valid format: DD/MM/YYYY')"
            >
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy ref="qDateProxy" transition-show="scale" transition-hide="scale">
                    <q-date v-model="formEntry.online_at" minimal mask="DD/MM/YYYY">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>


          <div class="col-xs-12 col-md-8 col-lg-7">
            <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Coordinate') }}</div>

            <div class="row q-col-gutter-sm">
              <div class="col-xs-12 col-md-6">
                <q-input
                  v-model="formEntry.latitude"
                  :label="$t('Latitude')"
                  :filled="!readonly"
                  class="no-arrows"
                  type="number"
                  step="any"
                  name="latitude"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.latitude"
                  :error="!!errors.latitude"
                  :error-message="errors.latitude"
                />
              </div>
              <div class="col-xs-12 col-md-6">
                <q-input
                  v-model="formEntry.longitude"
                  :label="$t('Longitude')"
                  :filled="!readonly"
                  class="no-arrows"
                  type="number"
                  step="any"
                  name="longitude"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.longitude"
                  :error="!!errors.longitude"
                  :error-message="errors.longitude"
                />
              </div>
            </div>
          </div>
          <div class="col-xs-12">
            <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('PIC') }}</div>

            <div class="row q-col-gutter-sm">
              <div class="col-xs-12 col-md-7">
                <q-input
                  v-model="formEntry.pic_name"
                  :label="$t('PIC Name')"
                  :filled="!readonly"
                  rows="1"
                  name="pic_name"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.pic_name"
                  :error="!!errors.pic_name"
                  :error-message="errors.pic_name"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.pic_phone_number"
                  :label="$t('PIC Phone Number')"
                  :filled="!readonly"
                  rows="1"
                  name="pic_phone_number"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.pic_phone_number"
                  :error="!!errors.pic_phone_number"
                  :error-message="errors.pic_phone_number"
                />
              </div>
            </div>
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
  terminal_name: null,
  location: null,
  mttr_hour: null,
  mttr_minute: null,
  total_time_hour: null,
  total_time_minute: null,
  online_at: null,
  latitude: null,
  longitude: null,
  pic_name: null,
  pic_phone_number: null,
  pic_phone_number2: null,
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
        terminal_name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Terminal name') })
        ],
        location: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Location') })
        ],
        online_at: [
          v => !v || (!!v && this.$utils.isDateValid(v)) || this.$t('{field} is invalid', { field: this.$t('Online at') })
        ],
        latitude: [

        ],
        longitude: [

        ],
        pic_name: [

        ],
        pic_phone_number: [

        ],
        sla: [

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
    },
  },
  methods: {
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form };

      if (form.created_at) {
        form.created_at = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
      }
      if (form.online_at) {
        form.online_at = date.formatDate(form.online_at, 'DD/MM/YYYY')
      }

      // if (form.mttr) {
      //   let formattedMttr = this.$utils.formatSecondsToTime(form.mttr, false, false)

      //   if (formattedMttr) {
      //     formattedMttr = formattedMttr.split(':')
      //     form.mttr_hour = this.$utils.padZero(parseInt(formattedMttr[0], 10) || 0)
      //     form.mttr_minute = this.$utils.padZero(parseInt(formattedMttr[1], 10) || 0)
      //   }
      // }

      // if (form.total_time) {
      //   let formattedTotalTime = this.$utils.formatSecondsToTime(form.total_time, false, false)

      //   if (formattedTotalTime) {
      //     formattedTotalTime = formattedTotalTime.split(':')
      //     form.total_time_hour = this.$utils.padZero(parseInt(formattedTotalTime[0], 10) || 0)
      //     form.total_time_minute = this.$utils.padZero(parseInt(formattedTotalTime[1], 10) || 0)
      //   }
      // }

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

      if (this.$utils.isDateValid(entry.online_at)) {
        entry.online_at = date.formatDate(date.extractDate(entry.online_at, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }

      // entry.mttr = 0

      // if (entry.mttr_hour) {
      //   entry.mttr += (entry.mttr_hour * 60 * 60)
      // }

      // if (entry.mttr_minute) {
      //   entry.mttr += (entry.mttr_minute * 60)
      // }

      // entry.total_time = 0

      // if (entry.total_time_hour) {
      //   entry.total_time += (entry.total_time_hour * 60 * 60)
      // }

      // if (entry.total_time_minute) {
      //   entry.total_time += (entry.total_time_minute * 60)
      // }

      delete entry.mttr_hour
      delete entry.mttr_minute
      delete entry.total_time_hour
      delete entry.total_time_minute

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/branches/${entry.id}` : '/v1/branches';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.$emit('success');
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Branch') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('branch') })
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
    },
    onDoubleInputChildKeydown(e) {
      let value = String(e.target.value).replace(/[^0-9]/g, '')
      const isNumber = /^[0-9]$/i.test(e.key)

      if (isNumber) {
        value = `${value}${e.key}`
      }

      if (value > 59 || String(value).length > 2) {
        if (isNumber) {
          e.preventDefault()
        }
      }
    },
  }
}
</script>

<style lang="scss" scoped>
.form-entry {
  @media (min-width: $breakpoint-sm-min) {
    max-width: 875px !important;
  }
}
</style>
