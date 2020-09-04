<?php

namespace App\Http\Controllers\User;

/**
 * User Controller
 *
 *
 * @package TokenLite
 * @author Softnio
 * @version 1.0.6
 */
use Auth;
use Validator;
use IcoHandler;
use Carbon\Carbon;
use App\Models\Page;
use App\Models\User;
use App\Models\IcoStage;
use App\Models\UserMeta;
use App\Models\Onepoint;
use App\Models\Sendone;
use App\Models\Setting;
use App\Models\Activity;
use App\Helpers\NioModule;
use App\Models\GlobalMeta;
use App\Models\Transaction;
use App\Models\Commission;
use App\Notifications\ConfirmOneEmail;
use Mail;
use App\Mail\EmailToUserOne;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use App\Notifications\PasswordChange;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Helpers\TokenCalculate as TC;
use App\Contracts\Sale as SaleInterface;
use App\Services\Sale as SaleServices;
use App\Contracts\Contract as ContractInterface;

class UserController extends Controller
{
    protected $handler;
    public function __construct(IcoHandler $handler)
    {
        $this->middleware('auth');
        $this->handler = $handler;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     * @version 1.0.0
     * @since 1.0
     * @return void
     */
    public function index()
    {
        /**Check Template Rex*/
        $user = Auth::user();
        $stage = active_stage();
        $contribution = Transaction::user_contribution();
        $tc = new \App\Helpers\TokenCalculate();
		$checknum = Onepoint::where('user',$user->id)->first();
		if($checknum == ''){
			$point = 0;
			$one = 0;
			$num = 0;
		}else{
			$point = $checknum->point;
			$one = $checknum->oneout;
			$num = $checknum->num;
		}
		$first_date = strtotime(DATE('Y-m-d H:i:s'));
			$second_date = strtotime($user->created_at);
			$datediff = abs($first_date - $second_date);
			$day = floor($datediff / (60*60*24));
		$checkonoff = UserMeta::where('userId',$user->id)->first();
        $active_bonus = $tc->get_current_bonus('active');

        return view('user.dashboard', compact('user', 'stage', 'active_bonus', 'contribution','checknum','checkonoff','point','one','num', 'day'));
    }
	public function sendonetouser(){
		$contribution = Transaction::user_contribution();
		$symbol = token_symbol();
		$tc = new TC();
		$bc = base_currency();
		$token_prices = $tc->calc_token(1, 'price');
		$pm_currency = PaymentMethod::Currency;
		$user = Auth::user();
		$myone = Onepoint::where('user',$user->id)->first();
		$maxone = 0;
		if($myone != '') $maxone = $myone->oneout;
        return view(
            'user.sendone',
            compact( 'maxone', 'contribution','symbol','token_prices','bc','pm_currency')
        );
	}
	public function confirmsendone(Request $request){
		
		$ret['msg'] = '';
		$ret['message'] = '';
		$ret['status'] = ''; 
		$code = Sendone::where('code',$request->input('code'))->orderBy('created_at','desc')->first();
		$user = Auth::user();
		$my = Onepoint::where('user',$user->id)->first();
		if($code != ''){
			$sendone = $code->one;
			$userone = Onepoint::where('user',$code->touser)->first();
			if($userone != ''){
				$userone->oneout = $userone->oneout + $sendone;
				$userone->save();
			}else{
				/* create new */
				$input['onein'] = 0;
				$input['oneout'] = $sendone;
				$input['num'] = 0;
				$input['point'] = 0;
				$input['user'] = $code->touser;
				$result = Onepoint::create($input);
			}
			
			
			$myone = Onepoint::find($my->id);
			$myone->oneout = $myone->oneout - $sendone;
			$myone->save();
			
			$delcode = Sendone::find($code->id);
			$delcode->code = 0;
			$delcode->save();
			$ret['msg'] = 'success';
			$ret['message'] = 'Your request has been successfully!';
			$ret['status'] = 'Your request has been successfully!';
		}else{
			$ret['msg'] = 'warning';
			$ret['message'] = 'Invalid Code';
			$ret['status'] = 'Invalid Code';	
		}
		return response()->json($ret);
	}
	public function sendone(Request $request)
    {
		$user = Auth::user();
		
		$email = $request->input('email');
		$phone = $request->input('4phone');
		$sendone = $request->input('sendone');
		// $email = 'hntloveit@gmail.com';
		// $phone = '7179';
		// $sendone = 100;*/
		
		$my = Onepoint::where('user',$user->id)->first();
		$checkme = User::find($user->id);
		$checkuser = User::where('email',$email)->where('mobile','like','%'.$phone)->first();
		$checksetting = Setting::where('field','on_offonetransfer')->first();
		if($my->oneout == 0){
			$ret['msg'] = 'warning';
			$ret['message'] = 'Not enought ONE';
			$ret['status'] = 'Not enought ONE';
			return response()->json($ret);
		}
		
		
		if ($checkuser !='' && $checkme->sendone_user ==1 && $checksetting->value ==1) {
			
			/*send ONE to user - need OTP here before send ONE */
			/*
			$useron = Onepoint::where('user',$checkuser->id)->first();
			$userone = Onepoint::find($useron->id);
			$userone->oneout = $userone->oneout + $sendone;
			$userone->save();
			
			$myone = Onepoint::find($my->id);
			$myone->oneout = $myone->oneout - $sendone;
			$myone->save();*/
			/*send mail*/
			
			$input['fromuser'] = $user->id;
			$input['touser'] = $checkuser->id;
			$input['one'] = $sendone;
			$input['code'] = str_random(6);
			$result = Sendone::create($input);
			$data = (object) [
                    'user' => (object) ['name' => $user->name, 'email' => $user->email],
                    'subject' => 'Code to confirm your send ONE',
                    'greeting' => 'Hello '.$user->name,
                    'text' => 'Please add this code below to confirm  your form request.<br/><br/> <b>'.$input['code'].'</b>',
                ];
			$when = now()->addMinutes(1);
			 if ($result->code != null) {
				 try {
                    Mail::to($user->email)
                    ->later($when, new EmailToUserOne($data));
                    $ret['msg'] = 'success';
					$ret['message'] = 'Send ONE to user success!';
					$ret['key'] = 'next';
					$ret['status'] = 'Please check your email to enter the code to confirm!';
                } catch (\Exception $e) {
                    $ret['errors'] = $e->getMessage();
                    $ret['msg'] = 'warning';
                    $ret['message'] = __('messages.mail.issues');
					$ret['status'] = $e->getMessage();
                }
				return response()->json($ret);
               
            }
			
		} else {
			$ret['msg'] = 'warning';
			$ret['message'] = 'Wrong email or Recipient information is incomplete, it needs additional';
			$ret['status'] = 'Wrong email or Recipient information is incomplete, it needs additional';
		}
		if($checkme->sendone_user ==0 || $checksetting->value ==0){
			$ret['msg'] = 'warning';
			$ret['message'] = 'The admin has turned off this feature';
			$ret['status'] = 'The admin has turned off this feature';
		}
		if(empty($email) || empty($phone) || empty($sendone)){
			$ret['msg'] = 'warning';
			$ret['message'] = 'Empty data';
			$ret['status'] = 'Empty data';
		}
		
		return response()->json($ret);
		
    }
	

    /**
     * Show the user account page.
     *
     * @return \Illuminate\Http\Response
     * @version 1.0.0
     * @since 1.0
     * @return void
     */
    public function account()
    {
        $countries = $this->handler->getCountries();
        $user = Auth::user();
        $userMeta = UserMeta::getMeta($user->id);

        $g2fa = new Google2FA();
        $google2fa_secret = $g2fa->generateSecretKey();
        $google2fa = $g2fa->getQRCodeUrl(
            site_info().'-'.$user->name,
            $user->email,
            $google2fa_secret
        );

        return view('user.account', compact('user', 'userMeta','countries', 'google2fa', 'google2fa_secret'));
    }

    /**
     * Show the user account activity page.
     * and Delete Activity
     *
     * @return \Illuminate\Http\Response
     * @version 1.0.0
     * @since 1.0
     * @return void
     */
    public function account_activity()
    {
        $user = Auth::user();
        $activities = Activity::where('user_id', $user->id)->orderBy('created_at', 'DESC')->limit(50)->get();

        return view('user.activity', compact('user', 'activities'));
    }

    /**
     * Show the user account token management page.
     *
     * @return \Illuminate\Http\Response
     * @version 1.0.0
     * @since 1.1.2
     * @return void
     */
    public function mytoken_balance()
    {
        if(gws('user_mytoken_page')!=1) {
            return redirect()->route('user.home');
        }
        $user = Auth::user();
        $token_account = Transaction::user_mytoken('balance');
        $token_stages = Transaction::user_mytoken('stages');
        $user_modules = nio_module()->user_modules();
        return view('user.account-token', compact('user', 'token_account', 'token_stages', 'user_modules'));
    }

    /**
     * Activity delete
     *
     * @version 1.0.0
     * @since 1.0
     * @return void
     */
    public function account_activity_delete(Request $request)
    {
        $id = $request->input('delete_activity');
        $ret['msg'] = 'info';
        $ret['message'] = "Nothing to do!";

        if ($id !== 'all') {
            $remove = Activity::where('id', $id)->where('user_id', Auth::id())->delete();
        } else {
            $remove = Activity::where('user_id', Auth::id())->delete();
        }
        if ($remove) {
            $ret['msg'] = 'success';
            $ret['message'] = __('messages.delete.delete', ['what'=>'Activity']);
        } else {
            $ret['msg'] = 'warning';
            $ret['message'] = __('messages.form.wrong');
        }
        if ($request->ajax()) {
            return response()->json($ret);
        }
        return back()->with([$ret['msg'] => $ret['message']]);
    }

    /**
     * update the user account page.
     *
     * @return \Illuminate\Http\Response
     * @version 1.2
     * @since 1.0
     * @return void
     */
    public function account_update(Request $request)
    {
        $type = $request->input('action_type');
        $ret['msg'] = 'info';
        $ret['message'] = __('messages.nothing');

        if ($type == 'personal_data') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'email' => 'required|email',
                'dateOfBirth' => 'required|date_format:"m/d/Y"'
            ]);

