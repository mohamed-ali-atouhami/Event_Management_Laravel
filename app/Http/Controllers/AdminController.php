<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }
    public function dashboardOverview()
    {
        $userCount = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $organizerCount = User::where('role', 'organizer')->count();
        $attendeeCount = User::where('role', 'attendee')->count();
        $eventCount = Event::count();

        return response()->json([
            'user_count' => $userCount,
            'admin_count' => $adminCount,
            'organizer_count' => $organizerCount,
            'attendee_count' => $attendeeCount,
            'event_count' => $eventCount,
        ]);
    
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query();
        
        // Check if a 'role' query parameter is provided and is valid
        if ($request->has('role')) {
            $role = $request->query('role');
            $validRoles = ['admin', 'organizer', 'attendee'];
            if (in_array($role, $validRoles)) {
                $query->where('role', $role);
            }
        }
        $users = $query->get();
        return UserResource::collection($users);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request) : JsonResponse
    {
        //
        $validateForms = $request->validated();
        $user = User::create($validateForms);
        $response =  new UserResource($user);
        return response()->json([
            "user" => $response,
            "message" => __("User Created successfully !!")
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user) : JsonResponse
    {
        //
        $response =  new UserResource($user);
        return response()->json([
            "user" => $response,
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user) : JsonResponse
    {
        //
        $validateForms = $request->validated();
        $user->update($validateForms);
        $response =  new UserResource($user);
        return response()->json([
            "user" => $response,
            "message" => __("User Updated successfully !!")
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) : JsonResponse
    {
        //
        $user->delete();
        $response =  new UserResource($user);
        return response()->json([
            "user" => $response,
            "message" => __("User Deleted successfully !!")
        ],200);
    }
}
