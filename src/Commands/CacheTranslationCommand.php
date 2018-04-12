<?php

/**
 * Artisan Command to cache Translations from the Database.
 */
namespace Hokan22\LaravelTranslator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Hokan22\LaravelTranslator\TranslatorFacade;
use Hokan22\LaravelTranslator\Models\TranslationIdentifier;

/**
 * Class CacheTranslationCommand
 *
 * @package  Hokan22\LaravelTranslator\commands
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */
class CacheTranslationCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translator:cache {locale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command will cache the Translation in JSON Format';

    protected $cache = [];

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $locale = $this->argument('locale');

        if (!is_string($locale))
        {
            $this->warn("Please specify only one locale");
            return;
        }

        $file_path = TranslatorFacade::getConfigValue('cache_path').$locale.'/';

        $groups = $this->getGroups();
        $translations = $this->loadFromDB($locale);

        if (!file_exists($file_path)) {
            $this->alert("The defined cache folder (".$file_path.") does not exists.");
            if (!$this->confirm('Do you want to create it now?')) {
                return;
            }
            mkdir($file_path, 0775, true);
        }

        foreach ($groups as $key => $group) {
            $array = [];

            $tmp = $translations->where('group', $group);

            foreach ($tmp as $identifier) {
                if ($identifier->translations->count() <= 0) {
                    $array[$identifier->identifier] = $identifier->identifier;
                } elseif ($identifier->translations()->first()->translation == null) {
                    $array[$identifier->identifier] = $identifier->identifier;
                } else {
                    $array[$identifier->identifier] = $identifier->translations()->first()->translation;
                }
            }

            if (!empty($array)) {
                $file_name = $file_path.$group.'.json';
                file_put_contents($file_name, json_encode($array));
            }
        }
    }

    /**
     * Get all used groups from translation identifiers
     *
     * @return array
     */
    protected function getGroups()
    {
        return DB::table('translation_identifiers')->select('group')->groupBy(['group'])->get()->pluck('group');
    }

    /**
     * Get all translation identifier with translation from the given locale
     *
     * @param string $locale The locale from which the translations to load
     * @return TranslationIdentifier|\Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function loadFromDB($locale)
    {
        $trans_identifier = new TranslationIdentifier();

        $trans_identifier = $trans_identifier->with('translations')->whereHas('translations', function ($item) use ($locale)
        {
            return $item->where('locale', $locale);
        }
        )->orWhereHas('translations', null, '<=', 0)
            ->get();

        return $trans_identifier;
    }
}