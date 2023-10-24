import { decrypt } from 'src/services/http-crypter'

export default ({ Vue }) => {
  let constants = {}

  if (process.env.CLIENT) {
    try {
      const appData = JSON.parse(decrypt(window._APP_DATA || ''));
      constants = appData.constants || {}
    } catch (err) {
      console.error(err)
    }
  }

  Vue.prototype.$constant = constants
}
