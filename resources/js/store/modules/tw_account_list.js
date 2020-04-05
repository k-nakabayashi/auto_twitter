// Twitterアカウントリストを保持しております。
const state = () => ({

  data: {},
})

const actions = {

  actTw_Account_List: function (context, value) {
    context.commit('mutateTw_Account_List', value);
  },
}

const mutations = {
  mutateTw_Account_List: function (state, value) {
    state.data = value;
  },   
}

const getters = {
  getTw_Account_List:  state => {
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