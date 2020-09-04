<?php

namespace App\Http\Controllers\Auth;
/**
 * Register Controller
 *
 * @package TokenLite
 * @author Softnio
 * @version 1.1.2
 */

use Cookie;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Referral;
use App\Models\UserMeta;
use App\Helpers\ReCaptcha;
use App\Helpers\IcoHandler;
use Illuminate\Http\Request;
use App\Notifications\ConfirmEmail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Libs\Hierarchy;
use App\Contracts\Contract as ContractInterface;
use App\Services\Sale as SaleServices;
use App\Libs\Sale as Salelibs;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers, ReCaptcha;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     * @version 1.0.0
     */
    protected $redirectTo = '/register/success';

    /**
     * Create a new controller instance.
     *
     * @version 1.0.0
     * @return void
     */
    protected $handler;
    public function __construct(IcoHandler $handler)
    {
        $this->handler = $handler;
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        if (application_installed(true) == false) {
            return redirect(url('/install'));
        }
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        if(recaptcha()) {
            $this->checkReCaptcha($request->recaptcha);
        }
        $have_user = User::where('role', 'admin')->count();
        /**Check Template Rex*/
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user) ? : redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @version 1.0.1
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $term = get_page('terms', 'status') == 'active' ? 'required' : 'nullable';
        return Validator::make($data, [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'terms' => [$term],
        ], [
            'terms.required' => __('messages.agree'),
            'email.unique' => 'The email address you have entered is already registered. Did you <a href="' . route('password.request') . '">forget your login</a> information?',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @version 1.2.1
     * @since 1.0.0
     * @return \App\Models\User
     */
    protected function create(array $data)
    { 
		
        $have_user = User::where('role', 'admin')->count();
        $type = ($have_user >= 1) ? 'user' : 'admin';
		$type = $data['role']; 
        $email_verified = ($have_user >= 1) ? null : now();
		$input['name'] = strip_tags($data['name']); 
		$input['email'] = $data['email'];
		$input['password'] = Hash::make($data['password']);
		$input['lastLogin'] = date('Y-m-d H:i:s');
		$input['role'] = $type;
		$input['left_node'] = 1;
		$input['right_node'] = 2;
		$result = User::create($input);
		//$user = User::find($result->id);
        // $user = User::create([
            // 'name' => strip_tags($data['name']),
            // 'email' => $data['email'],
            // 'password' => Hash::make($data['password']),
            // 'lastLogin' => date('Y-m-d H:i:s'),
            // 'role' => $type,
            // 'left_node' => 1,
            // 'right_node' => 2
        // ]);*/
        if ($result) {
            if ($have_user <= 0) {
                save_gmeta('site_super_admin', 1, $result->id);
            }
            
            $refer_blank = true;
            if(is_active_referral_system()) { 
                if (Cookie::has('ico_nio_ref_by')) {
                    $ref_id = Cookie::get('ico_nio_ref_by');
                    $ref_user = User::where('id', $ref_id)->where('email_verified_at', '!=', null)->first();
                    if ($ref_user) {
						$upadteuser = User::where('id',$result->id)->first();
                        $upadteuser->referral = $ref_id;
                        $upadteuser->referralInfo = json_encode([
                            'user' => $ref_user->id,
                            'name' => $ref_user->name,
                            'time' => now()
                        ]);
                        
						$upadteuser->level = $ref_user->level + 1;
						$upadteuser->grand_parent = $ref_user->referral;
						try {
							$upadteuser->save();
						} catch (\Exception $e) {
							echo '<pre>';print_r($e->getMessage());exit('jhbjh');
						}
						$upadteuser->save();
                        $this->create_referral_or_not($result->id, $ref_user->id);
						$refer_blank = false;
                        //Cookie::queue(Cookie::forget('ico_nio_ref_by'));
                    }
                }
            }
            if($result->role=='user' && $refer_blank==true) {
                $this->create_referral_or_not($result->id);
            }
            
           
			$user = User::find($result->id);
			$hierarchy = new Hierarchy();
			try {
				$hierarchy->addChildNode($user->referral, $user);
			} catch (\Exception $e) {
				echo '<pre>';print_r($e->getMessage());exit('jhbjh');
			}
			
            $meta = UserMeta::create([ 'userId' => $user->id ]);

            $meta->notify_admin = ($type=='user')?0:1;
            $meta->email_token = str_random(65);
            $cd = Carbon::now(); 
            $meta->email_expire = $cd->copy()->addMinutes(75);
            $meta->save();

            if ($user->email_verified_at == null) {
                try {
                    $user->notify(new ConfirmEmail($user));
                } catch (\Exception $e) {
                    session('warning', 'User registered successfully, but we unable to send confirmation email!');
                }
            }
        }
        return $user;
    }

    /**
     * Create user in referral table.
     *
     * @param  $user, $refer
     * @version 1.0
     * @since 1.1.2
     * @return \App\Models\User
     */
    protected function create_referral_or_not($user, $refer=0) {
        Referral::create([ 'user_id' => $user, 'user_bonus' => 0, 'refer_by' => $refer, 'refer_bonus' => 0 ]);
    }
}
