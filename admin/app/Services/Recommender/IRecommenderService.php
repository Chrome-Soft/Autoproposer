<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Recommender;

use App\Proposer;
use App\Segment;
use App\UserData;

interface IRecommenderService
{
    public function segmentify(UserData $userData): Segment;
    public function recommend(Segment $segment, Proposer $proposer);
    public function storeRecommendations(array $segmentIds);
    public function trainNeuralNetwork();
}
