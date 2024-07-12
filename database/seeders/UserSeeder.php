<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use DateTime;
use Google\Cloud\Core\Timestamp;
use Faker\Factory as Faker;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $section=Section::create([
        //     'name' => 'Section 1',
        // ]);
        $guard = 'super super admin';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            echo "cURL Success";
        }
        curl_close($ch);
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount(config_path('firebase-credentials.json'));
        $auth = $factory->createAuth();
       //dd($request->email,$request->password);
        // Create the Firebase Auth user
        // $timestamp = new Timestamp(new \DateTime());
        // $dateTime = $timestamp->get()->format('Y-m-d H:i:s');
        $date = Carbon::now()->setTimezone('Europe/Moscow');
        $formattedDate = $date->format('F d, Y \a\t g:i:s A \U\T\CP');
        //$timestamp = Carbon::parse($formattedDate)->timestamp;
        // Dump the DateTime object
        
        
        $firebaseUser = $auth->createUserWithEmailAndPassword('g.m.admin@gmail.com', 'gmadmin1594826!@#$0');
        // Prepare data for Firebase Firestore
        $dateTime = new \DateTime(date('Y-m-d H:i:s'));
        $timestamp = new Timestamp($dateTime);
        $firebaseData = [
            'userid' => $firebaseUser->uid,
            'name' => 'General Manager Admin',
            'email' => 'g.m.admin@gmail.com',
            'created_at'=>$timestamp
             // Note: Consider not storing plain passwords.
        ];
        
        // Create the Firestore document
        $firestore = $factory->createFirestore()->database();
        
        $firestore->collection('users')->document($firebaseUser->uid)->set($firebaseData);
    
        // Create the user in the local database
        //$userData['uid'] = $firebaseUser->uid;
        $role=Role::where('name',$guard)->first();
        $user=User::create([
            'name' => 'General Manager Admin',
            'email' => 'g.m.admin@gmail.com',
            'password' => Hash::make('gmadmin1594826!@#$0'),
            'guard' => $guard,
            'section_id'=>1,
            'is_online'=>0,
            'uid'=>$firebaseUser->uid
        ]);
        $user->assignRole($role->id);
        
        // $faker = Faker::create();
        
        // for($i=0;$i<200;$i++){
        //     User::create([
        //         'name' => $faker->name,
        //         'email' => $faker->email,
        //         'password' => Hash::make('123123'),
        //     ]);
        // }
    }
}
