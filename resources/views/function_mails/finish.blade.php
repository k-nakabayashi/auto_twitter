@component('mail::message')
{{-- Greeting --}}
# {{$name}} @lang('様') <br>

@lang('オートついったーをご利用いただき誠にありがとうございます。')

{{-- Intro Lines --}}

{{-- Outro Lines --}}

{{-- Salutation --}}

@lang('下記のアカウントの'){{$text}}<br>

{{$account_name}}

# @lang('オートついったー!')

{{-- Subcopy --}}

@endcomponent
