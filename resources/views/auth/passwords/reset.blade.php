@extends('layouts.app_back')

@section('content')
<div class="c-modal-Form register js-Modal" style="display: block;">
    <dl class="u-rel c-modal-Form__inner" style="z-index: 1;">
        <i class="cancel fas fa-times js-Modal__close"></i>
        <dt>オートついったー</dt>
        <dt class="u-mt-16 u-mb-40">パスワードリセット</dt>
        <dd>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="c-modal-Form-ch-block">
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


                <button class="a-Btn--large u-mt-32" type="submit"><p>リセット</p></button>

            </form>
            <p class="A-Policy">Term of use. Privacy policy</p>
        </dd>
    </dl>
</div>
@endsection