            if ($validator->fails()) {
                $msg = __('messages.form.wrong');
                if ($validator->errors()->hasAny(['name', 'email', 'dateOfBirth'])) {
                    $msg = $validator->errors()->first();
                }

                $ret['msg'] = 'warning';
                $ret['message'] = $msg;
                return response()->json($ret);
            } else {
                $user = User::FindOrFail(Auth::id());
                $user->name = strip_tags($request->input('name'));
                $user->email = $request->input('email');
                $user->mobile = strip_tags($request->input('mobile'));
                $user->dateOfBirth = $request->input('dateOfBirth');
                $user->nationality = strip_tags($request->input('nationality'));
                $user_saved = $user->save();

                if ($user) {
                    $ret['msg'] = 'success';
                    $ret['message'] = __('messages.update.success', ['what' => 'Account']);
                } else {
                    $ret['msg'] = 'warning';
                    $ret['message'] = __('messages.update.warning');
                }
            }
        }
        if ($type == 'wallet') {
            $validator = Validator::make($request->all(), [
                'wallet_name' => 'required',
                'wallet_address' => 'required|min:10'
            ]);

            if ($validator->fails()) {
                $msg = __('messages.form.wrong');
                if ($validator->errors()->hasAny(['wallet_name', 'wallet_address'])) {
                    $msg = $validator->errors()->first();
                }

                $ret['msg'] = 'warning';
                $ret['message'] = $msg;
                return response()->json($ret);
            } else {
                $is_valid = $this->handler->validate_address($request->input('wallet_address'), $request->input('wallet_name'));
                if ($is_valid) {
                    $user = User::FindOrFail(Auth::id());
                    $user->walletType = $request->input('wallet_name');
                    $user->walletAddress = $request->input('wallet_address');
                    $user_saved = $user->save();

                    if ($user) {
                        $ret['msg'] = 'success';
                        $ret['message'] = __('messages.update.success', ['what' => 'Wallet']);
                    } else {
                        $ret['msg'] = 'warning';
                        $ret['message'] = __('messages.update.warning');
                    }
                } else {
                    $ret['msg'] = 'warning';
                    $ret['message'] = __('messages.invalid.address');
                }
            }
        }
        if ($type == 'wallet_request') {
            $validator = Validator::make($request->all(), [
                'wallet_name' => 'required',
                'wallet_address' => 'required|min:10'
            ]);

            if ($validator->fails()) {
                $msg = __('messages.form.wrong');
                if ($validator->errors()->hasAny(['wallet_name', 'wallet_address'])) {
                    $msg = $validator->errors()->first();
                }

                $ret['msg'] = 'warning';
                $ret['message'] = $msg;
                return response()->json($ret);
            } else {
                $is_valid = $this->handler->validate_address($request->input('wallet_address'), $request->input('wallet_name'));
                if ($is_valid) {
                    $meta_data = ['name' => $request->input('wallet_name'), 'address' => $request->input('wallet_address')];
                    $meta_request = GlobalMeta::save_meta('user_wallet_address_change_request', json_encode($meta_data), auth()->id());

                    if ($meta_request) {
                        $ret['msg'] = 'success';
                        $ret['message'] = __('messages.wallet.change');
                    } else {
                        $ret['msg'] = 'warning';
                        $ret['message'] = __('messages.wallet.failed');
                    }
                } else {
                    $ret['msg'] = 'warning';
                    $ret['message'] = __('messages.invalid.address');
                }
            }
        }
        if ($type == 'notification') {
            $notify_admin = $newsletter = $unusual = 0;

            if (isset($request['notify_admin'])) {
                $notify_admin = 1;
            }
            if (isset($request['newsletter'])) {
                $newsletter = 1;
            }
            if (isset($request['unusual'])) {
                $unusual = 1;
            }

            $user = User::FindOrFail(Auth::id());
            if ($user) {
                $userMeta = UserMeta::where('userId', $user->id)->first();
                if ($userMeta == null) {
                    $userMeta = new UserMeta();
                    $userMeta->userId = $user->id;
                }
                $userMeta->notify_admin = $notify_admin;
                $userMeta->newsletter = $newsletter;
                $userMeta->unusual = $unusual;
                $userMeta->save();
                $ret['msg'] = 'success';
                $ret['message'] = __('messages.update.success', ['what' => 'Notification']);
            } else {
                $ret['msg'] = 'warning';
                $ret['message'] = __('messages.update.warning');
            }
        }
        if ($type == 'security') {
            $save_activity = $mail_pwd = 'FALSE';

            if (isset($request['save_activity'])) {
                $save_activity = 'TRUE';
            }
            if (isset($request['mail_pwd'])) {
                $mail_pwd = 'TRUE';
            }

            $user = User::FindOrFail(Auth::id());
            if ($user) {
                $userMeta = UserMeta::where('userId', $user->id)->first();
                if ($userMeta == null) {
                    $userMeta = new UserMeta();
                    $userMeta->userId = $user->id;
                }
                $userMeta->pwd_chng = $mail_pwd;
                $userMeta->save_activity = $save_activity;
                $userMeta->save();
                $ret['msg'] = 'success';
                $ret['message'] = __('messages.update.success', ['what' => 'Security']);
            } else {
                $ret['msg'] = 'warning';
                $ret['message'] = __('messages.update.warning');
            }
        }
        if ($type == 'account_setting') {
            $notify_admin = $newsletter = $unusual = $notifications = 0;
            $save_activity = $mail_pwd = 'FALSE';
            $user = User::FindOrFail(Auth::id());

            if (isset($request['save_activity'])) {
                $save_activity = 'TRUE';
            }
            if (isset($request['mail_pwd'])) {
                $mail_pwd = 'TRUE';
            }

            $mail_pwd = 'TRUE'; //by default true
            if (isset($request['notify_admin'])) {
                $notify_admin = 1;
            }
            if (isset($request['newsletter'])) {
                $newsletter = 1;
            }
            if (isset($request['unusual'])) {
                $unusual = 1;
            }
			if (isset($request['notifications'])) {
                $notifications = 1;
            }


            if ($user) {
                $userMeta = UserMeta::where('userId', $user->id)->first();
                if ($userMeta == null) {
                    $userMeta = new UserMeta();
                    $userMeta->userId = $user->id;
                }

                $userMeta->notify_admin = $notify_admin;
                $userMeta->newsletter = $newsletter;
                $userMeta->unusual = $unusual;
                $userMeta->notifications = $notifications;

                $userMeta->pwd_chng = $mail_pwd;
                $userMeta->save_activity = $save_activity;

                $userMeta->save();
                $ret['msg'] = 'success';
                $ret['message'] = __('messages.update.success', ['what' => 'Account Settings']);
            } else {
                $ret['msg'] = 'warning';
                $ret['message'] = __('messages.update.warning');
            }
        }
        if ($type == 'pwd_change') {
            //validate data
            $validator = Validator::make($request->all(), [
                'old-password' => 'required|min:6',
                'new-password' => 'required|min:6',
                're-password' => 'required|min:6|same:new-password',
            ]);
            if ($validator->fails()) {
                $msg = __('messages.form.wrong');
                if ($validator->errors()->hasAny(['old-password', 'new-password', 're-password'])) {
                    $msg = $validator->errors()->first();
                }

                $ret['msg'] = 'warning';
                $ret['message'] = $msg;
                return response()->json($ret);
            } else {
                $user = Auth::user();
                if ($user) {
                    if (! Hash::check($request->input('old-password'), $user->password)) {
                        $ret['msg'] = 'warning';
                        $ret['message'] = __('messages.password.old_err');
                    } else {
                        $userMeta = UserMeta::where('userId', $user->id)->first();
                        $userMeta->pwd_temp = Hash::make($request->input('new-password'));
                        $cd = Carbon::now();
                        $userMeta->email_expire = $cd->copy()->addMinutes(60);
                        $userMeta->email_token = str_random(65);
                        if ($userMeta->save()) {
                            try {
                                $user->notify(new PasswordChange($user, $userMeta));
                                $ret['msg'] = 'success';
                                $ret['message'] = __('messages.password.changed');
                            } catch (\Exception $e) {
                                $ret['msg'] = 'warning';
                                $ret['message'] = __('messages.email.password_change',['email' => get_setting('site_email')]);
                            }
                        } else {
                            $ret['msg'] = 'warning';
                            $ret['message'] = __('messages.form.wrong');
                        }
                    }
                } else {
                    $ret['msg'] = 'warning';
                    $ret['message'] = __('messages.form.wrong');
                }
            }
        }
        if($type == 'google2fa_setup'){
            $google2fa = $request->input('google2fa', 0);
            $user = User::FindOrFail(Auth::id());
            if($user){
                // Google 2FA
                $ret['link'] = route('user.account');
                if(!empty($request->google2fa_code)){
                    $g2fa = new Google2FA();
                    if($google2fa == 1){
                        $verify = $g2fa->verifyKey($request->google2fa_secret, $request->google2fa_code);
                    }else{
                        $verify = $g2fa->verify($request->google2fa_code, $user->google2fa_secret);
                    }

                    if($verify){
                        $user->google2fa = $google2fa;
                        $user->google2fa_secret = ($google2fa == 1 ? $request->google2fa_secret : null);
                        $user->save();
                        $ret['msg'] = 'success';
                        $ret['message'] = __('Successfully '.($google2fa == 1 ? 'enable' : 'disable').' 2FA security in your account.');
                    }else{
                        $ret['msg'] = 'error';
                        $ret['message'] = __('You have provide a invalid 2FA authentication code!');
                    }
                }else{
                    $ret['msg'] = 'warning';
                    $ret['message'] = __('Please enter a valid authentication code!');
                }
            }
        }

        if ($request->ajax()) {
            return response()->json($ret);
        }
        return back()->with([$ret['msg'] => $ret['message']]);
    }

    public function password_confirm($token)
    {
        $user = Auth::user();
        $userMeta = UserMeta::where('userId', $user->id)->first();
        if ($token == $userMeta->email_token) {
            if (_date($userMeta->email_expire, 'Y-m-d H:i:s') >= date('Y-m-d H:i:s')) {
                $user->password = $userMeta->pwd_temp;
                $user->save();
                $userMeta->pwd_temp = null;
                $userMeta->email_token = null;
                $userMeta->email_expire = null;
                $userMeta->save();

                $ret['msg'] = 'success';
                $ret['message'] = __('messages.password.success');
            } else {
                $ret['msg'] = 'warning';
                $ret['message'] = __('messages.password.failed');
            }
        } else {
            $ret['msg'] = 'warning';
            $ret['message'] = __('messages.password.token');
        }

        return redirect()->route('user.account')->with([$ret['msg'] => $ret['message']]);
    }

    /**
     * Get pay now form
     *
     * @version 1.0.0
     * @since 1.0
     * @return void
     */
    public function get_wallet_form(Request $request)
    {
        return view('modals.user_wallet')->render();
    }

    /**
     * Show the user Referral page
     *
     * @version 1.0.0
     * @since 1.0.3
     * @return void
     */
    public function referral(SaleInterface $saleInterface)
    { 
        $page = Page::where('slug', 'referral')->where('status', 'active')->first();
        $reffered = User::where('referral', auth()->id())->get();
		$user = Auth::user();
		
        if(get_page('referral', 'status') == 'active'){
            return view('user.referral', compact('page', 'reffered', 'user'));
        }else{
            abort(404);
        }
    }
	public function referraldetail($id)
    { 
        $page = Page::where('slug', 'referral')->where('status', 'active')->first();
        $reffered = User::where('referral', $id)->get();
		$user = User::find($id);
		$refid = $id;
		$trnxs = Transaction::where('user', $id)
                    ->where('status', '!=', 'deleted')
                    ->where('status', '!=', 'new')
                    ->whereNotIn('tnx_type', ['withdraw'])
                    ->orderBy('created_at', 'DESC')->get();
        $transfers = Transaction::get_by_own(['tnx_type' => 'transfer'])->get()->count();
        $referrals = Transaction::get_by_own(['tnx_type' => 'referral'])->get()->count();
        $bonuses   = Transaction::get_by_own(['tnx_type' => 'bonus'])->get()->count();
        $refunds   = Transaction::get_by_own(['tnx_type' => 'refund'])->get()->count();
        $has_trnxs = (object) [
            'transfer' => ($transfers > 0) ? true : false,
            'referral' => ($referrals > 0) ? true : false,
            'bonus' => ($bonuses > 0) ? true : false,
            'refund' => ($refunds > 0) ? true : false
        ];
		$send_one = Sendone::where('fromuser',$id) ->leftJoin('users', 'users.id', '=', 'sendone.touser')->get();
		$receive_one = Sendone::where('touser',$id) ->leftJoin('users', 'users.id', '=', 'sendone.fromuser')->get();
		$commission = Commission::where('user',$id) ->leftJoin('users', 'users.id', '=', 'commission.fromuser')->get();
		
        if(get_page('referral', 'status') == 'active'){
            return view('user.referraldetail', compact('page', 'reffered', 'user', 'refid', 'trnxs', 'has_trnxs', 'send_one', 'receive_one', 'commission' ));
        }else{
            abort(404);
        }
    }
	public function getAjaxTree(SaleServices $saleServices)
    { 
		$saleServices->connect();
		global $conn;
		$user = Auth::user();
		$node_id = $user->id;
		//$sql = "SELECT * from users where level > 0"; * this is get all /
		$sql = " SELECT * FROM users";
		$result = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));

			$items = array();
			 while($row = mysqli_fetch_array($result))
				 { $items[$row['id']] = array('id' => $row['id'], 'phone' => $row['mobile'], 'label' => $row['name'], 'referral' => $row['referral'], 'level' => $row['level']);
			 }

		echo  $saleServices->createTreeView($items, $user->id,$user->level);
  
    }
	public function getAjaxTreeID($node_id, SaleServices $saleServices)
    { 
		$saleServices->connect();
		global $conn;
		mysqli_set_charset($conn,"utf8");
		//$sql = "SELECT * from users where level > 0"; * this is get all */
		$sql = "SELECT * FROM users";
		$result = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));

			$items = array();
			 while($row = mysqli_fetch_array($result))
				 { $items[$row['id']] = array('id' => $row['id'], 'phone' => $row['mobile'], 'label' => $row['name'], 'referral' => $row['referral'], 'level' => $row['level']);
			 }
		$user = User::find($node_id);
		echo  $saleServices->createTreeView($items, $user->id);
  
    }
}
