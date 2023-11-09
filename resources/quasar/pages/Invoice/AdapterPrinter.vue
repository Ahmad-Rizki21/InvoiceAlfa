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
              :class="{ selected: selectedPrinter && selectedPrinter.name === printer.name }"
              :tabindex="i"
              v-for="(printer, i) in printers"
              :key="i"
              @click.prevent="onPrinterSelected(printer)"
            >
              <div class="printer-item-inner">
                <div class="printer-icon">
                  <q-icon name="print" />
                </div>

                <div class="printer-name">
                  {{ printer.name }}
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
          :label="$t('Print')"
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
      appDownloadUrl: this.$miscData.app_download,
      streamPrinterInterval: null
    }
  },
  watch: {
    visible: {
      immediate: true,
      handler(n) {
        this.$nextTick(() => {
          this.selectedPrinter = null
          this.adapterFds = []
          this.printers = []
          this.$ws.reset()
          this.connect()
        })
      }
    }
  },
  async beforeDestroy() {
    this.$ws._closeFns = []
    clearInterval(this.streamPrinterInterval)
    await this.$ws.close()
  },
  methods: {
    async connect() {
      this.$ws._closeFns = []
      this.isConnecting = true
      const isConnected = await this.connectWebsocket()
      console.log({isConnected})
      if (!isConnected) {
        this.connectErrorCode = 99
      } else {
        this.connectErrorCode = null
        this.$ws.onClose(() => {
          this.isConnected = false
          this.connectErrorCode = 99
        })
        await this.$utils.delay(1000)
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

        this.$ws.onceEvent('get', 'get:printer_adapter_fd:response', (e, ws, data) => {
          this.adapterFds = data.adapter_fds || []
          try {
            if (this.adapterFds.length) {
              this.$ws.onceEvent('get', 'get:printers', (e, ws, data) => {
                clearTimeout(timeoutId)
                console.log('received get:printers')
                this.printers = (data.printers || []).map(v => {
                  v.adapter_fd = data.adapter_fd;

                  return v
                })

                if (this.printers.length) {
                  resolve(true)
                } else {
                  resolve(false)
                }
              })

              this.adapterFds.forEach(v => {
                console.log('sending get:printers')
                this.$ws.send({
                  mode: 'get',
                  id: 'get:printers',
                  source: this.$ws.appSource,
                  adapter_fd: v
                })
              })
            } else {
              resolve(false)
            }
          } catch (e) {
            console.error(e)
            resolve(false)
          }
        })

        this.$ws.connect()

        timeoutId = setTimeout(() => {
          resolve(false)
        }, 25000)
      })
    },
    async streamPrinters() {
      clearInterval(this.streamPrinterInterval)

      setInterval(() => {
        if (this.adapterFds.length) {
          this.adapterFds.forEach(v => {
            this.$ws.send({
              mode: 'get',
              id: 'get:printers',
              source: this.$ws.appSource,
              adapter_fd: v
            })
          })

          this.$ws.onceEvent('get', 'get:printers', (e, ws, data) => {
            if (data.printers.length) {
              this.printers = (data.printers || []).map(v => {
                v.adapter_fd = data.adapter_fd;

                return v
              })

              if (this.selectedPrinter && !this.printers.find(v => v.name == this.selectedPrinter.name)) {
                this.selectedPrinter = null
              }
            }
          })
        }
      }, 20000)
    },
    onPrinterSelected(printer) {
      this.selectedPrinter = printer
    },
    async onSubmit() {
      if (this.loading || this.isLoading) {
        return;
      }

      if (!this.selectedPrinter) {
        this.$q.notify({ message: this.$t('Please select printer device first') })
        return;
      }

      this.isLoading = true

      this.$ws.send({
        mode: 'action',
        id: 'print:invoice',
        source: this.$ws.appSource,
        adapter_fd: this.selectedPrinter.adapter_fd,
        printer_name: this.selectedPrinter.name,
        invoices: this.entries.map(v => v.id)
      })

      this.$q.notify({ message: this.$t('The print command has been sent') })

      await this.$utils.delay(5000)

      this.isLoading = false;

      this.cancel()

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
          display: -webkit-box;
          -webkit-line-clamp: 2;
          -webkit-box-orient: vertical;
          overflow: hidden;

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
