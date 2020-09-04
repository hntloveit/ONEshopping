<?php
namespace App\Services;
use App\Contracts\Contract as ContractInterface;
use App\Models\AgencyContract;
use App\Models\Contract as ContractModel;
use App\Models\AgencyContract as AgencyContractModel;
class Contract implements ContractInterface{
    public function addContract($user_id,$data){
        $contract = new ContractModel();

        $buy_date = time();
        $contract->user_id = $user_id;
        $contract->cost = $data['cost'];
        $contract->product_value = $data['product_value'];
        $contract->serial = $data['serial'];
        $contract->buy_date = date('Y-m-d H:i:s',$buy_date) ;
        $contract->end_date = date('Y-m-d H:i:s',strtotime("+1 year",$buy_date));

        if(isset($data['add_insurance']) && $data['add_insurance'] == 'on') {
            $insurance_start = date('Y-m-d H:i:s',strtotime("30 days",$buy_date));
            $contract->insurance_serial = $data['serial'];
            $contract->insurance_start = $insurance_start;
        }

        if($contract->save()){
            $contract_number = 'HDBT.'.sprintf("%'.09d",$contract->id).'/'.date('Y').'/DVS';
            $contract->contract_number = $contract_number;
            $contract->save();
            return $contract;
        }

        return false;
    }

    public function getPendingAgency(){
        return AgencyContract::paginate(10);
    }

    public function getByUser($user_id) {
        $contracts = ContractModel::where('user_id', $user_id)->paginate(10);
        return $contracts;
    }

    public function addAgencyContract($data){
        return AgencyContractModel::create([
            'user_id' => $data['user_id'],
            'owner_info' => serialize($data['owner']),
            'client_info' => serialize($data['client']),
            'type' => $data['type']
        ]);
    }

    public function editAgencyContract($data){
        $contract = AgencyContractModel::find($data['id']);
        $contract->owner_info = serialize($data['owner']);
        $contract->client_info = serialize($data['client']);
        if(isset($data['approve'])) {
            $contract->accepted_by = $data['admin_id'];
            $contract->status = 1;
        }
        $contract->save();
    }

    public function getAgencyContractByUser($type,$user_id,$page = null){
        if(is_null($page)) {
            $contracts = AgencyContractModel::where('type',$type)->where('user_id',$user_id)->get();
        } else {
            $contracts = AgencyContractModel::where('type',$type)->where('user_id',$user_id)->paginate($page);
        }
        return $contracts;
    }

    public function getAgencyContractByID($id){
        return AgencyContractModel::find($id);
    }

    public function setAgencyContractStatus($id,$status,$admin_id) {
        $contract = AgencyContractModel::find($id);
        $contract->status = $status;
        $contract->accepted_by = $admin_id;
        $contract->save();
    }
}