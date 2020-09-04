<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Auth;
use Validator;
use IcoHandler;
use Carbon\Carbon;
use App\Models\Page;
use App\Models\User;
use App\Models\Setting;
use App\Models\UserMeta;
use App\Models\Onepoint;
class UpdateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $all = Onepoint::all();
		foreach($all as $item){
			$first_date = strtotime(DATE('Y-m-d H:i:s'));
			$second_date = strtotime($item->updated_at);
			$datediff = abs($first_date - $second_date);
			$day = floor($datediff / (60*60*24));
			if($day >= 1){
				$heso = Setting::where('field','numpercent')->first();
				$update = Onepoint::find($item->id);
				$value = $update->oneout + ($item->point * $heso->value);
				$update->oneout = $update->oneout + $value;
				$update->point = $update->point - $value;
				$update->save();
				$this->info('status:update Command Run successfully!');
			}
			
		}
    }
}
