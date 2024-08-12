<?php

// /////////////////////////////////////////////////////////////////////////////
// PLEASE DO NOT RENAME OR REMOVE ANY OF THE CODE BELOW. 
// YOU CAN ADD YOUR CODE TO THIS FILE TO EXTEND THE FEATURES TO USE THEM IN YOUR WORK.
// /////////////////////////////////////////////////////////////////////////////

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\PlayerSkill;
use App\Enums\PlayerPositionEnum;
use App\Enums\PlayerSkillEnum;
use Illuminate\Validation\Rule;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::with('skills')->get();

        $players = $players->map(function ($player) {
            return [
                'id' => $player->id,
                'name' => $player->name,
                'position' => $player->position,
                'playerSkills' => $player->skills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'skill' => $skill->skill,
                        'value' => $skill->value,
                        'playerId' => $skill->player_id
                    ];
                })
            ];
        });

        return response()->json($players);
    }

    public function show($userID)
    {
        $player = Player::with('skills')->where('id', $userID)->first();

        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }

        $playerData = [
            'id' => $player->id,
            'name' => $player->name,
            'position' => $player->position,
            'playerSkills' => $player->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'skill' => $skill->skill,
                    'value' => $skill->value,
                    'playerId' => $skill->player_id
                ];
            })
        ];

        return response()->json($playerData);
    }

    public function store(Request $request)
    {

        try {
            $data = $request->validate([
                'name' => 'required|string',
                'position' => [
                    'required',
                    Rule::in(PlayerPositionEnum::getValues()), // Validate against enum values
                ],
                'playerSkills' => 'required|array|min:1',
                'playerSkills.*.skill' => [
                    'required',
                    Rule::in(PlayerSkillEnum::getValues()), // Validate against enum values
                ],
                'playerSkills.*.value' => 'required|integer',
            ], [
                'position.in' => 'Invalid value for position: :input',
                'playerSkills.*.skill.in' => 'Invalid value for skill: :input',
            ]);

            $player = Player::create([
                'name' => $data['name'],
                'position' => $data['position'],
            ]);

            foreach ($data['playerSkills'] as $skillData) {
                $skill = new PlayerSkill([
                    'skill' => $skillData['skill'],
                    'value' => $skillData['value'],
                ]);
                $player->skills()->save($skill);
            }
    
            // Prepare the response structure
            $response = [
                'id' => $player->id,
                'name' => $player->name,
                'position' => $player->position,
                'playerSkills' => $player->skills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'skill' => $skill->skill,
                        'value' => $skill->value,
                        'playerId' => $skill->player_id,
                    ];
                })->toArray(),
            ];
    
            return response()->json($response, 201);
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error($e);

            // Determine the appropriate error message based on the exception or validation failure
            $errorMessage = $e instanceof \Illuminate\Validation\ValidationException ?
                $e->validator->errors()->first() : 'An error occurred while processing the request.';

            return response()->json(['message' => $errorMessage], 400);
        }
    }

    public function update(Request $request, $playerID)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'position' => [
                    'required',
                    Rule::in(PlayerPositionEnum::getValues()), // Validate against enum values
                ],
                'playerSkills' => 'required|array|min:1',
                'playerSkills.*.skill' => [
                    'required',
                    Rule::in(PlayerSkillEnum::getValues()), // Validate against enum values
                ],
                'playerSkills.*.value' => 'required|integer',
            ], [
                'position.in' => 'Invalid value for position: :input',
                'playerSkills.*.skill.in' => 'Invalid value for skill: :input',
            ]);

            $player = Player::findOrFail($playerID);

            $player->name = $data['name'];
            $player->position = $data['position'];
            $player->save();

            // Update player skills
            $player->skills()->delete(); // Delete existing player skills
            foreach ($data['playerSkills'] as $skillData) {
                $skill = new PlayerSkill([
                    'skill' => $skillData['skill'],
                    'value' => $skillData['value'],
                ]);
                $player->skills()->save($skill);
            }

            // Prepare the response structure
            $response = [
                'id' => $player->id,
                'name' => $player->name,
                'position' => $player->position,
                'playerSkills' => $player->skills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'skill' => $skill->skill,
                        'value' => $skill->value,
                        'playerId' => $skill->player_id,
                    ];
                })->toArray(),
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error($e);

            // Determine the appropriate error message based on the exception or validation failure
            $errorMessage = $e instanceof \Illuminate\Validation\ValidationException ?
                $e->validator->errors()->first() : 'An error occurred while processing the request.';

            return response()->json(['message' => $errorMessage], 400);
        }
    }

    public function destroy($playerID)
    {
        try {
            $player = Player::findOrFail($playerID);

            // Delete associated player skills
            $player->skills()->delete();

            // Delete the player
            $player->delete();

            return response()->json(['message' => 'Player deleted successfully'], 200);

        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            \Log::error($e);

            // Return error response
            return response()->json(['message' => 'Failed to delete player'], 500);
        }
    }

    public function teamProcess(Request $request)
    {
        $criteria = $request->json()->all();

        $selectedPlayers = [];

        foreach ($criteria as $criterion) {
            $position = $criterion['position'];
            $mainSkill = $criterion['mainSkill'];
            $numberOfPlayers = $criterion['numberOfPlayers'];

            // Query players based on position and main skill
            $playersQuery = Player::with('skills')
                ->where('position', $position)
                ->whereHas('skills', function ($query) use ($mainSkill) {
                    $query->where('skill', $mainSkill);
                });

            // Ensure we select only the required number of players
            $players = $playersQuery->orderByDesc('value')->limit($numberOfPlayers)->get();

            // If not enough players found with the main skill, fetch highest skill value players
            if ($players->isEmpty()) {
                $players = Player::with('skills')
                    ->where('position', $position)
                    ->orderByDesc('value')
                    ->limit($numberOfPlayers)
                    ->get();
            }

            // Check if enough players were found
            if ($players->count() < $numberOfPlayers) {
                return response()->json(['message' => "Insufficient number of players for position: $position"], 400);
            }

            // Add selected players to the result
            foreach ($players as $player) {
                $selectedPlayers[] = [
                    'name' => $player->name,
                    'position' => $player->position,
                    'playerSkills' => $player->skills->map(function ($skill) {
                        return [
                            'skill' => $skill->skill,
                            'value' => $skill->value,
                        ];
                    })
                ];
            }
        }

        return response()->json($selectedPlayers);
    }

}
