<template>
<div>
  <p class="a-Page-Title f-txt-2" v-if="page_data === '/home'">アカウントリスト</p>
  <p class="a-Page-Title f-txt-2" v-if="page_data === '/target_list'">ターゲットアカウントリスト</p>
  <p class="a-Page-Title f-txt-2" v-if="page_data === '/keyword'">「フォロー」キーワード</p>
  <p class="a-Page-Title f-txt-2" v-if="page_data === '/favorite_keyword'">「いいね」キーワード</p>
  <p class="a-Page-Title f-txt-2" v-if="page_data === '/my_tw_account'">{{account_name}}@{{account_screen_name}}</p>
</div>
</template>


<script>
//ページ毎のタイトル表示をしております・
export default {
  name: "page_title",
  data: function  () {return{
    account_name: "",
    account_screen_name: "",
  }},
  updated: function () {
    this.account_name = this.tw_account_data.name;
    this.account_screen_name = this.tw_account_data.screen_name;
  },
  mounted: function () {
    this.account_name = this.tw_account_data.name;
    this.account_screen_name = this.tw_account_data.screen_name;
  }
}
</script>

<style lang="scss" scoped>
</style>