<template>
  <div class="form-page q-pa-sm q-gutter-md">
    <q-card class="q-pa-sm">
      <q-form ref="form" class="form-entry" greedy :class="{ readonly }" @submit.prevent="onSubmit">
        <q-card-section>
          <div class="row">
            <div :class="{ 'col-xs-12': !readonly, 'col-xs-11': readonly }">

              <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Details') }}</div>

              <div class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md">
                <div class="col-xs-12 col-sm-8 col-md-10 col-lg-8">
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
              </div>

              <div class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md">
                <div class="col-xs-12 col-sm-4 col-md-4" :class="{ 'col-md-5': readonly, 'col-lg-4': readonly }">
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

                <div class="col-xs-12 col-sm-4 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.npwp"
                    :label="$t('NPWP')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="npwp"
                    mask="##.###.###.#-###.###"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.npwp"
                    :error="!!errors.npwp"
                    :error-message="errors.npwp"
                  />
                </div>
              </div>


              <div class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md">
                <div class="col-xs-12 col-sm-4 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
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
              </div>

              <div class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md">
                <div class="col-xs-12 col-md-10 col-lg-8">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.address"
                    :label="$t('Address') + '*'"
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
                    :label="$t('Address')"
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
              </div>

              <div class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md">
                <div class="col-xs-12 col-sm-4 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.landline_number"
                    :label="$t('Landline Number')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="landline_number"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.landline_number"
                    :error="!!errors.landline_number"
                    :error-message="errors.landline_number"
                  />
                </div>
                <div class="col-xs-12 col-sm-4 col-md-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.phone_number"
                    :label="$t('Phone Number')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="phone_number"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.phone_number"
                    :error="!!errors.phone_number"
                    :error-message="errors.phone_number"
                  />
                </div>
              </div>

              <div class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formattedApprovalDate"
                    :label="$t('SAT-HO Approval Date')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="approval_date"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.approval_date"
                    :label="$t('SAT-HO Approval Date')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="approval_date"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.approval_date"
                    :error="!!errors.approval_date"
                    :error-message="errors.approval_date"
                    mask="##/##/####"
                    :hint="$utils.isDateValid(formEntry.approval_date) ? undefined : $t('Valid format: DD/MM/YYYY')"
                  >
                    <template v-slot:append>
                      <q-icon v-if="!readonly" name="event" class="cursor-pointer">
                        <q-popup-proxy transition-show="scale" transition-hide="scale">
                          <q-date v-model="formEntry.approval_date" minimal mask="DD/MM/YYYY">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>

                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formattedFoApprovalDate"
                    :label="$t('SAT-HO Approval Date for FO')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="fo_approval_date"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.fo_approval_date"
                    :label="$t('SAT-HO Approval Date for FO')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="fo_approval_date"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.fo_approval_date"
                    :error="!!errors.fo_approval_date"
                    :error-message="errors.fo_approval_date"
                    mask="##/##/####"
                    :hint="$utils.isDateValid(formEntry.fo_approval_date) ? undefined : $t('Valid format: DD/MM/YYYY')"
                  >
                    <template v-slot:append>
                      <q-icon v-if="!readonly" name="event" class="cursor-pointer">
                        <q-popup-proxy transition-show="scale" transition-hide="scale">
                          <q-date v-model="formEntry.fo_approval_date" minimal mask="DD/MM/YYYY">
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

              <div class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.offering_letter_reference_number"
                    :label="$t('Offering Letter')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="offering_letter_reference_number"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.offering_letter_reference_number"
                    :label="$t('Offering Letter')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="offering_letter_reference_number"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.offering_letter_reference_number"
                    :error="!!errors.offering_letter_reference_number"
                    :error-message="errors.offering_letter_reference_number"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.fo_offering_letter_reference_number"
                    :label="$t('FO Offering Letter')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="fo_offering_letter_reference_number"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.fo_offering_letter_reference_number"
                    :label="$t('FO Offering Letter')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="fo_offering_letter_reference_number"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.fo_offering_letter_reference_number"
                    :error="!!errors.fo_offering_letter_reference_number"
                    :error-message="errors.fo_offering_letter_reference_number"
                  />
                </div>
              </div>


              <div class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.issuance_number"
                    :label="$t('Basis for issuing invoice')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="issuance_number"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.issuance_number"
                    :label="$t('Basis for issuing invoice')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="issuance_number"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.issuance_number"
                    :error="!!errors.issuance_number"
                    :error-message="errors.issuance_number"
                  />
                </div>

                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.fo_issuance_number"
                    :label="$t('Basis for issuing FO invoice')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="fo_issuance_number"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.fo_issuance_number"
                    :label="$t('Basis for issuing FO invoice')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="fo_issuance_number"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.fo_issuance_number"
                    :error="!!errors.fo_issuance_number"
                    :error-message="errors.fo_issuance_number"
                  />
                </div>
              </div>


              <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Virtual Account') }}</div>
              <div class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.transfer_to_virtual_account_bank_name"
                    :label="$t('Bank Name')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="transfer_to_virtual_account_bank_name"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.transfer_to_virtual_account_bank_name"
                    :label="$t('Bank Name')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="transfer_to_virtual_account_bank_name"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.transfer_to_virtual_account_bank_name"
                    :error="!!errors.transfer_to_virtual_account_bank_name"
                    :error-message="errors.transfer_to_virtual_account_bank_name"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.transfer_to_virtual_account_number"
                    :label="$t('Virtual Account Number')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="transfer_to_virtual_account_number"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.transfer_to_virtual_account_number"
                    :label="$t('Virtual Account Number')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="transfer_to_virtual_account_number"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.transfer_to_virtual_account_number"
                    :error="!!errors.transfer_to_virtual_account_number"
                    :error-message="errors.transfer_to_virtual_account_number"
                  />
                </div>
              </div>

              <div v-if="isCreate|| formEntry.has_password" class="q-mb-md text-subtitle text-weight-bold">{{ $t('Login Credentials') }}</div>
              <div v-if="isCreate" class="row q-col-gutter-x-sm q-mb-md">
                <q-checkbox v-model="createAccessLogin">
                  {{ $t('Create {entity}', { entity: $t('Login Credentials') }) }}
                </q-checkbox>
              </div>
              <div v-if="(isCreate && createAccessLogin) || (!isCreate && !readonly) || formEntry.has_password" class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.email"
                    :label="$t('Email') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="email"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.email"
                    :label="$t('Email')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="email"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.email"
                    :error="!!errors.email"
                    :error-message="errors.email"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.username"
                    :label="$t('Username')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="username"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.username"
                    :label="$t('Username')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="username"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.username"
                    :error="!!errors.username"
                    :error-message="errors.username"
                  />
                </div>
              </div>

              <div v-if="isCreate && createAccessLogin" class="row q-col-gutter-x-sm q-mb-md">
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.password"
                    :label="$t('Password') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    type="password"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="password"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.password"
                    :label="$t('Password') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    type="password"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="password"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.password"
                    :error="!!errors.password"
                    :error-message="errors.password"
                  />
                </div>
                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-4">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-if="readonly"
                    v-show="!fetching"
                    :value="formEntry.password_confirmation"
                    :label="$t('Confirm Password') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    type="password"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="password_confirmation"
                    autocomplete="off"
                    :dense="!readonly"
                  />
                  <q-input
                    v-else
                    v-show="!fetching"
                    v-model="formEntry.password_confirmation"
                    :label="$t('Confirm Password') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    type="password"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="password_confirmation"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.password_confirmation"
                    :error="!!errors.password_confirmation"
                    :error-message="errors.password_confirmation"
                  />
                </div>
              </div>

              <template v-if="readonly">
                <div class="row">
                  <div class="col-xs-12 col-md-5 col-lg-4">
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

    <div v-if="!isCreate" class="table-relation">

      <template v-if="entry && entry.id">
        <transition
          appear
          enter-active-class="animated fadeIn"
          leave-active-class="animated fadeOut"
        >
          <table-franchise
            v-if="childTab === 'franchise'"
            :distribution-center="entry"
            as-relation
            key="franchise"
          />
          <table-store
            v-else-if="childTab === 'store'"
            :customer="entry"
            key="store"
          />
        </transition>
      </template>
    </div>
  </div>
