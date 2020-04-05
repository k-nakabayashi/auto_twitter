import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import account_cards from '../design/components/tw/card/account_cards.vue';
import account_detail_card from "../design/components/tw/card/account_detail_card.vue";
import account_target_cards from "../design/components/tw/card/account_target_cards.vue";
import keyword_cards from "../design/components/tw/card/keyword_cards.vue";
import favorite_keyword_cards from "../design/components/tw/card/favorite_keyword_cards.vue";
import how_to_use from "../design/components/tw/other/how_to_use.vue";

const routes = [
  {
      path: '/home',
      components: {
        account_cards: account_cards,
      },
  },

  {
      path: '/my_tw_account',
      components: {
        account_cards: account_detail_card,
      },
  },
  
  {
    path: '/my_tw_account:id',
    components: {
      account_cards: account_detail_card,
    },
  },

  {
    path: '/target_list',
    components: {
      account_cards: account_target_cards,
    }
  },
  {
    path: '/keyword',
    components: {
      account_cards: keyword_cards,
    }
  },

  {
    path: '/favorite_keyword',
    components: {
      account_cards: favorite_keyword_cards,
    }
  },
  

  {
    path: '/how_to_use',
    components: {
      account_cards: how_to_use,
    }
  },


];

// 5.
const router = new VueRouter({

    mode: 'history',
    routes,
})

// 6.
export default router;