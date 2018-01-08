<?php
/*
 * File:     LanguageCommand.php
 * Category: -
 * Author:   MSG
 * Created:  02.08.17 12:08
 * Updated:  -
 *
 * Description:
 *  -
 */


namespace Hokan22\LaravelTranslator\commands;

use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use Hokan22\LaravelTranslator\TranslatorFacade;

/**
 * Class SearchTranslationsCommand
 * @package App\Console\Commands
 */
class SearchTranslationsCommand extends Command{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "translator:search";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Searches through all used translations in PHP files. After gathering Strings to translate, they will be inserted into the Database for further treatment.";

    private $found_identifier = 0;
    private $found_parameters = 0;
    private $found_invalid = 0;
    private $new_identifier = 0;
    private $dupl_identifier = 0;

    private $bar;

    /**
     * Create a new command instance.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(){

        // Get start time
        $start = microtime(true);


        $this->line('');

        $aFiles = [];

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
                        $this->addMissing($result, $key);
                    }
                }
            }
            $this->bar->advance();
        }

        $this->bar->finish();
        $this->line('');
        $this->line('');

        $this->table(['Num', 'Translations...'],[
            [$this->found_identifier, "Found"],
            [$this->new_identifier,   "New"],
            [$this->dupl_identifier,  "Duplicates"],
            [$this->found_parameters, "With Parameters"],
            [$this->found_invalid,    "Invalid"],
        ]);

        $this->line('');

        $this->info('Finished in: ' . number_format(microtime(true) - $start, 2) . 'sec');
    }

    private function addMissing($result, $group) {

        foreach ($result as $item) {
            try{

                $identifier = trim($item['identifier']);
                $this->found_identifier++;

                $parameters = null;

                if (isset($item['parameters'])) {

                    $parameter_string = $item['parameters'];

                    if (substr($parameter_string, 0, 1 ) === '[') {
                        $parameter_string = substr($parameter_string, 1, -1);
                    }

                    $parameter_array = explode(",",$parameter_string);

                    $parameters = array();
                    foreach($parameter_array as $parameter )
                    {
                        $parameter = explode("=>",$parameter);

                        $key = str_replace([" ", "'"],"", $parameter[0]);
                        $value = str_replace([" ", "'"],"", $parameter[1]);

                        $parameters[$key] = $value;
                    }

                    $this->found_parameters++;
                }

                if(!isset($aTranslations[$identifier])){
                    $aTranslations[$identifier] = TranslatorFacade::hasIdentifier($identifier);

                    if (! $aTranslations[$identifier]) {
                        TranslatorFacade::addMissingIdentifier($identifier, $parameters, $group);
                        $this->bar->clear();
                        $this->info('Adding: "'.$identifier.'" to database');
                        $this->bar->display();
                        $this->new_identifier++;
                    }
                }
                else {
                    $this->dupl_identifier++;
                }

            }catch(\Exception $e){
                $this->bar->clear();
                $this->info($identifier.' '.strlen($identifier));
                $this->info($e->getMessage());
                $this->line('');
                $this->bar->display();
                $this->found_invalid++;
            }
        }
    }
}