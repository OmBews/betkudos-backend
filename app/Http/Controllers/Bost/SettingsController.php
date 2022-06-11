<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Nullable;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('requires.password')->only('update');
        $this->middleware('permission:edit settings')->only('update');
    }

    /**
     * @return JsonResource
     */
    public function index()
    {
        return new JsonResource(setting($this->prefix()));
    }

    public function update(Request $request)
    {
        $validated = $this->validateUpdate($request);

        foreach ($validated as $key => $value) {
            if (setting($this->buildKey($key)) === null) {
                return $this->sendSettingNotFoundResponse($key, $request);
            }

            setting([$this->buildKey($key) => $value])->save();
        }

        return $this->index();
    }

    protected function validateUpdate(Request $request): array
    {
        return $request->validate([
            'block_sports_book' => 'required|boolean'
        ]);
    }

    protected function prefix(): string
    {
        return 'global';
    }

    private function buildKey(string $key)
    {
        return $this->prefix() . '.' . $key;
    }

    protected function sendSettingNotFoundResponse(string $key, Request $request)
    {
        $data = [
            "message" => trans('bost.settings.not_found', ['key' => $key]),
        ];

        return response()->json($data, 404);
    }

    public function maintenance(Request $request)
    {
        $request->validate([
            'status' => 'integer|nullable'
        ]);

        try {
            $checkRow = SiteSetting::first();

            if ($checkRow) {
                $checkRow->status = $request->status;
                $checkRow->save();
            } else {
                $setting = new SiteSetting();
                $setting->status = $request->status;
                $setting->save();
            }
            return response()->json(['message' => 'Successfully Done']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th]);
        }
    }

    public function getMaintenance()
    {
        return SiteSetting::first();
    }
}
