<?php
namespace App\Services;
use App\Contracts\Sale as SaleInterface;
use DB;
use Auth;
use App\User;
use App\Libs\Hierarchy;
use App\Models\Onepoint as OnepointModel;
class Sale implements SaleInterface{	
	public function connect(){	
		global $conn;		
		$servername = "localhost";	
		$username   = "root";	
		$password   = "";	
		$dbname     = "vikioneos";	
		$conn = mysqli_connect($servername, $username, $password, $dbname) or die("Connection failed: " . mysqli_connect_error());
		/* check connection */	
		if (mysqli_connect_errno()) {	
		printf("Connect failed: %s\n", mysqli_connect_error());	
		exit();		}	
		mysqli_set_charset($conn,"utf8");	
	}	
	
	public function createTreeView($array, $currentParent, $currLevel = 0, $prevLevel = -1) {
		foreach ($array as $categoryId => $category) {		
			if ($currentParent == $category['referral']) {	
				if ($currLevel > $prevLevel) echo " <ul> "; 
				if ($currLevel == $prevLevel) echo " </li> ";
				$user = Auth::user();
				$total_one = OnepointModel::where('user','=',$category['id'])->first();
				echo '<li><span>'.$category['label'].' &nbsp;  <a class="a_click" href="https://localhost/vikioneos/public/user/referral/detail/'.$category['id'].'">Xem </a>  &nbsp;&nbsp;<i class="icon-basket"></i><span class="red"></span></span>';			
				if ($currLevel > $prevLevel) { $prevLevel = $currLevel; }	
				$currLevel++; 		
				$this->createTreeView ($array, $categoryId, $currLevel, $prevLevel);
				$currLevel--;		
			}   
		}	
		if ($currLevel == $prevLevel) echo " </li>  </ul> ";	
	}
    public function getTreeDetail($user_id,$depth){
        $hie = new Hierarchy();
        $tree = $hie->getLocalSubNodes($user_id,$depth);

        return $tree;
    }
	public function getTreeFull(){        
		$hie = new Hierarchy();       
		$tree = $hie->fullTreeNewbie(); 
	return $tree;    }
    public function getSaleHistory($user_id) {
        $sale = SaleModel::where('sale.user_id',$user_id)->orderBy('sale.id', 'desc')->get();
        return $sale;
    }

    public function getBillHistory($user_id){
        $log = SaleLogModel::where('user_id',$user_id)->orderBy('id', 'desc')->get();
        return $log;
    }
}