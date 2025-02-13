<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => '9e30b848-5171-4187-bacc-a67da80adf02',
                'name' => 'admin',
                'email' => 'admin@mail.com',
                'email_verified_at' => NULL,
                'password' => '$2y$12$Rn7zh05cJjcoJCLffAAxy.YgY6NpBBdKPy3oX1TFvIU2Zy1JkjSPO',
                'remember_token' => NULL,
                'satker_id' => 1,
                'role' => 'user',
                'created_at' => '2025-02-12 00:41:13',
                'updated_at' => '2025-02-12 00:41:13',
            ),
        ));
        
        
    }
}