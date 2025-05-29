<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Schema;

class SessionController extends Controller
{
    public function getActiveSessions()
    {
        try {
            // First check if sessions table exists
            if (!Schema::hasTable('sessions')) {
                return response()->json([
                    'count' => 0,
                    'sessions' => [],
                    'error' => 'Sessions table does not exist'
                ], 500);
            }

            // Check if there are any sessions
            $sessionsCount = DB::table('sessions')->count();
            if ($sessionsCount === 0) {
                return response()->json([
                    'count' => 0,
                    'sessions' => []
                ]);
            }

            // Get sessions with user information
            $sessions = DB::table('sessions')
                ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
                ->select('sessions.*', 'users.name', 'users.email')
                ->where('last_activity', '>=', Carbon::now()->subMinutes(30)->timestamp)
                ->get();

            $formattedSessions = $sessions->map(function ($session) {
                $agent = new Agent();
                if (isset($session->user_agent)) {
                    $agent->setUserAgent($session->user_agent);
                }
                
                return [
                    'id' => $session->id,
                    'user' => [
                        'name' => $session->name ?? 'Guest',
                        'email' => $session->email ?? 'N/A'
                    ],
                    'ip_address' => $session->ip_address ?? 'Unknown',
                    'last_activity' => isset($session->last_activity) ? Carbon::createFromTimestamp($session->last_activity)->diffForHumans() : 'N/A',
                    'device' => $agent->device() ? $agent->device() : 'Unknown',
                    'browser' => $agent->browser() ? $agent->browser() . ' ' . $agent->version($agent->browser()) : 'Unknown',
                    'is_current' => $session->id === session()->getId()
                ];
            });

            return response()->json([
                'count' => $sessions->count(),
                'sessions' => $formattedSessions
            ]);
            
        } catch (Exception $e) {
            Log::error('Error fetching active sessions: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'count' => 0,
                'sessions' => [],
                'error' => 'Failed to load active sessions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function terminate($sessionId)
    {
        try {
            // Check if user is admin
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied. Only administrators can terminate sessions.'
                ], 403);
            }
            
            // Don't allow terminating current session
            if ($sessionId === session()->getId()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot terminate your own session'
                ], 403);
            }

            DB::table('sessions')->where('id', $sessionId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error terminating session: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session: ' . $e->getMessage()
            ], 500);
        }
    }
} 