// ログイン中のユーザーのデータを保持しております。
const state = () => ({

  auth_data: "",
})

const actions = {

  actAuth: function (context, value) {
    context.commit('mutateAuth', value);
  },
}

const mutations = {
  mutateAuth: function (state, value) {
    state.auth_data = value;
  },   
}

const getters = {
  getAuth_Vuex:  state => {
      return state.auth_data;
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