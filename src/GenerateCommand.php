<?php
namespace SymfonyMongoDBDocumentMaker;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Generate command for Symfony console component.
 * 
 * @author Samuel Tallet <samuel.tallet@gmail.com>
 */
class GenerateCommand extends Command {

    /**
     * Returns the list of supported mapping types by Doctrine MongoDB ODM.
     * 
     * @see https://www.doctrine-project.org/projects/doctrine-mongodb-odm/en/current/reference/basic-mapping.html#doctrine-mapping-types
     */
    private function getSupportedFieldTypes() : array {

        return [
            'bin',
            'bin_bytearray',
            'bin_custom',
            'bin_func',
            'bin_md5',
            'bin_uuid',
            'boolean',
            'collection',
            'custom_id',
            'date',
            'date_immutable',
            'decimal128',
            'file',
            'float',
            'hash',
            'id',
            'int',
            'key',
            'object_id',
            'raw',
            'string',
            'timestamp'
        ];

    }

    /**
     * Configures the "generate:document" command.
     */
    public function configure() : GenerateCommand {

        $this->setName('generate:document')
             ->setDescription('Generate a PHP class for a MongoDB document.');

        return $this;

    }

    /**
     * Executes the "generate:document" command.
     */
    public function execute(InputInterface $input, OutputInterface $output) : int {

        $questionHelper = $this->getHelper('question');

        $question = new Question(
            '<info>> Enter the name of the MongoDB collection.</info> <comment>(e.g. user)</comment>' . PHP_EOL
        );

        $collectionName = $questionHelper->ask($input, $output, $question);

        if ( is_null($collectionName) ) {

            $output->write('<error>Error: You entered no collection name.</error>');
            return 1;

        }

        $fieldName = null;
        $fields = [];

        do {

            $question = new Question(
                '<info>> Enter the name of a new MongoDB field.</info> <comment>(e.g. firstname)</comment>'
                . ' <info>or press enter if you want to stop adding fields.</info>' . PHP_EOL
            );

            $fieldName = $questionHelper->ask($input, $output, $question);

            if ( !is_null($fieldName) ) {

                $question = new Question(
                    '<info>> Enter the type of the "' . $fieldName . '" MongoDB field.</info>'
                    . ' <comment>(e.g. boolean, collection, date, int, string)</comment>' . PHP_EOL
                );
    
                $fieldType = $questionHelper->ask($input, $output, $question);

                if ( !in_array($fieldType, $this->getSupportedFieldTypes()) ) {

                    $output->write(
                        '<error>Error: You entered an invalid field type.'
                        . ' See: https://www.doctrine-project.org/projects/doctrine-mongodb-odm/en/current/reference/basic-mapping.html#doctrine-mapping-types</error>'
                    );
                    return 2;

                }

                $fields[$fieldName]['type'] = $fieldType;

            }

        } while ( !is_null($fieldName) );

        $phpClassGenerator = new PHPClassGenerator($collectionName, $fields);
        $phpClassFilename = $phpClassGenerator->generate();

        $output->write(
            '<info>PHP class successfully generated here: ' . $phpClassFilename . '.' . PHP_EOL
            . 'Now move this file to: ${SymfonyProjectDir}/src/Document.</info>'
        );

        return 0;

    }

}