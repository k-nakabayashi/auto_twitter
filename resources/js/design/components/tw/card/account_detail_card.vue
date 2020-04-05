<template>

  <div>
    <div class="c-Hmg icon-wrapper">
      <i class="a-I2 fas fa-cog animated flash infinite slower" v-on:click="showBoard()"></i>
    </div>
    <div class="c-Bar u-rel is-Opened">
      <div class="icon-wrapper">
        <i class="fas fa-undo" v-on:click="backHome()"></i>
      </div>
      <detailBtn
        v-on:emitBtn="setBtn" v-on:emitOpen_List="setOpen_List"
        v-on:emitTargets="setTargets" 
        v-on:emitSelected_Tweet="setSelected_Tweet"
      ></detailBtn>
    </div>
 


    <p class="action-title f-txt-2" v-show="open_list == 'tweet_list_all' || open_list == 'tweet_list_registerd' || open_list == 'tweet_list_posted'"
    >ツイートリスト<span v-show="open_list == 'tweet_list_registerd'">：予約一覧</span><span v-show="open_list == 'tweet_list_posted'">：投稿済み一覧</span></p>

    <tweetCards 
    :open_list="open_list"
    ></tweetCards>
    

    <p class="action-title f-txt-2" v-show="open_list == 'all_follow_list'"
    >ターゲットフォロワーリスト</p>

    <p class="action-title f-txt-2" v-show="open_list == 'unfollow_list'"
    >アンフォローリスト</p>

    <p class="action-title f-txt-2" v-show="open_list == 'follow_list'"
    >フォローリスト</p>
    <p class="action-title f-txt-2" v-show="open_list == 'follower_list'"
    >フォロワーリスト</p>
    
    <ul class="u-row follow_list all m-Cards-Wrapper u-scroll3"
    v-show="open_list == 'all_follow_list' || open_list == 'unfollow_list' || open_list == 'follow_list' || open_list == 'follower_list'"
    >
      <li v-for="(item) in targets" class="c-Target u-col-2-sm u-col-6-smx" v-bind:key="item.id">
        <div class="u-container u-card-back u-rel">
          <p class="a-Txt">{{item.name}}<br>{{item.screen_name}}</p>
        </div>
      </li>
    </ul>


    <div class="c-modal-Form favorite_auto js-Modal">
      <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
          <i class="cancel fas fa-times js-Modal__close"></i>
          <dt>オートついったー</dt>
          <dt v-if="btn == 'favorite_start'" class="a-Title f-txt-3">自動いいね<br>開始</dt>
          <dt v-if="btn == 'favorite_stop'" class="a-Title f-txt-3">自動いいね：中止・削除</dt>
          <dt v-if="btn == 'favorite_restart'" class="a-Title f-txt-3">自動いいね<br>再開</dt>
          <dt v-if="btn == 'favorite_release'" class="a-Title f-txt-3">自動いいね<br>凍結解除通知</dt>
          <dt class="u-my-8 u-txt-left" v-if="btn != 'favorite_release'">選択中のキーワード</dt>
          <dd class="search-keyword">
              <div class="u-scroll2 c-modal-Form-ch-block" v-if="btn != 'favorite_release'">

                <!-- 登録済みキーワード一覧表示 -->
                <ul class="c-Keywords">
                  <li v-for="item in favorite_keyword_data" class="c-Keywords__Inner" v-bind:key="item.id">
                    <label 
                      v-if="(tw_account_data.favorite_key_pattern_id==item.key_pattern_id)"
                    class="u-container u-card-back">
                      <div class="c-Keywords-Inner u-txt-l">
                        
                        <div v-for="(key_pare, index) in item.keyword" v-bind:key="key_pare.id">
                          
                          <p v-if="index != 0" class="btn a-Btn--pink">
                            <span>{{key_pare.opt}}</span>
                          </p>
                          <p class="btn a-Btn--grey">
                            <span>{{key_pare.txt}}</span>
                          </p>
                        </div>
                      </div>      
                      <!-- end c-Keywords-Inner  -->
                    </label>
                  </li> 
                </ul>
                <!-- end 登録済みキーワード一覧表示 -->

              </div>
              <div v-if="btn == 'favorite_start'">
                <button v-on:click="releaseFavoriteKeyword()" class="a-Btn--grey u-mt-32" type="submit"><p>選択解除</p></button>
                <button 
                  v-on:click="bootAutoFunction(false, 
                    tw_account_data.tw_account_id, 
                    'favorite', 'start'
                  )" 
                class="a-Btn--pink u-mt-32" type="submit"><p>開始する</p>
                </button>
              </div>
              
              <button 
                v-on:click="bootAutoFunction(false, 
                  tw_account_data.tw_account_id, 
                  'favorite', 'delete',
                  'search_tweets', 3,
                )" 
                v-if="btn == 'favorite_stop'"  class="a-Btn--grey u-mt-32" type="submit"><p>削除する</p>
              </button>

              <button 
                v-on:click="bootAutoFunction(false, 
                  tw_account_data.tw_account_id, 
                  'favorite', 'stop',
                  'search_tweets', 3,
                )" 
                v-if="btn == 'favorite_stop'"  class="a-Btn--pink u-mt-32" type="submit"><p>中止する</p>
              </button>

              <button 
                v-on:click="bootAutoFunction(false, 
                  tw_account_data.tw_account_id, 
                  'favorite', 'restart',
                  'search_tweets', 2,
                  'favorites_create', 2
                )" 
                v-if="btn == 'favorite_restart'"  class="a-Btn--pink u-mt-32" type="submit"
              ><p>再開</p>
              </button>

              <button 
                v-on:click="bootAutoFunction(true, 
                  tw_account_data.tw_account_id, 
                  'favorite', 'release',
                  'search_tweets', 4,
                  'favorites_create', 4,
                )" 
                v-if="btn == 'favorite_release'"  class="a-Btn--pink u-mt-32" type="submit"
              ><p>通知する</p>
              </button>
          </dd>
          <dd class="a-Policy">Term of use. Privacy policy</dd>
      </dl>
    </div>

    <div class="c-modal-Form favorite_key js-Modal">
      <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
          <i class="cancel fas fa-times js-Modal__close"></i>
          <dt>オートついったー</dt>

          <dt class="a-Title f-txt-3">自動いいね用<br>キーワード選択</dt>

            <div class="search-keyword">
                <div class="u-scroll2 c-modal-Form-ch-block">

                  <!-- 登録済みキーワード一覧表示 -->
                  <ul class="c-Keywords">
                    <li v-for="item in favorite_keyword_data" class="c-Keywords__Inner" v-bind:key="item.id">
                      <label v-bind:for="item.key_pattern_id" class="u-container u-card-back">
                        <input v-bind:id="item.key_pattern_id" type="radio" v-model="favorite_key_pattern_id" name="follow" v-bind:value="item.key_pattern_id">
                        <div class="c-Keywords-Inner u-txt-l">
                          
                          <div v-for="(key_pare, index) in item.keyword" v-bind:key="key_pare.id">
                            
                            <p v-if="index != 0" class="btn a-Btn--pink">
                              <span>{{key_pare.opt}}</span>
                            </p>
                            <p class="btn a-Btn--grey">
                              <span>{{key_pare.txt}}</span>
                            </p>
                          </div>
                        </div>      
                        <!-- end c-Keywords-Inner  -->
                      </label>
                    </li> 
                  </ul>
                  <!-- end 登録済みキーワード一覧表示 -->

                </div>
                <button v-on:click="setFavoriteKeyword()" class="a-Btn--pink u-mt-32" type="submit"><p>選択</p></button>
            </div>
          <p class="a-Policy">Term of use. Privacy policy</p>
      </dl>
    </div>
    
    <div class="c-modal-Form tweet_release js-Modal">
      <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
          <i class="cancel fas fa-times js-Modal__close"></i>
          <dt>オートついったー</dt>
          <dt class="a-Title f-txt-3">自動ツイート<br>凍結解除通知をしますか？</dt>
          <dd>
            <button 
              v-on:click="bootAutoFunction(true, 
                tw_account_data.tw_account_id, 
                'tweet', 'release',
                'statuses_update', 4,
              )" 
              class="a-Btn--pink u-mt-32" type="submit"><p>通知</p>
            </button>
          </dd>
          <dt class="a-Policy">Term of use. Privacy policy</dt>
      </dl>
    </div>
	</div>
    
