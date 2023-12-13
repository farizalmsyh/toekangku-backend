<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User;
        $user->type = "Internal";
        $user->name = "Administrator";
        $user->email = "administrator@toekangku.id";
        $user->password = Hash::make("123456");
        $user->nik = "INTERNAL";
        $user->phone = "INTERNAL";
        $user->gender = "Laki-Laki";
        $user->birth_date = "2023-12-12";
        $user->address_province = "INTERNAL";
        $user->address_city = "INTERNAL";
        $user->address_subdistrict = "INTERNAL";
        $user->address_village = "INTERNAL";
        $user->address_zipcode = "INTERNAL";
        $user->save();
    }
}
