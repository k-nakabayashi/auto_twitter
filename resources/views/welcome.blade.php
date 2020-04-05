
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="_token" content="{{ csrf_token() }}">
        <title>オートついったー</title>

        <meta name="keywords"  content="twitter,sns集客,マーケティング,自動フォロー" />
        <meta name="description"  content="オートついったー：Twitterを使ったSNSマーケティングを考えている方におすすめです。自動フォロー・いいね・ツイートが簡単操作で行えます。" />

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        
        <!-- Styles -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">

        <link href="{{ asset('css/reboot.css') }}" rel="stylesheet">
        <link href="{{ asset('css/main.css') }}" rel="stylesheet">
        <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
        <script scr="{{ asset('js/home.js') }}"></script>
    </head>
    <body id="top">
        <header class="l-header">
            <div class="top-Nav u-container">
                <nav class="u-row">

                    <div class="u-col-3 u-col-1-lg tops-Nav__img">
                      <figure class="u-row-center">
                            <img class="a-Main-logo" src="{{ asset('images/icons/main-logo.png')}}" alt="ロゴ">
                        </figure>
                    </div>

                    <div class="tops-Nav__actions u-col-8 u-col-4-sm">
                        <div class="u-row u-h-100">
                        @auth
                            <a class="menu toHome u-col-6" href="{{ url('/home') }}"><span>Home</span></a>
                        @else
                            <p class="menu u-col-6 js-Modal__btn--register"><span>新規登録</span></p>
                            <p class="menu u-col-6 js-Modal__btn--login"><span>ログイン</span></p>
                        @endauth
                        </div>
                    </div>
                </nav>
            </div>


        </header>

        <div class="c-modal-Form inTop register js-Modal">
            <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
                <i class="cancel fas fa-times js-Modal__close"></i>
                <dt>オートついったー</dt>
                <dt class="u-mt-16">新規登録</dt>

                    <form name="form_register" method="POST" action="/register">
                        @csrf
        
                        <div class="c-modal-Form-ch-block">
                            <input type="text" placeholder="名前" class="@error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <input type="email" placeholder="メールアドレス" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <input type="password" placeholder="パスワワード" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <input type="password" placeholder="確認ため、もう一度パスワードを入力してください" class="form-control" name="password_confirmation" required autocomplete="new-password">   
                        </div>
                        <button class="a-Btn--large u-mt-32" type="submit"><p>登録</p></button>

                    </form>
        
                <div class="c-modal-Form__links u-mt-40 u-mb-16">
                    <p class="js-Modal__btn--login"><span>登録済みの方はログイン画面へ</span></p>
                    <p class="u-mt-16 js-Modal__btn--repass"><span>パスワードを忘れた方はこちらへ</span></p>
                </div>

                <p class="A-Policy">Term of use. Privacy policy</p>
            </dl>
        </div>

        <div class="c-modal-Form inTop login u-mx-a js-Modal">
            <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
                <i class="cancel fas fa-times js-Modal__close"></i>
                <dt>オートついったー</dt>
                <dt class="u-mt-16">ログイン</dt>

                    <form name="form_login" method="POST" action="{{ route('login') }}">
                    @csrf
        
                        <div class="c-modal-Form-ch-block">
                            <input type="email" placeholder="メールアドレス" class="@error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <input type="password" placeholder="パスワワード"  class="@error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        

                        <button class="a-Btn--large u-mt-32" type="submit"><p>ログイン</p></button>

                    </form>
        
                <div class="c-modal-Form__links u-mt-40 u-mb-16">
                    <p class="js-Modal__btn--register"><span>新規登録はこちら</span></p>
                    <p class="u-mt-16 js-Modal__btn--repass"><span>パスワードを忘れた方はこちらへ</span></p>
                </div>

                <p class="A-Policy">Term of use. Privacy policy</p>
            </dl>
        </div>

        <div class="c-modal-Form inTop repass js-Modal">
            <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
                <i class="cancel fas fa-times js-Modal__close"></i>
                <dt>オートついったー</dt>
                <dt class="u-mt-16">パスワード再発行</dt>

                   <form name="form_repass" method="POST" action="{{ route('password.email') }}">
                        @csrf
        
                        <div class="c-modal-Form-ch-block">
                            <input placeholder="メールアドレス" id="email" type="email" name="email" value="" required="required" autocomplete="email" autofocus="autofocus" class="form-control ">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button class="a-Btn--large u-mt-32" type="submit"><p>再発行</p></button>

                    </form>
        
                <div class="c-modal-Form__links u-mt-40 u-mb-16">
                    <p class="js-Modal__btn--register"><span>新規登録はこちら</span></p>
                    <p class="js-Modal__btn--login u-mt-16"><span>登録済みの方はログイン画面へ</span></p>
                </div>

                <p class="A-Policy">Term of use. Privacy policy</p>
            </dl>
        </div>

        <div class="js-Modal__cover inTop js-Modal__close">&nbsp;</div>

        <main class="l-main">
            
            <header class="c-Hero">
                <div class="u-container">
                    <div class="c-Hero__Inner">
                        <div class="c-Hero-Inner">
                            <h1 class="f-txt-1">あ、本当だ！<br>自動でフォローできてる！<br>ツイッター集客が手軽に！</h1>
                        </div>
                    </div>
                </div>
            </header>

            <article class="m-Article">
                <section class="c-Catch">
                    <div class="u-container u-Inner4">
                        <h2 class="a-Title f-txt-1">市場調査・集客ツール<br>「オートついったー」<img class="a-Main-logo" src="{{ asset('images/icons/main-logo.png')}}" alt="ロゴ"></h2>
                        <p class="f-txt-4">Twitter上で「フォロー・いいね・ツイート」を自動で行うことで、<br>手軽に市場調査・集客が行えます。</p>
                    </div>
                </section>

                <section class="c-Points u-Inner-p">
                    <div class="u-container-sm u-Inner2">
                        <h2 class="a-Title f-txt-2">「オートついったー」3つの特徴</h2>
                        <ul class="a-List u-row">
                            <li class="c-Points__inner u-col-4-sm">

                                <dl class="u-Inner3">
                                    <dt><figure><i class="fas fa-user-friends"></i></figure></dt>
                                    <dt class="a-Title2">自動フォロー</dt>
                                    <dd class="a-txt">
                                        <p>お好みのワードを設定するだけ！</p>
                                        <p>様々なワードで簡単に<br>集客対象を絞ることができます！</p>
                                    </dd>

                                </dl>

                            </li>

                            <li class="c-Points__inner u-col-4-sm">
                                <dl class="u-Inner3">
                                    <dt><figure><i class="far fa-thumbs-up"></i></figure></dt>
                                    <dt class="a-Title2">自動いいね</dt>
                                    <dd class="a-txt">
                                        <p>お好みのワードを設定するだけ！</p>
                                        <p>得意分野で手軽に<br>市場調査ができます！</p>
                                    </dd>
                                </dl>
                            </li>

                            <li class="c-Points__inner u-col-4-sm">
                                <dl class="u-Inner3">
                                    <dt><figure><i class="fas fa-comments"></i></figure></dt>
                                    <dt class="a-Title2">予約ツイート</dt>
                                    <dd class="a-txt">
                                        <p>ツイート内容を事前に予約！</p>
                                        <p>閲覧数が多い時間<br>を狙って投稿可能に！</p>
                                    </dd>
                                </dl>
                            </li>
                        </ul>
                    </div>
                </section>

                <section class="c-Contents">
                    <div class="u-container">
                        <h2 class="a-Title f-txt-1">見込み客集めは大変...</h2>
                        <div class="u-rel">

       
                            <figure class="a-Img">
                                <picture>
                                    <source srcset="{{ asset('images/top/bussiness_man.jpg') }}" media="(min-width: 1025px)">
                                    <source srcset="{{ asset('images/top/bussiness_man2.jpg') }}" media="(max-width: 1024px)">
                                    <img src="{{ asset('images/top/bussiness_man.jpg') }}" alt="">
                                </picture>
                            </figure>
                            <div class="c-Detail">
                                <dl class="u-Inner4">
                                    <dt class="a-Txt f-txt-4">営業されてますと、集客...<br class="u-lg">これが一番悩みのタネ...</dt>
                                    <dd class="a-Txt2">営業するにも<span>見込みリスト</span>作成から大変ですよね。</dd>
                                    <dd>一件ずつ電話にメール...それがアポまで繋げるのも一苦労...</dd>
                                    <dd><span>アポ取り</span>ができたとしても、それが契約に繋がるのか...<dd>
                                    <dd>見込みリストさえあれば、<span>熱い見込み客</span>を絞り込めるのに...</dd>
                                    <dd>見込みリストを<span>効率的</span>に作るにはどうしたらいいんだ...っ！</dd>
                                </dl>
                            </div>
                            
 
                        </div>

                    </div>
                </section>

                <section class="c-Affinity u-Inner-p">
                    <div class="u-container u-Inner2">
                        <h2 class="a-Title f-txt-1">私も以前同じく毎日アポ取り・営業の日々に疲弊しておりました。</h2>
                        <ul class="c-List u-Inner4 u-txt--center">
                            <li>見込みリストをつくるため、アポ取りで毎日数百件の電話をかける日々</li>
                            <li>苦労してアポを取っても契約に結びつかず、ただ心も体も疲弊の日々</li>
                            <li>こんなに辛いことを続けるのは無理があると思う日々</p>
                        </ul>
                    </div>
                </section>

                <section class="c-Contents">
                    <div class="u-container">
                        <h2 class="a-Title f-txt-1">この集客の悩みを<br>なんとかするにはどうしたらいいでしょうか？</h2>
                        <div class="u-rel">
                            <figure class="a-Img is-Reverse">
                                <picture>
                                    <source srcset="{{ asset('images/top/thinking.jpg') }}" media="(min-width: 1025px)">
                                    <source srcset="{{ asset('images/top/thinking2.jpg') }}" media="(max-width: 1024px)">
                                    <img src="{{ asset('images/top/thinking.jpg') }}" />
                                </picture>
                            </figure>
                            <div class="c-Detail is-Reverse">
                                <dl class="u-Inner4">
                                    <dt class="a-Txt f-txt-4">見込みを１件ずつ手作業で<br class="u-lg">作るのは非常に大変ですよね？</dt>
                                    <dd class="a-Txt2">私は<span>ネット集客</span>できればいいのでは？と考え到りました。</dd>
                                    <dd>ネットで<span>自動で一気に見込み客</span>を集めることができれば非常に楽になります。集めた見込みリストから熱い客を絞り込む。それができれば営業としてはかなり楽になりますよね？</dd>
                                    <dd>なんとかしても営業職の皆様のご苦労を軽減したく思い、</dd>
                                    <dd>日本人ユーザーが多い<span>Twitter</span>を使った自動集客ツール<br class="u-lg"><span>「オートついったー」</span>を開発致しました。</dd>
                                </dl>
                            </div>
                        </div>

                    </div>
                </section>

                <section class="c-QA u-Inner-p u-Inner2">
                    <h2 class="a-Title f-txt-2">よくある質問</h2>
                    <dl class="c-QA__Inner">
                        <dt class="u-indent has-Q f-txt-4">ツールといったって、難しいんじゃいないの？」</dt>
                        <dl class="u-indent has-A">大丈夫です。<br>Twitterをご利用されている方でしたら、クリックで進めるだけです。</dl>
                        <dt class="u-indent has-Q f-txt-4">「でも、そんな自動でやるとTwitterに怒られたりしないの？」</dt>
                        <dl class="u-indent has-A">大丈夫です。<br>Twitter社は規約通りに運用すれば問題ないという見解を出しており、その規約通りに開発しておりますのでご心配無用です。</dl>
                    </dl>
                </section>
            </article>
        </main>

        <footer class="l-Footer">
            <p class="a-Copy">Term of use. Privacy policy</p>
        </header>

        <script>
            // // サーバからのデータ受信を行った際の動作
            // let xhr = new XMLHttpRequest();
            // xhr.onload = function (e) {
            //     if (xhr.readyState === 4) {
            //         console.log(this.response);
            //         if (xhr.status === 200) {
            //             console.log(this.response);
            //             if( this.response ){
            //                 console.log(this.response);
            //                 // 読み込んだ後処理したい内容をかく
            //             }
            //         }
            //     }
 
            // };
            // // 計算ボタンを押した際の動作
            // function showData(form) {
            //     let query = 
            //         "&column=" + "email" + 
            //         "&value=" + form.email.value + 
            //         "&model_name=Tw_Account" + 
            //         "&_token=" + $("meta[name='_token']")[0].content;
                    
            //     console.log(query);
            //     xhr.open('GET', "RestApi/show" + query, true);
            //     // xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
            //     // フォームに入力した値をリクエストとして設定
            //     xhr.responseType = 'json';
            //     xhr.send(null);
            // }

            function submitForm_login() {
                return true;
                // let result = showData(document.form_login);
                // console.log(result);
                // return false;
                // if (result) {
                    // return true;
                // }
                // return false;
            }
   
            function submitForm_register() {
                alert(111);
                $.ajax({
                    type: 'GET',
                    url: 'RestApi/show',
                    data: {
                        column: 'email',
                        value: document.form_register.email,
                        model_name: "Tw_Account",
                        __token: $("meta[name='_token']")[0].content,
                        
                    },
                }).then(function (data) {
                    console.log(data);
                    alert(222);
                    if (data) {

                    } else {

                    }
                });
                return false;
                // let result = getAxios(document.form_register);
                // if (result) {
                //     alert(111);
                //     return true;
                // } else {
                //     alert(222);
                //     return false;
                // }
       
                // let result = showData(document.form_register);
                // console.log(result);
                // return false;
                // if (result) {
                    
                // }
                // return false;
            }
 

            function submitForm_repass() {
                return true;
                // document.form_repass;
                // return true;
            }

            $(".js-Modal__btn--register").on("click", function () {
                $(".js-Modal__cover").show();
                $(".c-modal-Form.login ").hide();
                $(".c-modal-Form.repass").hide();

                $(".c-modal-Form.register ").toggle();
            });

            $(".js-Modal__btn--login").on("click", function () {
                $(".js-Modal__cover").show();
                $(".c-modal-Form.register ").hide();
                $(".c-modal-Form.repass").hide();

                $(".c-modal-Form.login ").toggle();
                $(".js-Modal__btn--login button").on('click', function (e) {
                    // e.preventDefault();
                    post($('.c-modal-Form.login'));
                });
            });

            $(".js-Modal__btn--repass").on("click", function () {
                $(".js-Modal__cover").show();
                $(".c-modal-Form.register ").hide();
                $(".c-modal-Form.login").hide();

                $(".c-modal-Form.repass").toggle();
            });


            $(".js-Modal__close").on("click", function () {
                $(".c-modal-Form ").hide();
                $(".js-Modal__cover").hide();
            });


        </script>

        <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-132770288-5"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'UA-132770288-5');
</script>
    </body>
</html>