</template>


<script>
//アカウント詳細ページです。ここでフォロー済みリスト表示と自動いいねを行います。

import detailBtn from '../btn/detail_btn.vue';
import tweetCards from './account_detail/tweet_cards.vue';
export default {
  name: "account_detail_cards",
  props: {
    action: "",
  },
  components: {
    detailBtn, tweetCards,
  },

  data: function () { return {

    selected_tweet: "",
    favorite_key_pattern_id: null,
    btn: "",
    open_list: "",
    targets: "",
 
  }},


  methods: {
    showBoard: function () {
      $('.c-Bar').toggleClass('is-Opened');
    },
    backHome: function () {
      this.$store.dispatch('page/actPage', '/home');
      this.$router.push('/home');
    },
    //下記、セットメソッドはボタンコンポーネントからのデータ受け取り
    setBtn: function (btn) {
      this.btn = btn;
    },
    setOpen_List: function (open_list) {
      this.open_list = open_list;
    },
    setTargets: function (targets) {
      this.targets = targets;
    },
    setSelected_Tweet: function (selected_tweet) {
      this.selected_tweet = selected_tweet;
    },

    //ここまでemitのセッター
    showFollowTargets: function (open, $pattern) {
      let params = {
          model_name: "Tw_Target_Friend",
          tw_account_id: this.tw_account_data.tw_account_id,
        };

      //フォロワーターゲットは現在選択中のキーワードで絞り込む
      if (open == "all_follow_list") {
        params.key_pattern_id = this.tw_account_data.key_pattern_id;
      }

      this.$http.get($pattern, {     
        params,
      })
      .then(res => {

        var data = res.data;
        let message = "";
        switch (open) {
          case "all_follow_list":
            message = "フォロワーターゲットリスト";
            break;
          case "unfollow_list":
            message = "アンフォローリスト";
            break;
          case "follow_list":
            message = "フォロー済みリスト";
            break;
          case "follower_list":
            message = "フォロワーリスト";
            break;
        }
        var result = this.alertErrors(message + "の取得に失敗しました。", data.errors);
        if (result == false) {
          return;
        }

        if (data.data.length == 0) {
          alert(message + "がまだありません。");
          return;
        }

        this.targets = data.data;
        this.open_list = open;
  
      })
      .catch(err => {
        alert("失敗");
        console.log(err);
      }).finally(() => {
        console.log('finally')
      });
    },



    //自動いいね
    setFavoriteKeyword: function () {
    
      if (this.favorite_key_pattern_id == null) {
        alert("「いいね」で絞り込むキーワードを選択してください");
        return;
      }

      //一つ目のキーワードはand
      var query = 
        "?tw_account_id=" + this.tw_account_data.tw_account_id + 
        "&favorite_key_pattern_id=" + this.favorite_key_pattern_id +
        "&model_name=Tw_Account";

      this.$http.get('/RestApi/edit/edit'+query)
      .then(res => {
      
        var data = res.data;
        var result = this.alertErrors("キーワードの選択に失敗しました。", data.errors);
        if (result == false) {
          return;
        } 

        alert("キーワードの選択に成功しました。");
        this.favorite_key_pattern_id == null;
        var tw_account = data.data;
        this.$store.dispatch('tw_account/actTw_Account', tw_account);
        $('.favorite-btn.api-stop1').addClass('isHidden');
        $('.favorite-btn.api-stop2').removeClass('isHidden');
      
        this.getTw_Accounts();
        this.modal_close_btn();
      })
      .catch(err => {
        alert("キーワードの選択に失敗しました。");
        console.log(err);
      }).finally(() => {
        console.log('finally')
      });

    },

    releaseFavoriteKeyword: function () {
    
      //一つ目のキーワードはand
      var query = 
        "?tw_account_id=" + this.tw_account_data.tw_account_id + 
        "&favorite_key_pattern_id=0" +
        "&model_name=Tw_Account";

      this.$http.get('/RestApi/edit/edit'+query)
      .then(res => {
      
        var data = res.data;
        var result = this.alertErrors("キーワードの選択解除に失敗しました。", data.errors);
        if (result == false) {
          return;
        } 

        alert("キーワードの選択解除に成功しました。");
        var tw_account = data.data;
        this.$store.dispatch('tw_account/actTw_Account', tw_account);
        $('.favorite-btn.api-stop2').addClass('isHidden');
        $('.favorite-btn.api-stop1').removeClass('isHidden');

        this.getTw_Accounts();
        this.modal_close_btn();
      })
      .catch(err => {
        alert("キーワードの選択に失敗しました。");
        console.log(err);
      }).finally(() => {
        console.log('finally')
      });

    },

    showFavoriteModal: function (btn) {
      this.btn = btn;
      $(".js-Modal__cover").fadeIn();
      $(".c-modal-Form." + btn).fadeIn();
    },

    showFavoriteAutoModal: function (btn) {
      this.btn = btn;
      $(".js-Modal__cover").fadeIn();
      $(".c-modal-Form.favorite_auto").fadeIn();
    },

  }
}
</script>
<style lang="scss" scoped>
.c-Bar {
  transition: transform .6s ease-in-out;
}
.action-title {
 
  margin-bottom: 10px;
  @media screen and (min-width: 576px) {
    margin-top: 40px;
  }
}
.c-Account {
  margin-top: 40px;
  > * {
    margin-left: 0;
    @media screen and (max-width: 991px){
      margin-right: 0;
    }
  }
}
.c-Account__img {
  min-width: 100px;
  img {
    object-fit: contain;
    margin-bottom: auto;
    border-radius: 10px;
    overflow: hidden;
  }
}
.c-Tweet {
  
  .a-Txt {
    word-break: break-word;
  }
}
.c-Target {
  display: inline-block;
  width: auto;
  height: 50px;
  > * {
    padding-top: 12px;
    padding-bottom: 12px;
  }
  .a-Txt {
    font-size: 12px;
    max-width: 100px;
    overflow: hidden;
    word-break: keep-all;
    height: 40px;
  }
}

.c-Bar {
  padding: 16px;
  background: #FFFFFF 0% 0% no-repeat padding-box;
  box-shadow: 5px 5px 10px 5px #00000045;

  @media screen and (max-width: 575px) {
    position: fixed;
    z-index: 10;
    top: 100px;
    max-width: 90vw;
  }
  @media screen and (min-width: 768px) {
    max-width: 760px;
  }
  @media screen and (min-width: 576px) {
    max-width: 760px;
  }
  @media screen and (max-width: 767px) {
    transform: translateX(-200%);
  }
}
.c-Hmg {
  top: 40px !important;
  right: 22px;
  i:before {
    color: #48A2F1;
  }
  @media screen and (max-width: 768px) and (min-width: 575px) {
    transform: translateX(200%);
  }
  // @media screen and (min-width: 768px) {
  //   top: 80px !important;
  // }

    
}
.m-Cards-Wrapper > * {
  margin-bottom: 40px;
}
</style>