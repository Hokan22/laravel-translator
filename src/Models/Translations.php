<?php

/**
 * Model
 */
namespace Hokan22\LaravelTranslator\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


/**
 * Class Translations
 *
 * @property int        $id
 * @property string     $locale
 * @property string     $translation
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property-read TranslationIdentifier $text
 *
 * @method static Builder|Translations create(array $attributes = [])
 * @method static Builder|Translations findOrFail($value)
 * @method static Builder|Translations whereBody($value)
 * @method static Builder|Translations whereCommentableId($value)
 * @method static Builder|Translations whereCommentableType($value)
 * @method static Builder|Translations whereCreatedAt($value)
 * @method static Builder|Translations whereId($value)
 * @method static Builder|Translations whereUpdatedAt($value)
 * @method static Builder|Translations whereUserId($value)
 * @method static Builder|Translations whereHas($relation, \Closure $callback = null, $operator = '>=', $count = 1)
 * @method static Builder|Translations where($column, $operator = null, $value = null, $boolean = 'and')
 *
 * @package  Hokan22\LaravelTranslator\Models
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */
class Translations extends Model
{
    /** @var string This is set to get more information when eloquent fails to access the model with a composite key */
    protected $primaryKey = 'this_model_uses_composite_keys';

    public $incrementing = false;

    /**
     * @var array $fillable Database fields fillable by eloquent
     */
    protected $fillable = [
        'locale',
        'translation',
    ];

    /** @var array $touches Set to automatically update the timestamp from the according identifier */
    protected $touches = ['translationIdentifier'];

    /**
     * Return the relation to the TranslationIdentifier
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function translationIdentifier() {
        return $this->belongsTo(TranslationIdentifier::class);
    }
}