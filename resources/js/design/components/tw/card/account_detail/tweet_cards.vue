<template>

  <div>

    <ul class="u-row tweet_list all m-Cards-Wrapper u-scroll"
    v-show="open_list == 'tweet_list_all'"
    >

      <li v-for="(item) in no_reserved_list" v-bind:key="item.id" class="u-col-3 u-col-6-smx">

        <div class="c-Tweet">
          <div class="c-Items u-container u-card-back u-rel">
            <p class="a-Btn--pink tweet-btn api-stop2" 
              v-on:click="openFuncModal('tweet_start', item)"
              v-bind:class="{ isHidden: !(item.tweet_status == 0) || (tw_account_data.statuses_update == 0)}"
            ><span>予約する</span></p>

            <p class="a-Chip" 
            v-bind:class="{ isHidden: !(tw_account_data.statuses_update == 0)}"
           ><span>制限中</span></p>

            <!-- 
            <p class="a-Btn--pink tweet-btn api-release" 
              v-on:click="openFuncModal('tweet_release', item)"
              v-bind:class="{ isHidden: !(item.tweet_status == 4) }"
            ><span>凍結中</span></p> -->


            <div class="icon-wrapper"
            v-on:click="openFuncModal('tweet_delete', item)"
            v-if="item.tweet_status != 4"
            >
              <i class="fas fa-times"></i>
            </div>
            <p class="a-Txt">{{item.detail}}</p>
            <div class="c-Btn-Wrapper">
              <p class="a-Chip"
                v-for="(tags) in item.tags" 
                v-bind:key="tags.id"
              ><span>#{{tags}}</span></p>
            </div>
          </div>
        </div>

      </li>

    </ul>

    <ul class="u-row tweet_list registerd m-Cards-Wrapper u-scroll"
      v-if="open_list == 'tweet_list_registerd'"
      >

      <li v-for="(item) in reserved_list" 
      v-bind:key="item.id"
      class="c-Tweet u-col-3 u-col-6-smx">

        <div class="c-Items u-container u-card-back u-rel">

          <p class="a-Chip is-pink tweet-btn api-doing" 
            v-bind:class="{ isHidden: !(item.tweet_status == 1)}"
          ><span>予約中</span></p>

          <p class="a-Chip tweet-btn api-restart"
          v-bind:class="{ isHidden: !(item.tweet_status == 2)}"
          ><span>再開中</span></p>

          <p class="a-Btn--pink tweet-btn api-tmp_stop" 
            v-on:click="openFuncModal('tweet_restart', item)"
            v-bind:class="{ isHidden: !(item.tweet_status == 3)}"
          ><span>一時停止中</span></p>

          <p v-if="item.tweet_status == 4" class="a-Chip"><span>投稿済み</span></p>

          <div class="icon-wrapper"
          v-on:click="openFuncModal('tweet_delete', item)"
          >
            <i class="fas fa-times"></i>
          </div>
          
          <p v-if="item.tweet_status !=4" class="a-Time">投稿予約日時：<br class="u-smx">{{item.tweet_timing}}</p>
          <p v-if="item.tweet_status ==4" class="a-Time">投稿日時：<br class="u-smx">{{item.updated_at}}</p>
          <p class="a-Txt">{{item.detail}}</p>
          <div class="c-Btn-Wrapper">
            <p class="a-Chip"
              v-for="(tags) in item.tags" 
              v-bind:key="tags.id"
            ><span>#{{tags}}</span></p>
          </div>
        </div>
      </li>
      
    </ul>

    <ul class="u-row tweet_list posted m-Cards-Wrapper u-scroll"
      v-if="open_list == 'tweet_list_posted'"
      >

      <li v-for="(item) in posted_list" 
      v-bind:key="item.id"
      class="c-Tweet u-col-3 u-col-6-smx">

        <div class="c-Items u-container u-card-back u-rel">
          <p class="a-Chip"><span>投稿済み</span></p>          
          <p v-if="item.tweet_status !=4" class="a-Time">投稿予約日時：<br class="u-smx">{{item.tweet_timing}}</p>
          <p v-if="item.tweet_status ==4" class="a-Time">投稿日時：<br class="u-smx">{{item.updated_at}}</p>
          <p class="a-Txt">{{item.detail}}</p>
          <div class="c-Btn-Wrapper">
            <p class="a-Chip"
              v-for="(tags) in item.tags" 
              v-bind:key="tags.id"
            ><span>#{{tags}}</span></p>
          </div>
        </div>
      </li>
      
    </ul>






    <div class="c-modal-Form tweet_auto js-Modal">
      <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
          <i class="cancel fas fa-times js-Modal__close"></i>
          <dt>オートついったー</dt>
          <dt v-if="btn == 'tweet_start'" class="a-Title f-txt-3">自動ツイート予約</dt>
          <dt v-if="btn == 'tweet_stop'" class="a-Title f-txt-3">自動ツイート中止</dt>
          <dt v-if="btn == 'tweet_restart'" class="a-Title f-txt-3">自動ツイート再開</dt>
          <dt v-if="btn == 'tweet_release'" class="a-Title f-txt-3">自動ツイート凍結解除通知</dt>
          <dt v-if="btn == 'tweet_delete'" class="a-Title f-txt-3">自動ツイート<br>削除しますか？</dt>

          <div>
              <div class="c-modal-Form-ch-block u-scroll2"
              >
                <!-- <input v-model="screen_name" placeholder="スクリーンネームを記入してください。" type="text" name="content" required="required" autofocus="autofocus" class="form-control"> -->
                <p v-show="selected_tweet.tweet_status != 0" class="a-Time">投稿予約日時：<br class="u-smx">{{selected_tweet.tweet_timing}}</p>
                <label v-show="selected_tweet.tweet_status == 0 && btn != 'tweet_delete'" for="timing" class="u-txt--l">予約日時：<input id="timing" class="set_time" v-model="tweet_timing" type="datetime-local" min="" max=""></label>                 
                <p class="a-Detail u-txt--l">{{selected_tweet.detail}}</p>
                <div class="c-Btn-Wrapper u-mt-16">
                  <p class="a-Chip"
                    v-for="(tags) in selected_tweet.tags" 
                    v-bind:key="tags.id"
                  ><span>#{{tags}}</span></p>
                </div>
              </div>

              <button 
                v-on:click="reserveTweet(selected_tweet)" 
                v-if="btn == 'tweet_start'"
              　class="a-Btn--pink u-mt-32" type="submit">
              <p>予約</p>
              </button>

              <button 
                v-on:click="bootAutoFunction(false, 
                  selected_tweet.id, 
                  'tweet', 'stop',
                  'statuses_update', 3,
                )" 
                v-if="btn == 'tweet_stop'"  class="a-Btn--pink u-mt-32" type="submit">
              <p>中止</p>
              </button>

              <button 
                v-on:click="bootAutoFunction(false, 
                  selected_tweet.id, 
                  'tweet', 'restart',
                  'statuses_update', 2,
                )" 
                v-if="btn == 'tweet_restart'"  class="a-Btn--pink u-mt-32" type="submit"
              ><p>再開</p>
              </button>

              <button 
                v-on:click="bootAutoFunction(true, 
                  selected_tweet.id, 
                  'tweet', 'release',
                  'statuses_update', 4,
                )" 
                v-if="btn == 'tweet_release'"  class="a-Btn--pink u-mt-32" type="submit"
              ><p>通知</p>
              </button>
              
              <button 
                v-on:click="bootAutoFunction(false, 
                  selected_tweet.id, 
                  'tweet', 'delete',
                  'statuses_update', null,
                )" 
                v-if="btn == 'tweet_delete'"  class="a-Btn--pink u-mt-32" type="submit"
              ><p>削除</p>
              </button>

          </div>
          <p class="a-Policy">Term of use. Privacy policy</p>
      </dl>
    </div>

    <div class="c-modal-Form tweet_register js-Modal">
      <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
          <i class="cancel fas fa-times js-Modal__close"></i>
          <dt>オートついったー</dt>
          <dt class="a-Title f-txt-3">自動ツイート<br></dt>
            <div>
                <div class="c-modal-Form-ch-block c-Modal-Input">
                  <p class="u-txt--l">文字数：{{tweet_counter}} / 140</p>
                  <textarea type="text" class="a-Area" v-model="tweet_detail" placeholder="投稿内容を記入してください" style="margin-top: 8px;"></textarea>
                  <div class="u-ch-max u-txt--l">
                    <label for="tweet_tag u-txt--l" class="u-txt--left">ハッシュタグを記載してください</label>
                    <input v-model="tweet_tag" id="tweet_tag" type="text" placeholder="例) #ツイッター #おもしろ">
                  </div>
                </div>
                <button v-on:click="registerTweet()" class="a-Btn--pink u-mt-32" type="submit"><p>登録</p></button>
            </div>
          <p class="a-Policy">Term of use. Privacy policy</p>
      </dl>
    </div>


	</div>
    
