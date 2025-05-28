<?php

namespace App\Http\Controllers;
use App\Models\Sites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SitesController extends Controller
{
    public function sites()
    {
        return view('sites.index');
    }

    public function storeSite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:191',
            'url'         => 'required|url|max:191',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'type'        => 'required|in:general,blog,shop,portfolio',
            'theme'       => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        // Create site using mass assignment
        $site = Sites::create([
            'name'        => $request->input('name'),
            'url'         => $request->input('url'),
            'description' => $request->input('description'),
            'status'      => $request->input('status'),
            'type'        => $request->input('type'),
            'theme'       => $request->input('theme'),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Site created successfully!',
        ]);
    }

    public function fetchSite(Request $request)
    {
        $sites = Sites::all();
        return response()->json([
            'sites' => $sites,
        ]);
    }

    public function editSite($id)
    {
        $site = Sites::findOrFail($id);
        if($site){
            return response()->json([
                'status' => 200,
                'site' => $site,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Site not found',
            ]);
        }
    }

    public function updateSite(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:191',
            'url'         => 'required|url|max:191',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'type'        => 'required|in:general,blog,shop,portfolio',
            'theme'       => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $site = Sites::findOrFail($id);
        if ($site) {
            $site->update($request->all());
            return response()->json([
                'status' => 200,
                'message' => 'Site updated successfully!',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Site not found',
            ]);
        }
    }

    public function destroySite($id)
    {
        $site = Sites::findOrFail($id);
        if ($site) {
            $site->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Site deleted successfully!',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Site not found',
            ]);
        }
    }
}
