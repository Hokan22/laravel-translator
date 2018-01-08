<?php
/*
 * File:     Translations.php
 * Category: Model
 * Author:   alexander
 * Created:  13.11.2017 12:20
 * Updated:  -
 *
 * Description:
 *  -
 */

namespace Hokan22\LaravelTranslator\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


/**
 * Class Translations
 * @package App\Models
 *
 * @property int        $id
 * @property string     $locale
 * @property string     $translation
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property-read TranslationIdentifier $text
 *
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
 */
class Translations extends Model {


    protected $primaryKey = 'this_model_uses_composite_keys';

    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'locale',
        'translation',
    ];

    protected $touches = ['translationIdentifier'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function translationIdentifier(){
        return $this->belongsTo(TranslationIdentifier::class);
    }

}