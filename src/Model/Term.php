<?php

namespace AcornDB\Model;

use Corcel\Concerns\AdvancedCustomFields;
use AcornDB\Concerns\MetaFields;
use Corcel\Model;
use Corcel\Model\Taxonomy;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Term extends Model
{
    use MetaFields;
    use AdvancedCustomFields;

    /**
     * @var string
     */
    protected $table = 'terms';

    /**
     * @var string
     */
    protected $primaryKey = 'term_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    public function taxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'term_id');
    }
}
