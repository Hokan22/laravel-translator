<?php

namespace App\Console\Commands\Fixes;

use Hokan22\LaravelTranslator\Models\TranslationIdentifier;
use Hokan22\LaravelTranslator\TranslatorFacade;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;

class ClearUnusedTranslationsCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:schueco:add_missing_positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    protected $cache = [];

    /**
     * Create a new command instance.
     *
     */
    public function __construct() {
        parent::__construct();
    }

    public function handle(){

        // Get start time
        $start = microtime(true);
        $not_used = 0;

        $this->line('');

        $aFiles = $this->getAllIdentifier();

        $aDB = $this->loadFromDB();

        foreach ($aDB as $identifier) {

            if(!in_array($identifier, $aFiles)) {

                $this->line('\''.$identifier.'\' is not used anymore!');
                $task = $this->choice('What do you want me to do?', ['Nothing', 'Verify' ,'Remove'], 0);

                if ($task === 'Verify') {
                    $this->verifyMissing($identifier);
                    $task = $this->choice('What do you want me to do?', ['Nothing' ,'Remove'], 0);
                }


                $not_used++;
            }
        }

        $this->info($not_used.' Translations no longer used.');
        $this->line('');

        $this->info('Finished in: ' . number_format(microtime(true) - $start, 2) . 'sec');
    }

    /**
     * @return boolean
     */
    public function verifyMissing($string) {
        $aFiles = [];
        $found = false;

        $valid_extensions = ['php', 'html', 'js'];
        $folders = [
            'app',
            'resources/views',
            'resources/assets',
        ];

        foreach($folders as $folder){
            $aFiles = array_merge($aFiles, File::allFiles(base_path().'/'.$folder));
        }

        $num_files = count($aFiles);

        $this->bar = $this->output->createProgressBar($num_files);
        $this->bar->setMessage('Analyzing '.$num_files.' files');
        $this->bar->setFormat('very_verbose');

        /** @var File $file */
        foreach ($aFiles as $file) {

            $extension = $file->getExtension();

            if(in_array($extension, $valid_extensions)){

                $content = file_get_contents($file);
                if (str_contains($content, $string)){
                    $this->bar->clear();
                    $this->warn($string.' is used in: '. $file->getPath());
                    $found = true;
                    $this->bar->display();
                }

            }
            $this->bar->advance();
        }
        $this->bar->finish();
        $this->line('');

        if (!$found) $this->info($string.' does not occur as plain text!');

        return true;
    }

    /**
     * @return array
     */
    public function getAllIdentifier() {
        $aFiles = [];
        $return = [];

        $found_identifier = 0;

        $valid_extensions = ['php', 'html', 'js'];
        $folders = [
            'app',
            'resources/views',
            'resources/assets',
        ];

        $regexes = [
            'default'   => '/(?:[\@|\_]t\()\'(?\'identifier\'.*?)\'(?:\)|(?:, (?\'parameters\'\[.*\]))(?:\)|, \'(?\'locale\'\w*?)\'))/',
            'js'        => '/\$filter\(\'translate\'\)\(\'(?\'identifier\'.*?)\'\)/'
        ];

        foreach($folders as $folder){
            $aFiles = array_merge($aFiles, File::allFiles(base_path().'/'.$folder));
        }

        TranslatorFacade::setLocale('de_DE');

        $num_files = count($aFiles);

        $this->bar = $this->output->createProgressBar($num_files);
        $this->bar->setMessage('Analyzing '.$num_files.' files');
        $this->bar->setFormat('very_verbose');

        foreach ($aFiles as $file) {

            $extension = $file->getExtension();

            if(in_array($extension, $valid_extensions)){
                $content = file_get_contents($file);

                foreach ($regexes as $key => $regex) {
                    preg_match_all($regex, $content, $result, PREG_SET_ORDER);

                    if(!empty($result[0])){
                        foreach ($result as $item) {
                            $found_identifier++;
                            $return[] = $item['identifier'];
                        }
                    }
                }
            }
            $this->bar->advance();
        }

        $this->bar->finish();
        $this->line('');

        $this->info($found_identifier.' Translations found.');

        return $return;
    }

    /**
     * Get all translation identifier with translation from the given locale
     *
     * @return TranslationIdentifier|\Illuminate\Database\Eloquent\Collection|static[]
     */
    private function loadFromDB () {
        // Get all Texts with translations for the given locale
        $trans_identifier =   TranslationIdentifier::pluck('identifier')->all();

        return $trans_identifier;
    }
}
