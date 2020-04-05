@component('mail::message')
{{-- Greeting --}}
# {{$name}} @lang('様') <br>

@lang('オートついったーをご利用いただき誠にありがとうございます。')

{{-- Intro Lines --}}

{{-- Outro Lines --}}

{{-- Salutation --}}

@lang('下記のアカウントの'){{$text}}<br>

{{$account_name}} <br>

## @lang('制限解除までは少々お時間がかかります。ご了承ください。')

# @lang('オートついったー!')

{{-- Subcopy --}}

@endcomponent
