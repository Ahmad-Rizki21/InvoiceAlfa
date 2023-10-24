const state = {
  firstVisit: true,
  finishedGroups: {}
};

const getters = {
  firstVisit: (state) => state.firstVisit,
  finishedGroups: (state) => state.finishedGroups
};

const mutations = {
  SET_FIRST_VISIT(state, payload) {
    state.firstVisit = payload;
  },
  SET_FINISHED_GROUPS(state, groups) {
    state.finishedGroups = groups
  },
  ADD_FINISHED_GROUP(state, group) {
    state.finishedGroups[group] = true;
  }
};

const actions = {
  async initStore({ commit }) {
    const firstVisit = this._vm.$localStorage.get('__tg_fv');

    if (firstVisit) {
      commit('SET_FIRST_VISIT', !!firstVisit)
    }

    const finishedGroups = this._vm.$localStorage.get('__tg_fgs');

    if (finishedGroups) {
      commit('SET_FINISHED_GROUPS', finishedGroups || {})
    }
  },
	setFirstVisit({ commit }, payload) {
    commit('SET_FIRST_VISIT', payload);
	},
  finish({ commit, state }, group) {
    commit('ADD_FINISHED_GROUP', group)

    this._vm.$localStorage.set('__tg_fgs', state.finishedGroups)
  }
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
	actions,
};
