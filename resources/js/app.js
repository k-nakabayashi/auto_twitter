//下準備
window.Vue = require('vue');
import store from "./store";
import router from './router';

//起動
require('./bootstrap');

//Vueオブジェクト設定
// import ResizeObserver from 'resize-observer-polyfill'
import { mapGetters } from 'vuex';

import title_btn from './design/components/tw/btn/title_btn.vue';
import main_menu from './design/components/common/main_menu.vue';
import page_title from './design/components/common/page_title.vue';
Vue.mixin({

    data: function(){ return {
      page: "",
      url_auth_api: "https://api.twitter.com/oauth/authorize?oauth_token=",

     }},
    
    methods: {

      modal_close_btn: function () {
        $(".c-modal-Form ").hide();
        $(".js-Modal__cover").hide();
      },
    
      //auto : follow, unfollow,
      //pattern : start, stop, restart
      setAutoFunctionStarter: function (auto, pattern) {

        var obj = {
          pattern: pattern,
          url: '/' + pattern + 'AutoFunction',
          message: "",
          fadein: '.' + auto + '-btn',
          fadeout: '.' + auto + '-btn',
        };

        switch (auto){
          case ("follow"):
            obj.message = "自動フォロー"
            this.setAutoFunctionStarter__inner(obj);
            break;
          case ("unfollow"):
            obj.message = "自動アンフォロー"
            this.setAutoFunctionStarter__inner(obj);
            break;
          case ('favorite'):
            obj.message = "自動いいね"
            this.setAutoFunctionStarter__inner(obj);
            break;          
          case ('tweet'):
            obj.message = "自動ツイート"
            this.setAutoFunctionStarter__inner(obj);
            break;
        }
        return obj;
      },

      setAutoFunctionStarter__inner: function (obj) {
        switch (obj.pattern){
          case ("start"):
            obj.fadeout += '.api-stop2';
            obj.fadein += '.api-doing';
            obj.message += "の開始";
            break;
          case ("restart"):
            obj.fadeout += '.api-tmp_stop';
            obj.fadein += '.api-restart';
            obj.message += "の再開";
            break;
          case ("stop"):
            obj.fadeout += '.api-doing';
            obj.fadein += '.api-tmp_stop';
            obj.message += "の停止";
            break;
          case ("release"):
            // obj.fadein += '';　api-restartかapi-stop1
            obj.fadeout += '.api-release';
            obj.message += "の凍結確認";
            break;
          case ("delete"):
            obj.fadeout += '.api-doing';
            obj.fadein += '.api-delete';
            obj.message += "の削除";
            break;
        }
        return obj;
      },

      //各種：自動機能のstart,stop,restart, deleteを受け持つメソッド
      bootAutoFunction: function (
        release_flag=false, //凍結解除通知で使います。
        id, auto, pattern, 
        api1=null, apivalue1=null, 
        api2=null, apivalue2=null,
        api3=null, apivalue3=null,
        ) 
      {
      
        var setting = this.setAutoFunctionStarter(auto, pattern);
        var data = {
          // tw_account_id: id,
          pattern: pattern,
        }

        switch (auto) {
          case 'follow':
            data.domain = auto;
            break;
          case 'favorite':
            data.domain2 = auto;
            break;
          case 'unfollow':
            data.domain3 = auto;
            break;
          case 'tweet':
            data.domain4 = auto;
            break;
        }
        

        //あとでバックエンド側のinputからの取得を変える
        //tw_account_id→idに統一する
        if (auto != 'tweet') {
          data['tw_account_id'] = id;
        } else if (auto == 'tweet')  {
          data['id'] = id;
        }
        console.log(data);

        if (api1) {
          data['queue_name1'] = api1;
        }
        if(api2) {
          data['queue_name2'] = api2;
        }
        if(api3) {
          data['queue_name3'] = api3;
        }
        if (apivalue1) {
          data["auto_"+api1] = apivalue1;
        }
        if (apivalue2) {
          data["auto_"+api2] = apivalue2;
        }
        if (apivalue3) {
          data["auto_"+api3] = apivalue3;
        }

        
        this.$http.post(setting.url, data
        ).then(res => {

          var data = res.data;
          var result = this.alertErrors(setting.message + "に失敗しました。", data.errors);
          if (result == false) {
            return;
          }

          //凍結からの再開をする場合
          if (release_flag == true) {
            let release_target = data.next_btn;

            if (release_target == 0) {
              //初期状態になる。
             
              setting.fadein += '.api-stop1';
            } else if (release_target == 2) {
              //再開状態になる。
              
              setting.fadein += '.api-restart';

            } else {
              return;
            }
          }

          if (auto == 'follow' || auto == 'unfollow') {

            var class_name = this.tw_account_list_data[this.selected_acccunt].name;
            $('.' + class_name + ' ' + setting.fadeout).addClass('isHidden');
            $('.' + class_name + ' ' + setting.fadein).removeClass('isHidden');
          }
          
          if (auto == 'favorite') {
            this.$store.dispatch('tw_account/actTw_Account', data.data);
            $(setting.fadeout).addClass('isHidden');
            $(setting.fadein).removeClass('isHidden');
          }

          this.getTw_Accounts();

          if (auto == 'tweet') {
            this.$store.dispatch('tw_account/actTw_Account', data.data);         
            this.getTweet_List(data.data.tw_account_id);
          }
          
          
          alert(setting.message + "に成功しました。"); 
          this.modal_close_btn();
        })
        .catch(err => {
          alert("予期せぬエラー発生。");
          console.log(err);
          
        }).finally(() => {
    
          console.log('finally')
        });
      },

      getTw_Accounts: function () {

        this.$http.get('/RestApi', {
          params: {
            id: this.auth_data.app_id,
            model_name: "Tw_Account",
          },
        }).then(res => {

          var data = res.data;

          if (Object.keys(data.errors).length > 0) {
            var error_messaage = "";
            for (var i = 0; i < Object.keys(data.errors).length; i ++) {
              error_messaage += data.errors[i];
            }
            alert(error_messaage);
            alert("アカウント取得に失敗しました。下記が原因です。\n" + error_messaage);
            return;
            
          } else {
            this.$store.dispatch('tw_account_list/actTw_Account_List', data['data']);
          }

  
        })
        .catch(err => console.log(err))
        .finally(() => {
          console.log('finally')
        });
      
      },

      getTw_Account_data: function (id) {
  
        this.$http.get('/RestApi/show', {
          params: {
            column: 'tw_account_id',
            value: id,
            model_name: "Tw_Account",
          },
        }).then(res => {

          var data = res.data;

          if (Object.keys(data.errors).length > 0) {
            var error_messaage = "";
            for (var i = 0; i < Object.keys(data.errors).length; i ++) {
              error_messaage += data.errors[i];
            }
            alert(error_messaage);
            alert("アカウント取得に失敗しました。下記が原因です。\n" + error_messaage);
            return;
            
          } 
         
          this.$store.dispatch('tw_account/actTw_Account', data['data'][0]);
  
        })
        .catch(err => console.log(err))
        .finally(() => {
          console.log('finally')
        });
      
      },

      getTargetAccounts: function () {

        this.$http.get('/RestApi', {
          params: {
            id: this.auth_data.app_id,
            model_name: "Tw_Target_Account",
          },
        }).then(res => {
  
          var data = res.data;
          var result = this.alertErrors("アカウントの取得に失敗しました。", data.errors);
          if (result == false) {
            return;
          } 
          this.$store.dispatch('target_account/actTarget_Account', data.data);
   
        })
        .catch(err => console.log(err))
        .finally(() => {
          console.log('finally')
        });
      
      },

      getKewords: function ($model) {

        this.$http.get('/RestApi', {
          params: {
            id: this.auth_data.app_id,
            model_name: $model,
          },
        }).then(res => {

          var data = res.data;
          var key_obj = data.data;

          for (var i = 0; i < Object.keys(key_obj).length; i ++) {
      
            key_obj[i].keyword = JSON.parse(key_obj[i].keyword);

            for (var t = 0; t < Object.keys(key_obj[i].keyword).length; t ++) {
              key_obj[i].keyword;
              key_obj[i].keyword[t] = JSON.parse(key_obj[i].keyword[t]);  
            }
          }

          if ($model == 'Key_Pattern') {
            this.$store.dispatch('keyword/actKeyword', key_obj);
          } else if ($model == 'Favorite_Key_Pattern') {
            this.$store.dispatch('favorite_keyword/actFavorite_Keyword', key_obj);
          }
  
        })
        .catch(err => console.log(err))
        .finally(() => {
          console.log('finally')
        });
      
      },

      //アカウントごとにツイートを取れるようにする
      getTweet_List: function ($id) {
        this.$http.get('/RestApi', {
          params: {
            column: 'tw_account_id',
            value: $id,
            model_name: "Tw_Auto_Tweet",
          },
        }).then(res => {
  
          var data = res.data;
     
          var result = this.alertErrors("登録ツイートの取得に失敗しました。", data.errors);
          if (result == false) {
            return;
          }
          
          var tweet_obj = data.data;
          for (var i = 0; i < Object.keys(tweet_obj).length; i ++) {
           
            tweet_obj[i].tags = JSON.parse(tweet_obj[i].tags);
            
          }
          
          this.$store.dispatch('tweet_list/actTweet_List', tweet_obj);
   
        })
        .catch(err => console.log(err))
        .finally(() => {
          console.log('finally')
        });
      
      },


      alertErrors: function (message, errors) {
        if (errors.length > 0) {
          var error_messaage = message + "下記が原因です。\n";
          for (var i = 0; i < errors.length; i ++) {
            error_messaage += errors[i] + "\n";
          }
          
          alert(error_messaage);
          return false;
        } 
        return true;
      },

      //下記、Animation.cssを利用しております
      //  https://daneden.github.io/animate.css/

      //TargetClass；デフォルトだと、ここにイベントリスナーとアニメが発生。
      //InnerClass：これを指定すると、TargetClassにイベントリスナーを設定しながら、アニメはInnerClassで発生
      //AnimeClass：イベント時に起動するアニメ
      //AnimeClass2：AnimeClass完了後にクリックすると起動するアニメ
      //sequential_anime：これがfalseだと、イベント発生すると、AnimeClass,AnimeClass2が一連の動きになる。trueだとイベントで管理できる
      attachAnimation: function (
        event_name,TargetClass, 
        AnimeClass, AnimeClass2 = null,
        InnerClass = null, 
        sequential_anime = true
        ) 
      {

        var $target_dom = $(TargetClass);
        var $animating_dom = "";

        // $target_dom = $(InnerClass);
        return inner();

        function inner () {

          $target_dom.each(function(){
            
          
            $(this).on(event_name, function () {

              if (InnerClass == null) {
            
                $animating_dom = $(this);
              
              } else {
                
                $animating_dom = $(this).find(InnerClass);
              }
        


              if (sequential_anime == true) {

                $animating_dom.removeClass(AnimeClass);
                $animating_dom.removeClass(AnimeClass2);
                
                //アニメーションが完了すると、クラスが外れる
                $animating_dom.addClass(AnimeClass)
                .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                  $animating_dom.removeClass(AnimeClass);

                  $animating_dom.addClass(AnimeClass2)
                  .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                    $animating_dom.removeClass(AnimeClass2);
                  });
                });
                
                //クリック有無で起動をコントロール
              } else {
        
                if ($animating_dom.hasClass(AnimeClass) == true) {

                  $animating_dom.removeClass(AnimeClass);
                  $animating_dom.addClass(AnimeClass2)
                  .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                    $animating_dom.removeClass(AnimeClass2);

                  });
                } else {

                  $animating_dom.addClass(AnimeClass);
                }
              }
              
            });
          
          });

        }
      },

      //スクロールイベント
      // ajust : スクロールイベント起動位置を指定。デフォルトでは、dom.offset()になる
      //スクロールイベントを早めに起動する時は、ajusに負の値にしてください。
      //remove : これをtrueにすると、再度スクロールした時、アニメーションが再度起動可能に
      //ただし、アニメーション起動タイミングが早すぎて、調整が完成していないです。
      addScrollAnime:function (TargetClass, AnimeClass, ajust = 0, remove = false) {
        var $target;
        var init = 'u-init-opa';
        return inner();

        function inner () {

          $(TargetClass).each(function(){
            $target = $(this)
            $target.addClass(init);
            attchScrollEvent($target, AnimeClass, ajust, remove, init);

          });

        }

      },
      addScrollAnime_with_slide:function  (TargetClass, AnimeClass, ajust, remove = false) {
        var $target;
        var init = 'u-inint-transform';
        return inner();

        function inner () {

          $(TargetClass).each(function(){
            $target = $(this)
            $target.addClass(init);

            attchScrollEvent($target, AnimeClass, ajust, remove, init);

          });

        }

      },
      attchScrollEvent: function ($target, AnimeClass, ajust, remove, init) {
        $target.attr('animated', 'false');

        if (remove == true) {

          //調整中
          $(window).scroll(function() {
            target_height = $target.height();
            //アニメーション起動
            if ($(window).scrollTop() > ($target.offset().top + ajust) && $(window).scrollTop() < ($target.offset().top + target_height + ajust)){
              $target.addClass(AnimeClass);
              // .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
              //   $target.css(init.style, init.value + '!important');
              //   $target.removeClass(AnimeClass);
                
              // });

            } else {
              $target.removeClass(AnimeClass);
            };

          });

        } else {

          $(window).scroll(function() {

            //アニメーション起動
            if ($(window).scrollTop() > ($target.offset().top + ajust)){

              if ($target.attr('animated') == 'false') {
                $target.addClass(AnimeClass)
                .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                  $target.removeClass(init);
                  $target.removeClass(AnimeClass);
                  $target.attr('animated', 'true');
                });
              };
              }

          });
        }
      },



    },
    
    computed: {


      ...mapGetters([
        'auth/getAuth_Vuex',
        'page/getPage_Vuex',
        'tw_account/getTw_Account',
        'tw_account_list/getTw_Account_List',
        'target_account/getTarget_Account',
        'keyword/getKeyword',
        'favorite_keyword/getFavorite_Keyword',
        'tweet_list/getTweet_list',
        
      ]),


      auth_data: function () {
         //直レンダリングではセッションから取ってこれるか？
        return store.state.auth.auth_data;
      },

      page_data: function () {
        if (store.state.page.data == "") {
          this.$store.dispatch('page/actPage', location.pathname);
        }

        return store.state.page.data;
      },

      tw_account_data: function () {
        return store.state.tw_account.data;
      },

      tw_account_list_data: function () {
        return store.state.tw_account_list.data;
      },
      
      target_account_data: function () {

        return store.state.target_account.data;
      },
      
      keyword_data: function () {
        return store.state.keyword.data;
      },

      favorite_keyword_data: function () {
        return store.state.favorite_keyword.data;
      },
      
      tweet_list_data: function () {
        return store.state.tweet_list.data;
      },

      api_list_data: function () {
        return store.state.api_list.data;
      },

      
    },
    components: {
      page_title,main_menu,
      title_btn,
    },

    created: function () {

      switch (location.pathname) {
        case "/home":
          this.page = "Twittterアカウントリスト";
          break;
        case "/my_tw_account":
          this.page = "Twittterアカウント";
          break;
          
        case "/target_list":
          this.page = "ターゲットアカウントリスト";
          break;
        case "/keyword":
          this.page = "キーワード登録";
          break;
        case "/favorite_keyword":
          this.page = "「いいね」キーワード登録";
          break;
        case "/account":
            this.page = "AutoTwitterアカウント";
            break;
          case "/how_to_use":
            this.page = "「オートついったー」の使い方";
            break;
        default:
          this.$router.push("/");
          break;
      }
    },
    
    mounted: function () {

      this.$store.dispatch('page/actPage', location.pathname);

      this.attachAnimation('click', '.a-Btn--large','bounceIn');
      this.attachAnimation('click', '.a-Btn--pink:not(.api-restart)','bounceIn');
      this.attachAnimation('click', '.a-Btn--grey:not(.api-restart)','bounceIn');
      this.attachAnimation('click', '.a-Btn--blue:not(.api-restart)','bounceIn');

      this.$nextTick(function () {
        $(".js-Modal__close").on("click", function () {
          $(".c-modal-Form").fadeOut();
          $(".js-Modal__cover").fadeOut();
          $(".js-Modal__cover2").fadeOut();
          $('.c-Main-Menus').removeClass('is-Opened');
        });
      })
     
    },

    updated: function () {
      $("body").attr("id", this.page_data.slice(1));

      
      this.attachAnimation('click', '.a-Btn--large','bounceIn');
      this.attachAnimation('click', '.a-Btn--pink:not(.api-restart)','bounceIn');
      this.attachAnimation('click', '.a-Btn--grey:not(.api-restart)','bounceIn');
      this.attachAnimation('click', '.a-Btn--blue:not(.api-restart)','bounceIn');
      
      this.$nextTick(function () {
        $(".js-Modal__close").on("click", function () {
          $(".c-modal-Form").fadeOut();
          $(".js-Modal__cover").fadeOut();
          $(".js-Modal__cover2").fadeOut();
          $('.c-Main-Menus').removeClass('is-Opened');
        });
      })
    },
  });
