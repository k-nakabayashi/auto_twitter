<template>

  <ul class="u-row m-Cards-Wrapper u-scroll">
    <li 
      v-bind:key="item.id"
      v-for="(item, index) in tw_account_list_data" 
      class="c-Account c-Animated-Item u-col-4-sm u-p-16">

      <div class="u-container u-card-back u-rel "
        :class="{[item.name] : true, is_Suspended: item.suspended == true}"
        >
        <div class="icon-wrapper">
          <i v-on:click="showDetail(item)" class="fas fa-cog"></i>
          <i v-on:click="showDeleteModal(item)" class="fas fa-times"></i>
        </div>
        <dl>
          
          <dt 
          :class="{[item.name] : true, is_Suspended: item.suspended == true}"
          class="a-Name f-txt-4">{{item.name}}@{{item.screen_name}}</dt>

          <dd class="a-Detail u-pl-16 u-mt-8">{{item.description}}</dd>
      
          <dt class="u-mt-16 f-txt-4">自動機能の状態</dt>
          <actionbtn 
          :account="item" :index="index"
          v-on:emitBtn="setBtn" v-on:emitSelected_Account="setSelected_Account"
          >
          </actionbtn>

        </dl>

        <p class="a-Btn--grey check_Suspention is_Suspended" 
        v-on:click="check_Suspention(item.tw_account_id)" 
        :class="{ isHidden: item.suspended == false}"
        ><span>凍結解除通知</span></p>
      </div>
    </li>
    
    
<li class="c-modal-Form key js-Modal">
  <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
      <dt>
        <i class="cancel fas fa-times js-Modal__close"></i>
      </dt>
      <dt>オートついったー</dt>

      <dt v-if="btn == 'follow_key'" class="a-Title f-txt-3">自動フォロー用<br>キーワード選択</dt>
        <div class="search-keyword">
            <div class="u-scroll2 c-modal-Form-ch-block">

              <!-- 登録済みキーワード一覧表示 -->
              <ul class="c-Keywords">
                <li v-for="item in keyword_data" class="c-Keywords__Inner" v-bind:key="item.id">
                  <label v-bind:for="item.key_pattern_id" class="u-container u-card-back">
                    <input v-bind:id="item.key_pattern_id" type="radio" v-model="key_pattern_id" name="follow" v-bind:value="item.key_pattern_id">
                    <div class="c-Keywords-Inner u-txt-l">
                      
                      <div class="c-Chip-Wrapper" v-for="(key_pare, index) in item.keyword" v-bind:key="key_pare.id">
                        
                        <p v-if="index != 0" class="a-Chip">
                          <span>{{key_pare.opt}}</span>
                        </p>
                        <p class="a-Chip is-pink f-txt-3">
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
            <button v-if="btn == 'follow_key'" v-on:click="setFollowingKeyword()" class="a-Btn--pink u-mt-32" type="submit"><p>選択</p></button>
            <button v-if="btn == 'favorite_key'"  class="a-Btn--pink u-mt-32" type="submit"><p>選択</p></button>
        </div>
      <dt class="a-Policy">Term of use. Privacy policy</dt>
  </dl>
</li>

