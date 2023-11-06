const state = {
  viewColumns: {},
  lastInvoiceChildTab: 'franchise'
};

const getters = {
  viewColumns: (state) => state.viewColumns,
  lastInvoiceChildTab: (state) => state.lastInvoiceChildTab,
};

const mutations = {
  SET_VIEW_COLUMNS(state, payload) {
    state.viewColumns = payload
  },
  SET_VIEW_COLUMN(state, { page, columns }) {
    state.viewColumns[page] = columns;
  },
  SET_LAST_INVOICE_CHILD_TAB(state, payload) {
    state.lastInvoiceChildTab = payload
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
	},
  setLastInvoiceChildTab({ commit }, tab) {
    commit('SET_LAST_INVOICE_CHILD_TAB', tab)
  }
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
	actions,
};
