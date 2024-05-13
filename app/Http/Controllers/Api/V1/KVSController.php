<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Models\KVS;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class KVSController extends ApiController
{

    const TTL_STORE_IDEMPOTENCY = 3600;

    function index(Request $request): JsonResponse
    {
        $userId = $request->header("X-USER-ID");

        if (empty($userId) || !is_numeric($userId)) {
            return $this->badRequestResponse("User id required");
        }

        $userId = intval($userId);

        $result = KVS::query()->where(KVS::FK_USER_ID, $userId)->get(KVS::ATTR_KEY);

        return $this->successResponse(array_values(array_map(fn($item) => $item[KVS::ATTR_KEY], $result->toArray())));
    }

    function show(string $key, Request $request): JsonResponse
    {
        $userId = $request->header("X-USER-ID");

        $result = KVS::query()->where(KVS::FK_USER_ID, $userId)->where(KVS::ATTR_KEY, $key)->get(KVS::ATTR_VALUE);

        if ($result->count() == 0) {
            return $this->badRequestResponse("Resource with key[$key] does not exists.");
        }

        return $this->successResponse(json_decode($result[0]->value, true));
    }

    function store(Request $request): JsonResponse
    {
        $key = $request->get("key");
        $value = $request->get("value");

        return $this->saveItem($request, $key, $value, "store");
    }

    function update(string $key, Request $request): JsonResponse
    {
        return $this->saveItem($request, $key, $request->all(), "update");
    }

    function destroy(string $key, Request $request): JsonResponse
    {
        $userId = $request->header("X-USER-ID");

        if (empty($userId) || !is_numeric($userId)) {
            return $this->badRequestResponse("User id required");
        }

        return Cache::remember("delete_$userId" . "_$key", self::TTL_STORE_IDEMPOTENCY, function () use ($key, $userId) {

            $model = KVS::query()->where(KVS::FK_USER_ID, $userId)->where(KVS::ATTR_KEY, $key)->first();

            if ($model == null) {
                return $this->badRequestResponse("Resource with key[$key] does not exists.");
            }

            if ($model->delete()) {
                return $this->successResponse();
            }
            return $this->internalErrorResponse();
        });
    }

    private function saveItem(Request $request, string $key, $value, string $action): JsonResponse
    {

        $userId = $request->header("X-USER-ID");

        if (empty($userId) || !is_numeric($userId)) {
            return $this->badRequestResponse("User id required");
        }


        if (str_contains($key, " ")) {
            return $this->badRequestResponse("Key invalid format by spaces");
        }

        $key = normalize_text($key);

        try {
            $value = json_encode((is_array($value)) ? $value : json_decode($value, true));
            $cacheKey = "kvs_key_" . $action . "_$userId" . "_$key" . "_" . hash("sha256", $value);

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            if ($action == "store" && KVS::query()->where(KVS::FK_USER_ID, $userId)->where(KVS::ATTR_KEY, $key)->exists()) {
                return $this->badRequestResponse("Key [$key] already exists");
            }

            if ($action == "update" && !KVS::query()->where(KVS::FK_USER_ID, $userId)->where(KVS::ATTR_KEY, $key)->exists()) {
                return $this->badRequestResponse("Key [$key] don't exits");
            }

            return Cache::remember($cacheKey, self::TTL_STORE_IDEMPOTENCY, function () use ($key, $value, $userId, $action) {

                $data = [KVS::FK_USER_ID => $userId, KVS::ATTR_KEY => $key];

                if ($action == "store") {
                    $data[KVS::ATTR_VALUE] = $value;
                }

                $model = KVS::query()->where(KVS::FK_USER_ID, $userId)->where(KVS::ATTR_KEY, $key)->firstOrNew($data);

                if ($action == "store" && $model->save()) {
                    return $this->successResponse(["key" => $key, "value" => json_decode($value, true)]);
                }

                if ($action == "update" && $model->updateOrFail([KVS::ATTR_VALUE => $value])) {
                    return $this->successResponse(json_decode($value, true));
                }

                throw new \InvalidArgumentException("Operation $action can not made.");
            });
        } catch (\Exception $e) {
            report($e);
            if (is_env_debug()) {
                throw $e;
            }
        }

        return $this->internalErrorResponse();
    }
}
