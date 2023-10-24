const state = {
	profile: null,
	permissions: []
};

const getters = {
	check: (state) => Boolean(state.profile),
	loggedIn: (state) => Boolean(state.profile),
	user: (state) => state.profile,
	permissions: (state) => state.permissions,
  locale: (state) => state.profile ? state.profile.locale : 'id',
};

const mutations = {
	SET_PROFILE(state, payload) {
		state.profile = payload;
	},
	SET_PERMISSIONS(state, payload) {
		state.permissions = payload || [];
	}
};

const actions = {
	async initStore({ dispatch }) {
		const vm = this._vm;
		const token = vm.$storage.get('_token');
		const user = vm.$storage.get('_user');

		if (!token) {
			await dispatch('logout', false);
			return;
		}

		await dispatch('login', { token, user });

		await dispatch('fetch');
	},
	login({ dispatch, commit }, { user, token }) {
		if (user) {
			const vm = this._vm;
			vm.$storage.set('_user', user);

			if (token) {
  			vm.$storage.set('_token', token);
			}

			commit('SET_PROFILE', user);

			if (token) {
  			vm.$api.defaults.headers.common['Authorization'] = `Bearer ${token.access_token}`;
			}
		}
	},
	async fetch({ dispatch, commit }) {
		const vm = this._vm;
		try {
			let { data } = await vm.$api.get('/v1/auth/me');

      if (data.data) {
				await dispatch('login', { user: data.data.user });
        commit('SET_PERMISSIONS', data.data.permissions);
			} else {
				await dispatch('logout');
			}
		} catch (err) {
			if (err && err.response && err.response.data && err.response.data.message === 'Unauthenticated.') {
				await dispatch('logout');
			} else {
				await dispatch('logout');
			}
		}
	},
  async update({ dispatch, commit }, payload) {
    const vm = this._vm;
    try {
      const { data } = await vm.$api.patch('/v1/auth/me', payload);
      if (data.data) {
        await dispatch('login', { user: data.data.user });
        commit('SET_PERMISSIONS', data.data.permissions);
        return data.data.user;
      }
    } catch (err) {}

  },
	async logout({ commit }, hard = true) {
		this._vm.$storage.set('_user', null);
		this._vm.$storage.set('_token', null);
		this._vm.$storage.clear();

		if (hard) {
			try {
				await this._vm.$api.post('/v1/auth/logout');
			} catch (err) {
				//
			}
		}
		commit('SET_PROFILE', null);
	},
  async getToken () {
    return await this._vm.$storage.get('_token');
  }
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
	actions,
};
