<template>
<div>
  <div v-if="page_data === '/home'" class="l-main-app__btn">

      <p class="a-Btn--pink">
        <span v-if="req_token == undefined && req_token_secret == undefined" v-on:click="getRequestToken()">まずトークンを取得</span>
        <a v-if="req_token !== undefined && req_token_secret !== undefined" v-bind:href="url_auth_api+req_token">アカウント登録</a>
      </p>
      <p class="u-mt-16 f-txt-4"
      v-bind:class="{ isHidden: (Object.keys(target_account_data).length != 0)}"
      >ターゲットアカウントを登録してください。</p>
	</div>

  <div v-if="page_data === '/target_list'" class="l-main-app__btn">
    <p v-if="Object.keys(tw_account_list_data).length > 0" class="a-Btn--pink" v-on:click="openModal()">
      <span>ターゲット登録</span>
    </p>
  </div>

  <div v-if="page_data === '/keyword'" class="l-main-app__btn">
      <p class="a-Btn--pink" v-on:click="openModal()">
        <span>登録</span>
      </p>   
	</div>

  <div v-if="page_data === '/favorite_keyword'" class="l-main-app__btn">
      <p class="a-Btn--pink" v-on:click="openModal()">
        <span>登録</span>
      </p>  
	</div>


  <div v-if="page_data === '/tweet'" class="l-main-app__btn">
      <p class="a-Btn--pink" v-on:click="openModal()">
        <span>登録</span>
      </p>
	</div>

  
  <div class="c-modal-Form target_list_title js-Modal">
    <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
        <i class="cancel fas fa-times js-Modal__close"></i>
        <dt>オートついったー</dt>
        <dt class="a-Title f-txt-3">ターゲットアカウント登録</dt>
          <div>
              <div class="c-modal-Form-ch-block">
                <input v-model="screen_name" placeholder="スクリーンネームを記入してください。" type="text" name="key" required="required" autofocus="autofocus" class="form-control">
              </div>
              <button v-on:click="getTargetAccount()" class="a-Btn--pink u-mt-32" type="submit"><p>登録</p></button>
          </div>
        <p class="a-Policy">Term of use. Privacy policy</p>
    </dl>
  </div>


  <div class="c-modal-Form keyword_title js-Modal">
    <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
        <i class="cancel fas fa-times js-Modal__close"></i>
        <dt>オートついったー</dt>
        <dt class="a-Title f-txt-3">キーワード登録</dt>
          <div class="search-keyword">
              <div class="c-modal-Form-ch-block">
                <input v-model="keyword" placeholder="キーワード" type="text" name="key0" required="required" autofocus="autofocus" class="form-control u-mb-16">
                <p class="a-Exmple">and検索： キー1 キー2</p>
                <p class="a-Exmple">or検索： キー1  <span>or</span>  キー2</p>
                <p class="a-Exmple">not検索： キー1  <span>not</span>  キー2</p>
              </div>

              <button v-on:click="registerKeyword('Key_Pattern')" class="a-Btn--pink u-mt-32" type="submit"><p>登録</p></button>
          </div>
        <p class="a-Policy">Term of use. Privacy policy</p>
    </dl>
  </div>

  <div class="c-modal-Form favorite_keyword_title js-Modal">
    <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
        <i class="cancel fas fa-times js-Modal__close"></i>
        <dt>オートついったー</dt>
        <dt class="a-Title f-txt-3">キーワード登録</dt>
          <div class="search-keyword">
              <div class="c-modal-Form-ch-block">
                <input v-model="keyword" placeholder="キーワード" type="text" name="key0" required="required" autofocus="autofocus" class="form-control u-mb-16">
                <p class="a-Exmple">and検索： キー1 キー2</p>
                <p class="a-Exmple">or検索： キー1  <span>or</span>  キー2</p>
                <p class="a-Exmple">not検索： キー1  <span>not</span>  キー2</p>
              </div>

              <button v-on:click="registerKeyword('Favorite_Key_Pattern')" class="a-Btn--pink u-mt-32" type="submit"><p>登録</p></button>
          </div>
        <p class="a-Policy">Term of use. Privacy policy</p>
    </dl>
  </div>



  <div class="js-Modal__cover js-Modal__close">&nbsp;</div>

</div>
</template>