<li class="c-modal-Form auto follow js-Modal">
  <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
      <i class="cancel fas fa-times js-Modal__close"></i>
      <dt>オートついったー</dt>
      <dt v-if="btn == 'fo-llow'" class="a-Title f-txt-3">自動フォロー<br>開始しますか？</dt>
      <dt v-if="btn == 'follow_stop'" class="a-Title f-txt-3">自動フォロー：中止・削除</dt>
      <dt v-if="btn == 'follow_restart'" class="a-Title f-txt-3">自動フォロー<br>再開しますか？</dt>
      <dt v-if="btn == 'follow_release'" class="a-Title f-txt-3">自動フォロー<br></dt>
      <dt class="u-my-8 u-txt-left"
      v-show="btn != 'follow_release'"
      >選択中のキーワード</dt>
      
      <dd class="search-keyword">
        <div class="u-scroll2 c-modal-Form-ch-block"
        v-show="btn=='follow' || btn=='follow_stop' || btn=='follow_restart'"
        >

          <!-- 登録済みキーワード一覧表示 -->
          <ul class="c-Keywords">
            <li v-for="item in keyword_data" class="c-Keywords__Inner" v-bind:key="item.id">
              <label 
                v-show="(tw_account_data.key_pattern_id==item.key_pattern_id)" 
                v-bind:for="item.key_pattern_id" 
                class="u-container u-card-back">
                <div class="c-Keywords-Inner u-txt-l">
                  
                  <div class="c-Chip-Wrapper" v-for="(key_pare, index) in item.keyword" v-bind:key="key_pare.id">
                    
                    <p v-if="index != 0" class="a-Chip">
                      <span>{{key_pare.opt}}</span>
                    </p>
                    <p class="a-Chip is-pink">
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
        


        <button v-if="btn == 'follow'" 
        v-on:click="releaseKeySetting()" 
        class="a-Btn--grey u-mt-32" type="submit">
        <p>キーワード選択を解除する</p></button>

        <button v-if="btn == 'follow'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 'follow', 'start'
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>開始する</p>
        </button>

        <button 
          v-if="btn == 'follow_stop'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 'follow', 'delete',
            'followers_list', 3,
            'friendships_create', 3
          )" 
          class="a-Btn--grey u-mt-32" type="submit"><p>削除する</p>
        </button>

        <button 
          v-if="btn == 'follow_stop'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 'follow', 'stop',
            'followers_list', 3,
            'friendships_create', 3
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>中止する</p>
        </button>

        <button 
          v-if="btn == 'follow_restart'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 'follow', 'restart',
            'followers_list', 2,
            'friendships_create', 2
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>再開する</p>
        </button>

        <button 
          v-if="btn == 'follow_release'" 
          v-on:click="bootAutoFunction(true, 
            tw_account_data.tw_account_id, 'follow', 'release',
            'followers_list', 4,
            'friendships_create', 4,
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>通知する</p>
        </button>

      </dd>
      <dt class="a-Policy">Term of use. Privacy policy</dt>
  </dl>
</li>

<li class="c-modal-Form auto unfollow js-Modal">
  <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
      <i class="cancel fas fa-times js-Modal__close"></i>
      <dt>オートついったー</dt>

      <dt v-if="btn == 'unfollow'" class="a-Title f-txt-3">自動アンフォロー<br>開始しますか？</dt>
      <dt v-if="btn == 'unfollow_stop'" class="a-Title f-txt-3">自動アンフォロー：中止・削除</dt>
      <dt v-if="btn == 'unfollow_restart'" class="a-Title f-txt-3">自動アンフォロー<br>再開しますか？</dt>
      <dt v-if="btn == 'unfollow_release'" class="a-Title f-txt-3">自動アンフォロー<br>凍結解除通知をしますか？</dt>
      
      <dd class="search-keyword">        
        
        <button v-if="btn == 'unfollow'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 'unfollow', 'start'
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>開始する</p>
        </button>

        <button 
          v-if="btn == 'unfollow_stop'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 
             'unfollow', 'delete',
            'user_show', 3,
            'friendships_lookup', 3,
            'friendships_destroy', 3,
          )" 
          class="a-Btn--grey u-mt-32" type="submit"><p>削除する</p>
        </button>

        <button 
          v-if="btn == 'unfollow_stop'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 
            'unfollow', 'stop',
            'user_show', 3,
            'friendships_lookup', 3,
            'friendships_destroy', 3,
 
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>中止する</p>
        </button>

        <button 
          v-if="btn == 'unfollow_restart'" 
          v-on:click="bootAutoFunction(false, 
            tw_account_data.tw_account_id, 
            'unfollow', 'restart',
            'user_show', 2,
            'friendships_lookup', 2,
            'friendships_destroy', 2
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>再開する</p>
        </button>

        <button 
          v-if="btn == 'unfollow_release'" 
          v-on:click="bootAutoFunction(true, 
            tw_account_data.tw_account_id, 
            'unfollow', 'release',
            'users_show', 4,
            'friendships_lookup', 4,
            'friendships_destroy', 4
          )" 
          class="a-Btn--pink u-mt-32" type="submit"><p>通知する</p>
        </button>

      </dd>
      <dt class="a-Policy">Term of use. Privacy policy</dt>
  </dl>
