// 選択中のTwitterアカウントを保持しております。
const state = () => ({

  data: "",
})

const actions = {

  actTw_Account: function (context, value) {
    context.commit('mutateTw_Account', value);
  },
}

const mutations = {
  mutateTw_Account: function (state, value) {
    state.data = value;
  },   
}

const getters = {
  getTw_Account:  state => {
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