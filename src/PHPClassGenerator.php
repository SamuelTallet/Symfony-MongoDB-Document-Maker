<?php
namespace SymfonyMongoDBDocumentMaker;

use Nayjest\StrCaseConverter\Str;

/**
 * PHP class generator.
 * 
 * @author Samuel Tallet <samuel.tallet@gmail.com>
 */
class PHPClassGenerator {

    /**
     * Indentation of generated code.
     * 
     * @var string
     */
    private const INDENT = '    ';

    /**
     * Name of MongoDB collection.
     * 
     * @var string
     */
    private $collectionName;

    /**
     * Metadata of MongoDB fields.
     * 
     * @var array
     */
    private $fields;

    public function __construct(string $collectionName, array $fields) {

        $this->collectionName = $collectionName;
        $this->fields = $fields;
        
    }

    /**
     * Generates a PHP class for a MongoDB document.
     * 
     * @return string The path of generated class. 
     */
    public function generate() : string {

        $phpClass = '<?php' . PHP_EOL;
        $phpClass .= 'namespace App\Document;' . PHP_EOL . PHP_EOL;

        $phpClass .= 'use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;' . PHP_EOL . PHP_EOL;

        $phpClass .= '/**' . PHP_EOL;
        $phpClass .= ' * @MongoDB\Document(collection="' . $this->collectionName . '")' . PHP_EOL;
        $phpClass .= ' */' . PHP_EOL;
        $phpClass .= 'class ' . Str::toCamelCase($this->collectionName) . ' {' . PHP_EOL . PHP_EOL;

        // Properties.

        $phpClass .= self::INDENT . '/**' . PHP_EOL;
        $phpClass .= self::INDENT . ' * @MongoDB\Id' . PHP_EOL;
        $phpClass .= self::INDENT . ' */' . PHP_EOL;
        $phpClass .= self::INDENT . 'private $id;' . PHP_EOL . PHP_EOL;

        foreach ($this->fields as $fieldName => $fieldMetadata) {

            $phpClass .= self::INDENT . '/**' . PHP_EOL;
            $phpClass .= self::INDENT . ' * @MongoDB\Field(type="' . $fieldMetadata['type'] . '")' . PHP_EOL;
            $phpClass .= self::INDENT . ' */' . PHP_EOL;
            $phpClass .= self::INDENT . 'private $' . $fieldName . ';' . PHP_EOL . PHP_EOL;

        }

        // Getters and setters.

        $phpClass .= self::INDENT . 'public function getId() {' . PHP_EOL;
        $phpClass .= self::INDENT . self::INDENT . 'return $this->id;' . PHP_EOL;
        $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

        $phpClass .= self::INDENT . 'public function setId($id) {' . PHP_EOL;
        $phpClass .= self::INDENT . self::INDENT . '$this->id = $id;' . PHP_EOL;
        $phpClass .= self::INDENT . self::INDENT . 'return $this;' . PHP_EOL;
        $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

        foreach ($this->fields as $fieldName => $fieldMetadata) {

            $phpClass .= self::INDENT . 'public function ' . ( $fieldMetadata['type'] === 'boolean' ? 'is' : 'get' ) . Str::toCamelCase($fieldName) . '() {' . PHP_EOL;
            $phpClass .= self::INDENT . self::INDENT . 'return $this->' . $fieldName . ';' . PHP_EOL;
            $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

            $phpClass .= self::INDENT . 'public function set' . Str::toCamelCase($fieldName) . '($' . $fieldName . ') {' . PHP_EOL;
            $phpClass .= self::INDENT . self::INDENT . '$this->' . $fieldName . ' = $' . $fieldName . ';' . PHP_EOL;
            $phpClass .= self::INDENT . self::INDENT . 'return $this;' . PHP_EOL;
            $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

        }

        $phpClass .= '}';

        $phpClassFilename = __DIR__ . '/../' . Str::toCamelCase($this->collectionName) . '.php';

        if ( file_put_contents($phpClassFilename, $phpClass) === false ) {
            throw new \Exception('Impossible to write the generated PHP class.');
        }

        $phpClassFilename = realpath($phpClassFilename);

        if ( $phpClassFilename === false ) {
            throw new \Exception('Impossible to get the absolute path to the generated PHP class.');
        }

        return $phpClassFilename;

    }

}