const app = new Vue({
    el: '#app',
    store,
    router,
    data: {

    },
    beforeCreate: function () {
      
    },
    created: function()  {
      this.$store.dispatch('auth/actAuth', window.Laravel.auth_data);

      this.getTw_Accounts();
      this.getKewords('Key_Pattern');
      this.getTargetAccounts();

    },

    mounted: function () {



      if(location.pathname === "/my_tw_account"){
        this.$router.push({ path: '/my_tw_account'});
      }

      this.getKewords('Favorite_Key_Pattern');

      this.$nextTick(function () {
        if (window.initial_data != null) {
          if (window.initial_data.tw_duplication === true) {
            alert("登録済みのアカウントです。");
          }
          if (window.initial_data.restricted_creating) {
            alert("登録できるアカウント数１０を超えたので登録できません。");
          }
        }
      });
 
    },
});

const common = new Vue({
  el: '#common',
  store,
  router,
});
const app_page_title = new Vue({
  el: '#page_title',
  store,
  router,
});

//jquery
document.addEventListener('DOMContentLoaded', function(){
  require("./jquery_anime/modal");

  // let $load =  $('.loading');
  // $load.removeClass('loaded');
  // $load.addClass('spinner');
  
  // $('.c-Animated-Item').each(function () {
  //   //コンテンツのfadeIn開始
  //   $(this).addClass('fadeIn')
  //   .on('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', 
  //     function(){
  //       $(this).removeClass('fadeIn');
  //       $load.addClass('loaded');
  //     });
  // });

},false);