</li>

<li class="c-modal-Form delete js-Modal">
  <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
      <i class="cancel fas fa-times js-Modal__close"></i>
      <dt>オートついったー</dt>
      <dt class="f-txt-3 u-mt-16 u-mb-40">下記のアカウント<br>削除しますか？</dt>
        <p class="u-my-40 name">{{delete_account_name}}</p>
        <button v-on:click="deleteAccount(delete_account_id)" class="u-my-16 a-Btn--pink" type="submit"><p>はい</p></button>
      <p class="a-Policy">Term of use. Privacy policy</p>
  </dl>
</li>

	</ul>
</template>

<script>
//アカウントリストを表示し、フォロー・アンフォローを行います。

import actionbtn from '../btn/modal_openning_btn.vue';

export default {
  
  name: "account_cards",

  components: {
    actionbtn,
  },
  
  data: function () { return {
    selected_acccunt: null,
    key_pattern_id:  null,
    delete_account_name:  null,
    delete_account_id:  null,
    selected_keyword: {},
    btn: "",//モーダルを開くと、モーダルで表示するボタンがbtnによって変わる
    api_list: [],
    restriction_name: [
      'followers_list', 'users_show', 
      'friendships_create',
    ],
  }},

  methods: {

    //emitを受け取るセットメソッド
    setBtn:function (btn) {
      this.btn = btn;
    },
    setSelected_Account:function (index) {
      this.selected_acccunt = index;
    },
    
    getKeyPattern(tw_account_id) {

    },

    setFollowingKeyword: function () {
      if (this.key_pattern_id == null) {
     
        alert("フォローで絞り込むキーワードを選択してください");
        return;
      }

      //一つ目のキーワードはand
      var query = 
        "?tw_account_id=" + this.tw_account_data.tw_account_id + 
        "&key_pattern_id=" + this.key_pattern_id +
        "&model_name=Tw_Account";

      this.$http.get('/RestApi/edit/edit'+query)
      .then(res => {
      
        var data = res.data;
        var result = this.alertErrors("キーワードの選択に失敗しました。", data.errors);
        if (result == false) {
          return;
        } 

        alert("キーワードの選択に成功しました。");
        var tw_account = data.data;
        this.$store.dispatch('tw_account/actTw_Account', tw_account);
        this.getTw_Accounts();
     

        var class_name = this.tw_account_list_data[this.selected_acccunt].name;
        $('.' + class_name + ' .follow-btn.api-stop1').addClass('isHidden');
        $('.' + class_name + ' .follow-btn.api-stop2').removeClass('isHidden');
         this.modal_close_btn();
      })
      .catch(err => {
        alert("キーワードの選択に失敗しました。");
        console.log(err);
      }).finally(() => {
       
        console.log('finally')
      });

    },

    releaseKeySetting: function () {

      //一つ目のキーワードはand
      var query = 
        "?tw_account_id=" + this.tw_account_data.tw_account_id + 
        "&key_pattern_id=0" +
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
        //フロント側でも更新 vuex経由
        for (var i = 0; i < Object.keys(this.tw_account_list_data).length; i ++) {
          this.tw_account_list_data[i]['tw_account_id'];
          if (this.tw_account_list_data[i]['tw_account_id'] == tw_account['tw_account_id']) {
            this.tw_account_list_data[i] = tw_account;
            break;
          }
        }

        var class_name = this.tw_account_list_data[this.selected_acccunt].name;
        
        $('.' + class_name + ' .follow-btn.api-stop2').addClass('isHidden');
        $('.' + class_name + ' .follow-btn.api-stop1').removeClass('isHidden');

        this.modal_close_btn();
      })
      .catch(err => {
        alert("キーワードの選択解除に失敗しました。");
        console.log(err);
      }).finally(() => {
        
        console.log('finally')
      });

    },
    
    stopAutoFollow: function (id) {
      
      var query = 
        "?tw_account_id=" + this.tw_account_data.tw_account_id + 
        "&auto_followers_list=3&auto_friendships_create=3" + 
        "&model_name=Tw_Account";

      this.$http.get('/RestApi/edit/edit'+query)
      .then(res => {

        var data = res.data;
        var result = this.alertErrors("自動フォローの一時停止に失敗しました。", data.errors);
        if (result == false) {
          return;
        }
        alert("自動フォローを一時停止しました。"); 

        var class_name = this.tw_account_list_data[this.selected_acccunt].name;
        $('.' + class_name + ' .follow-btn.api-doing').addClass('isHidden');
        $('.' + class_name + ' .follow-btn.api-tmp_stop').removeClass('isHidden');
     this.modal_close_btn();
      })
      .catch(err => {
        alert("予期せぬエラー発生。");
        console.log(err);
        
      }).finally(() => {

        console.log('finally')
      });
    },
    
    showDetail: function (detail) {
      this.$store.dispatch('tw_account/actTw_Account', detail);
      this.$store.dispatch('page/actPage', "/my_tw_account");
      this.$router.push({ 
        path: '/my_tw_account', 
        query: {id: detail.tw_account_id}
      });
    },

    showDeleteModal: function ($account) {
      this.delete_account_name = $account.name + "@" + $account.screen_name;
      this.delete_account_id = $account.tw_account_id;
      $(".js-Modal__cover").fadeIn();
      $(".c-modal-Form.delete").fadeIn();
    },

    deleteAccount: function ($id) {

      var query = "?tw_account_id=" + $id + "&model_name=Tw_Account";

      this.$http.delete('/RestApi/delete'+query)
      .then(res => {

        var data = res.data;
        var result = this.alertErrors("アカウントの削除に失敗しました。", data.errors);
        if (result == false) {
          return;
        }

        alert("アカウントの削除に成功しました。\n ・アカウントの更新を行います");
        this.getTw_Accounts();
        this.modal_close_btn();
      })
      .catch(err => {
        alert("アカウントの削除に失敗しました。");
        console.log(err);
      }).finally(() => {

        console.log('finally')
      });
    },
    
    check_Suspention: function ($id) {

      this.$http.post('/checkSuspention', {
        tw_account_id: $id,
      })
      .then(res => {
      　var data = res.data;
        var result = this.alertErrors("凍結解除を確認できませんでした。", data.errors);
        if (result == false) {
          return;
        }
        $('.is_Suspended').each(function () {
          $(this).removeClass('is_Suspended');
        });
        
        $('.a-Btn--grey.check_Suspention').addClass('isHidden');

        alert("凍結解除を確認できました。");
        
        this.getTw_Accounts();
      })
      .catch(err => console.log(err))
      .finally(() => {
        console.log('finally')
      });
    },


  },

  mounted: function () {
    this.getTw_Accounts();
  }
  
}
</script>
<style lang="scss" scoped>
.c-Account {
  .a-Detail {
    font-size: .6rem;
    overflow: visible;
  }

}

