<?php

namespace AcornDB\Model\Meta;

use AcornDB\Model\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMeta extends Meta
{
    /**
     * @var string
     */
    protected $table = 'postmeta';

    /**
     * @var array
     */
    protected $fillable = ['meta_key', 'meta_value', 'post_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
