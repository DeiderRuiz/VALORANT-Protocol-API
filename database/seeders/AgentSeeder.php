<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')->pluck('id', 'email');
        $roles = DB::table('agent_classes')->pluck('id', 'name');
        $nationalities = DB::table('nationalities')->pluck('id', 'country_name');

        $agents = json_decode(file_get_contents(database_path('data/agents.json')), true);

        foreach ($agents as $agent) {
            $slug = Str::slug($agent['name']);

            $profilePhotoPath = 'agents/' . $slug . '/' . basename($agent['profile_photo']);

            // copiar la imagen al storage público
            Storage::disk('public')->put(
                $profilePhotoPath,
                file_get_contents(public_path('agents/' . $slug . '/' .  basename($agent['profile_photo'])))
            );

            $agentPhotoPath = 'agents/' . $slug . '/' . basename($agent['agent_photo']);
            // copiar la imagen al storage público
            Storage::disk('public')->put(
                $agentPhotoPath,
                file_get_contents(public_path('agents/' . $slug . '/' . basename($agent['agent_photo'])))
            );

            Agent::create([
                'user_id' => $users[$agent['user_email']],
                'name' => $agent['name'],
                'bio' => $agent['bio'],
                'profile_photo' => $profilePhotoPath,
                'agent_photo' => $agentPhotoPath,
                'nationality_id' => !empty($agent['nationality'])
                                    ? $nationalities[$agent['nationality']]
                                    : null,
                'class_id' => $roles[$agent['class_name']],
                'available' => true,
                'is_draft' => false,
            ]);
        }
    }
}
