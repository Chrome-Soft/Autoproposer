<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\Segment\ExpressionNormalizer;


use Illuminate\Support\Str;

class NormalizerFactory
{
    public static function create($field, $relation, $value, string $whereFunction = 'where')
    {
        if ($field == 'search_term')
            return new SearchTermNormalizer($field, $relation, $whereFunction, $value);

        if ($relation == 'IS NULL' || $relation == 'IS NOT NULL')
            return new NullableNormalizer($field, $relation, $whereFunction);

        if ($field == 'created_at')
            return new CreatedAtNormalizer('user_data.' . $field, $relation, $whereFunction, $value);

        if ($field == 'visited_url')
            return new VisitedUrlNormalizer($field, $relation, $whereFunction, $value);

        if ($field == 'visited_path')
            return new VisitedPathNormalizer($field, $relation, $whereFunction, $value);

        if (Str::contains($field, 'version'))
            return new VersionNormalizer($field, $relation, $whereFunction, $value);

        if ($relation == 'LIKE' || $relation == 'NOT LIKE')
            return new ContainsNormalizer($field, $relation, $whereFunction, $value);

        if (is_numeric($value))
            return new NumericNormalizer($field, $relation, $whereFunction, $value);

        switch (strtolower($value)) {
            case 'igen':
            case 'nem':
                return new BoolNormalizer($field, $relation, $whereFunction, $value);
                break;
        }

        return new StringNormalizer($field, $relation, $whereFunction, $value);
    }
}
