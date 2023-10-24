import Vue from 'vue'

class Auth {
  constructor({ $store, $api, $constant }) {
    this.$store = $store;
    this.$api = $api;
    this.$constant = $constant;
  }

  get user() {
    return this.$store.getters['auth/user'] || {}
  }

  get id() {
    const user = this.user;

    return user ? user.id : null;
  }

  get check() {
    return !!this.$store.getters['auth/check']
  }

  get loggedIn() {
    return !!this.$store.getters['auth/loggedIn']
  }

  get permissions() {
    return this.$store.getters['auth/permissions'] || []
  }

  get authType() {
    const user = this.user
    return user ? user.auth_type : null
  }

  hasAnyRoles(roleKeys) {
    const userRole = this.user.role_id;

    if (!Array.isArray(roleKeys)) {
      return userRole == roleKeys;
    }

    return roleKeys.some(r => r == userRole)
  }

  hasAllRoles(roleKeys) {
    const userRole = this.user.role_id;

    if (!Array.isArray(roleKeys)) {
      return userRole == roleKeys;
    }

    return roleKeys.every(r => r == userRole)
  }

  hasRoles(roleKeys) {
    return this.hasAnyRoles(roleKeys)
  }

  hasRole(roleKeys) {
    return this.hasRoles(roleKeys)
  }

  isSuperUser() {
    return this.hasRole(this.$constant.role.TYPE_SUPER_ADMIN);
  }

  can(permissions) {
    return true
    if (!Array.isArray(permissions)) {
      permissions = [permissions]
    }

    const perms = this.permissions
    const len = permissions.length
    let i = 0

    for (i; i < len; i++) {
      if (!perms.includes(permissions[i])) {
        return false
      }
    }

    return true
  }

}

export default async ({ Vue, store }) => {
  await store.dispatch('auth/initStore')

  Vue.prototype.$auth = new Auth({
    $store: store,
    $api: Vue.prototype.$api,
    $constant: Vue.prototype.$constant
  })
}

