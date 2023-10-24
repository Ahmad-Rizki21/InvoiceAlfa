const state = {
  breadcrumbs: []
};

const getters = {
  breadcrumbs: (state) => state.breadcrumbs
};

const mutations = {
  SET_BREADCRUMBS(state, payload) {
    state.breadcrumbs = payload;
  }
};

const actions = {
	set({ commit }, payload) {
    commit('SET_BREADCRUMBS', payload);
	}
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
	actions,
};
