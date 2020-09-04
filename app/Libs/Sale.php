<?php
namespace App\Libs;

use DB;
use App\Models\Onepoint;
use App\Models\OnepointLog;
use App\Models\Commission;
use App\Models\User;
use App\Models\Setting;
use App\User as UserModel;
use App\Libs\Hierarchy;
class Sale
{

	
    public function addBill($user_id, $point, $onepoint_logid){
		$user = User::find($user_id);	
        $user_level = $user->level;
		if ($user_level > 1) { 
			$hierarchy = new Hierarchy();
			$path = $hierarchy->singlePathNewbie($user_id);	
			foreach ($path as $key => $node_id) {
				if($node_id['id'] != 0) $this->addPrice($user_id, $point, $node_id['id'],$onepoint_logid);
			}	
			
		}
	}
    

    public function addPrice($user_id, $point, $node_id, $onepoint_logid){ 
		/*add log chi tiet*/
		$check_user = User::find($user_id);
		$level = $check_user->level -1;
		$referral = Setting::where('field','referral'.$level)->first();
		$commission['point'] = $referral->value/100 * $point;
		$commission['user'] = $node_id;
		$commission['fromuser'] = $user_id;
		$commission['onepoint_logid'] = $onepoint_logid;
		$po = Commission::create($commission);
		/*cong vao tong o Onepoint*/
		$onepoint = Onepoint::where('user',$node_id)->first();
		if($onepoint != ''){
			$onepoint->point = $onepoint->point + $commission['point'];
			$onepoint->save();
		}else{
			$input['onein'] = 0;
			$input['num'] = 0;
			$input['point'] = $commission['point'];
			$input['user'] = $node_id;
			$result = Onepoint::create($input);
		}
		
		$hierarchy = new Hierarchy();
		$path = $hierarchy->singlePathNewbie($node_id);	
		foreach ($path as $key => $node_id) {
			if($node_id['id'] != 0) $this->addPrice($user_id, $point, $node_id['id'],$onepoint_logid);
		}
	}
    
	
    public static function calculateIncome($user_id, $type)
    {
        $income = 0;        if ($type == 'direct') {            $income = DB::table('sale')->where('user_id', $user_id)->sum('direct_income');        } 		if ($type == 'indirect') {            $income = DB::table('sale')->where('user_id', $user_id)->sum('indirect_income');        }		if ($type == 'ck') {            $income = DB::table('sale')->where('user_id', $user_id)->sum('ck');        }		if ($type == 'cpkt') {            $income = DB::table('sale')->where('user_id', $user_id)->sum('cpkt');        }		if ($type == 'kt') {            $income = DB::table('sale')->where('user_id', $user_id)->sum('kt');        }
        return $income;
    }

    public static function calculateTotalSale($user_id){
        $ck = DB::table('sale')->where('user_id', $user_id)->sum('ck');
        $cpkt = DB::table('sale')->where('user_id', $user_id)->sum('cpkt');  
		$kt = DB::table('sale')->where('user_id', $user_id)->sum('kt');

        $total = $ck + $cpkt + $kt;
        return $total;
    }
	public static function getPricePro($id){
        $price = DB::table('product')->where('id', $id)->first();
        return $price;
    }
}