<?php

namespace AcornDB\Model\Meta;

use Corcel\Model;
use Corcel\Model\Collection\MetaCollection;

abstract class Meta extends Model
{
    /**
     * @var string
     */
    protected $primaryKey = 'meta_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $appends = ['value'];

    public function getValueAttribute(): mixed
    {
        $metaValue = $this->meta_value;

        if ($this->isSerialized($metaValue)) {
            try {
                return unserialize($metaValue);
            } catch (\Exception $e) {
                return $metaValue;
            }
        }

        return $metaValue;
    }

    protected function isSerialized($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);

        return preg_match('/^(a|O|s|i|d):[0-9]+:/', $value);
    }

    public function newCollection(array $models = []): MetaCollection
    {
        return new MetaCollection($models);
    }
}
