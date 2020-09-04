@extends('layouts.admin')
@section('title', 'POINT - ONE Settings')
@section('content')
<div class="page-content">
    <div class="container">
        <div class="row">
            <div class="main-content col-lg-12">
                @include('layouts.messages')
                @include('vendor.notice')
                <div class="content-area card">
                    <div class="card-innr">
                        <div class="card-head">
                            <h4 class="card-title">POINT - ONE Settings</h4>
                        </div>
                        <ul class="nav nav-tabs nav-tabs-line" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab_point">POINT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab_one">ONE</a>
                            </li>

                        </ul>{{-- .nav-tabs-line --}}

                        <div class="tab-content" id="website-setting">
                            <div class="tab-pane fade show active " id="tab_point">
                                <form action="{{ route('admin.ajax.settings.update') }}" class="validate-modern" method="POST" id="update_settings">
                                    @csrf
                                    <input type="hidden" name="type" value="point">
                                    <div class="d-flex align-items-center justify-content-between pdb-1x">
                                        <h5 class="card-title-md text-primary">ONE(original) -> POINT</h5>
									</div>
										<p>ONE(original) will be multiply with this number -> POINT</p>
                                    
                                    <div class="row">
                                        <div class="col-xl-4 col-md-6 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">The first time</label>
                                                <div class="input-wrap">
                                                    <input class="input-bordered" required="" type="number" data-validation="required" name="num1" value="{{ get_setting('num1') }}">
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-6 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">The second time</label>
                                                <div class="input-wrap">
                                                    <input class="input-bordered" required="" type="number" data-validation="required" name="num2" value="{{ get_setting('num2') }}">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-6 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">The third time</label>
                                                <input class="input-bordered" required="" type="number" data-validation="required" name="num3" value="{{ get_setting('num3') }}">
                                            </div>
                                        </div>

                                        <div class="col-xl-4 col-md-6 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">The forth time</label>
                                                <input class="input-bordered" required="" type="number" data-validation="required" name="num4" value="{{ get_setting('num4') }}">
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-6 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">The fifth time</label>
                                                <div class="input-wrap">
                                                    <input class="input-bordered" required="" type="number" data-validation="required" name="num5" value="{{ get_setting('num5') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-6 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">The sixth time</label>
                                                <input class="input-bordered" required="" type="number" data-validation="required" name="num6" value="{{ get_setting('num6') }}">
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="gaps-1x"></div>
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary save-disabled" disabled><i class="ti ti-reload mr-2"></i>Update</button>
                                    </div>
                                    <div class="gaps-0-5x"></div>
                                </form>
                            </div>
                            <div class="tab-pane fade " id="tab_one">
                                <form action="{{ route('admin.ajax.settings.update') }}" class="validate-modern" method="post" id="update_general_settings">
                                    @csrf
                                    <input type="hidden" name="type" value="one">
                                    <div class="d-flex align-items-center justify-content-between pdb-1x">
                                        <h5 class="card-title-md text-primary">POINT -> ONE</h5>
                                    </div>
									<p>POINT will be multiply with this number -> ONE(the end)</p>
                                    <div class="row">
                                        <div class="col-xl-6 col-md-12 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">The number will be multiply with POINT</label>
                                                <div class="input-wrap">
                                                    <input class="input-bordered" required="" type="number" data-validation="required" name="numpercent" value="{{ get_setting('numpercent') }}">
                                                </div>
                                                <span class="input-note">Enter the number of percent.</span>
                                            </div>
                                        </div>
										<div class="col-xl-6 col-md-12 col-sm-12">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">ON/OFF ONE transfer feature.</label>
                                                <div class="input-wrap">
                                                    <input name="on_offonetransfer" class="input-switch input-switch-sm" type="checkbox" {{ get_setting('on_offonetransfer')==1 ? 'checked ' : '' }} id="onetransfer">
													<label for="onetransfer"><span>OFF</span><span class="over">ON</span></label>
											   </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary save-disabled" disabled><i class="ti ti-reload mr-2"></i>Update</button>
                                    </div>
                                    <div class="gaps-0-5x"></div>
                                </form>
                            </div>
                            
                            
                            
                        </div>
                    </div>{{-- .card-innr --}}
                </div>{{-- .card --}}
            </div>{{-- .col --}}
        </div>{{-- .container --}}
    </div>{{-- .container --}}
</div>{{-- .page-content --}}
@endsection