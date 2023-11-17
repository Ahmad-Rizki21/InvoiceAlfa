const state = {
  dark: false,
  sidebar: undefined,
  sidebarMiniVariant: false,
  dashboardCardMiniSummaryTimeTakenType: 'avg',
  dashboardCardMiniSummaryUnpaidCustomer: null,
  storedSearches: {},
};

const getters = {
  settings: (state) => {
    return { ...state };
  },
  storedSearches: (state) => state.storedSearches
};

const mutations = {
  SET_SETTINGS(state, payload) {
    for (const key in payload) {
      state[key] = payload[key];
    }
  },
  SAVE_STORED_SEARCHES(state, { id, payload }) {
    state.storedSearches[id] = JSON.stringify(payload);

    const keys = Object.keys(state.storedSearches);

    if (keys.length > 3) {
      try {
        delete state.storedSearches[keys[0]];
      } catch (e) {
        //
      }
    }
  }
};

const actions = {
  async initStore({ dispatch }) {
    const vm = this._vm;
    const storage = vm.$storage;

    const settings = storage.get('__app_settings');

    if (settings) {
      dispatch('set', settings);
    }
  },
  set({ commit }, payload) {
    commit('SET_SETTINGS', payload);
  },
  save({ getters }) {
    const settings = { ...getters.settings };
    delete settings.sidebar
    delete settings.sidebarMiniVariant

    this._vm.$storage.set('__app_settings', settings);
  },
  storeSearches({ commit }, { id, payload }) {
    commit('SAVE_STORED_SEARCHES', { id, payload });
  },
  toggleSidebar({ dispatch, state }) {
    dispatch('set', { sidebar: !state.sidebar });
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};
