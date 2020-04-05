// 表示中のページデータを保持しております。
const state = () => ({

  data: "",
})

const actions = {

  actPage: function (context, value) {
    context.commit('mutatePage', value);
  },
}

const mutations = {
  mutatePage: function (state, value) {
    state.data = value;
  },   
}

const getters = {
  getPage_Vuex:  state => {
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