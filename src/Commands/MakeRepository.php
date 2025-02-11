<?php

namespace Nanvaie\DatabaseRepository\Commands;

//use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nanvaie\DatabaseRepository\Creators\BaseCreator;
use Nanvaie\DatabaseRepository\Creators\CreatorRepository;
use Nanvaie\DatabaseRepository\CustomMySqlQueries;

class MakeRepository extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:make-repository {table_name} {selected_db?}
    {--k|foreign-keys : Detect foreign keys}
    {--d|delete : Delete resource}
    {--f|force : Override/Delete existing repository class}
    {--g|add-to-git : Add created file to git repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository';

    use CustomMySqlQueries;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $this->setArguments();
//        dd($this->selectedDb);
        $repositoryName = $this->entityName.'Repository';
//        $sqlRepositoryName = 'MySql'.$this->entityName.'Repository';
        $sqlRepositoryName = ucwords($this->selectedDb).$this->entityName.'Repository';
        $sqlRepositoryVariable = 'repository';
        $relativeRepositoryPath = config('repository.path.relative.repositories') . "$this->entityName" . DIRECTORY_SEPARATOR;
        $repositoryStubsPath = __DIR__ . '/../../' . config('repository.path.stub.repositories.base');
        $filenameWithPath = $relativeRepositoryPath . $repositoryName . '.php';

        $this->checkDelete($filenameWithPath,$repositoryName,"Repository");
        $this->checkDirectory($relativeRepositoryPath);
        $this->checkClassExist($this->repositoryNamespace,$repositoryName,"Repository");

        $columns = $this->getAllColumnsInTable($this->tableName);
        $this->checkEmpty($columns,$this->tableName);

        $RepoCreator = new CreatorRepository(
            $columns,
            $sqlRepositoryVariable,
            $sqlRepositoryName,
            $repositoryStubsPath,
            $this->detectForeignKeys,
            $this->tableName,
            $this->entityVariableName,
            $this->entityName,
            $this->entityNamespace,
            $repositoryName,
            $this->interfaceName,
            $this->repositoryNamespace,
            $this->selectedDb
        );
        $creator = new BaseCreator($RepoCreator);
        $baseContent = $creator->createClass($filenameWithPath,$this);

        $this->finalized($filenameWithPath, $repositoryName, $baseContent);
    }
}
