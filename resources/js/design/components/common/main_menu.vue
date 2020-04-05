<template>
  <ul class="menu-list">
    <li class="c-Main-Menus__element top"><a href="/home">HOME</a></li>
    <li v-on:click="changePage('home')" class="c-Main-Menus__element home">
        <p>Twitter アカウント</p>
    </li>
    <li v-on:click="changePage('target_list')" class="c-Main-Menus__element target_list">
        <p>ターゲット<br>アカウント</p>
    </li>
    <li v-on:click="changePage('keyword')" class="c-Main-Menus__element keyword">
        <p>「フォロー」<br>キーワード登録</p>
    </li>
    <li v-on:click="changePage('favorite_keyword')" class="c-Main-Menus__element favorite_keyword">
        <p>「いいね」<br>キーワード登録</p>
    </li>
    <li v-on:click="changePage('how_to_use')" class="c-Main-Menus__element how_to_use">
        <p>使い方</p>
    </li>
    <li v-on:click="showModal()" class="c-Main-Menus__element logout">
        <p>ログアウト</p>
    </li>


    <li class="c-modal-Form logout js-Modal">
      <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
          <i class="cancel fas fa-times js-Modal__close"></i>
          <dt>オートついったー</dt>
          <dt class="a-Title f-txt-3">ログアウトしますか？</dt>
            <div class="search-keyword">
                <button v-on:click="logout('Favorite_Key_Pattern')" class="a-Btn--pink u-mt-32" type="submit"><p>はい</p></button>
            </div>
          <p class="a-Policy">Term of use. Privacy policy</p>
      </dl>
    </li>
    <div class="js-Modal__cover2 js-Modal__close">&nbsp;</div>

  </ul>
</template>

<script>
//左の青いメインメニューです。
//ページ遷移を行なっております。
export default {
  name: "main_menu",
  data: function () { return {
   
  }},
  computed: {

  },
  
  mounted: function () {
   $("." + this.page_data.slice(1)).addClass("isActive");
  },

  methods: {
    changePage: function (page) {

      $("li").each(function(){
        $(this).removeClass("isActive");
      });
      $("." + page).addClass("isActive");
      
      this.$store.dispatch('page/actPage', "/" +page);
      this.$router.push(page);

      setTimeout(function(){
        $('.c-Main-Menus').removeClass('is-Opened');
        $('.c-modal-Form').fadeOut();
        $(".js-Modal__cover").fadeOut();
      }, 700);
    

      // this.attachAnime();
    },

    showModal: function () {
      
      setTimeout(function(){
        $(".c-modal-Form.logout").fadeIn();
        $(".js-Modal__cover").fadeIn();
        $(".js-Modal__cover2").fadeIn();
      }, 500);
  
    },


    logout: function () {

      this.$http.post('/logout', {
        '_token' : window.csrfToken,
      }).then(res => {
        alert('ログアウトしました。');
        window.location.href = "/";
      })
      .catch(err => {
        alert("予期せぬエラー発生。");
        console.log(err);
        
      }).finally(() => {
        this.modal_close_btn();
        console.log('finally')
      });
    },


  },
}
</script>

<style lang="scss" scoped>
.isActive {
    background: #fff;

  p {
    color: #48A2F1;
    transition: color 0.5s;
    position: relative;

    &:before {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      height: 90%;
      width: 3px;
      background: #1E71BA;
       @media screen and (min-width: 576px){
        height: 120%;
       }
    }
  }

}
.c-modal-Form {
  left: 5%;
  right: 5%;
  width: 100vw;
}

.js-Modal__cover2 {
  background: rgba(0,0,0, 0.5);
  width: 100vw;
  height: 100vh;
  top: 0;
  left: 0;
  position: fixed;
  display: none;
  cursor: pointer;
  z-index: 10;
}
.top {
  @media screen and (min-width: 575px) {
    position: fixed;
    top: 0;
  }
  text-align: center;
  padding: 0!important;
  *{
    color: white;
    height: 100%;
    height: 64px;
    width: 220px;
    padding: 24px 16px 24px 40px;
    display: inline-block;
  }
  cursor: pointer;
  &:hover {
    * {
      color: #1E71BA;
    }
  }
}
</style>