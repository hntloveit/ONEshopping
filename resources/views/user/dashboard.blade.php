@extends('layouts.user')
@section('title', __('User Dashboard'))
@php
$has_sidebar = false;
$base_currency = base_currency();
@endphp

@section('content')
<div class="content-area user-account-dashboard">
    @include('layouts.messages')
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12">
            {!! UserPanel::user_balance_card($contribution, ['vers' => 'side', 'class'=> 'card-full-height']) !!}
			
		</div>
		
		<div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card card-full-height card-token "><div class="card-innr token-balance"><h6 class="card-sub-title card-title-sm">POINT</h6><span class="point lead">{!! to_num_token($point) !!}</span><span class="white ml-20">POINT</span><div class="gaps-0-5x"></div></div></div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card card-full-height card-token card-bg"><div class="card-innr token-balance"><h6 class="card-sub-title card-title-sm">ONE</h6><span class="one lead">{!! to_num_token($one) !!}</span><span class="white ml-20">ONE</span><div class="gaps-0-5x"></div><br/>
				@if($one > 0)<div class="d-flex align-items-center justify-content-between mb-0"><a href="{!! route('user.sendonetouser') !!}" class="btn btn-md btn-success">SEND TO USER NOW</a></div>@endif
			</div></div>
        </div>
		<div class="col-lg-6 col-sm-12">
            {!! UserPanel::user_token_block('', ['vers' => 'buy']) !!}
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="account-info card card-full-height">
                <div class="card-innr">
                    {!! UserPanel::user_account_status() !!}
                    <div class="gaps-2x"></div>
                    {!! UserPanel::user_account_wallet() !!}
                </div>
            </div>
        </div>
        @if(get_page('home_top', 'status') == 'active')
        <div class="col-12 col-lg-7 col-sm-12">
            {!! UserPanel::content_block('welcome', ['image' => 'welcome.png', 'class' => 'card-full-height']) !!}
        </div>
        <div class="col-12 col-lg-5 col-sm-12">
            {!! UserPanel::token_sales_progress('',  ['class' => 'card-full-height']) !!}
        </div>
        @endif
		<input type="hidden" id="checknum" name="checknum" value="{!! $num !!}" />
		<input type="hidden" id="checkonoff" name="checkonoff" value="{!! $checkonoff->notifications !!}" />
		<input type="hidden" id="checkonoff" name="day" value="{!! $day !!}" />
    </div>
</div>
@endsection
@section('scripts')
        
		


@show