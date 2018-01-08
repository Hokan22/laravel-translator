<?php
/*
 * File:     TranslationIdentifier.php
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
 * Class TranslationIdentifier
 * @package App\Models
 *
 * @property int        $id
 * @property string     $identifier
 * @property array      $parameters
 * @property string     $page_name
 * @property string     $description
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property-read Translations|null $text_translations
 *
 * @method static Builder|TranslationIdentifier findOrFail($value)
 * @method static Builder|TranslationIdentifier whereBody($value)
 * @method static Builder|TranslationIdentifier whereCommentableId($value)
 * @method static Builder|TranslationIdentifier whereCommentableType($value)
 * @method static Builder|TranslationIdentifier whereCreatedAt($value)
 * @method static Builder|TranslationIdentifier whereId($value)
 * @method static Builder|TranslationIdentifier whereUpdatedAt($value)
 * @method static Builder|TranslationIdentifier whereUserId($value)
 * @method static Builder|TranslationIdentifier whereHas($relation, \Closure $callback = null, $operator = '>=', $count = 1)
 * @method static Builder|TranslationIdentifier where($column, $operator = null, $value = null, $boolean = 'and')
 */
class TranslationIdentifier extends Model {

    /**
     * @var array
     */
    // TODO: Translation Group (JS, Server)
    protected $fillable = [
        'identifier',
        'parameters',
        'group',
        'page_name',
        'description',
    ];

    protected $casts = [
        'parameters' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations(){
        return $this->hasMany(Translations::class);
    }

}