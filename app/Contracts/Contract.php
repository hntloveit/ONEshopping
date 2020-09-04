<?php
namespace App\Contracts;
interface Contract {
    public function addContract($user_id,$data);
    public function getByUser($user_id);
    public function getPendingAgency();
    public function addAgencyContract($data);
    public function editAgencyContract($data);
    public function getAgencyContractByUser($type,$user_id,$page);
    public function getAgencyContractByID($id);
    public function setAgencyContractStatus($id,$status,$admin_id);
}