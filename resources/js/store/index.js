import Vue from 'vue';
import Vuex from 'vuex';

//ログインしているユーザーのデータ
import auth from "./modules/auth";


import tw_account from "./modules/tw_account";
import tw_account_list from "./modules/tw_account_list";
import target_account from "./modules/target_account";
import page from "./modules/page";
import keyword from "./modules/keyword";
import favorite_keyword from "./modules/Favorite_Keyword";
import tweet_list from "./modules/tweet_list";

Vue.use(Vuex);
 
const store = new Vuex.Store({

  modules: {
    auth,
    tw_account,
    tw_account_list,
    target_account,
    page,
    keyword,
    tweet_list,
    favorite_keyword,
  }
});

export default store;