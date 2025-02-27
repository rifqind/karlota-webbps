<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->paginated) $paginated = $request->paginated;
        else $paginated = 10;
        if ($request->currentPage) $currentPage = $request->currentPage;
        else $currentPage = 1;

        $query = User::query();
        $number = 1;
        $dataToCounted = $query->join('regions as r', 'r.satker_id', '=', 'users.satker_id')
            ->select(['users.*', 'r.name as satker']);

        if ($request->orderAttribute) {
            $order = $request->orderAttribute;
            if (sizeof($order) > 2) $query->orderBy($order['label'], $order['value']);
            else $query->orderBy('satker_id', 'asc');
        } else $query->orderBy('satker_id', 'asc');

        if ($request->ArrayFilter) {
            $filter = $request->ArrayFilter;
            if (!empty($filter['name'])) {
                $query->where('users.name', 'like', '%' . $filter['name'] . '%');
            }
            if (!empty($filter['satker'])) {
                $query->where('r.name', 'like', '%' .  $filter['satker'] . '%');
            }
            if (!empty($filter['role'])) {
                $query->where('role', 'like', '%' .  $filter['role'] . '%');
            }
        }

        $countData = $dataToCounted->count();
        $data = $query->paginate($paginated, ['*'], 'page', $currentPage);
        foreach ($data as $key => $value) {
            # code...
            $value->number = $number;
            $number++;
        }
        if ($request->paginated) {
            return response()->json([
                'user' => $data,
                'countData' => $countData
            ]);
        }
        $regions = Region::select(['satker_id as value', 'name as label'])->distinct('satker_id')->get();
        return Inertia::render('User/Index', [
            'user' => $data,
            'countData' => $countData,
            'satker' => $regions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'satker_id' => ['required', 'integer'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'role' => ['required', 'string'],
        ]);
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            if ($request->id) {
                $request->validate(['email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($request->id)]]);
                $updated_data = User::findOrFail($request->id);
                $updated_data->update($validated);
                $message = [
                    'type' => 'message',
                    'message' => 'Berhasil mengedit user tersebut'
                ];
                array_push($notification, $message);
                DB::commit();
                return redirect()->route('user.index')->with('notification', $notification);
            } else
                $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);
            $new_data = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password),
                'role' => $validated['role'],
                'satker_id' => $validated['satker_id'],
            ]);
            $message = [
                'type' => 'message',
                'message' => 'Berhasil menambah pengguna baru'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('user.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika menambah pengguna baru',
                'error' => $th->getMessage()
            ];
            array_push($notification, $message);
            return redirect()->route('user.index')->with('notification', $notification);
        }
    }

    public function fetch(String $id)
    {
        $fetched = User::find($id);
        return response()->json(['data' => $fetched]);
    }

    public function destroy(String $id)
    {
        $notification = [];
        try {
            //code...
            DB::beginTransaction();
            $data_to_delete = User::findOrFail($id);
            $data_to_delete->delete();
            $message = [
                'type' => 'message',
                'message' => 'Berhasil menghapus pengguna tersebut'
            ];
            array_push($notification, $message);
            DB::commit();
            return redirect()->route('user.index')->with('notification', $notification);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            $message = [
                'type' => 'error',
                'message' => 'Ada kesalahan ketika menghapus pengguna tersebut',
                'error' => $th->getMessage()
            ];
            return redirect()->route('user.index')->with('notification', $notification);
        }
    }
}
