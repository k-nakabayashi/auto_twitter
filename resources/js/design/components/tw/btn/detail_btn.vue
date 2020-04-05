<template>
  <div class="c-DetailBtns">
    <div class="c-Btn-Wrapper u-mb-16">
      <p class="a-Btn--blue showbtn" id="follow_btns" v-on:click="showBtns('follow_btns')"><span>リスト</span></p>
      <p class="a-Btn--blue showbtn" id="favorite_btns" v-on:click="showBtns('favorite_btns')"><span>自動いいね</span></p>
      <p class="a-Btn--blue showbtn" id="tweet_btns" v-on:click="showBtns('tweet_btns')"><span>予約ツイート</span></p>
    </div>

    <div class="c-Btn-Wrapper" v-show="show_btns=='follow_btns'">
      <p v-on:click="showFollowTargets('all_follow_list', '/getFollowerTarget')" class="a-Btn--grey"><span>フォロワーターゲット</span></p>
      <p v-on:click="showFollowTargets('unfollow_list', '/getUnFollow')" class="a-Btn--grey"><span>アンフォロー</span></p>
      <p v-on:click="showFollowTargets('follow_list', '/getFollow')" class="a-Btn--grey"><span>フォロー済み</span></p>
      <p v-on:click="showFollowTargets('follower_list', '/getFollower')" class="a-Btn--grey"><span>フォロワー</span></p>
    </div>

    <!-- 自動いいね -->

    <div class="c-Btn-Wrapper" v-show="show_btns=='favorite_btns'">
      <p class="a-Btn--grey favorite-btn api-stop1" 
        v-on:click="showFavoriteModal('favorite_key')"
        v-bind:class="{ isHidden: !(tw_account_data.favorite_key_pattern_id == null || tw_account_data.favorite_key_pattern_id == '')}"
      ><span>キーワード登録</span></p>
      
      <p class="a-Btn--pink favorite-btn api-stop2" 
        v-on:click="showFavoriteAutoModal('favorite_start')"
        v-bind:class="{ isHidden: !(tw_account_data.favorite == 0) || (tw_account_data.favorite_key_pattern_id == null || tw_account_data.favorite_key_pattern_id == '')}"
      ><span>開始</span></p>

      <p class="a-Btn--pink favorite-btn api-doing" 
        v-on:click="showFavoriteAutoModal('favorite_stop')"
        v-bind:class="{ isHidden: !(tw_account_data.favorite == 1)}"
      ><span>起動中</span></p>
      
      <p class="a-Btn--grey favorite-btn api-restart"
      v-bind:class="{ isHidden: !(tw_account_data.favorite == 2)}"
      ><span>再開中</span></p>

      <p class="a-Chip favorite-btn api-delete" 
        v-bind:class="{ isHidden: !(tw_account_data.favorite == 5)}"
      ><span>削除中</span>
      </p>

      <p class="a-Btn--pink favorite-btn api-tmp_stop" 
        v-on:click="showFavoriteAutoModal('favorite_restart')"
        v-bind:class="{ isHidden: !(tw_account_data.favorite == 3)}"
      ><span>一時停止中</span></p>

      <p class="a-Btn--pink favorite-btn api-release" 
        v-on:click="showFavoriteAutoModal('favorite_release')"
        v-bind:class="{ isHidden: !(tw_account_data.favorite == 4) }"
      ><span>凍結中</span></p>
    </div>

    <!-- 自動ツイート -->

    <div class="c-Btn-Wrapper" 
    v-show="show_btns=='tweet_btns'"
    v-bind:class="{ isHidden: (tw_account_data.tweet == 4) }"
    >
      <p class="a-Btn--pink"
      v-on:click="openTweetModal('tweet_register')"
      ><span>登録</span></p>

      <p class="a-Btn--grey"
      v-on:click="showTweetList('tweet_list_all')"
      ><span>リスト</span></p>

      <p class="a-Btn--grey"
      v-on:click="showTweetList('tweet_list_registerd')"
      ><span>予約済み</span></p>

      <p class="a-Btn--grey"
      v-on:click="showTweetList('tweet_list_posted')"
      ><span>投稿済み</span></p>

    </div>
    <div class="c-Btn-Wrapper" 
    v-on:click="openTweetModal('tweet_release')"
    v-show="show_btns=='tweet_btns'"
    v-bind:class="{ isHidden: !(tw_account_data.tweet == 4) }"
    >
      <p class="a-Btn--pink favorite-btn api-release" 
      
      ><span>凍結中</span></p>
    </div>



    
  </div>
</template>

<script>
//アカウント詳細ページ 「/my_tw_account」で使用するボタンを表示しております。

