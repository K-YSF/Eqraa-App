<?php

namespace App\Console\Commands;
use App\Models\BadgeUser;
use App\Models\Challenge;
use Illuminate\Console\Command;

class CheckChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-challenges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allChallenges = Challenge::all();
        if(isset($allChallenges))
        {
             foreach ($allChallenges as $challenge)
             {
                 if(isset($challenge))
                 {
                    if($challenge->end_date < now())
                    {
                        if(!$challenge->badges_distributed)
                        {
                            $challengeUsers = $challenge->users;
                            $challengeBadges = $challenge->badges;
                            if(count($challengeUsers) > 0 && count($challengeBadges) > 0)
                            {
                                foreach ($challengeUsers as $user) 
                                {
                                    if($user->pivot->progress >= 99)
                                    {
                                       foreach ($challengeBadges as $badge)
                                       {   
                                           BadgeUser::create([
                                                'user_id' => $user->id,
                                                'badge_id' => $badge->id
                                           ]);    
                                       }
                                    }
                                }
                            }
                            $challenge->badges_distributed = 1;
                            $challenge->save();
                        }
                    }
                 }
             }
        }
    }
}
