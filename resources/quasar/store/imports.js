const state = {
  importPath: null,
  status: null,
  processingPage: 1,
  hasError: false
};

const getters = {
  status: (state) => state.status,
  importPath: (state) => state.importPath,
  processingPage: (state) => state.processingPage,
  hasError: (state) => state.hasError
};

const mutations = {
  SET_STATUS(state, payload) {
    state.status = payload
  },
  SET_IMPORT_PATH(state, payload) {
    state.importPath = payload;
  },
  SET_PROCESSING_PAGE(state, payload) {
    state.processingPage = payload
  },
  SET_HAS_ERROR(state, payload) {
    state.hasError = payload
  }
};

const actions = {
  async initStore({ commit }) {
    const importState = this._vm.$localStorage.get('__impstt');

    if (importState) {
      commit('SET_STATUS', importState.status)
      commit('SET_IMPORT_PATH', importState.importPath)
    }
  },
	setImportPath({ commit }, payload) {
    commit('SET_IMPORT_PATH', payload)
	},
  setStatus({ commit }, payload) {
    commit('SET_STATUS', payload)
  },
  setProcessingPage({ commit }, payload) {
    commit('SET_PROCESSING_PAGE', payload)
  },
  setHasError({ commit }, payload) {
    commit('SET_HAS_ERROR', !!payload)
  },
  sync({ state }) {
    this._vm.$localStorage.set('__impstt', {
      status: state.status,
      importPath: state.importPath,
      processingPage: state.processingPage
    }, { ttl: 1000 * 60 * 3 })
  },
  reset({ commit }) {
    commit('SET_STATUS', null)
    commit('SET_IMPORT_PATH', null)
    commit('SET_PROCESSING_PAGE', 1)
    commit('SET_HAS_ERROR', false)
    this._vm.$localStorage.unset('__impstt')
  }
};

export default {
	namespaced: true,
	state,
	getters,
	mutations,
	actions,
};
