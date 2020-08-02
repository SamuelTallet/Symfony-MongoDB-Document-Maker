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
     * Metadata of MongoDB collection.
     * 
     * @var array
     */
    private $collection;

    /**
     * Metadata of MongoDB fields.
     * 
     * @var array
     */
    private $fields;

    public function __construct(array $collection, array $fields) {

        $this->collection = $collection;
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

        $phpClass .= 'use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;' . PHP_EOL;
        $phpClass .= 'use Doctrine\Common\Collections\ArrayCollection;' . PHP_EOL . PHP_EOL;

        $phpClass .= '/**' . PHP_EOL;
        $phpClass .= ' * @MongoDB\\' . ( $this->collection['is_embedded'] ? 'Embedded' : '' ) . 'Document(collection="' . $this->collection['name'] . '")' . PHP_EOL;
        $phpClass .= ' */' . PHP_EOL;
        $phpClass .= 'class ' . Str::toCamelCase($this->collection['name']) . ' {' . PHP_EOL . PHP_EOL;

        // Properties.

        $phpClass .= self::INDENT . '/**' . PHP_EOL;
        $phpClass .= self::INDENT . ' * @MongoDB\Id' . PHP_EOL;
        $phpClass .= self::INDENT . ' */' . PHP_EOL;
        $phpClass .= self::INDENT . 'private $id;' . PHP_EOL . PHP_EOL;

        foreach ($this->fields as $fieldName => $fieldMetadata) {

            $phpClass .= self::INDENT . '/**' . PHP_EOL;

            if ( array_key_exists('type', $fieldMetadata) ) {

                $phpClass .= self::INDENT . ' * @MongoDB\Field(type="' . $fieldMetadata['type'] . '")' . PHP_EOL;

            } elseif ( array_key_exists('embed_one', $fieldMetadata) ) {

                $phpClass .= self::INDENT . ' * @MongoDB\EmbedOne(targetDocument=' . Str::toCamelCase($fieldMetadata['embed_one']) . '::class)' . PHP_EOL;

            } elseif ( array_key_exists('embed_many', $fieldMetadata) ) {

                $phpClass .= self::INDENT . ' * @MongoDB\EmbedMany(targetDocument=' . Str::toCamelCase($fieldMetadata['embed_many']) . '::class)' . PHP_EOL;

            }

            $phpClass .= self::INDENT . ' */' . PHP_EOL;
            $phpClass .= self::INDENT . 'private $' . $fieldName . ';' . PHP_EOL . PHP_EOL;

        }

        // Constructor.

        $phpClass .= self::INDENT . 'public function __construct() {' . PHP_EOL;

        foreach ($this->fields as $fieldName => $fieldMetadata) {

            if ( array_key_exists('embed_many', $fieldMetadata) ) {

                $phpClass .= self::INDENT . self::INDENT . '$this->' . $fieldName . ' = new ArrayCollection();' . PHP_EOL;
            
            }

        }

        $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

        // Getters and setters.

        $phpClass .= self::INDENT . 'public function getId() {' . PHP_EOL;
        $phpClass .= self::INDENT . self::INDENT . 'return $this->id;' . PHP_EOL;
        $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

        $phpClass .= self::INDENT . 'public function setId($id) {' . PHP_EOL;
        $phpClass .= self::INDENT . self::INDENT . '$this->id = $id;' . PHP_EOL;
        $phpClass .= self::INDENT . self::INDENT . 'return $this;' . PHP_EOL;
        $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

        foreach ($this->fields as $fieldName => $fieldMetadata) {

            $phpClass .= self::INDENT . 'public function ' . ( array_key_exists('type', $fieldMetadata) && $fieldMetadata['type'] === 'boolean' ? 'is' : 'get' ) . Str::toCamelCase($fieldName) . '() {' . PHP_EOL;
            $phpClass .= self::INDENT . self::INDENT . 'return $this->' . $fieldName . ';' . PHP_EOL;
            $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

            $phpClass .= self::INDENT . 'public function set' . Str::toCamelCase($fieldName) . '($' . $fieldName . ') {' . PHP_EOL;
            $phpClass .= self::INDENT . self::INDENT . '$this->' . $fieldName . ' = $' . $fieldName . ';' . PHP_EOL;
            $phpClass .= self::INDENT . self::INDENT . 'return $this;' . PHP_EOL;
            $phpClass .= self::INDENT . '}' . PHP_EOL . PHP_EOL;

        }

        $phpClass .= '}';

        $phpClassFilename = __DIR__ . '/../' . Str::toCamelCase($this->collection['name']) . '.php';

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