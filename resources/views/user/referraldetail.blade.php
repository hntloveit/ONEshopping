@extends('layouts.user')
@section('title', ucfirst($page->title))
@php
($has_sidebar = true)
@endphp

@section('content')
<div class="content-area content-area-mh card user-account-pages page-{{ $page->slug }}">
    <div class="card-innr">
        <div class="card-head">
            <h2 class="card-title card-title-lg">{{__(replace_shortcode($page->title))}}</h2>
            @if($page->meta_description!=null)
            <p class="large">{{ replace_shortcode($page->meta_description) }}</p>
            @endif
        </div>
        @if(!empty($page->description))
        <div class="card-text">
            {!! replace_shortcode(auto_p($page->description)) !!}
        </div>
        @endif

        <div class="gaps-1x"></div>
        <div class="referral-form">
            <h4 class="card-title card-title-sm">{{ __('Referral URL') }}</h4>
        @if($user->level < 18)    
			<div class="copy-wrap mgb-1-5x mgt-1-5x">
                <span class="copy-feedback"></span>
                <em class="copy-icon fas fa-link"></em>
                <input type="text" class="copy-address" value="{{ route('public.referral').'?ref='.set_id(auth()->id().'&refme='.auth()->id()) }}" disabled>
                <button class="copy-trigger copy-clipboard" data-clipboard-text="{{ route('public.referral').'?ref='.set_id(auth()->id()) }}"><em class="ti ti-files"></em></button>
            </div>
            <p class="text-light mgmt-1x"><em><small>{{ __('Use above link to refer your friend and get referral bonus.') }}</small></em></p>
        @else
			<div class="copy-wrap mgb-1-5x mgt-1-5x">
			<p class="text-light mgmt-1x"><em><small>You are the smallest level</small></em></p>
			</div>
		@endif
		</div>
        <div class="sap sap-gap"></div>
        <div class="card-head">
            <h4 class="card-title card-title-sm">{{ __('Referral Lists') }}</h4>
        </div>
		<div class="portlet-body">
			<div class="table-scrollable">
			   <div id="tree-data"></div>
			</div>
		</div>
		<div class="sap sap-gap"></div>
		<div class="card-head">
            <h4 class="card-title card-title-sm">{{ __('Referral Info') }}</h4>
        </div>
		<div class="portlet-body">
			<div class="table-scrollable">
			   <p>Name: {{$user->name}}</p>
			   <p>Email: {{$user->email}}</p>
			   <p>Mobile: {{$user->mobile}}</p>
			</div>
		</div>
        <div class="sap sap-gap"></div>
		<div class="card-head">
            <h4 class="card-title card-title-sm">{{ __('Referral History') }}</h4>
        </div>
		<div class="portlet-body">
			<ul class="nav nav-tabs nav-tabs-line" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" data-toggle="tab" href="#tab_transactions">Transactions</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#tab_send">Send ONE</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#tab_receive">Receive ONE</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" data-toggle="tab" href="#tab_commission">Commission</a>
				</li>
			</ul>
			<div class="tab-content" id="website-setting">
                 <div class="tab-pane fade show active " id="tab_transactions">
					<table class="data-table dt-filter-init user-tnx">
						<thead>
							<tr class="data-item data-head">
								<th class="data-col tnx-status dt-tnxno">{{ __('Tranx NO') }}</th>
								<th class="data-col dt-token">{{ __('Tokens') }}</th>
								<th class="data-col dt-amount">{{ __('Amount') }}</th>
								<th class="data-col dt-base-amount">{{ base_currency(true) }} {{ __('Amount') }}</th>
								<th class="data-col dt-account">{{ __('To') }}</th>
								<th class="data-col dt-type tnx-type"><div class="dt-type-text">{{ __('Type') }}</div></th>
								<th class="data-col"></th>
							</tr>
						</thead>
						<tbody>
							@foreach($trnxs as $trnx)
							@php 
								$text_danger = ( $trnx->tnx_type=='refund' || ($trnx->tnx_type=='transfer' && $trnx->extra=='sent') ) ? ' text-danger' : '';
							@endphp
							<tr class="data-item tnx-item-{{ $trnx->id }}">
								<td class="data-col dt-tnxno">
									<div class="d-flex align-items-center">
										<div class="data-state data-state-{{ str_replace(['progress','canceled'], ['pending','canceled'], __status($trnx->status, 'icon')) }}">
											<span class="d-none">{{ ($trnx->status=='onhold') ? ucfirst('pending') : ucfirst($trnx->status) }}</span>
										</div>
										<div class="fake-class">
											<span class="lead tnx-id">{{ $trnx->tnx_id }}</span>
											<span class="sub sub-date">{{_date($trnx->tnx_time)}}</span>
										</div>
									</div>
								</td>
								<td class="data-col dt-token">
									<span class="lead token-amount{{ $text_danger }}">{{ (starts_with($trnx->total_tokens, '-') ? '' : '+').$trnx->total_tokens }}</span>
									<span class="sub sub-symbol">{{ token_symbol() }}</span>
								</td>
								<td class="data-col dt-amount{{ $text_danger }}">
									@if ($trnx->tnx_type=='referral'||$trnx->tnx_type=='bonus') 
										<span class="lead amount-pay">{{ '~' }}</span>
									@else 
									<span class="lead amount-pay{{ $text_danger }}">{{ to_num($trnx->amount, 'max') }}</span>
									<span class="sub sub-symbol">{{ strtoupper($trnx->currency) }} <em class="fas fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="1 {{ token('symbol') }} = {{ to_num($trnx->currency_rate, 'max').' '.strtoupper($trnx->currency) }}"></em></span>
									@endif
								</td>
								<td class="data-col dt-usd-amount">
									@if ($trnx->tnx_type=='referral'||$trnx->tnx_type=='bonus') 
										<span class="lead amount-pay">{{ '~' }}</span>
									@else 
									<span class="lead amount-pay{{ $text_danger }}">{{ to_num($trnx->base_amount, 'auto') }}</span>
									<span class="sub sub-symbol">{{ base_currency(true) }} <em class="fas fa-info-circle" data-toggle="tooltip" data-placement="bottom" title="1 {{ token('symbol') }} = {{ to_num($trnx->base_currency_rate, 'max').' '.base_currency(true) }}"></em></span>
									@endif
								</td>
								<td class="data-col dt-account">
									@php 
									$pay_to = ($trnx->payment_method=='system') ? '~' : ( ($trnx->payment_method=='bank') ? explode(',', $trnx->payment_to) : show_str($trnx->payment_to) );
									$extra = ($trnx->tnx_type == 'refund') ? (is_json($trnx->extra, true) ?? $trnx->extra) : '';
									@endphp
									@if($trnx->tnx_type == 'refund')
										<span class="sub sub-info">{{ $trnx->details }}</span>
										@if($extra->trnx)
										<span class="sub sub-view"><a href="javascript:void(0)" class="view-transaction" data-id="{{ $extra->trnx }}">View Transaction</a></span>
										@endif
									@else
										@if($trnx->refund != null)
										<span class="sub sub-info text-danger">{{ __('Refunded #:orderid', ['orderid' => set_id($trnx->refund, 'refund')]) }}</span>
										@else
										<span class="lead user-info">{{ ($trnx->payment_method=='bank') ? $pay_to[0] : ( ($pay_to) ? $pay_to : '~' ) }}</span>
										@endif
										<span class="sub sub-date">{{ ($trnx->checked_time) ? _date($trnx->checked_time) : _date($trnx->created_at) }}</span>
									@endif
								</td>
								<td class="data-col dt-type">
									<span class="dt-type-md badge badge-outline badge-md badge-{{ __(__status($trnx->tnx_type,'status')) }}">{{ ucfirst($trnx->tnx_type) }}</span>
									<span class="dt-type-sm badge badge-sq badge-outline badge-md badge-{{ __(__status($trnx->tnx_type, 'status')) }}">{{ ucfirst(substr($trnx->tnx_type, 0,1)) }}</span>
								</td>
								<td class="data-col text-right">
									@if($trnx->status == 'pending' || $trnx->status == 'onhold')
										@if($trnx->tnx_type != 'transfer')
										<div class="relative d-inline-block d-md-none">
											<a href="#" class="btn btn-light-alt btn-xs btn-icon toggle-tigger"><em class="ti ti-more-alt"></em></a>
											<div class="toggle-class dropdown-content dropdown-content-center-left pd-2x">
												<ul class="data-action-list">
													<li><a href="javascript:void(0)" class="btn btn-auto btn-primary btn-xs view-transaction" data-id="{{ $trnx->id }}"><span>{{__('Pay')}}</span><em class="ti ti-wallet"></em></a></li>
													@if($trnx->checked_time != NUll)
													<li><a href="{{ route('user.ajax.transactions.delete', $trnx->id) }}" class="btn btn-danger-alt btn-xs btn-icon user_tnx_trash" data-tnx_id="{{ $trnx->id }}"><em class="ti ti-trash"></em></a></li>
													@endif
												</ul>
											</div>
										</div>

										<ul class="data-action-list d-none d-md-inline-flex">
											<li><a href="javascript:void(0)" class="btn btn-auto btn-primary btn-xs view-transaction" data-id="{{ $trnx->id }}"><span>{{__('Pay')}}</span><em class="ti ti-wallet"></em></a></li>
											@if($trnx->checked_time != NUll)
											<li><a href="{{ route('user.ajax.transactions.delete', $trnx->id) }}" class="btn btn-danger-alt btn-xs btn-icon user_tnx_trash" data-tnx_id="{{ $trnx->id }}"><em class="ti ti-trash"></em></a></li>
											@endif
										</ul>
										@else 
											<a href="javascript:void(0)" class="view-transaction btn btn-light-alt btn-xs btn-icon" data-id="{{ $trnx->id }}"><em class="ti ti-eye"></em></a>
										@endif
									@else
									<a href="javascript:void(0)" class="view-transaction btn btn-light-alt btn-xs btn-icon" data-id="{{ $trnx->id }}"><em class="ti ti-eye"></em></a>
										@if($trnx->checked_time == NUll && ($trnx->status == 'rejected' || $trnx->status == 'canceled'))
										<a href="{{ route('user.ajax.transactions.delete', $trnx->id) }}" class="btn btn-danger-alt btn-xs btn-icon user_tnx_trash" data-tnx_id="{{ $trnx->id }}"><em class="ti ti-trash"></em></a>
										@endif
									@endif
								</td>
							</tr>{{-- .data-item --}}
							@endforeach
						</tbody>
					</table>
				 </div>
				 <div class="tab-pane fade show " id="tab_send">
					<table class="data-table dt-filter-init user-tnx">
						<thead>
							<tr class="data-item data-head">
								<th class="data-col tnx-status dt-tnxno">{{ __('Send NO') }}</th>
								<th class="data-col dt-token">{{ __('Send to') }}</th>
								<th class="data-col dt-amount">{{ __('ONE Amount') }}</th>
								<th class="data-col dt-account">{{ __('Date') }}</th>
								<th class="data-col"></th>
							</tr>
						</thead>
						<tbody>
							@foreach($send_one as $item)
							
							<tr class="data-item tnx-item-{{ $item->id }}">
								<td class="data-col dt-tnxno">
									<div class="d-flex align-items-center">
										<div class="data-state data-state-">
											<span class="d-none">{{ ($item->code=='') ? ucfirst('pending') : ucfirst($item->code) }}</span>
										</div>
										<div class="fake-class">
											<span class="lead tnx-id">SEND00{{ $item->id }}</span>
										</div>
									</div>
								</td>
								<td class="data-col dt-token">
									<span class="lead token-amount">{{ $item->name }}</span>
									<span class="sub sub-symbol">{{ $item->email,$item->mobile }}</span>
								</td>
								<td class="data-col dt-amount">
									<span class="lead token-amount">{{ $item->one }}</span>
									<span class="sub sub-symbol">ONE</span>
								</td>
								
								<td class="data-col dt-type">
									<span class="lead token-amount">{{ _date($item->updated_at) }}</span>
								</td>
								<td class="data-col text-right">
									
								</td>
							</tr>{{-- .data-item --}}
							@endforeach
						</tbody>
					</table>
				 </div>
				 <div class="tab-pane fade show " id="tab_receive">
					<table class="data-table dt-filter-init user-tnx">
						<thead>
							<tr class="data-item data-head">
								<th class="data-col tnx-status dt-tnxno">{{ __('Receive NO') }}</th>
								<th class="data-col dt-token">{{ __('From user') }}</th>
								<th class="data-col dt-amount">{{ __('ONE Amount') }}</th>
								<th class="data-col dt-account">{{ __('Date') }}</th>
								<th class="data-col"></th>
							</tr>
						</thead>
						<tbody>
							@foreach($receive_one as $item)
							
							<tr class="data-item tnx-item-{{ $item->id }}">
								<td class="data-col dt-tnxno">
									<div class="d-flex align-items-center">
										<div class="data-state data-state-approved">
											<span class="d-none">{{ ($item->code=='') ? ucfirst('pending') : ucfirst($item->code) }}</span>
										</div>
										<div class="fake-class">
											<span class="lead tnx-id">SEND00{{ $item->id }}</span>
										</div>
									</div>
								</td>
								<td class="data-col dt-token">
									<span class="lead token-amount">{{ $item->name }}</span>
									<span class="sub sub-symbol">{{ $item->email,$item->mobile }}</span>
								</td>
								<td class="data-col dt-amount">
									<span class="lead token-amount">{{ $item->one }}</span>
									<span class="sub sub-symbol">ONE</span>
								</td>
								
								<td class="data-col dt-type">
									<span class="lead token-amount">{{ _date($item->updated_at) }}</span>
								</td>
								<td class="data-col text-right">
									
								</td>
							</tr>{{-- .data-item --}}
							@endforeach
						</tbody>
					</table>
				 </div>
				 <div class="tab-pane fade show " id="tab_commission">
					<table class="data-table dt-filter-init user-tnx">
						<thead>
							<tr class="data-item data-head">
								<th class="data-col tnx-status dt-tnxno">{{ __('Commission NO') }}</th>
								<th class="data-col dt-token">{{ __('From user') }}</th>
								<th class="data-col dt-amount">{{ __('POINT Amount') }}</th>
								<th class="data-col dt-account">{{ __('Date') }}</th>
								<th class="data-col"></th>
							</tr>
						</thead>
						<tbody>
							@foreach($commission as $item)
							
							<tr class="data-item tnx-item-{{ $item->id }}">
								<td class="data-col dt-tnxno">
									<div class="d-flex align-items-center">
										<div class="data-state data-state-approved">
											<span class="d-none">{{ ($item->code=='') ? ucfirst('pending') : ucfirst($item->code) }}</span>
										</div>
										<div class="fake-class">
											<span class="lead tnx-id">SEND00{{ $item->id }}</span>
										</div>
									</div>
								</td>
								<td class="data-col dt-token">
									<span class="lead token-amount">{{ $item->name }}</span>
									<span class="sub sub-symbol">{{ $item->email,$item->mobile }}</span>
								</td>
								<td class="data-col dt-amount">
									<span class="lead token-amount">{{ $item->point }}</span>
									<span class="sub sub-symbol">POINT</span>
								</td>
								
								<td class="data-col dt-type">
									<span class="lead token-amount">{{ _date($item->updated_at) }}</span>
								</td>
								<td class="data-col text-right">
									
								</td>
							</tr>{{-- .data-item --}}
							@endforeach
						</tbody>
					</table>
				 </div>
			</div>
		</div>
    </div>
</div>
@endsection
@push('footer')
<link href="{{asset('assets/css/styles.css')}}" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="{{asset('css/style.min.css')}}" type="text/css">
    <script src="{{asset('assets/js/jquery.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/jstree.js')}}"></script>
	
    <script>
		$(document).on("click", ".a_click", function() {
			var on = $(this).attr('href');
			window.location.href = on;
		});
        jQuery(document).ready(function(){
			
			
			var ajaxResponse = '';
			jQuery.ajax({
				url 	: '{!! action('User\UserController@getAjaxTreeID',$refid) !!}',
				async  : false,
				success : function(response)
				{
					ajaxResponse = response;
				}
			});
			// render js tree*/
			var tree = jQuery("#tree-data");
			tree.html(ajaxResponse);
			tree.jstree({	
				 "checkbox" : {
				  "keep_selected_style" : false
				},
				"plugins" : [ "checkbox" ]
			});
			tree.jstree(true).open_all();
			jQuery('li[data-checkstate="checked"]').each(function() {
				tree.jstree('check_node', $(this));
			});
			tree.jstree(true).close_all();
    
		});
    </script>
@endpush