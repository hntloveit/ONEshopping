<?php
namespace App\Contracts;
interface Sale {
    public function getTreeDetail($user_id,$depth);  
	public function getTreeFull();
    public function getSaleHistory($user_id);
    public function getBillHistory($user_id);
}