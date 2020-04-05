
@extends('layouts.mainapp')

@section('content')
<div id="app">


		<div class="l-main-app__title">
			<title_btn></title_btn>
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
