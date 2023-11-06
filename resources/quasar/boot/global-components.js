import Breadcrumbs from 'src/components/Breadcrumbs'
import Monthpicker from 'quasar-monthpicker'

export default ({ Vue }) => {
  Vue.component('Monthpicker', Monthpicker)
  Vue.component('Breadcrumbs', Breadcrumbs)
  Vue.component('Excerpt', () => import('../components/Excerpt.vue'))
  Vue.component('AvatarField', () => import('../components/AvatarField.vue'))
  Vue.component('TextEditor', () => import('../components/TextEditor.vue'))
  Vue.component('AutocompleteCustomer', () => import('../components/AutocompleteCustomer.vue'))
  Vue.component('AutocompleteDistributionCenter', () => import('../components/AutocompleteDistributionCenter.vue'))
  Vue.component('AutocompleteFranchise', () => import('../components/AutocompleteFranchise.vue'))
  Vue.component('AutocompleteRole', () => import('../components/AutocompleteRole.vue'))
  Vue.component('SelectTicketStatus', () => import('../components/SelectTicketStatus.vue'))
  Vue.component('SelectTicketDownTime', () => import('../components/SelectTicketDownTime.vue'))
  Vue.component('DialogImport', () => import('../components/DialogImport.vue'))
}
