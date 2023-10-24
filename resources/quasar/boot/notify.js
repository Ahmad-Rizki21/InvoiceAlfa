// file: /src/boot/notify.js

export default({ Vue }) => {
  const $qNotify = Vue.prototype.$q.notify

  function _addNotification(opts, vm) {
    let extraOpts = {};

    if (opts && opts.error instanceof Error) {
      extraOpts = opts;
      opts = opts.error
      delete extraOpts.error;
    }

    if (opts instanceof Error) {
      let errMsg = opts.message;
      if (opts.response && opts.response.status === 429 && opts.response.headers['x-ratelimit-reset']) {
        let rateLimitTime = opts.response.headers['x-ratelimit-reset']

        if (rateLimitTime) {
          rateLimitTime = this.$dayjs.unix(rateLimitTime).tz()

          if (rateLimitTime.isValid()) {
            rateLimitTime = rateLimitTime.diff(this.$dayjs().tz(), 's')
            if (rateLimitTime > 0) {
              errMsg = this.$t(`Too many attempts. Please wait for more ${rateLimitTime} seconds to continue.`)
            }
          }
        }
      } else if (opts.response && opts.response.data) {
        if (opts.response.data.message) {
          errMsg = opts.response.data.message;
        }

        if (opts.response.data.errors && typeof opts.response.data.errors === 'object') {
          for (const key in opts.response.data.errors) {
            if (Array.isArray(opts.response.data.errors[key]) && opts.response.data.errors[key].length >= 0) {
              errMsg = opts.response.data.errors[key][0];
            }

            break;
          }
        }
      }

      opts = {
        ...extraOpts,
        message: errMsg
      }
    }

    return $qNotify(opts, vm)
  }

  Vue.prototype.$q.notify = _addNotification
  Vue.prototype.$q.notify.setDefaults = $qNotify.setDefaults
  Vue.prototype.$q.notify.registerType = $qNotify.registerType
}
