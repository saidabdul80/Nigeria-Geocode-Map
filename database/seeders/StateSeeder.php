<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = array(
            array('id' => '1','name' => 'Abia','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '2','name' => 'Adamawa','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '3','name' => 'Akwa ibom','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '4','name' => 'Anambra','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '5','name' => 'Bauchi','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '6','name' => 'Bayelsa','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '7','name' => 'Benue','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '8','name' => 'Borno','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '9','name' => 'Cross river','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '10','name' => 'Delta','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '11','name' => 'Ebonyi','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '12','name' => 'Edo','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '13','name' => 'Ekiti','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '14','name' => 'Enugu','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '15','name' => 'Fct','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '16','name' => 'Gombe','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '17','name' => 'Imo','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '18','name' => 'Jigawa','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '19','name' => 'Kaduna','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '20','name' => 'Kano','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '21','name' => 'Katsina','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '22','name' => 'Kebbi','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '23','name' => 'Kogi','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '24','name' => 'Kwara','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '25','name' => 'Lagos','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '26','name' => 'Nasarawa','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '27','name' => 'Niger','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '28','name' => 'Ogun','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '29','name' => 'Ondo','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '30','name' => 'Osun','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '31','name' => 'Oyo','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '32','name' => 'Plateau','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '33','name' => 'Rivers','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '34','name' => 'Sokoto','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '35','name' => 'Taraba','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '36','name' => 'Yobe','country_id' => '160','created_at' => NULL,'updated_at' => NULL),
            array('id' => '37','name' => 'Zamfara','country_id' => '160','created_at' => NULL,'updated_at' => NULL)
          );

          State::insert($states);

          State::all()->each(function($state) {
            $state->status = 0;
            $state->save();
          });
    }
}
