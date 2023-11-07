<template>
  <q-card class="adapter-printer q-pa-sm">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div class="text-h6 q-px-md q-pt-sm">
        {{ $t('Print') }}
      </div>
      <q-card-section class="q-mb-md scroll" style="max-height: 60vh">
        <div v-if="isConnecting" class="connect-loading">
          <q-spinner-radio
            color="primary"
            size="6.65em"
          />

          <div class="info">
            Connecting to available printer adapter&hellip;
          </div>
        </div>
        <div v-else-if="connectErrorCode" class="connect-error">
          <q-icon
            color="negative"
            name="error"
            size="4.1em"
          />

          <p class="info">
            <template v-if="connectErrorCode === 99">
              Could not find printer adapter connection.<br />Seems like no adapter open anywhere.
            </template>
            <template v-else>
              Failed to connect to any printer adapter.
            </template>
          </p>

          <div class="reconnect">
            <q-btn
              outline
              color="secondary"
              @click="connect"
            >
              {{ $t('Reconnect') }}
            </q-btn>
          </div>
        </div>
        <div v-else class="printer-list">
          <div v-if="!adapterFds.length" class="empty-fds">
            Could not find printer adapter connection.<br />Seems like no adapter open anywhere.
          </div>
          <template v-else>
            <div
              class="printer-item"
              role="button"
              :class="{ selected: selectedPrinter && selectedPrinter.name === i }"
              :tabindex="i"
              v-for="i in 22"
              :key="i"
              @click.prevent="onPrinterSelected({ name: i })"
            >
              <div class="printer-item-inner">
                <div class="printer-icon">
                  <q-icon name="print" />
                </div>

                <div class="printer-name">
                  HP Deskjet 30 Lorem Ipsum sit dolor amet {{  i*923298 }}
                </div>

                <div
                  v-show="selectedPrinter && selectedPrinter.name === i"
                  class="icon-selected"
                >
                  <q-icon
                    size="xs"
                    name="check"
                    color="white"
                  />
                </div>
              </div>
            </div>
          </template>
        </div>

        <div v-if="connectErrorCode" class="troubleshoot">
          <q-separator class="q-mb-lg" />

          <template v-if="$q.lang.isoName === 'id'">
            <ul>
              <li>
                Pastikan Anda telah membuka Aplikasi <strong>Invoice Printer Adapter</strong> di komputer lokal Anda yang sudah terhubung dengan perangkat printer.
                <template v-if="appDownloadUrl">
                  Anda dapat mengunduhnya <a :href="appDownloadUrl" download>di sini</a>.
                </template>
              </li>
              <li>
                Klik tombol <strong>Reconnect</strong>, untuk menyambung ulang koneksi.
              </li>
              <li>
                Jika kesalahan masih berlanjut, tutup aplikasi Invoice Printer Adapter sepenuhnya dan buka kembali. Pastikan aplikasi dapat mendeteksi/terhubung dengan perangkat printer.
              </li>
            </ul>
          </template>
          <template v-else>
            <ul>
              <li>
                Make sure you have opened the <strong>Invoice Printer Adapter</strong> on your local computer that is connected to the printer device.
                <template v-if="appDownloadUrl">
                  You can download the adapter app <a :href="appDownloadUrl" download>here</a>.
                </template>
              </li>
              <li>
                Click the <strong>Reconnect</strong> button, to try to connect to the adapter again.
              </li>
              <li>
                If the error still persists, close the Invoice Printer Adapter application completely and reopen it. Make sure the adapter app can detect/connect with the printer device.
              </li>
            </ul>
          </template>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn
          :label="connectErrorCode ? $t('Close') : $t('Cancel')"
          flat
          :disable="loading || isLoading"
          @click="cancel"
        />
        <q-btn
          v-if="!(isConnecting || connectErrorCode)"
          type="submit"
          :label="$t('Update')"
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
  name: null,
  password: null,
  password_confirmation: null
}

