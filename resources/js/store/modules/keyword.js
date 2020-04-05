// 「自動フォロー」用のキーワードを保持しております。
const state = () => ({

  data: {},
})

const actions = {

  actKeyword: function (context, value) {
    context.commit('mutateKeyword', value);
  },
}

const mutations = {
  mutateKeyword: function (state, value) {
    state.data = value;
  },   
}

const getters = {
  getKeyword:  state => {
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