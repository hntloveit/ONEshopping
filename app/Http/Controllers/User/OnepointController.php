<?php

namespace App\Http\Controllers\User;
/**
 * Onepoint Controller
 *
 *
 * @package TokenLite
 * @author Softnio
 * @version 1.0.0
 */
use Auth;
use App\Models\IcoStage;
use App\PayModule\Module;
use App\Models\Transaction;
use App\Models\Onepoint;
use App\Models\OnepointLog;
use App\Models\Commission;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Notifications\TnxStatus;
use App\Http\Controllers\Controller;
use App\Libs\Sale;
use App\Services\Sale as SaleServices;

class OnepointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @version 1.0.0
     * @since 1.0
     * @return void
     */
    public function index()
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @version 1.0.0
     * @since 1.0
     * @return void
     *
     * @throws \Throwable
     */
    
    public function commission($user_id, $point, $onepoint_logid){
		$sale = new Sale();
		$sale->addBill($user_id, $point, $onepoint_logid);
	}
    public function onepoint(Request $request, $id='')
    {
		$user = Auth::user();
		$checknum = Onepoint::where('user',$user->id)->orderBy('created_at','desc')->first();
		
		if($checknum == ''){
			$heso = Setting::where('field','num1')->first();
			
			$value = $user->tokenBalance * $heso->value;
			$input['onein'] = 0;
			$input['num'] = 1;
			$input['point'] = $value;
			$input['user'] = $user->id;
			$result = Onepoint::create($input);
			
			$inpoint['user'] = $user->id;
			$inpoint['point'] = $value;
			$point = OnepointLog::create($inpoint);
			$sale = new Sale();
			$sale->addBill($user->id, $value, $point->id);
			
			
			
			$userupdate = User::find($user->id);
			$userupdate->tokenBalance = 0;
			$userupdate->save();
			return response()->json(['status' => 'success','value' => $value]);
		}else{
			$first_date = strtotime(DATE('Y-m-d H:i:s'));
			$second_date = strtotime($user->created_at);
			$datediff = abs($first_date - $second_date);
			$day = floor($datediff / (60*60*24));
			if($checknum->num == 6 && $day > 365){
				$heso = Setting::where('field','num1')->first();
				$update = Onepoint::find($checknum->id);
				
				$value = $update->point + ($user->tokenBalance * $heso->value);
				$update->onein = 0;
				$update->num = 1;
				$update->point = $value;
				$update->save();
				
				$inpoint['user'] = $user->id;
				$inpoint['point'] = $user->tokenBalance * $heso->value;
				$point = OnepointLog::create($inpoint);
				$sale = new Sale();
				$sale->addBill($user->id, $value, $point->id);
				
				$userupdate = User::find($user->id);
				$userupdate->tokenBalance = 0;
				$userupdate->save();
				return response()->json(['status' => 'success','value' => $value]);	
			}
			if($checknum->num < 6 && $user->tokenBalance > 0){
				$so = $checknum->num + 1;
				$heso = Setting::where('field','num'.$so)->first();
				$update = Onepoint::find($checknum->id);
				$value = $update->point + ($user->tokenBalance * $heso->value);
				$point = $user->tokenBalance * $heso->value;
				$update->onein = 0;
				$update->num = $checknum->num + 1;
				$update->point = $value;
				$update->save();
				
				$inpoint['user'] = $user->id;
				$inpoint['point'] = $user->tokenBalance * $heso->value;
				$po = OnepointLog::create($inpoint); 
				$sale = new Sale();
				try {
					$sale->addBill($user->id, $point, $po->id);
				 } catch (\Exception $e) {
                    $ret['errors'] = $e->getMessage();
                    $ret['msg'] = 'warning';
                    $ret['message'] = __('messages.mail.issues');
					$value = $e->getMessage();
                }
				
				
				$userupdate = User::find($user->id);
				$userupdate->tokenBalance = 0;
				$userupdate->save();
				return response()->json(['status' => 'success','value' => $value]);
			}
		}
		return response()->json(['status' => 'faild']);
    }
	

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\Response
     * @version 1.0.0
     * @since 1.0
     */
    public function destroy(Request $request, $id='')
    {
       
    }
}
