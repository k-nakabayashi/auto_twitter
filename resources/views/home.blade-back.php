
<script>///aaaa
if ((`<?php echo $_SESSION['req_oauth_token']; ?>`) !== null) {

    window.req_token = (`<?php echo $_SESSION['req_oauth_token']; ?>`);
    window.req_token_secret = (`<?php echo $_SESSION['req_oauth_token_secret']; ?>`);
    window.tw_accounts = JSON.parse(`<?php echo $tw_account_array?>`);

} else {
    location.reload();
}

 
</script>
@extends('layouts.app')

@section('content')
<!-- 
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div> -->

<div id="app">


	<div class="u-txt-l">
        <title_btn></title_btn>
        <?php
    // echo '<p>下記の認証情報を取得しました。(<a href="'.explode( "?", $_SERVER["REQUEST_URI"] )[0].'">もう1回やってみる</a>)</p>';

        ?>
        <following_key></following_key>
    </div>
    <router-view name="account_cards"></router-view>
</div>

<!-- end app -->

	<dl>
			<dt></dt>
			<dd></dd>
	</dl>
</div>
@endsection
