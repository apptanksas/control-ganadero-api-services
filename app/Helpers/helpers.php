<?php

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

if (!function_exists("log_debug")) {

    /**
     * Log a debug message
     *
     * @param Throwable $e
     */
    function log_debug(\Throwable $e)
    {
        $message = "[" . $e->getFile() . ":" . $e->getCode() . "]" . $e->getMessage();

        if (is_env_production()) {
            logger($message);
            return;
        }

        if (is_env_debug() && (is_null(Request::capture()->getContentType()) || Request::capture()->isJson())) {
            return;
        }

        print($message . "\n");
    }

}

if (!function_exists("cast")) {

    /**
     * Cast a object class to another other class
     *
     * @param mixed $object
     * @param string $class
     * @return mixed
     */
    function cast($object, $class): mixed
    {
        return unserialize(sprintf('O:%d:"%s"%s', strlen($class), $class, strstr(strstr(serialize($object), '"'), ':')));
    }

}

if (!function_exists('generateUrl')) {
    /**
     * Generate the URL to a named route.
     *
     * @param string $routeName
     * @param mixed $parameters
     * @param bool $asSlug
     * @param bool $absolute
     * @return string
     */
    function generateUrl(string $routeName, $parameters = [], $asSlug = true, $absolute = true): string
    {
        if ($asSlug) {
            foreach ($parameters as $key => $value) {
                $parameters[$key] = Str::slug($value);
            }
        }

        return route($routeName, $parameters, $absolute);
    }
}

if (!function_exists('decodeSlug')) {

    /**
     * Decode a slug text
     *
     * @param string $slug
     * @return string
     */
    function decodeSlug(string $slug): string
    {
        $text = preg_replace("/[-_]/i", " ", $slug);
        return trim(ucwords($text));
    }
}

if (!function_exists('updateVariableEnvironment')) {

    function updateVariableEnvironment(string $variableName, string $currentValue, string $newValue)
    {
        $path = App::environmentFilePath();

        $escaped = preg_quote('=' . $currentValue, '/');
        $search = "/^{$variableName}{$escaped}/m";

        if (!file_exists($path)) {
            return;
        }

        file_put_contents($path, preg_replace($search, "$variableName=" . $newValue, file_get_contents($path)));
    }

}

if (!function_exists("get_resource")) {

    function get_resource(string $resource, string $type): string
    {
        if (config("app.asset_url") && is_env_production()) {
            return asset("res/" . $type . "/$resource.$type") . "?v=" . config("app.version");
        }

        return url("$type/$resource.$type") . "?v=" . config("app.version");
    }
}

if (!function_exists("trans_plural")) {

    function trans_plural(string $key, int $quantity, string $quantityDisplay = null): string
    {
        $str = trans("plural.$key");

        if ($quantity == 1) {
            return sprintf($str[0], $quantityDisplay ?? $quantity);
        }

        return sprintf($str[1], $quantityDisplay ?? $quantity);
    }

}

if (!function_exists("is_null_empty")) {

    #[Pure] function is_null_empty($value): bool
    {
        return is_null($value) || empty($value);
    }

}

if (!function_exists("is_not_empty")) {

    function is_not_empty($value): bool
    {
        return !empty($value);
    }
}


if (!function_exists("is_not_null")) {

    #[Pure] function is_not_null($value): bool
    {
        return !is_null($value);
    }

}

if (!function_exists("is_env_production")) {
    function is_env_production(): bool
    {
        return config("app.env") === "production";
    }
}

if (!function_exists("is_env_dev")) {

    function is_env_debug(): bool
    {
        return config("app.env") === "dev" || config("app.env") === "debug" || config("app.env") === "local";
    }
}

if (!function_exists("is_env_test")) {

    function is_env_test(): bool
    {
        return config("app.env") === "test" || config("app.env") === "testing";
    }
}


if (!function_exists("extract_query_sql")) {
    function extract_query_sql($query): string
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }
}

if (!function_exists("parse_bool_val")) {

    #[Pure] function parse_bool_val($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

if (!function_exists("is_encrypted")) {

    #[Pure] function is_encrypted(string $value): bool
    {
        return strlen($value) > 100;
    }
}

if (!function_exists("collection_to_list")) {

    #[Pure] function collection_to_list(array|Collection $collection, string $modelClass = null): array
    {
        return BaseModel::collectionQueryToList($collection, $modelClass);
    }
}

if (!function_exists("clone_object")) {
    function clone_object(mixed $arr): mixed
    {
        return unserialize(serialize($arr));
    }
}

if (!function_exists("url_image")) {
    function url_image($pathImage): string
    {
        if (filter_var($pathImage, FILTER_VALIDATE_URL)) {
            return $pathImage;
        }

        return url($pathImage);
    }
}

if (!function_exists("array_merge_all")) {
    function array_merge_all(array $data): array
    {
        $output = [];

        if (!array_contains_arrays($data)) {
            return $data;
        }

        foreach ($data as $index => $value) {
            $output = array_merge($output, array_merge_all($value));
        }
        return $output;
    }
}

if (!function_exists("array_contains_arrays")) {
    function array_contains_arrays(array $data): bool
    {
        return isset($data[array_key_first($data)]) &&
            isset($data[array_key_last($data)]) &&
            is_array($data[array_key_first($data)]) &&
            is_array($data[array_key_last($data)]);
    }
}
if (!function_exists("array_rand_weighted")) {
    function array_rand_weighted(array $values, array $weights, int $num = 1): mixed
    {
        $count = count($values);
        $i = 0;
        $n = 0;
        $base = mt_rand(1, array_sum($weights));
        while ($i < $count) {
            $n += $weights[$i];
            if ($n >= $base) {
                break;
            }
            $i++;
        }

        if ($num == 1) {
            return $values[$i];
        }

        $output = [];
        while (count($output) < $num) {
            $selected = array_rand_weighted($values, $weights);
            if (in_array($selected, $output)) {
                continue;
            }
            $output[] = $selected;
        }

        return $output;
    }
}


if (!function_exists("abortJSONResponse")) {

    function abortJSONResponse($code, $message, string $codeError): JsonResponse
    {
        $data = ["status" => $code, "error" => [
            "code" => $codeError,
            "message" => $message
        ]];
        return response()->json($data)->setStatusCode($code);
    }

}