</template>

<script>
import { date } from 'quasar'

const DEFAULT_FORM_ENTRY = {
  code: null,
  name: null,
  email: null,
  username: null,
  location: null,
  address: null,
  approval_date: null,
  fo_approval_date: null,
  offering_letter_reference_number: null,
  fo_offering_letter_reference_number: null,
  issuance_number: null,
  fo_issuance_number: null,
  password: null,
  password_confirmation: null,
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
          v => !v || (!!v && v.length > 3) || this.$t('{field} must be at least {length} characters', { field: this.$t('Username'), length: 3 }),
          v => !v || (!!v && /^[a-zA-Z0-9_\.]+$/.test(v)) || this.$t('{field} can only contain alphanumeric characters, underscores, and periods', { field: this.$t('Username') }),
          v => !v || (!!v && /^[^0-9][a-zA-Z0-9_\.]+$/.test(v)) || this.$t('{field} must be starts with letter', { field: this.$t('Username') })
        ],
        email: [
          v => !this.createAccessLogin || !!v || this.$t('{field} is required', { field: this.$t('Email') }),
          (v) =>
            (!!v && this.$utils.isEmail(v)) ||
            this.$t('{field} is invalid', { field: this.$t('Email') }),
        ],
        password: [
          v => !this.createAccessLogin || !!v || this.$t('{field} is required', { field: this.$t('Password') }),
          v => v && v.length >= 6 || this.$t('{field} must be at least {length} characters', { field: this.$t('Password'), length: 6 })
        ],
        password_confirmation: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password confirmation') }),
          v => v === this.formEntry.password || this.$t('{field} does not match', { field: this.$t('Password confirmation') })
        ]
      },
      errors: DEFAULT_FORM_ENTRY,
      isEditable: false,
      defaultFormEntry: DEFAULT_FORM_ENTRY,
      childTab: 'franchise',
      createAccessLogin: false
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
    },
    formattedApprovalDate() {
      if (this.readonly && this.formEntry.approval_date) {
        return date.formatDate(date.extractDate(this.formEntry.approval_date, 'DD/MM/YYYY'), 'DD MMM YYYY')
      }
    },
    formattedFoApprovalDate() {
      if (this.readonly && this.formEntry.fo_approval_date) {
        return date.formatDate(date.extractDate(this.formEntry.fo_approval_date, 'DD/MM/YYYY'), 'DD MMM YYYY')
      }
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

        this.$nextTick(() => {
          setTimeout(() => {
            this.$forceUpdate()
          }, 100)
        })
      }
    },
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

        if (form.approval_date) {
          form.approval_date = date.formatDate(form.approval_date, 'DD/MM/YYYY')
        }
        if (form.fo_approval_date) {
          form.fo_approval_date = date.formatDate(form.fo_approval_date, 'DD/MM/YYYY')
        }
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

      if (entry.approval_date) {
        entry.approval_date = date.formatDate(date.extractDate(entry.approval_date, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }

      if (entry.fo_approval_date) {
        entry.fo_approval_date = date.formatDate(date.extractDate(entry.fo_approval_date, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }

      entry.distribution_center_id = entry.distribution_center_id || this.$route.query.distribution_center_id;

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/franchises/${entry.id}` : '/v1/franchises';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.defaultFormEntry = { ...this.formEntry }
          this.$emit('success', data.data.franchise);
          this.isEditable = false
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Franchise') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('franchise') })
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
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('franchise') }),
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
          let { data } = await this.$api.delete(`/v1/franchises/${this.entry.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.$emit('deleted', data)
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Franchise') }) })
              this.$emit('deleted', data)
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('franchise') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
      })
    },
    onTableRelationFranchiseCreate() {
      this.$router.push({
        path: `/franchises/create`,
        query: {
          distribution_center_id: this.entry.id
        }
      })
    },
    onTableRelationStoreCreate() {
      this.$router.push({
        path: `/stores/create`,
        query: {
          distribution_center_id: this.entry.id
        }
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
