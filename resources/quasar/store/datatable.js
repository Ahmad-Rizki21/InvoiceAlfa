const state = {
  viewColumns: {}
};

const getters = {
  viewColumns: (state) => state.viewColumns
};

const mutations = {
  SET_VIEW_COLUMNS(state, payload) {
    state.viewColumns = payload
  },
  SET_VIEW_COLUMN(state, { page, columns }) {
    state.viewColumns[page] = columns;
  }
};

const actions = {
  async initStore({ commit }) {
    const viewColumns = this._vm.$localStorage.get('__vwcol');

    if (viewColumns) {
      commit('SET_VIEW_COLUMNS', viewColumns)
    }
  },
	setViewColumn({ commit, state }, { page, columns}) {
    commit('SET_VIEW_COLUMN', { page, columns });

    this._vm.$localStorage.set('__vwcol', state.viewColumns);
	}
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
	actions,
};
