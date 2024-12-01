<?php

namespace AcornDB\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use UnexpectedValueException;

trait MetaFields
{
    protected array $builtInClasses = [
        \AcornDB\Model\Post::class =>  \AcornDB\Model\Meta\PostMeta::class,
        \Corcel\Model\Comment::class => \Corcel\Model\Meta\CommentMeta::class,
        \Corcel\Model\Term::class => \Corcel\Model\Meta\TermMeta::class,
        \Corcel\Model\User::class => \Corcel\Model\Meta\UserMeta::class,
    ];

    public function fields(): hasMany
    {
        return $this->meta();
    }

    public function meta(): hasMany
    {
        return $this->hasMany($this->getMetaClass(), $this->getMetaForeignKey());
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function getMetaClass(): string
    {
        foreach ($this->builtInClasses as $model => $meta) {
            if ($this instanceof $model) {
                return $meta;
            }
        }

        throw new UnexpectedValueException(sprintf(
            '%s must extends one of Corcel built-in models: Comment, Post, Term or User.',
            static::class
        ));
    }

    /**
     * @throws UnexpectedValueException
     */
    protected function getMetaForeignKey(): string
    {
        foreach ($this->builtInClasses as $model => $meta) {
            if ($this instanceof $model) {
                return sprintf('%s_id', strtolower(class_basename($model)));
            }
        }

        throw new UnexpectedValueException(sprintf(
            '%s must extends one of Corcel built-in models: Comment, Post, Term or User.',
            static::class
        ));
    }

    public function scopeHasMeta(
        Builder $query,
        array|string $meta,
        mixed $value = null,
        string $operator = '='
    ): Builder {
        if (!is_array($meta)) {
            $meta = [$meta => $value];
        }

        foreach ($meta as $key => $value) {
            $query->whereHas('meta', function (Builder $query) use ($key, $value, $operator) {
                if (!is_string($key)) {
                    return $query->where('meta_key', $operator, $value);
                }
                $query->where('meta_key', $operator, $key);

                return is_null($value) ? $query :
                    $query->where('meta_value', $operator, $value);
            });
        }

        return $query;
    }

    public function scopeHasMetaLike(Builder $query, string $meta, mixed $value = null): Builder
    {
        return $this->scopeHasMeta($query, $meta, $value, 'like');
    }

    public function saveField(string $key, mixed $value): bool
    {
        return $this->saveMeta($key, $value);
    }

    public function saveMeta(string|array $key, mixed $value = null): bool
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->saveOneMeta($k, $v);
            }
            $this->load('meta');

            return true;
        }

        return $this->saveOneMeta($key, $value);
    }

    private function saveOneMeta(string $key, mixed $value): bool
    {
        $meta = $this->meta()->where('meta_key', $key)
            ->firstOrNew(['meta_key' => $key]);

        $result = $meta->fill(['meta_value' => $value])->save();
        $this->load('meta');

        return $result;
    }

    public function createField(string $key, mixed $value): Model
    {
        return $this->createMeta($key, $value);
    }

    public function createMeta(array|string $key, mixed $value = null): Model|Collection
    {
        if (is_array($key)) {
            return collect($key)->map(function ($value, $key) {
                return $this->createOneMeta($key, $value);
            });
        }

        return $this->createOneMeta($key, $value);
    }

    private function createOneMeta(string $key, mixed $value): Model
    {
        $meta =  $this->meta()->create([
            'meta_key' => $key,
            'meta_value' => $value,
        ]);
        $this->load('meta');

        return $meta;
    }

    public function getMeta(string $attribute): mixed
    {
        if ($meta = $this->meta->{$attribute}) {
            return $meta;
        }

        return null;
    }
}
