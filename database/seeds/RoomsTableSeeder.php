<?php

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Asso;
use App\Models\Room;

class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rooms = [
            [
                'location'  => 'BDE-UTC (1er étage)',
                'owner'      => Asso::where('login', 'bde')->first(),
            ],
        ];

        foreach ($rooms as $room) {
            Room::create([
                'location_id'   => Location::where('name', $room['location'])->first()->id,
            ])->changeOwnerTo($room['owner'])->save();
        }
    }
}