export default {
  name: 'AdapterPrinter',
  props: {
    loading: Boolean,
    closable: {
      type: Boolean,
      default: true
    },
    entries: {
      type: Array,
      default() {
        return []
      }
    },
    visible: Boolean
  },
  data() {
    return {
      isLoading: false,
      rules: {
        name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Name') })
        ],
      },
      errors: {},
      adapterFds: [],
      printers: [],
      selectedPrinter: null,
      isConnecting: true,
      connectErrorCode: null,
      appDownloadUrl: this.$miscData.app_download
    }
  },
  watch: {
    visible: {
      immediate: true,
      handler(n) {
        this.$nextTick(() => {
          this.connect()
        })
      }
    }
  },
  async beforeDestroy() {
    await this.$ws.close()
  },
  methods: {
    async connect() {
      this.isConnecting = true
      const isConnected = await this.connectWebsocket()
      console.log({isConnected})
      if (!isConnected) {
        this.connectErrorCode = 99
      } else {
        this.connectErrorCode = null
        this.streamPrinters()
      }
      this.isConnecting = false

    },
    async connectWebsocket() {
      return new Promise((resolve) => {
        let timeoutId = null

        this.$ws.onReady(async (ws) => {
          ws.send({
            mode: 'get',
            id: 'get:printer_adapter_fd',
            source: this.$ws.appSource
          })
        })

        this.$ws.onceMessage((e, ws) => {
          clearTimeout(timeoutId)

          try {
            const data = JSON.parse(e.data)

            if (data.mode === 'get') {
              if (data.id === 'get:printer_adapter_fd:response') {
                if (data.adapter_fds && data.adapter_fds.length) {
                  this.adapterFds = data.adapter_fds
                  console.log('asdkjaskdjkasdjkasjdksja')
                  resolve(true)
                } else {
                  resolve(false)
                }
              }
            }
          } catch (err) {
            console.error(err)
            resolve(false)
          }
        })

        this.$ws.connect()

        timeoutId = setTimeout(() => {
          resolve(false)
        }, 30000)
      })
    },
    async streamPrinters() {
      this.adapterFds.forEach(v => {
        console.log('sendingtoprinter',v)
        this.$ws.send({
          mode: 'get',
          id: 'get:printers',
          source: this.$ws.appSource,
          adapter_fd: v
        })
      })

      this.$ws.onMessage((e, ws) => {
        try {
          const data = JSON.parse(e.data)

          if (data.mode === 'get') {
            if (data.id === 'get:printers:response') {
              //
            }
          }
        } catch (err) {
          console.error(err)
        }
      })
    },
    onPrinterSelected(printer) {
      this.selectedPrinter = printer
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
        password: this.formEntry.password,
        password_confirmation: this.formEntry.password_confirmation
      }

      this.isLoading = true;

      try {
        let { data } = await this.$api.patch(`/v1/distribution-centers/${this.formEntry.id}`, entry);

        if (data.status === 'success') {
          this.$emit('success');
        }
        if (data.status === 'success') {
          this.$q.notify({ message: this.$t('{entity} updated', { entity: this.$t('Password') }) })
        } else {
          this.$t('Failed to update {entity}', { entity: this.$t('password') })
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

<style lang="scss">
.adapter-printer {
  min-width: 380px;

  @media (min-width: $breakpoint-sm-min) {
    max-width: 786px !important;
  }

  .connect-loading {
    text-align: center;

    .q-spinner {
      margin-bottom: 1.5rem;
    }
  }

  .connect-error {
    text-align: center;

    .q-icon {
      margin-bottom: 0.5rem;
    }

    .info {
      font-size: 1.2em;
    }

    .reconnect {
      margin-bottom: 1.5rem;
    }
  }

  .troubleshoot {
    margin-top: 1.5rem;
    text-align: left;
  }


  .printer-list {
    display: flex;
    flex-wrap: wrap;
    margin-left: -0.5rem;
    margin-right: -0.5rem;

    > .printer-item {
      padding: 0.5rem;
      width: 33.3333333333%;
      min-width: 15rem;
      opacity: 0.8;
      color: #333;

      > .printer-item-inner {
        border-radius: 3px;
        background-color: transparentize($primary, 0.9);
        padding: 0.5rem;
        position: relative;

        > .printer-icon {
          text-align: center;
          margin-bottom: 0.25rem;

          > .q-icon {
            font-size: 3em;
          }
        }

        > .printer-name {
          text-align: center;
        }

        > .icon-selected {
          position: absolute;
          right: 0.25rem;
          top: 0.25rem;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 0.15rem;
          border-radius: 50%;
          width: 1rem;
          height: 1rem;
          background-color: #{$positive};

          > .q-icon {
            font-size: 0.75em !important;
          }
        }
      }

      &:hover,
      &:focus {
        opacity: 1;

        > .printer-item-inner {
          background-color: transparentize($primary, 0.6);
          color: #fff;
        }
      }
      &.selected {
        opacity: 1;

        > .printer-item-inner {
          background-color: #{$primary};
          color: #fff;
        }
      }
    }

    > .empty-fds {
      min-height: 10rem;
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
  }
}
</style>
