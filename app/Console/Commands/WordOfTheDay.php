<?php

namespace App\Console\Commands;
use Carbon\Carbon;
use App\Models\Page;
use App\Models\User;
use App\Models\Setting;
use App\Models\UserMeta;
use App\Models\Onepoint;
use Illuminate\Console\Command;

class WordOfTheDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'word:day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'vascs Command description';

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
			if($item->point > 0){
				$first_date = strtotime(DATE('Y-m-d H:i:s'));
				$second_date = strtotime($item->updatecron);
				$datediff = abs($first_date - $second_date);
				$day = floor($datediff / (60*60*24));
				if($day >= 1){
					$heso = Setting::where('field','numpercent')->first();
					$update = Onepoint::find($item->id);
					$value = $item->point * $heso->value;
					$update->oneout = $update->oneout + $value;
					$update->point = $update->point - $value;
					$update->updatecron = DATE('Y-m-d H:i:s');
					$update->save();
					$this->info('word:day Command Run successfully!');
				}
			}
			
		}
    }
}
