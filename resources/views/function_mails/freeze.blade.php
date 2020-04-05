@component('mail::message')
{{-- Greeting --}}
# {{$name}} @lang('様') <br>

@lang('オートついったーをご利用いただき誠にありがとうございます。')

{{-- Intro Lines --}}

{{-- Outro Lines --}}

{{-- Salutation --}}

@lang('下記のアカウントが凍結されました。')<br>

{{$account_name}}<br>

## @lang('Twitterに凍結解除申請を行うようお願い致します。')<br>
## @lang('凍結解除ができましたら、オートついったーにて解除通知をお願い致します。')<br>

# @lang('オートついったー!')

{{-- Subcopy --}}

@endcomponent
