<?php

namespace BumpCore\EditorPhp\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class EditorPhp implements CastsAttributes
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return \BumpCore\EditorPhp\EditorPhp
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return \BumpCore\EditorPhp\Facades\EditorPhp::load($value);
    }

    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof \BumpCore\EditorPhp\EditorPhp)
        {
            return $value->toJson();
        }

        return $value;
    }
}
