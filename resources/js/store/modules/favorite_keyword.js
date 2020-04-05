// 「自動いいね」用のキーワードを保持しております。
const state = () => ({

  data: {},
})

const actions = {

  actFavorite_Keyword: function (context, value) {
    context.commit('mutateFavorite_Keyword', value);
  },
}

const mutations = {
  mutateFavorite_Keyword: function (state, value) {
    state.data = value;
  },   
}

const getters = {
  getFavorite_Keyword:  state => {
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