<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>オートついったー</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}<?php echo "?".uniqid()?>" defer></script>
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>
  
    <script>
        window.Laravel = { 
            csrfToken: '{{ csrf_token() }}',
            auth_data : {'app_id': '{{ Auth::id() }}', 'name': '{{ Auth::user()->name }}'},
        }


        //初期表示で使用するデータ：主にツイッターアカウント登録アクション後の表示に使用します。
        let initial_data = 
        `<?php 
            $data = !empty($initial_data)? $initial_data: null;
            echo $data;
        ?>`

        if (initial_data != "") {
            window.initial_data = JSON.parse(initial_data);
        }
    </script>
 

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <link href="{{ asset('css/reboot.css') }}" rel="stylesheet">
    <link href="{{ asset('css/utility.css') }}<?php echo "?".uniqid()?>" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}<?php echo "?".uniqid()?>" rel="stylesheet">
    <link href="{{ asset('css/other.css') }}<?php echo "?".uniqid()?>" rel="stylesheet">
    
</head>
<body>
<div class="loading spinner"></div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // $('.loading').addClass('spinner');
    // $('.loading').addClass('loaded');
},false);

</script>
    <header class="L-App-Header">
        <div class="c-Hmg icon-wrapper">
            <i class="a-I1 fas fa-bars"></i>
        </div>
        <div id="page_title">
            <page_title></page_title>
        </div>


        <div class="c-Main-Menus">
            <div id="common">
                <main_menu></main_menu>
                
            </div>
        </div>
    </header>
    <main class="l-main">

        <div class="C-Contents">
            <div class="u-container-fluid">
            @yield('content')


            </div>
        </div>
    </main>
   
    <script>
    let open_name = 'is-Opened';

    let $btn1 = $('.c-Hmg > .a-I1');
    
    $btn1.on('click', function(){
        let $target = $('.c-Main-Menus');
        if ($target.hasClass(open_name)) {
            $target.removeClass('is-Opened');
            $(".js-Modal__cover").fadeOut();

        } else {
            $target.addClass('is-Opened');
            $(".js-Modal__cover").fadeIn();
        }
    });


    // $(window).resize(function() {
 
    // });
    </script>
</body>
</html>