export default {
  name: "detail_btn",

  data: function () { return {
    btn: "",
    show_btns: ''
  }},

  methods: {

    showBtns: function (value) {
      $(".showbtn").each(function(){
        $(this).removeClass("isActive");
      });
      $("#"+value).addClass("isActive").fadeIn();
      this.show_btns = value;
    },

    //作成途中
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

        this.$emit('emitOpen_List', open);
        this.$emit('emitTargets', data.data);
        setTimeout(function(){
          $('.c-Bar').removeClass('is-Opened');
        }, 300);
      })
      .catch(err => {
        alert("失敗");
        console.log(err);
      }).finally(() => {
        console.log('finally');

      });
    },

    showTweetList: function (open) {
      this.$emit('emitOpen_List', open);
      setTimeout(function(){
        $('.c-Bar').removeClass('is-Opened');
      }, 300);

    },
    
    openTweetModal: function (pattern, item = null) {
      if (item != null) {
        this.$emit('emitSelected_Tweet', item);
      }
      $(".js-Modal__cover").fadeIn();
      $(".c-modal-Form." + pattern).fadeIn();
    },

    dateToFormatString: function  (date, fmt, locale, pad) {
      // %fmt% を日付時刻表記に。
      // 引数
      //  date:  Dateオブジェクト
      //  fmt:   フォーマット文字列、%YYYY%年%MM%月%DD%日、など。
      //  locale:地域指定。デフォルト（入力なし）の場合はja-JP（日本）。現在他に対応しているのはen-US（英語）のみ。
      //  pad:   パディング（桁数を埋める）文字列。デフォルト（入力なし）の場合は0。
      // 例：2016年03月02日15時24分09秒
      // %YYYY%:4桁年（2016）
      // %YY%:2桁年（16）
      // %MMMM%:月の長い表記、日本語では数字のみ、英語ではMarchなど（3）
      // %MMM%:月の短い表記、日本語では数字のみ、英語ではMar.など（3）
      // %MM%:2桁月（03）
      // %M%:月（3）
      // %DD%:2桁日（02）
      // %D%:日（2）
      // %HH%:2桁で表した24時間表記の時（15）
      // %H%:24時間表記の時（15）
      // %h%:2桁で表した12時間表記の時（03）
      // %h%:12時間表記の時（3）
      // %A%:AM/PM表記（PM）
      // %A%:午前/午後表記（午後）
      // %mm%:2桁分（24）
      // %m%:分（24）
      // %ss%:2桁秒（09）
      // %s%:秒（9）
      // %W%:曜日の長い表記（水曜日）
      // %w%:曜日の短い表記（水）
        var padding = function(n, d, p) {
            p = p || '0';
            return (p.repeat(d) + n).slice(-d);
        };
        var DEFAULT_LOCALE = 'ja-JP';
        var getDataByLocale = function(locale, obj, param) {
            var array = obj[locale] || obj[DEFAULT_LOCALE];
            return array[param];
        };
        var format = {
            'YYYY': function() { return padding(date.getFullYear(), 4, pad); },
            'YY': function() { return padding(date.getFullYear() % 100, 2, pad); },
            'MMMM': function(locale) {
                var month = {
                    'ja-JP': ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
                    'en-US': ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'],
                };
                return getDataByLocale(locale, month, date.getMonth());
            },
            'MMM': function(locale) {
                var month = {
                    'ja-JP': ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
                    'en-US': ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June',
                              'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'],
                };
                return getDataByLocale(locale, month, date.getMonth());
            },
            'MM': function() { return padding(date.getMonth()+1, 2, pad); },
            'M': function() { return date.getMonth()+1; },
            'DD': function() { return padding(date.getDate(), 2, pad); },
            'D': function() { return date.getDate(); },
            'HH': function() { return padding(date.getHours(), 2, pad); },
            'H': function() { return date.getHours(); },
            'hh': function() { return padding(date.getHours() % 12, 2, pad); },
            'h': function() { return date.getHours() % 12; },
            'mm': function() { return padding(date.getMinutes(), 2, pad); },
            'm': function() { return date.getMinutes(); },
            'ss': function() { return padding(date.getSeconds(), 2, pad); },
            's': function() { return date.getSeconds(); },
            'A': function() {
                return date.getHours() < 12 ? 'AM' : 'PM';
            },
            'a': function(locale) {
                var ampm = {
                    'ja-JP': ['午前', '午後'],
                    'en-US': ['am', 'pm'],
                };
                return getDataByLocale(locale, ampm, date.getHours() < 12 ? 0 : 1);
            },
            'W': function(locale) {
                var weekday = {
                    'ja-JP': ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
                    'en-US': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                };
                return getDataByLocale(locale, weekday, date.getDay());
            },
            'w': function(locale) {
                var weekday = {
                    'ja-JP': ['日', '月', '火', '水', '木', '金', '土'],
                    'en-US':  ['Sun', 'Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat'],
                };
                return getDataByLocale(locale, weekday, date.getDay());
            },
        };
        var fmtstr = ['']; // %%（%として出力される）用に空文字をセット。
        Object.keys(format).forEach(function(key) {
            fmtstr.push(key); // ['', 'YYYY', 'YY', 'MMMM',... 'W', 'w']のような配列が生成される。
        })
        var re = new RegExp('%(' + fmtstr.join('|') + ')%', 'g');
        // /%(|YYYY|YY|MMMM|...W|w)%/g のような正規表現が生成される。
        var replaceFn = function(match, fmt) {
        // match には%YYYY%などのマッチした文字列が、fmtにはYYYYなどの%を除くフォーマット文字列が入る。
            if(fmt === '') {
                return '%';
            }
            var func = format[fmt];
            // fmtがYYYYなら、format['YYYY']がfuncに代入される。つまり、
            // function() { return padding(date.getFullYear(), 4, pad); }という関数が代入される。
            if(func === undefined) {
                //存在しないフォーマット
                return match;
            }
            return func(locale);
        };
      return fmt.replace(re, replaceFn);
    },
    


    //キーワード登録をします。
    showFavoriteModal: function (btn) {
      this.$emit('emitBtn', btn);
      
      setTimeout(function(){
        $(".js-Modal__cover").fadeIn();
        $(".c-modal-Form." + btn).fadeIn();
      }, 500);
    },
    showFavoriteAutoModal: function (btn) {
      this.$emit('emitBtn', btn);

      setTimeout(function(){
        $(".js-Modal__cover").fadeIn();
        $(".c-modal-Form.favorite_auto").fadeIn();
      }, 500);
    },



  }
  
}
</script>
<style lang="scss" scoped>
.isActive {
    background: #fff;

  * {
    color: #48A2F1;
    transition: color 0.5s;
    position: relative;
  }

}
.c-DetailBtns {
  padding-top: 24px;
  @media screen and (min-width: 575px){
      padding-top: 0px;
  }

}

</style>