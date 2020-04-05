// ターゲットアカウントリストを保持しております。
const state = () => ({

  data: {},
})

const actions = {

  actTarget_Account: function (context, value) {
    context.commit('mutateTarget_Account', value);
  },
}

const mutations = {
  mutateTarget_Account: function (state, value) {
    state.data = value;
  },   
}

const getters = {
  getTarget_Account:  state => {
      return state.data;
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}
/*
export const plugins = [
  createPersistedState(),
]*/