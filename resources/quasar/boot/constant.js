import { decrypt } from 'src/services/http-crypter'

export default ({ Vue }) => {
  let constants = {}
  let appDownloadUrl = null

  if (process.env.CLIENT) {
    try {
      const appData = JSON.parse(decrypt(window._APP_DATA || ''));
      constants = appData.constants || {}

      try {
        appDownloadUrl = appData.url.adapter_download
      } catch (err) {}
    } catch (err) {
      console.error(err)
    }
  }

  Vue.prototype.$constant = constants
  Vue.prototype.$miscData = {
    app_download: appDownloadUrl
  }
}