<script>
// アカウント詳細ページ以外の各種ページで使用するボタンを表示しております。
export default {
  name: "title_btn",

  data: function () { return{
      req_token: undefined,
      req_token_secret: undefined,
      token_pare: {},
      screen_name: "",
       

      keyword : null,
      keypare: {},
      //keypare はリクエストする際、キーワードは下記のような構造になります
      // [
      //   {'opt':'and', 'txt': 'aa'},
      //   {'opt':'and', 'txt': 'bb'},
      //   {'opt':'or', 'txt': 'cc'},
      //   {'opt':'not', 'txt': 'dd'},
      // ]

  }},
 
  methods: {

    openModal: function () {
      let page_data = this.page_data;
      setTimeout(function(){

        $(".js-Modal__cover").fadeIn();

        switch (page_data) {
          case "/target_list":
            $(".c-modal-Form.target_list_title").fadeIn();
            break;
          case "/keyword":
            $(".c-modal-Form.keyword_title").fadeIn();
            break;
          case "/favorite_keyword":
            $(".c-modal-Form.favorite_keyword_title").fadeIn();
            break;
          case "/tweet":
            $(".c-modal-Form.tweet_title").fadeIn();
            break;
        }
      }, 500);


    },

    getRequestToken: function () {
      this.$http.post('/GetRequestToken', {
      })
      .then(res => {
      　var data = res.data;
        var result = this.alertErrors("リクエストトークン取得に失敗しました。", data.errors);

        if (result == false) {
          return;
        }

        var token_pare = data.data;
 
        if (token_pare.oauth_token != undefined && token_pare.oauth_token_secret != undefined) {
          this.req_token = token_pare.oauth_token;
          this.req_token_secret = token_pare.oauth_token_secret;
          alert("リクエストトークンを取得しました");

        } else {
          alert("エラー発生：リクエストトークン取得に失敗しました。")
        }
      })
      .catch(err => console.log(err))
      .finally(() => {
        console.log('finally')
      });
    },

    getTargetAccount: function () {

      this.$http.post('/createTarget', {
        screen_name: this.screen_name,
      })
      .then(res => {
        var data = res.data;
        
        var result = this.alertErrors("ターゲットアカウントの追加に失敗しました", data.errors);
        if (result == false) {
          return;
        }

        alert("ターゲットアカウントを追加しました。");
        this.target_account_data.push(data.data);

        $(".js-Modal__cover").fadeOut();
        $(".c-modal-Form").fadeOut();
      })
      .catch(err =>　{
        alert("ターゲットアカウントの追加に失敗しました。");
        console.log(err)
      })
      .finally(() => {

        console.log('finally')
      });
    },


    registerKeyword: function ($model) {

      //リクエスト前の準備

      this.prepareKeyPare();
      var data = {
          app_id: this.auth_data.app_id,
          keyword: this.keypare,
          model_name: $model,
        };

      //一つ目のキーワードはand
      this.$http.get('/RestApi/create', {
        params: data,

      }).then(res => {
        var data = res.data;

        var result = this.alertErrors("キーワード追加に失敗しました。", data.errors);
        if (result == false) {
          return;
        }
        
        alert("キーワード追加に成功しました。");
        if ($model == 'Key_Pattern') {
          this.getKewords('Key_Pattern');
  
        } else if ($model == 'Favorite_Key_Pattern') {
          this.getKewords('Favorite_Key_Pattern');
        }

        $(".js-Modal__cover").fadeOut();
        $(".c-modal-Form").fadeOut();
      })
      .catch(err => {
        alert("エラー発生：キーワード追加に失敗しました。");
        console.log(err)
      }).finally(() => {

        console.log('finally')
      });

    },
  
    prepareKeyPare: function () {
      //配列化する
      var result = this.keyword.split(/ |\　/);

      //格納配列準備
      var keyword_pare = [];

      //二つ目以降のキーワードを作る
      var pare = {'opt': 'and', 'txt': null};
      for (var i = 0; i < Object.keys(result).length; i ++) {

        if (!result[i]) {
          continue;
        }

        if (i == 0) {
          pare['txt'] = result[i];
           keyword_pare.push(pare);
           pare = {'opt': 'and', 'txt': null};
          continue;
        }

        if (['and', 'or', 'not'].indexOf(result[i]) == -1) {
          pare['txt'] = result[i];
          keyword_pare.push(pare);
          pare = {'opt': 'and', 'txt': null};
          continue;
        }
        
        //optionありの場合
        pare['opt'] = result[i];
      }
      this.keypare = keyword_pare;
    },

  },
}
</script>

<style lang="scss" scoped>
.a-Label {
  > * {
    text-align: left;
  }
}

</style>
