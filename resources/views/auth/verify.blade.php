@extends('layouts.app_back')

@section('content')

<div class="c-modal-Form register js-Modal" style="display: block;">
    <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
        <i class="cancel fas fa-times js-Modal__close"></i>
        <dt>オートついったー</dt>
        <dt class="u-mt-16 u-mb-40">メールアドレス本人確認</dt>

            <div>

                <div class="c-modal-Form-ch-block">

                    <p class="u-txt--left">登録したメールアドレスに、<br>確認メールを送りました。<br>そちらをクリックしログインをしてください。<br>もしまだ確認メールが届いていないのでしたら、<br>下記をクリックし確認メールの再送できます。</p>
                </div>


                <a href="{{ route('verification.resend') }}" class="a-Btn--large u-mt-32" type="submit"><p>確認メール再送</p></a>

            </div>

        <p class="A-Policy">Term of use. Privacy policy</p>
    </dl>
</div>
@endsection
