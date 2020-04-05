<template>
  <ul class="u-row m-Cards-Wrapper u-scroll">


    <li class="c-Account c-Animated-Item u-col-6-md u-p-16 u-rel" 
    v-for="item in target_cmp" 
    v-bind:key="item.id"
    >
      <div class="u-container u-card-back u-pl-24 u-rel">
        <div class="icon-wrapper">
          <i v-on:click="showDeleteModal(item)" class="fas fa-times"></i>
        </div>
        
        <div class="u-row-center-y">
          <figure class="c-Account__img u-col-3">
            <img v-bind:src="item.profile_image_url_https" alt="">
          </figure>
          <dl class="u-col-9-sm">
            <dt class="a-Name">{{item.name}}@{{item.screen_name}}</dt>
            <dt class="u-mt-8 u-pl-16">フォロー：<span>{{item.friends_count}}</span></dt>
            <dt class="u-pl-16">フォロワー：<span>{{item.followers_count}}</span></dt>
          </dl>
          
        </div>
      </div>
    </li>
<li class="c-modal-Form delete js-Modal">
  <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
      <i class="cancel fas fa-times js-Modal__close"></i>
      <dt>オートついったー</dt>
      <dt class=" f-txt-3 u-mt-16 u-mb-40">下記のアカウント<br>削除しますか？</dt>
        <p class="u-my-40 name">{{delete_account_name}}</p>
        <button v-on:click="deleteAccount(delete_account_id)" class="u-my-16 a-Btn--pink" type="submit"><p>はい</p></button>
      <p class="a-Policy">Term of use. Privacy policy</p>
  </dl>
</li>
          
	</ul>
</template>

<script>
//ターゲットアカウントリストを表示しております。
export default {
  name: "account_target_cards",
  props: {

  },

  data: function () { return {
    delete_account_name:  null,
    delete_account_id:  null,

  }},

  computed: {
    target_cmp: function () {
      return this.target_account_data;
    }
  },

  methods: {

    showDetail: function (id) {
      console.log(id);
      this.$router.push({ path: '/target'});
    },

    showDeleteModal: function ($account) {
      this.delete_account_name = $account.name + "@" + $account.screen_name;
      this.delete_account_id = $account.target_account_id;
      $(".js-Modal__cover").fadeIn();
      $(".c-modal-Form.delete").fadeIn();
    },
    deleteAccount: function ($id) {

      var query = "?target_account_id=" + $id + "&model_name=Tw_Target_Account";

      this.$http.delete('/RestApi/delete'+query)
      .then(res => {

        var data = res.data;
        var result = this.alertErrors("アカウントの削除に失敗しました。", data.errors);
        if (result == false) {
          return;
        }
        this.getTargetAccounts();

        alert("アカウントの削除に成功しました。\n ・アカウントの更新を行います");
        this.modal_close_btn();
      })
      .catch(err => {
        alert("アカウントの削除に失敗しました。");
        console.log(err);
      }).finally(() => {
        
        console.log('finally')
      });
    },
  }
}
</script>
<style lang="scss" scoped>
.c-modal-Form {
  @media screen and (max-width: 575px){
    top: 64px;
  }
}
.c-Account__img {
    @media screen and (max-width: 575px){
      display: none;
  }
}
</style>