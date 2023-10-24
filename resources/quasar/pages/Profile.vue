<template>
  <q-page class="page-profile">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <div class="row q-col-gutter-md">
      <div class="col-6">
        <q-card :flat="!$q.dark.mode" :borderless="!$q.dark.mode">
          <q-card-section v-if="formUser.id">
            <div class="row">
              <div class="col row">
                <div>
                  <avatar-field
                    :value="formUser.avatar"
                    :initial="$utils.initials(formUser.name || formUser.username || formUser.email)"
                    size="48px"
                    readonly
                  />
                </div>
                <div class="q-ml-md">
                  <h3 class="text-body1 q-ma-none">{{ formUser.name }}</h3>
                  <h6
                    class="text-subtitle1 q-ma-none"
                    :class="{ 'text-blue-grey-7': !$q.dark.mode, 'text-blue-grey-4': $q.dark.mode }"
                  >
                    @{{ formUser.username || formUser.hash }}
                  </h6>
                </div>
              </div>
              <div class="col text-right">
                <span
                  class="text-caption"
                  :class="{ 'text-blue-grey-7': $q.dark.mode, 'text-blue-grey-4': !$q.dark.mode }"
                >
                  {{ $t('Joined') }} <time :datetime="formUser.created_at" :title="formUser.created_at">{{ formUser.joined_at }}</time>
                </span>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { date } from 'quasar'
import Hashids from 'src/services/hashids'

export default {
  name: 'PageProfile',
  meta() {
    let title = this.$t("User's profile")
    const formUser = this.formUser || {}

    if (formUser.id) {
      title = formUser.name || formUser.username || title
    }

    return {
      title: (title ? title + ' - ' : '') + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    let title = this.$t("User's profile")
    const formUser = this.formUser || {}

    if (formUser.id) {
      title = formUser.name || formUser.username || title
    }

    return [
      'home',
      { text: title }
    ]
  },
  data() {
    return {
      formUser: {},
      isNotFound: false
    }
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.onRequest({ username: to.params.username })
    })
  },
  beforeRouteUpdate(to, from, next) {
    this.onRequest({ username: to.params.username })
    next()
  },
  methods: {
    async onRequest({ username }) {
      if (!username) {
        username = this.$route.params.username
      }

      const userId = Hashids.decodeUserId(username)

      username = userId || username

      this.isNotFound = false
      this.isLoading = true

      try {
        const { data } = await this.$api.get(`/v1/users/${username}`)

        if (data.status === 'success') {
          const formUser = data.data.user
          formUser.joined_at = date.formatDate(formUser.created_at, 'DD MMM YYYY')
          formUser.created_at = date.formatDate(formUser.created_at, 'YYYY-MM-DD HH:mm:ss')

          this.formUser = formUser
        }
      } catch (err) {
        this.isNotFound = true
        console.error(err)
      }

      this.isLoading = false
    }
  }
}
</script>

<style lang="scss">
.page-profile {
  padding: 3rem;
}
</style>
