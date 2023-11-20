import Breadcrumbs from 'src/components/Breadcrumbs'
import Monthpicker from 'quasar-monthpicker'

export default ({ Vue }) => {
  Vue.component('Monthpicker', Monthpicker)
  Vue.component('Breadcrumbs', Breadcrumbs)
  Vue.component('Excerpt', () => import('../components/Excerpt.vue'))
  Vue.component('AvatarField', () => import('../components/AvatarField.vue'))
  Vue.component('UploadField', () => import('../components/UploadField.vue'))
  Vue.component('TextEditor', () => import('../components/TextEditor.vue'))
  Vue.component('AutocompleteCustomer', () => import('../components/AutocompleteCustomer.vue'))
  Vue.component('AutocompleteDistributionCenter', () => import('../components/AutocompleteDistributionCenter.vue'))
  Vue.component('AutocompleteFranchise', () => import('../components/AutocompleteFranchise.vue'))
  Vue.component('AutocompleteRole', () => import('../components/AutocompleteRole.vue'))
  Vue.component('SelectInvoiceStatus', () => import('../components/SelectInvoiceStatus.vue'))
  Vue.component('DialogImport', () => import('../components/DialogImport.vue'))
  Vue.component('SignaturePad', () => import('../components/SignaturePad.vue'))
}
