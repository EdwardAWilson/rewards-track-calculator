<?php

namespace App\Http\Controllers;

use App\Models\RewardsTrack;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class ResultsController extends BaseController
{
	public function index(Request $request)
	{
		$requiredXP = RewardsTrack::CUMULATIVE_XP[$request->targetLevel] - RewardsTrack::CUMULATIVE_XP[$request->currentLevel];

		$expansionDay = strtotime($request->expansionDay);
		$currentDay = strtotime($request->currentDay);
		$daysLeft = ($expansionDay - $currentDay) / 86400;
		$possibleWeeklies = ceil($daysLeft / 7.0);
		$possibleDailiesPerWeek = $daysLeft / 7.0;

		if($request->tavernPass)
		{
			$level = $request->currentLevel;
			$dayOfWeek = date("w", $currentDay);

			while($daysLeft > 0)
			{
				if ($request->currentLevel < 20)
				{
					$bonus = 1.1;
				}
				else if ($request->currentLevel < 70)
				{
					$bonus = 1.15;
				}
				else
				{
					$bonus = 1.2;
				}

				if ($dayOfWeek === 1)
				{
					$requiredXP -= RewardsTrack::BIG_WEEKLY * $request->bigWeeklyQuests * $possibleWeeklies * $bonus;
					$requiredXP -= RewardsTrack::SMALL_WEEKLY * $request->smallWeeklyQuests * $possibleWeeklies * $bonus;
					$requiredXP -= RewardsTrack::DAILY * $possibleDailiesPerWeek * $request->dailyQuests * $bonus;
				}

				$requiredXP -= RewardsTrack::RANKED_XP_PER_HOUR * $request->rankedPlaytime * $daysLeft * $bonus;
				$requiredXP -= RewardsTrack::NON_RANKED_XP_PER_HOUR * $request->otherPlaytime * $daysLeft * $bonus;
				
				$dayOfWeek = ($dayOfWeek + 1) % 7;
			}
		}
		else
		{
			$requiredXP -= RewardsTrack::BIG_WEEKLY * $request->bigWeeklyQuests * $possibleWeeklies;
			$requiredXP -= RewardsTrack::SMALL_WEEKLY * $request->smallWeeklyQuests * $possibleWeeklies;
			$requiredXP -= RewardsTrack::DAILY * $possibleDailiesPerWeek * $request->dailyQuests;

			$requiredXP -= RewardsTrack::RANKED_XP_PER_HOUR * $request->rankedPlaytime * $daysLeft;
			$requiredXP -= RewardsTrack::NON_RANKED_XP_PER_HOUR * $request->otherPlaytime * $daysLeft;
		}

		$requiredXP = round($requiredXP);

		return view('results.index', compact('requiredXP'));
	}
}
