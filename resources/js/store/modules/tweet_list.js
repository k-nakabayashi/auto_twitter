// 登録しているツイートリストを保持しております。
const state = () => ({

  data: {},
})

const actions = {

  actTweet_List: function (context, value) {
    context.commit('mutateKeyword', value);
  },
}

const mutations = {
  mutateKeyword: function (state, value) {
    state.data = value;
  },   
}

const getters = {
  getTweet_list:  state => {
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