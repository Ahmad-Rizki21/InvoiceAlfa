
const routes = [
  {
    path: '/',
    component: () => import('layouts/HomeLayout.vue'),
    children: [
      { path: '', name: 'index', component: () => import('pages/Index.vue') },
      { path: 'c/pay', name: 'cust.pay', component: () => import('pages/CustPay.vue') },
    ]
  },
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      // { path: '', name: 'index', component: () => import('pages/Index.vue') },
      { path: 'users', name: 'users', component: () => import('pages/Users.vue') },
      { path: 'reports', name: 'reports', component: () => import('pages/Report.vue') },
      { path: 'invoices', name: 'invoices', component: () => import('pages/Invoice.vue') },
      { path: 'invoices/create', name: 'invoices.create', component: () => import('pages/InvoiceShow.vue') },
      { path: 'invoices/:id([0-9]+)', name: 'invoices.show', component: () => import('pages/InvoiceShow.vue') },
      { path: 'distribution-centers', name: 'distribution-centers', component: () => import('pages/DistributionCenter.vue') },
      { path: 'distribution-centers/create', name: 'distribution-centers.create', component: () => import('pages/DistributionCenterCreate.vue') },
      { path: 'distribution-centers/:id([0-9]+)', name: 'distribution-centers.show', component: () => import('pages/DistributionCenterShow.vue') },
      // { path: 'stores', name: 'stores', component: () => import('pages/Store.vue') },
      { path: 'stores/create', name: 'stores.create', component: () => import('pages/StoreCreate.vue') },
      { path: 'stores/:id([0-9]+)', name: 'stores.show', component: () => import('pages/StoreShow.vue') },
      // { path: 'franchises', name: 'franchises', component: () => import('pages/Franchise.vue') },
      { path: 'franchises/create', name: 'franchises.create', component: () => import('pages/FranchiseCreate.vue') },
      { path: 'franchises/:id([0-9]+)', name: 'franchises.show', component: () => import('pages/FranchiseShow.vue') },
      { path: 'template/invoices', name: 'template-invoices', component: () => import('pages/TemplateInvoice.vue') },
      { path: 'user-access', name: 'user-access', component: () => import('pages/UserAccess.vue') },
      { path: 'account/:tab?', name: 'account', component: () => import('pages/Account.vue') },
      { path: 'unauthorized-access', name: 'unauthorized-access', component: () => import('pages/UnauthorizedAccess.vue') },
      { path: '@:username([a-zA-Z0-9_\\.]+)', name: 'profile', component: () => import('pages/Profile.vue') },
    ]
  },
  {
    path: '/',
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: 'invoices/:id([0-9]+)/print', name: 'invoices.print', component: () => import('pages/InvoicePrint.vue') },
    ]
  },
  {
    path: '/login',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      { path: '', name: 'login', component: () => import('pages/Login.vue') },
    ]
  },
  // Always leave this as last one,
  // but you can also remove it
  {
    path: '*',
    component: () => import('layouts/HomeLayout.vue'),
    children: [
      { path: '', component: () => import('pages/Error404.vue') }
    ]
  }
]

export default routes
