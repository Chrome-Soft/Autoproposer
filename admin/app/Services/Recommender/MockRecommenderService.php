<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender;


use App\Proposer;
use App\Segment;
use App\UserData;

class MockRecommenderService implements IRecommenderService
{
    // Csak addig kell, amíg nincs élesítve a Python recommender service
    public function segmentify(UserData $userData)
    {
        return null;
    }

    public function recommend(Segment $segment, Proposer $proposer)
    {
        return null;
    }
}
