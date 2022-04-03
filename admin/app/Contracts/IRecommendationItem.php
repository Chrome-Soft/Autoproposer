<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Contracts;


interface IRecommendationItem
{
    public function getId();
    /** productIds és proposerIds indexek alatt tároljuk a különböző elemeket.
     * Ez a metódus adja vissza, hogy melyikbe tartozik
     */
    public function getBucketName(): string;
}
