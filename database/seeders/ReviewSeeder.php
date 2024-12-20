<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Profile;
use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;


class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        $profileIds = Profile::all()->pluck("id");

        foreach($profileIds as $profileId) {

            for($i = 0 ; $i < ($faker->numberBetween(20, 40)); $i++) {
                $newReview = new Review();
                $newReview->profile_id = $profileId;
                $newReview->votes = $faker->numberBetween(1,5);
                $newReview->content = $faker->realText(rand(50,150));
                $newReview->email = $faker->email();
                $newReview->first_name = $faker->firstName();
                $newReview->last_name = $faker->lastName();

                $startDate = Carbon::create(2024, 1, 1, 0, 0, 0);
                $endDate = Carbon::create(2024, 12, 31, 0, 0, 0);
                $randomDate = $faker->dateTimeBetween($startDate, $endDate);
                $newReview->created_at = $randomDate;
                $newReview->updated_at = $randomDate;
                $newReview->save();
            }
        }


    }
}
