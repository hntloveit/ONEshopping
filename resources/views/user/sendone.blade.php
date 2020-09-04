@extends('layouts.user')
@section('title', __('Purchase Token'))

@section('content')
@php
$content_class = 'col-lg-8';
@endphp

@include('layouts.messages')

<div class="newbie hide alert alert-dismissible fade alert-warning" role="alert">
    <a href="javascript:void(0)" class="close" data-dismiss="alert" aria-label="close">&nbsp;</a>
    <span>&nbsp;{!! session('status') !!}</span
></div>

<div class="content-area card">
    <div class="card-innr">
       <form id="nio-user-2fa" action="" method="" class="token-purchase validate-modern" novalidate="novalidate">
            @csrf
			<div class="card-head">
                <h4 class="card-title">
                Receiver's information
                </h4>
            </div>
            <div class="row">
				<div class="col-sm-6">
					<div class="input-item input-with-label">
						<label class="input-item-label">Receiver's email</label>
						<div class="input-wrap">
							<input required="required" data-validation="required" class="input-bordered" required="" type="email" data-validation="required" name="email" value="">
						</div>
						
					</div>
				</div>
				<div class=" col-sm-6">
					<div class="input-item input-with-label">
						<label class="input-item-label">The last 4 numbers of the recipient's phone</label>
						<div class="input-wrap">
							<input required="required" aria-required="true" class="input-bordered" minlength="4"  maxlength="4" min="1000" max="9999" required="" type="number" data-validation="required" name="4phone" value="">
						</div>

					</div>
				</div>
				<div class="col-sm-6">
					<div class="input-item input-with-label">
						<label class="input-item-label">The number of ONE will send</label>
						<div class="input-wrap">
							<input required="required" data-validation="required" max="{!! $maxone !!}" min="1" class="input-bordered valid" required="" type="number" data-validation="required" name="sendone" value="" aria-required="true" aria-invalid="false" aria-describedby="numpercent-error">
						</div>

					</div>
				</div>
			</div>

			<div class="d-sm-flex justify-content-between align-items-center">
				<span id="form1" type="" class="btn btn-primary">Send</span>
				<div class="gaps-2x d-sm-none"></div>
			</div>
        </form>
		<form id="confirm" action="{{ route('user.ajax.confirmsendone') }}" method="post" class="hide token-purchase validate-modern" novalidate="novalidate">
			@csrf
			<div class="card-head">
                <h4 class="card-title">
                Receiver's information
                </h4>
            </div>
            <div class="row">
				<div class="col-sm-6">
					<div class="input-item input-with-label">
						<label class="input-item-label">Confirm code from email</label>
						<div class="input-wrap">
							<input required="required" data-validation="required" class="input-bordered" type="text" data-validation="required" name="code" value="">
						</div>
						
					</div>
				</div>
				
			</div>
			<div class="d-sm-flex justify-content-between align-items-center">
				<button type="submit" class="btn btn-primary">Confirm</button>
				<div class="gaps-2x d-sm-none"></div>
			</div>
		</form>
    </div> {{-- .card-innr --}}
</div> {{-- .content-area --}}
@push('sidebar')
<div class="aside sidebar-right col-lg-4">
  
    {!! UserPanel::user_balance_card($contribution, ['vers' => 'side']) !!}
    <div class="token-sales card">
        <div class="card-innr">
            <div class="card-head">
                <h5 class="card-title card-title-sm">{{__('Token Sales')}}</h5>
            </div>
            <div class="token-rate-wrap row">
                <div class="token-rate col-md-6 col-lg-12">
                    <span class="card-sub-title">{{ $symbol }} {{__('Token Price')}}</span>
                    <h4 class="font-mid text-dark">1 {{ $symbol }} = <span>{{ to_num($token_prices->$bc, 'max') .' '. base_currency(true) }}</span></h4>
                </div>
                <div class="token-rate col-md-6 col-lg-12">
                    <span class="card-sub-title">{{__('Exchange Rate')}}</span>
                    @php
                    $exrpm = collect($pm_currency);
                    $exrpm = $exrpm->forget(base_currency())->take(2);
                    $exc_rate = '<span>1 '.base_currency(true) .' ';
                    foreach ($exrpm as $cur => $name) {
                        if($cur != base_currency() && get_exc_rate($cur) != '') {
                            $exc_rate .= ' = '.to_num(get_exc_rate($cur), 'max') . ' ' . strtoupper($cur);
                        }
                    }
                    $exc_rate .= '</span>';
                    @endphp
                    {!! $exc_rate !!}
                </div>
            </div>
            @if(!empty($active_bonus))
            <div class="token-bonus-current">
                <div class="fake-class">
                    <span class="card-sub-title">{{__('Current Bonus')}}</span>
                    <div class="h3 mb-0">{{ $active_bonus->amount }} %</div>
                </div>
                <div class="token-bonus-date">{{__('End at')}}<br>{{ _date($active_bonus->end_date, get_setting('site_date_format')) }}</div>
            </div>
            @endif
        </div>
    </div>
    {!! UserPanel::token_sales_progress('',  ['class' => 'mb-0']) !!}
</div>{{-- .col.aside --}}
@endpush
@endsection
@section('modals')
<div class="modal fade modal-payment" id="payment-modal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-md modal-dialog-centered">
        <div class="modal-content"></div>
    </div>
</div>
@endsection

@push('footer')
<script type="text/javascript">

    (function($){
        var $nio_user_2fa = $('#nio-user-2fa');
        if ($nio_user_2fa.length > 0) {
            //ajax_form_submit($nio_user_2fa);
			jQuery( "#form1" ).click(function() {	
				jQuery.ajax({
				  url: '{!! action('User\UserController@sendone') !!}',
				  type: 'post',
				  dataType: "json",
				  data: jQuery('#nio-user-2fa').serialize(),
				  success: function(response){
					  
					  if(response.key == "next"){
						  jQuery('#nio-user-2fa').hide();
						  jQuery('#confirm').show();
						  jQuery('.newbie').addClass('show');
						  jQuery('.newbie').html(response.status);
					  }else{
						  jQuery('.newbie').html(response.status);
						  jQuery('.newbie').addClass('show');
						  console.log('success', response.status);
						 
					  }
				  }
			  });
		  });
        }
		var $confirm = $('#confirm');
        if ($confirm.length > 0) {
            ajax_form_submit($confirm);
			
        }
    })(jQuery);
	
</script>
@endpush