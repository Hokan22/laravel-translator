<?php

namespace Hokan22\LaravelTranslator\Commands;

use Hokan22\LaravelTranslator\Models\TranslationIdentifier;
use Hokan22\LaravelTranslator\Models\Translations;
use Hokan22\LaravelTranslator\TranslatorFacade;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;

class ClearUnusedTranslationsCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translator:clear-unused';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    protected $cache = [];

    protected $found_identifier = 0;

    protected $folders;
    protected $extensions;


    /**
     * Create a new command instance.
     *
     */
    public function __construct() {
        parent::__construct();
    }

    public function handle() {
        // Get start time
        $start = microtime(true);
        $not_used = 0;
        $found_plain = 0;
        $removed = 0;

        $this->folders = TranslatorFacade::getConfigValue('search_folders');
        $this->extensions = TranslatorFacade::getConfigValue('search_extensions');

        $aFiles = $this->getAllIdentifier();

        $aDB = $this->loadFromDB();

        foreach ($aDB as $identifier) {

            if(!in_array($identifier->identifier, $aFiles)) {

                $found_as_plain = $this->verifyMissing($identifier->identifier);

                $this->line('');

                if ($found_as_plain) {
                    $this->warn('\''.$identifier->identifier.'\' was not found withing Translator directives');
                    $found_plain++;
                } else {
                    $this->line('\''.$identifier->identifier.'\' seems to be not used anymore');
                    $not_used++;
                }

                $task = $this->choice('What do you want me to do?', ['Nothing' ,'Remove'], 0);

                if ($task === 'Remove') {
                    $identifier->delete();
                    Translations::where('translation_identifier_id', $identifier->id)->delete();
                    $removed++;
                }
            }
        }
        
        $this->table(['Num', 'Identifier'],[
            [$this->found_identifier,  "In DB"],
            [$not_used,     "Not Found"],
            [$found_plain,  "Found Plain"],
            [$removed,  "Removed"],
        ]);
        
        $this->info($not_used.' Translations no longer used.');
        $this->line('');

        $this->info('Finished in: ' . number_format(microtime(true) - $start, 2) . 'sec');
    }

    /**
     * @param $identifier string The Identifier to search
     * @return boolean boolean True if Identifier was found false if it was not
     */
    public function verifyMissing($identifier) {
        $aFiles = [];
        $found = false;

        $valid_extensions = ['php', 'html', 'js'];
        $folders = [
            'app',
            'config',
            'resources/views',
            'resources/assets/js',
        ];

        foreach($folders as $folder){
            $aFiles = array_merge($aFiles, File::allFiles(base_path().'/'.$folder));
        }

        $num_files = count($aFiles);

        $this->bar = $this->output->createProgressBar($num_files);
        $this->bar->setMessage('Analyzing '.$num_files.' files');
        $this->bar->setFormat('very_verbose');

        $pattern = preg_quote($identifier, '/');
        $pattern = "/^.*$pattern.*\$/m";

        /** @var File $file */
        foreach ($aFiles as $file) {

            $extension = $file->getExtension();

            if(in_array($extension, $valid_extensions)){

                $content = file_get_contents($file);
                if (preg_match_all($pattern, $content, $matches)){
                    $this->bar->clear();
                    $this->warn('\''.$identifier.'\' is used in: \''. $file->getPath().'\'!');

                    foreach ($matches[0] as $match) {
                         $this->warn($match);
                    }
                    $found = true;
                    $this->bar->display();
                }
            }
            $this->bar->advance();
        }
        $this->bar->finish();

        return $found;
    }

    /**
     * @return array
     */
    public function getAllIdentifier() {
        $aFiles = [];
        $return = [];

        $regexes = [
            'default'   => '/(?:[\@|\_]t\()["\'](?\'identifier\'.*?)["\'](?:\)|(?:, (?\'parameters\'\[.*\]))(?:\)|, \'(?\'locale\'\w*?)\'))/',
            'js'        => '/\$filter\(\'translate\'\)\(\'(?\'identifier\'.*?)\'\)/'
        ];

        foreach($this->folders as $folder){
            $aFiles = array_merge($aFiles, File::allFiles(base_path().'/'.$folder));
        }

        //TranslatorFacade::setLocale('de_DE');

        $num_files = count($aFiles);

        $this->bar = $this->output->createProgressBar($num_files);
        $this->bar->setMessage('Analyzing '.$num_files.' files');
        $this->bar->setFormat('very_verbose');

        foreach ($aFiles as $file) {

            $extension = $file->getExtension();

            if(in_array($extension, $this->extensions)){
                $content = file_get_contents($file);

                foreach ($regexes as $key => $regex) {
                    preg_match_all($regex, $content, $result, PREG_SET_ORDER);

                    if(!empty($result[0])){
                        foreach ($result as $item) {
                            $this->found_identifier++;
                            $return[] = $item['identifier'];
                        }
                    }
                }
            }
            $this->bar->advance();
        }

        $this->bar->finish();
        $this->line('');

        $this->info($this->found_identifier.' Translations found.');

        return $return;
    }

    /**
     * Get all translation identifier with translation from the given locale
     *
     * @return TranslationIdentifier|\Illuminate\Database\Eloquent\Collection|static[]
     */
    private function loadFromDB () {
        // Get all Texts with translations for the given locale
        $trans_identifier =   TranslationIdentifier::select(['id', 'identifier'])->get();

        return $trans_identifier;
    }
}
