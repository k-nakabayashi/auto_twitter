<template>
  <dt class="c-Btn-Wrapper">

    <p class="a-Btn--grey follow-btn api-stop1" 
      v-on:click="openModal_For_Key(account, 'follow_key', index, 'follow')"
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length == 0) || !(account.key_pattern_id == null || account.key_pattern_id == '')}"
    >
    <span>フォロー</span>
    </p>
    
    <p class="a-Btn--pink follow-btn api-stop2" 
      v-on:click="openModal_For_auto(account, 'follow', index, 'follow')"
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length == 0) || (!(account.follow == 0) || (account.key_pattern_id == null || account.key_pattern_id == ''))}"
    >
      <span>フォロー可能</span>
    </p> 
    <p class="a-Btn--pink follow-btn api-doing" 
      v-on:click="openModal_For_auto(account, 'follow_stop', index, 'follow')"
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length == 0) || !(account.follow == 1) || (account.key_pattern_id == null || account.key_pattern_id == '')}"
    >
    <span>フォロー中</span>
    </p>

    <p class="a-Btn--grey follow-btn api-restart" 
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length == 0) || !(account.follow == 2) || (account.key_pattern_id == null || account.key_pattern_id == '') }"
    ><span>フォロー再開中</span>
    </p>

    <p class="a-Chip follow-btn api-delete" 
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length == 0) || !(account.follow == 5)}"
    ><span>フォロー削除中</span>
    </p>

    <p class="a-Btn--pink follow-btn api-tmp_stop" 
      v-on:click="openModal_For_auto(account, 'follow_restart', index, 'follow')"
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length == 0) || !(account.follow == 3) || (account.key_pattern_id == null || account.key_pattern_id == '')}"
    >
    <span v-bind:class="{ isHidden: !(account.follow == 3)}">フォロー 一時停止中</span>
    </p>

    <p class="a-Btn--pink follow-btn api-release" 
      v-on:click="openModal_For_auto(account, 'follow_release', index, 'follow')"
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length == 0) ||  !(account.follow == 4) || (account.key_pattern_id == null || account.key_pattern_id == '')}"
    >
    <span v-bind:class="{ isHidden: !(account.follow == 4)}">フォロー 凍結中</span>
    </p>


    <!-- end フォローボタン -->

    <!-- アンフォローボタン -->
    
    <p class="a-Btn--pink unfollow-btn api-stop2"
      v-if="account.unfollow_flag === 1"
      v-bind:class="{ isHidden: !(account.unfollow == 0)}"
      v-on:click="openModal_For_auto(account, 'unfollow', index, 'unfollow')"
    ><span>アンフォロー</span>
    </p>
    
    <p class="a-Btn--pink unfollow-btn api-doing" 
      v-if="account.unfollow_flag === 1"
      v-bind:class="{ isHidden: !(account.unfollow == 1)}"
      v-on:click="openModal_For_auto(account, 'unfollow_stop', index, 'unfollow')"
    ><span>アンフォロー中</span>
    </p>

    <p class="a-Btn--grey unfollow-btn api-restart" 
      v-if="account.unfollow_flag === 1"
      v-bind:class="{ isHidden: !(account.unfollow == 2)}"
    ><span>アンフォロー再開中</span>
    </p>

    <p class="a-Chip unfollow-btn api-delete" 
      v-bind:class="{ isHidden: !(account.unfollow == 5)}"
    ><span>アンフォロー削除中</span>
    </p>

    <p class="a-Btn--pink unfollow-btn api-tmp_stop" 
      v-if="account.unfollow_flag === 1"
      v-bind:class="{ isHidden: !(account.unfollow == 3)}"
      v-on:click="openModal_For_auto(account, 'unfollow_restart', index, 'unfollow')"
    ><span>アンフォロー 一時停止中</span></p>

    <p class="a-Btn--pink unfollow-btn api-release" 
      v-if="account.unfollow_flag === 1"
      v-bind:class="{ isHidden: account.unfollow != 4}"
      v-on:click="openModal_For_auto(account, 'unfollow_release', index, 'unfollow')"
    ><span>アンフォロー 凍結中</span></p>

    <!-- end アンフォローボタン -->    
  </dt>
</template>

<script>
//アカウントリストページ 「/home」で使用するボタンを表示しております。
export default {
  name: "modal_openning_btn",

  data: function () { return {
    selected_acccunt: null,
    key_pattern_id:  null,
    delete_account_name:  null,
    delete_account_id:  null,
    
    btn: "",//モーダルを開くと、モーダルで表示するボタンがbtnによって変わる

    // api_list: [],
    // key_selected_flag: [],

    // restriction_name: ['followers_list', 'users_show', 'friendships_create'],
    // check_api: {
    //   'auto_name': ['auto_followers_list', 'auto_users_show', 'auto_friendships_create'], 
    //   'restriction_name':['followers_list', 'users_show', 'friendships_create']
    // },

    api_list: [],
    key_selected_flag: [],
    restriction_name: [
      'followers_list', 'users_show', 
      'friendships_create',
    ],

    
  }},

  props: {
    'account': Object,
    'index': Number,
  },


  methods: {
    
    openModal_For_auto: function (account, btn, index, auto_name) {
 
      if (Object.keys(this.keyword_data).length == 0 && btn.indexOf('unfollow') != 0) {
        alert("キーワードが未登録なので登録してください。");
      }

      this.$emit('emitBtn', btn);
      this.$emit('emitSelected_Account', index);
      this.$store.dispatch('tw_account/actTw_Account', account);

      setTimeout(function(){
        $(".js-Modal__cover").fadeIn();
        $(".c-modal-Form.auto." + auto_name).fadeIn();
      }, 500);

    },

    openModal_For_Key: function (account, btn, index) {
     
      if (Object.keys(this.keyword_data).length == 0) {
        alert("キーワードが未登録なので登録してください。");
        return;
      }

      this.$emit('emitBtn', btn);
      this.$emit('emitSelected_Account', index);
      this.$store.dispatch('tw_account/actTw_Account', account);

      setTimeout(function(){
        $(".js-Modal__cover").fadeIn();
        $(".c-modal-Form.key").fadeIn();
      }, 500);
    },
  }
}
</script>
<style lang="scss" scoped>
</style>