.c-Keywords__Inner .u-card-back {
  cursor: pointer;
}
.c-modal-Form {
  @media screen and (max-width: 575px){
    top: 64px;
  }
}
.c-modal-Form.delete {
  .name {
    font-weight: bold;
  }
}

.c-Account > .is_Suspended {
  position: relative;

  &::before {
    content: "";
    position: absolute;
    background: #F78D8D;
    display: block;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 10;
    opacity: 0.5;
    border-radius: 10px;
    overflow-wrap: break-word;
    overflow: hidden;
  }
  // &::after {
  //   content: "凍結中";
  //   position: absolute;
  //   background: #fff;
  //   display: inline-block;
  //   bottom: 35%;
  //   left: 0;
  //   right: 0;
  //   margin: auto;
  //   width: 80%;
  //   z-index: 10;
  //   padding: 16px;
  //   border-radius: 10px;
  // }
}

.check_Suspention {
    z-index: 20;
    position: absolute;
    bottom: 10%;
    margin: auto;
    width: 140px;
    left: 0;
    right: 0;

}

.a-Name.is_Suspended {
  position: absolute;
  top: 10%;
  z-index: 20;
  margin: auto;
  display: inline-block;
  background: #fff;
  padding: 16px;
  border-radius: 10px;
  max-width: 90%;
  overflow: hidden;
  word-break: break-word;
}
</style>