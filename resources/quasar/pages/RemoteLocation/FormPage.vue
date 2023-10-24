<template>
  <div class="form-page q-pa-sm q-gutter-md">
    <q-card class="q-pa-sm">
      <q-form ref="form" class="form-entry" greedy :class="{ readonly }" @submit.prevent="onSubmit">
        <q-card-section>
          <div class="row">
            <div :class="{ 'col-xs-12': !readonly, 'col-xs-11': readonly }">

              <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Details') }}</div>

              <div class="row q-col-gutter-sm">
                <div class="col-xs-12 col-sm-7 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <autocomplete-customer
                    v-show="!fetching"
                    v-model="formEntry.customer_id"
                    :label="$t('Customer') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    name="customer_id"
                    autocomplete="off"
                    :rules="rules.customer_id"
                    :error="!!errors.customer_id"
                    :error-message="errors.customer_id"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.code"
                    :label="$t('Code') + '*'"
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
                <div class="col-xs-12 col-sm-6 col-md-4">
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
                    v-show="!fetching"
                    v-model="formEntry.terminal_name"
                    :label="$t('Terminal Name') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="terminal_name"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.terminal_name"
                    :error="!!errors.terminal_name"
                    :error-message="errors.terminal_name"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.distribution_center"
                    :label="$t('Distribution Center') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="distribution_center"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.distribution_center"
                    :error="!!errors.distribution_center"
                    :error-message="errors.distribution_center"
                  />
                </div>

                <div class="col-xs-12 col-md-9">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.address"
                    :label="$t('Address')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    name="address"
                    autocomplete="off"
                    :rules="rules.address"
                    :error="!!errors.address"
                    :error-message="errors.address"
                  />
                </div>
                <div class="col-xs-12 col-md-3 col-lg-2">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.postal_code"
                    :label="$t('Postal Code')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="postal_code"
                    autocomplete="off"
                    :dense="!readonly"
                    mask="#####"
                    :rules="rules.postal_code"
                    :error="!!errors.postal_code"
                    :error-message="errors.postal_code"
                  />
                </div>

                <div v-if="readonly" class="col-xs-12 col-md-8">
                  <q-field
                    v-show="!fetching"
                    :label="$t('Coordinate')"
                    :filled="!readonly"
                    class="no-arrows"
                    name="latitude"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :dense="!readonly"
                  >
                    <span v-if="formEntry.latitude && formEntry.longitude" class="text-color-default">
                      <span>{{ $t('Latitude') }}: {{ formEntry.latitude || '-' }}</span>
                      <span class="q-ml-sm">{{ $t('Longitude') }}: {{ formEntry.longitude || '-' }}</span>
                      <q-btn
                        :href="`http://maps.google.com/maps?q=${formEntry.latitude},${formEntry.longitude}`"
                        flat
                        target="_blank"
                        class="q-ml-xs"
                        style="margin-top: -0.25rem"
                        padding="xs"
                        icon="link"
                        round
                        size="sm"
                      >
                        <q-tooltip>
                          {{ $t('Open Google Maps') }}
                        </q-tooltip>
                      </q-btn>
                      <q-btn
                        flat
                        class="q-ml-xs"
                        style="margin-top: -0.25rem"
                        padding="xs"
                        icon="content_copy"
                        round
                        size="sm"
                        @click.prevent="$utils.copyToClipboard(`http://maps.google.com/maps?q=${formEntry.latitude},${formEntry.longitude}`);$q.notify({ message: $t('{entity} copied to clipboard', { entity: $t('URL') }) })"
                      >
                        <q-tooltip>
                          {{ $t('Copy {entity}', { entity: $t('URL') }) }}
                        </q-tooltip>
                      </q-btn>
                    </span>
                    <template v-else>-</template>
                  </q-field>
                </div>
                <template v-if="!readonly">
                  <div class="col-xs-12 col-md-2">
                    <q-input
                      v-show="!fetching"
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
                      stack-label
                      :placeholder="readonly ? '-' : ''"
                      :dense="!readonly"
                      :rules="rules.latitude"
                      :error="!!errors.latitude"
                      :error-message="errors.latitude"
                      @keypress="$globalListeners.onKeypressOnlyFloat($event)"
                    />
                  </div>
                  <div class="col-xs-12 col-md-2">
                    <q-input
                      v-show="!fetching"
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
                      stack-label
                      :placeholder="readonly ? '-' : ''"
                      :dense="!readonly"
                      :rules="rules.longitude"
                      :error="!!errors.longitude"
                      :error-message="errors.longitude"
                      @keypress="$globalListeners.onKeypressOnlyFloat($event)"
                    />
                  </div>
                </template>

                <div class="col-xs-12 col-sm-5 col-md-4" :class="{ 'offset-md-1': !readonly }">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.online_at"
                    :label="$t('Online Since At')"
                    :filled="!readonly"
                    name="online_at"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.online_at"
                    :error="!!errors.online_at"
                    :error-message="errors.online_at"
                    mask="##/##/####"
                    :hint="$utils.isDateValid(formEntry.online_at) ? undefined : $t('Valid format: DD/MM/YYYY')"
                  >
                    <template v-slot:append>
                      <q-icon v-if="!readonly" name="event" class="cursor-pointer">
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

              <div class="row q-col-gutter-sm q-mb-md">
                <div class="col-xs-12">
                  <div class="row q-col-gutter-sm">
                    <div class="col-xs-12 col-md-3">
                      <q-input
                        v-show="!fetching"
                        v-model="formEntry.pic_remote_name"
                        :label="$t('PIC Remote Name')"
                        :filled="!readonly"
                        name="pic_remote_name"
                        autocomplete="off"
                        :borderless="readonly"
                        :readonly="readonly"
                        stack-label
                        :placeholder="readonly ? '-' : ''"
                        :dense="!readonly"
                        :rules="rules.pic_remote_name"
                        :error="!!errors.pic_remote_name"
                        :error-message="errors.pic_remote_name"
                      />
                    </div>
                    <div class="col-xs-12 col-md-3">
                      <q-input
                        v-show="!fetching"
                        v-model="formEntry.pic_remote_phone_number"
                        :label="$t('PIC Remote Phone Number')"
                        :filled="!readonly"
                        name="pic_remote_phone_number"
                        autocomplete="off"
                        :borderless="readonly"
                        :readonly="readonly"
                        stack-label
                        :placeholder="readonly ? '-' : ''"
                        :dense="!readonly"
                        :rules="rules.pic_remote_phone_number"
                        :error="!!errors.pic_remote_phone_number"
                        :error-message="errors.pic_remote_phone_number"
                        @keypress="$globalListeners.onKeypressOnlyUnsignedNumber($event)"
                      />
                    </div>
                  </div>

                  <div class="row q-col-gutter-sm">
                    <div class="col-xs-12 col-md-3">
                      <q-input
                        v-show="!fetching"
                        v-model="formEntry.pic_it_sat_name"
                        :label="$t('PIC IT SAT Name')"
                        :filled="!readonly"
                        name="pic_it_sat_name"
                        autocomplete="off"
                        :borderless="readonly"
                        :readonly="readonly"
                        stack-label
                        :placeholder="readonly ? '-' : ''"
                        :dense="!readonly"
                        :rules="rules.pic_it_sat_name"
                        :error="!!errors.pic_it_sat_name"
                        :error-message="errors.pic_it_sat_name"
                      />
                    </div>
                    <div class="col-xs-12 col-md-3">
                      <q-input
                        v-show="!fetching"
                        v-model="formEntry.pic_it_sat_phone_number"
                        :label="$t('PIC IT SAT Phone Number')"
                        :filled="!readonly"
                        name="pic_it_sat_phone_number"
                        autocomplete="off"
                        :borderless="readonly"
                        :readonly="readonly"
                        stack-label
                        :placeholder="readonly ? '-' : ''"
                        :dense="!readonly"
                        :rules="rules.pic_it_sat_phone_number"
                        :error="!!errors.pic_it_sat_phone_number"
                        :error-message="errors.pic_it_sat_phone_number"
                        @keypress="$globalListeners.onKeypressOnlyUnsignedNumber($event)"
                      />
                    </div>
                  </div>
                </div>
              </div>


              <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Infrastructure Details') }}</div>

              <div class="row q-col-gutter-sm">
                <div class="col-xs-12 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" class="q-mb-md" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.infrastructure_type"
                    :label="$t('Infrastructure Type')"
                    :filled="!readonly"
                    name="infrastructure_type"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.infrastructure_type"
                    :error="!!errors.infrastructure_type"
                    :error-message="errors.infrastructure_type"
                  />
                </div>
              </div>

              <div class="row q-col-gutter-sm">
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.gsm_no"
                    :label="$t('GSM No.') + ' 1'"
                    :filled="!readonly"
                    name="gsm_no"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.gsm_no"
                    :error="!!errors.gsm_no"
                    :error-message="errors.gsm_no"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.gsm_provider"
                    :label="$t('GSM Provider') + ' 1'"
                    :filled="!readonly"
                    name="gsm_provider"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.gsm_provider"
                    :error="!!errors.gsm_provider"
                    :error-message="errors.gsm_provider"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.gsm_no2"
                    :label="$t('GSM No.') + ' 2'"
                    :filled="!readonly"
                    name="gsm_no2"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.gsm_no2"
                    :error="!!errors.gsm_no2"
                    :error-message="errors.gsm_no2"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.gsm_provider2"
                    :label="$t('GSM Provider') + ' 2'"
                    :filled="!readonly"
                    name="gsm_provider2"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.gsm_provider2"
                    :error="!!errors.gsm_provider2"
                    :error-message="errors.gsm_provider2"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.cid_no"
                    :label="$t('CID No')"
                    :filled="!readonly"
                    name="cid_no"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.cid_no"
                    :error="!!errors.cid_no"
                    :error-message="errors.cid_no"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.cid_provider"
                    :label="$t('CID Provider')"
                    :filled="!readonly"
                    name="cid_provider"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.cid_provider"
                    :error="!!errors.cid_provider"
                    :error-message="errors.cid_provider"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.fo_provider"
                    :label="$t('FO Provider')"
                    :filled="!readonly"
                    name="fo_provider"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.fo_provider"
                    :error="!!errors.fo_provider"
                    :error-message="errors.fo_provider"
                  />
                </div>
              </div>

              <div class="row q-col-gutter-sm q-mb-md">
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.pic_fo_provider_name"
                    :label="$t('PIC FO Provider Name')"
                    :filled="!readonly"
                    name="pic_fo_provider_name"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.pic_fo_provider_name"
                    :error="!!errors.pic_fo_provider_name"
                    :error-message="errors.pic_fo_provider_name"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.pic_fo_provider_phone_number"
                    :label="$t('PIC FO Provider Phone Number')"
                    :filled="!readonly"
                    name="pic_fo_provider_phone_number"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.pic_fo_provider_phone_number"
                    :error="!!errors.pic_fo_provider_phone_number"
                    :error-message="errors.pic_fo_provider_phone_number"
                    @keypress="$globalListeners.onKeypressOnlyUnsignedNumber($event)"
                  />
                </div>
              </div>

              <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Other Info') }}</div>

              <div class="row q-col-gutter-sm">
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" class="q-mb-sm" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.pic_service_point_name"
                    :label="$t('PIC Service Point Name')"
                    :filled="!readonly"
                    name="pic_service_point_name"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.pic_service_point_name"
                    :error="!!errors.pic_service_point_name"
                    :error-message="errors.pic_service_point_name"
                  />
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.pic_service_point_phone_number"
                    :label="$t('PIC Service Point Phone Number')"
                    :filled="!readonly"
                    name="pic_service_point_phone_number"
                    autocomplete="off"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    :dense="!readonly"
                    :rules="rules.pic_service_point_phone_number"
                    :error="!!errors.pic_service_point_phone_number"
                    :error-message="errors.pic_service_point_phone_number"
                    @keypress="$globalListeners.onKeypressOnlyUnsignedNumber($event)"
                  />
                </div>
              </div>

              <div class="row q-col-gutter-sm">
                <div class="col-xs-12 col-md-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.fo_contract_by_name"
                    :label="$t('FO Contract By')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="fo_contract_by_name"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.fo_contract_by_name"
                    :error="!!errors.fo_contract_by_name"
                    :error-message="errors.fo_contract_by_name"
                  />
                </div>

                <div class="col-xs-12 col-md-10">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.remark"
                    :label="$t('Remark')"
                    :filled="!readonly"
                    type="textarea"
                    rows="3"
                    :autogrow="readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="remark"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.remark"
                    :error="!!errors.remark"
                    :error-message="errors.remark"
                  />
                </div>
              </div>

              <template v-if="readonly">
                <div class="row">
                  <div class="col-xs-12 col-md-3">
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
            <div v-if="$auth.can(['edit.remote_location', 'delete.remote_location'])" v-show="readonly" class="col-xs-1 text-right">
              <q-btn icon="more_vert" padding="xs" size="md" flat>
                <q-menu auto-close anchor="bottom right" self="top right">
                  <q-list style="min-width: 100px">
                    <q-item v-if="$auth.can('edit.remote_location')" clickable @click="onEdit">
                      <q-item-section>{{ $t('Edit') }}</q-item-section>
                    </q-item>
                    <q-item v-if="$auth.can('delete.remote_location')" clickable @click="onDelete">
                      <q-item-section>{{ $t('Delete') }}</q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-btn>
            </div>
          </div>
        </q-card-section>

        <q-card-actions v-if="$auth.can(['create.remote_location', 'edit.remote_location']) && !readonly" align="right">
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
  customer_id: null,
  code: null,
  terminal_name: null,
  distribution_center: null,
  address: null,
  latitude: null,
  longitude: null,
  online_at: null,
  pic_remote_name: null,
  pic_remote_phone_number: null,
  pic_it_sat_name: null,
  pic_it_sat_phone_number: null,
  infrastructure_type: null,
  gsm_no: null,
  gsm_provider: null,
  gsm_no2: null,
  gsm_provider2: null,
  cid_no: null,
  cid_provider: null,
  fo_provider: null,
  pic_fo_provider_name: null,
  pic_fo_provider_phone_number: null,
  pic_service_point_name: null,
  pic_service_point_phone_number: null,
  fo_contract_by_name: null,
  remark: null
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
        customer_id: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Customer') })
        ],
        distribution_center: [
          v => !!v || this.$t('{field} is required', { field: this.$t('DC') })
        ],
        code: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Code') })
        ],
        name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Name') })
        ],
        terminal_name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Terminal Name') })
        ],
        address: [
          // v => !!v || this.$t('{field} is required', { field: this.$t('Address') })
        ],
        online_at: [
          v => !v || (!!v && this.$utils.isDateValid(v)) || this.$t('{field} is invalid', { field: this.$t('Online since at') })
        ],

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

      if (form.online_at) {
        form.online_at = this.$utils.revertDateFormat(form.online_at)
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

      if (entry.online_at) {
        entry.online_at = this.$utils.convertDateFormat(entry.online_at)
      }

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/remote-locations/${entry.id}` : '/v1/remote-locations';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.defaultFormEntry = { ...this.formEntry }
          this.$emit('success', data.data.remote_location);
          this.isEditable = false
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
          const validationErrors = {
            ...DEFAULT_FORM_ENTRY,
            ...err.validation.errors
          }

          if (validationErrors.remote_location_id) {
            validationErrors.remote_location = validationErrors.remote_location_id
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
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('remote location') }),
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
          let { data } = await this.$api.delete(`/v1/remote-locations/${this.entry.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.$emit('deleted', data)
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Remote location') }) })
              this.$emit('deleted', data)
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('remote location') })
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
