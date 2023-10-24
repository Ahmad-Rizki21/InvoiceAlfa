<template>
  <q-card class="form-entry q-pa-sm" :class="{ readonly }">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div v-if="!readonly" class="text-h6 q-px-md q-pt-sm">
        {{ $t(`${isCreate ? 'Create' : 'Update'} {entity}`, { entity: $t('Remote Location') }) }}
      </div>
      <q-card-section class="q-mb-md">

        <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Details') }}</div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-md-8">
            <autocomplete-customer
              v-model="formEntry.customer_id"
              :label="$t('Customer') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              name="customer_id"
              autocomplete="off"
              :rules="rules.customer_id"
              :error="!!errors.customer_id"
              :error-message="errors.customer_id"
            />
          </div>
          <div class="col-xs-12 col-md-4">
            <q-input
              v-model="formEntry.code"
              :label="$t('Code') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              name="code"
              autocomplete="off"
              dense
              :rules="rules.code"
              :error="!!errors.code"
              :error-message="errors.code"
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

            <div class="col-xs-12 col-md-8">
              <q-input
                v-model="formEntry.address"
                :label="$t('Address') + '*'"
                :filled="!readonly"
                :borderless="readonly"
                :readonly="readonly"
                dense
                name="address"
                autocomplete="off"
                :rules="rules.address"
                :error="!!errors.address"
                :error-message="errors.address"
              />
            </div>
            <div class="col-xs-12 col-md-4">
              <q-input
                v-model="formEntry.postal_code"
                :label="$t('Postal Code')"
                :filled="!readonly"
                :borderless="readonly"
                :readonly="readonly"
                name="postal_code"
                autocomplete="off"
                dense
                :rules="rules.postal_code"
                :error="!!errors.postal_code"
                :error-message="errors.postal_code"
              />
            </div>

            <div class="col-xs-12 col-sm-5 col-md-4">
              <q-input
                v-model="formEntry.online_at"
                :label="$t('Online Since At')"
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
        </div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-md-8 col-lg-7">
            <div class="q-mb-md text-subtitle">{{ $t('Coordinate') }}</div>

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
        </div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <div class="q-mb-md text-subtitle">{{ $t('PIC Remote') }}</div>

            <div class="row q-col-gutter-sm">
              <div class="col-xs-12 col-md-7">
                <q-input
                  v-model="formEntry.pic_remote_name"
                  :label="$t('PIC Remote Name')"
                  :filled="!readonly"
                  name="pic_remote_name"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.pic_remote_name"
                  :error="!!errors.pic_remote_name"
                  :error-message="errors.pic_remote_name"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.pic_remote_phone_number"
                  :label="$t('PIC Remote Phone Number')"
                  :filled="!readonly"
                  name="pic_remote_phone_number"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.pic_remote_phone_number"
                  :error="!!errors.pic_remote_phone_number"
                  :error-message="errors.pic_remote_phone_number"
                />
              </div>
            </div>
          </div>
          <div class="col-xs-12">
            <div class="q-mb-md text-subtitle">{{ $t('PIC SAT') }}</div>

            <div class="row q-col-gutter-sm">
              <div class="col-xs-12 col-md-7">
                <q-input
                  v-model="formEntry.pic_sat_name"
                  :label="$t('PIC SAT Name')"
                  :filled="!readonly"
                  name="pic_sat_name"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.pic_sat_name"
                  :error="!!errors.pic_sat_name"
                  :error-message="errors.pic_sat_name"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.pic_sat_phone_number"
                  :label="$t('PIC SAT Phone Number')"
                  :filled="!readonly"
                  name="pic_sat_phone_number"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.pic_sat_phone_number"
                  :error="!!errors.pic_sat_phone_number"
                  :error-message="errors.pic_sat_phone_number"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Infrastructure Details') }}</div>

            <div class="row q-col-gutter-sm">
              <div class="col-xs-12 col-md-7">
                <q-input
                  v-model="formEntry.infrastructure_type"
                  :label="$t('Infrastructure Type')"
                  :filled="!readonly"
                  name="infrastructure_type"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.infrastructure_type"
                  :error="!!errors.infrastructure_type"
                  :error-message="errors.infrastructure_type"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.no_gsm"
                  :label="$t('No. GSM')"
                  :filled="!readonly"
                  name="no_gsm"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.no_gsm"
                  :error="!!errors.no_gsm"
                  :error-message="errors.no_gsm"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.gsm_provider"
                  :label="$t('GSM Provider')"
                  :filled="!readonly"
                  name="gsm_provider"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.gsm_provider"
                  :error="!!errors.gsm_provider"
                  :error-message="errors.gsm_provider"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.no_gsm2"
                  :label="$t('No. GSM') + ' 2'"
                  :filled="!readonly"
                  name="no_gsm2"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.no_gsm2"
                  :error="!!errors.no_gsm2"
                  :error-message="errors.no_gsm2"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.gsm_provider2"
                  :label="$t('GSM Provider') + ' 2'"
                  :filled="!readonly"
                  name="gsm_provider2"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.gsm_provider2"
                  :error="!!errors.gsm_provider2"
                  :error-message="errors.gsm_provider2"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.cid_provider"
                  :label="$t('CID Provider')"
                  :filled="!readonly"
                  name="cid_provider"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.cid_provider"
                  :error="!!errors.cid_provider"
                  :error-message="errors.cid_provider"
                />
              </div>
              <div class="col-xs-12 col-md-5">
                <q-input
                  v-model="formEntry.fo_provider"
                  :label="$t('FO Provider')"
                  :filled="!readonly"
                  name="fo_provider"
                  autocomplete="off"
                  :borderless="readonly"
                  :readonly="readonly"
                  dense
                  :rules="rules.fo_provider"
                  :error="!!errors.fo_provider"
                  :error-message="errors.fo_provider"
                />
              </div>
            </div>



            <div class="row q-col-gutter-sm">
              <div class="col-xs-12">
                <div class="q-mb-md text-subtitle">{{ $t('PIC FO Provider') }}</div>

                <div class="row q-col-gutter-sm">
                  <div class="col-xs-12 col-md-7">
                    <q-input
                      v-model="formEntry.pic_fo_provider_name"
                      :label="$t('PIC FO Provider Name')"
                      :filled="!readonly"
                      name="pic_fo_provider_name"
                      autocomplete="off"
                      :borderless="readonly"
                      :readonly="readonly"
                      dense
                      :rules="rules.pic_fo_provider_name"
                      :error="!!errors.pic_fo_provider_name"
                      :error-message="errors.pic_fo_provider_name"
                    />
                  </div>
                  <div class="col-xs-12 col-md-5">
                    <q-input
                      v-model="formEntry.pic_fo_provider_phone_number"
                      :label="$t('PIC FO Provider Phone Number')"
                      :filled="!readonly"
                      name="pic_fo_provider_phone_number"
                      autocomplete="off"
                      :borderless="readonly"
                      :readonly="readonly"
                      dense
                      :rules="rules.pic_fo_provider_phone_number"
                      :error="!!errors.pic_fo_provider_phone_number"
                      :error-message="errors.pic_fo_provider_phone_number"
                    />
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Other Info') }}</div>

            <div class="row q-col-gutter-sm">
              <div class="col-xs-12">
                <div class="q-mb-md text-subtitle">{{ $t('PIC Service Point') }}</div>

                <div class="row q-col-gutter-sm">
                  <div class="col-xs-12 col-md-7">
                    <q-input
                      v-model="formEntry.pic_service_point"
                      :label="$t('PIC Service Point Name')"
                      :filled="!readonly"
                      name="pic_service_point"
                      autocomplete="off"
                      :borderless="readonly"
                      :readonly="readonly"
                      dense
                      :rules="rules.pic_service_point"
                      :error="!!errors.pic_service_point"
                      :error-message="errors.pic_service_point"
                    />
                  </div>
                  <div class="col-xs-12 col-md-5">
                    <q-input
                      v-model="formEntry.pic_service_point_phone_number"
                      :label="$t('PIC Service Point Phone Number')"
                      :filled="!readonly"
                      name="pic_service_point_phone_number"
                      autocomplete="off"
                      :borderless="readonly"
                      :readonly="readonly"
                      dense
                      :rules="rules.pic_service_point_phone_number"
                      :error="!!errors.pic_service_point_phone_number"
                      :error-message="errors.pic_service_point_phone_number"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div class="row q-col-gutter-sm">
              <div class="col-xs-12 col-md-4">
                <q-input
                  v-model="formEntry.fo_contract_by_name"
                  :label="$t('FO Contract By')"
                  :filled="!readonly"
                  :borderless="readonly"
                  :readonly="readonly"
                  name="fo_contract_by_name"
                  autocomplete="off"
                  dense
                  :rules="rules.fo_contract_by_name"
                  :error="!!errors.fo_contract_by_name"
                  :error-message="errors.fo_contract_by_name"
                />
              </div>
              <div class="col-xs-12 col-md-4">
                <q-input
                  v-model="formEntry.remark"
                  :label="$t('Remark')"
                  :filled="!readonly"
                  type="textarea"
                  rows="2"
                  :borderless="readonly"
                  :readonly="readonly"
                  name="remark"
                  autocomplete="off"
                  dense
                  :rules="rules.remark"
                  :error="!!errors.remark"
                  :error-message="errors.remark"
                />
              </div>
            </div>




          </div>
        </div>

        <template v-if="readonly">
          <div class="row">
            <div class="col-xs-12">
              <q-input
                :value="formEntry.created_at"
                :label="$t('Created At')"
                borderless
                readonly
                dense
              />
            </div>
          </div>
        </template>
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
import AutocompleteCustomer from '../../components/AutocompleteCustomer.vue'

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
  pic_remote_name: null,
  pic_remote_phone_number: null,
  pic_remote_phone_number2: null,
}

export default {
  components: { AutocompleteCustomer },
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
        pic_remote_name: [

        ],
        pic_remote_phone_number: [

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
        const endpoint = entry.id ? `/v1/remote-locations/${entry.id}` : '/v1/remote-locations';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.$emit('success');
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Remote location') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('remote location') })
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