</template>


<script>
// アカウント詳細ページで使用するツイートリスト表示し、ツイート関連機能を実行します。
export default {
  name: "tweet_cards",
  props: {
    open_list: String,
    //リスト表示で使う
  },
  
  data: function () { return {
    selected_tweet: "",
    tweet_detail: "",
    tweet_tag: "",
    tweet_tag_list: [],
    tweet_timing: "",
    tweet_counter: 0,
    
    btn: "",

   
  }},


  computed: {

    tweet_timing_data: function () {
      let data  = this.tweet_timing;
    },
    
    tweet_card_list: function () {
      return this.tweet_list_data;
    },

    no_reserved_list: function () {
      var tweets = [];
      for (var i = 0; i < Object.keys(this.tweet_list_data).length; i ++) {
        if (this.tweet_list_data[i].tweet_status == 0) {
          tweets.push(this.tweet_list_data[i]);
        }
      }
      return tweets;
    },

    reserved_list: function () {
      var tweets = [];
      for (var i = 0; i < Object.keys(this.tweet_list_data).length; i ++) {
        if (this.tweet_list_data[i].tweet_status == 1 || this.tweet_list_data[i].tweet_status == 2 || this.tweet_list_data[i].tweet_status == 3) {
          tweets.push(this.tweet_list_data[i]);
          continue;
        }
      }
      return tweets;
    },

    posted_list: function () {
      var tweets = [];
      for (var i = 0; i < Object.keys(this.tweet_list_data).length; i ++) {
        if (this.tweet_list_data[i].tweet_status == 4) {
          tweets.push(this.tweet_list_data[i]);
          continue;
        }
      }
      return tweets;
    },

  },

  mounted: function () {
    this.$nextTick(function () {
      this.getTweet_List(this.tw_account_data.tw_account_id);
      let now = new Date();
      let min_time = this.dateToFormatString(now, '%YYYY%-%MM%-%DD%T%HH%:%mm%');
      $('.set_time').attr('min', min_time);
      now.setDate(now.getDate() + 2);
      let max_time = this.dateToFormatString(now, '%YYYY%-%MM%-%DD%T%HH%:%mm%');
      $('.set_time').attr('max', max_time);
    });
  },

  methods: {

    openModal: function (btn, obj) {
      this.selected_tweet = obj;//対象のツイートのid
      this.btn = btn;
      var dom1 = $('.tweet_palceholder');
      console.log(dom1);
      dom1.attr('placeholder', this.selected_tweet.detail);
      let str_tag = "";
      for (let i = 0; i < (this.selected_tweet.tags).length; i ++) {
        if (i != 0) {
          str_tag += " ";
        }
        str_tag += (this.selected_tweet.tags)[i];
      }
      $('.edit_tweet').attr("placeholder", str_tag);


      $(".c-modal-Form.edit.delete.tweet_detail").fadeIn();
      $(".js-Modal__cover").fadeIn();

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


    //ツイートのハッシュタグ設定用
    prepareTweet_Tag: function () {

      this.tweet_tag_list = [];
      let flag = true;
      if (!this.tweet_tag) {
        return flag;
      }
      //配列化する
      var result = this.tweet_tag.split(' ');

      //二つ目以降のキーワードを作る
      var text = "";
      for (var i = 0; i < Object.keys(result).length; i ++) {
        if (!result[i]) {
          continue;
        }
        text = result[i].slice(0, 1);

        if (text != "#") {
          
          alert("・ハッシュタグには、先頭に 半角の#をつけてください。");
          this.tweet_tag_list = [];
          flag = false;
          break;
        }
        text = result[i].slice(1);
        
        //optionありの場合
        this.tweet_tag_list.push(text);
      }

      if (this.tweet_tag_list[0] == "") {
        alert("・ハッシュタグには#の後に文字を記載してください。");
        this.tweet_tag_list = [];
        flag = false;
      }
      return flag;
    },


    reserveTweet: function (tweet) {
      if (this.tweet_timing == "") {
        alert("投稿予約日時を設定してください。");
        return;
      }

      var setting = this.setAutoFunctionStarter('tweet', 'start');
      var data = {
        tweet_timing: this.tweet_timing,
        model_name: "Tw_Auto_Tweet",
        domain4: 'tweet',
        pattern: 'start',
        id: tweet.id
      };

      //一つ目のキーワードはand
      this.$http.post(setting.url, data
      ).then(res => {
        var data = res.data;

        var result = this.alertErrors("自動ツイートの予約に失敗しました。", data.errors);
        if (result == false) {
          return;
        }
        
        alert("自動ツイートの予約に成功しました。");
        this.$store.dispatch('tw_account/actTw_Account', data.data);
        this.getTweet_List(this.tw_account_data.tw_account_id);
        $(".js-Modal__cover").fadeOut();
        $(".c-modal-Form").fadeOut();

      }).catch(err => {
        alert("エラー発生：自動ツイートの予約に失敗しました。");
        console.log(err)
      }).finally(() => {
        console.log('finally')
      });
    },

    openFuncModal: function (btn, item) {
      this.selected_tweet = item;
      this.btn = btn;
      $(".c-modal-Form.tweet_auto").fadeIn();
      $(".js-Modal__cover").fadeIn();
    },
    openFuncModal2: function (btn, item) {
      this.selected_tweet = item;
      this.btn = btn;
      $(".c-modal-Form.tweet_edit_delete").fadeIn();
      $(".js-Modal__cover").fadeIn();
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

    checkStrNumber: function () {

      let counter = 0;
   
      for (let i of Array.from(this.tweet_detail)) {
        if (i == " " || i == "　") {
          continue;
        }
        counter ++;
      }
      for (let i of Array.from(this.tweet_tag)) {
        if (i == " " || i == "　") {
          continue;
        }
        counter ++;
      }
     this.tweet_counter = counter;


      // var ESCAPECHAR = ";,/?:@&=+$ ";
      // // URLエンコードされたUTF-8文字列表現の桁数とバイト数の対応テーブル
      // // encodeURI("あ") → "%E3%81%82" (9桁) → 3バイト
      // var ESCAPEDLEN_TABLE = [ 0, 1, 1, 1, 2, 3, 2, 3, 4, 3 ];
      // var counter = 0;
      // for (var i=0; i<this.tweet_detail.length; i++) {
      //   var c = this.tweet_detail.charAt(i);
      //   if (ESCAPECHAR.indexOf(c) >= 0) {
      //       counter++;
      //   } else {
      //       counter += ESCAPEDLEN_TABLE[encodeURI(c).length];
      //   }
      // }
      // for (var i=0; i<this.tweet_tag.length; i++) {
      //   var c = this.tweet_tag.charAt(i);
      //   if (ESCAPECHAR.indexOf(c) >= 0) {
      //       counter++;
      //   } else {
      //       counter += ESCAPEDLEN_TABLE[encodeURI(c).length];
      //   }
      // }
      // this.tweet_counter = counter;

    },


    //ツイート登録
    registerTweet: function () {
      if (this.tw_account_data.statuses_update == 0) {
        alert("制限中です。少々お待ちください。");
        $(".js-Modal__cover").fadeOut();
        $(".c-modal-Form").fadeOut();
        return;
      }
      this.checkStrNumber();
      let flag = true;
      flag = (this.tweet_counter == 0)? false : true;
      if (flag == false) {
        alert("ツイートする内容を記入してください。");
        return;
      }
      flag = (this.tweet_counter >= 140)? false : true;
      if (flag == false) {
        alert("ツイートできる文字数140をオーバーしています。");
        return;
      }
      flag = this.prepareTweet_Tag();
      
      if (flag == false) {
        return;
      }

        var data = {
          tw_account_id: this.tw_account_data.tw_account_id,
          detail: this.tweet_detail,
          tags: this.tweet_tag_list,
          model_name: "Tw_Auto_Tweet",
        };
        //一つ目のキーワードはand
        this.$http.get('/RestApi/create', {
          params: data,

        }).then(res => {

          var data = res.data;
          var result = this.alertErrors("ツイート登録に失敗しました。", data.errors);
          if (result == false) {
            return;
          }

          alert("ツイート登録に成功しました。\n・次は自動ツイート予約してください");
          this.$store.dispatch('tw_account/actTw_Account', data.data);     
          this.getTweet_List(this.tw_account_data.tw_account_id);

          $(".js-Modal__cover").fadeOut();
          $(".c-modal-Form").fadeOut();
        })
        .catch(err => {
          alert("エラー発生");
          console.log(err)
        }).finally(() => {

          console.log('finally')
        });
      
    },

    prepareTweet_Tag: function () {

      this.tweet_tag_list = [];
      let flag = true;
      if (!this.tweet_tag) {
        return flag;
      }
      //配列化する
      var result = this.tweet_tag.split(' ');

      //二つ目以降のキーワードを作る
      var text = "";
      for (var i = 0; i < Object.keys(result).length; i ++) {
        if (!result[i]) {
          continue;
        }
        text = result[i].slice(0, 1);

        if (text != "#") {
          
          alert("・ハッシュタグには、先頭に 半角の#をつけてください。");
          this.tweet_tag_list = [];
          flag = false;
          break;
        }
        text = result[i].slice(1);
        if(text == null || text == '' || text == '　' || text == null) {
          alert("・ハッシュタグ#の直後の空文字を取り除いてください。");
          this.tweet_tag_list = [];
          flag = false;
          break;
        }
        //optionありの場合
        this.tweet_tag_list.push(text);
      }

      if (this.tweet_tag_list[0] == "") {
        alert("・ハッシュタグには#の後に文字を記載してください。");
        this.tweet_tag_list = [];
        flag = false;
      }
      return flag;
    },
  }
}
</script>
<style lang="scss" scoped>
.action-title {
  margin-top: 40px;
  margin-bottom: 10px;
}
.c-Account__img {
  img {
    object-fit: contain;
    margin-bottom: auto;
  }
}
.a-Textarea {
  min-height: 160px;
  text-align: left;
}


</style>