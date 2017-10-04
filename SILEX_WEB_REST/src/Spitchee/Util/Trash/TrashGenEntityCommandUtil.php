<?php

// Bullshit 

// TO-DO : Si une autre customCommand, classe abstraite parente abstractisant l'interface avec une methode execute(logique) et une register(options), et un enfant par commande

const ENTITY_DIR = '/Users/Nico/projets/spitchee/backoffice/real/SILEX_WEB_REST/src/Spitchee/Entity';

(new ConsoleManager())->prepareEntity()->getBuilder()->writeEntity();


class Builder {
    private $entity;

    public function __construct(Entity $Entity) {
        $this->entity = $Entity;
    }

    public function writeEntity() {
        file_put_contents(
            ENTITY_DIR . '/' . $this->entity->getName() . '.php',
            $this->buildContent()
        );
    }

    private function buildContent() {
        $content  = '<?php' . PHP_EOL;
        $content .= PHP_EOL . $this->buildNamespace() . PHP_EOL;
        $content .= PHP_EOL . $this->buildUses();
        $content .= PHP_EOL . $this->buildClass();

        return $content;
    }

    private function buildUses() {
        $uses = array(
            'Doctrine\\ORM\\Mapping\\Column', 'Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\GeneratedValue',
            'Doctrine\\ORM\\Mapping\\Id', 'Doctrine\\ORM\\Mapping\\JoinColumn', 'Doctrine\\ORM\\Mapping\\JoinTable',
            'Doctrine\\ORM\\Mapping\\ManyToMany', 'Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\OneToOne',
            'Doctrine\\ORM\\Mapping\\ManyToOne', 'Doctrine\\ORM\\Mapping\\Table', 'Gedmo\\Mapping\\Annotation as Gedmo'
        );
        $str = '';

        foreach ($uses as $use) {
            $str .= 'use ' . $use . ';' . PHP_EOL;
        }

        return $str;
    }

    private function buildNamespace() {
        return 'namespace Entity;';
    }

    private function buildClass() {
        $content = '/**' . PHP_EOL .
            ' * @Entity(repositoryClass="' . $this->entity->getName() . 'Repository")' . PHP_EOL .
            ' * @Table(name="' . $this->entity->getName() . '")' . PHP_EOL;

        if ($this->entity->getIsGedmo())
            $content .= ' * @Gedmo\\SoftDeleteable(fieldName="deleted")' . PHP_EOL;

        $content .= ' */' . PHP_EOL;

        $content .= 'class ' . $this->entity->getName() . PHP_EOL  . '{' . PHP_EOL;

        $content .= $this->buildIdField() . PHP_EOL;

        foreach ($this->getFieldsSorted() as $field) {
            $content .= $field->getClassAttributeImplementation() . PHP_EOL;
        }

        $content .= '}';

        return $content;
    }

    private function buildIdField() {
        return Field::getCommentSurronded(
            Field::getStrSurronded(
                ' * @Column(name="id", type="integer")'
            ) .
            Field::getStrSurronded(
                ' * @Id()'
            ) .
            Field::getStrSurronded(
                ' * @GeneratedValue(strategy="IDENTITY")'
                , false)
        ) . Field::getStrSurronded('protected $id;');
    }

    /**
     * @return Field[]
     */
    private function getFieldsSorted() {
        $response = array();
        $gedmos = array();
        $relations = array();
        $simples = array();

        foreach ($this->entity->Fields as $field) {
            if ($field->getType()->getName() === 'gedmos')
                $gedmos[] = $field;
            elseif ($field->getType()->isRelationship())
                $relations[] = $field;
            else
                $simples[] = $field;
        }

        if (count($simples))
            $response = $simples;
        if (count($relations))
            $response = array_merge($response, $relations);
        if (count($gedmos))
            $response = array_merge($response, $gedmos);

        return $response;
    }
}

class Field {
    /**
     * @var Type
     */
    private $Type;
    private $name;
    public $nullable = false;
    public $unique = false;
    private $dynOptions = array();

    public function getType() { return $this->Type; }
    public function setType(Type $type)   { $this->Type = $type; }
    public function setDynOpt($key, $val) { $this->dynOptions[$key] = $val; }
    public function setName($name)  { $this->name = $name; }
    public function getDynOpt($key) { return isset($this->dynOptions[$key]) ? $this->dynOptions[$key] : ''; }
    private function getStaticOptionsStr() { return ($this->nullable ? ', nullable=true' : '') . ($this->unique ? ', unique=true' : ''); }
    static public function getStaticOptions() { return ['nullable', 'unique']; } // Doit avoir exatement le meme nom que la propriété
    static public function getStrSurronded($str, $eol = true) { return chr(9) . $str . ($eol ? PHP_EOL : ''); }
    static public function getCommentSurronded($str) { return self::getStrSurronded('/**') . $str . PHP_EOL . self::getStrSurronded(' */'); }
    private function getRelationType() {
        if (! $this->getType()->isRelationship())
            return null;
        if ($this->getType()->getName() === 'OneToMany')
            return 'mappedBy';
        if ($this->getType()->getName() === 'ManyToOne')
            return 'inversedBy';
        if ($this->getDynOpt('leader(1/0)') !== '1')
            return 'mappedBy';

        return 'inversedBy';
    }
    public function getClassAttributeImplementation() {

        if ($this->getType()->getName() === 'gedmos') {
            $dateStrType = 'type="' . $this->getDynOpt('datekind') . '"';

            return $this->getCommentSurronded(
                $this->getStrSurronded(' * @Column(name="created", ' . $dateStrType . ')') .
                $this->getStrSurronded(' * @Gedmo\\Timestampable(on="create")', false)
            ) . $this->getStrSurronded('protected $created;') . PHP_EOL .
            $this->getCommentSurronded(
                $this->getStrSurronded(' * @Column(name="updated", ' . $dateStrType . ')') .
                $this->getStrSurronded(' * @Gedmo\\Timestampable(on="create")', false)
            ) . $this->getStrSurronded('protected $updated;') . PHP_EOL .
            $this->getCommentSurronded(
                $this->getStrSurronded(' * @Column(name="deleted", ' . $dateStrType . ', nullable=true)', false)
            ) . $this->getStrSurronded('protected $deleted;');
        }

        if ($this->getType()->isRelationship()) {
            $relation = $this->getType()->getName();
            $targetEntity = $this->getDynOpt('targetEntity');
            $relationType = $this->getRelationType();

            $str = $this->getStrSurronded(
                ' * @' . $relation . '(targetEntity="' . $targetEntity . '", ' . $relationType . '=' . 'TODO)', false
            );

            if ($relationType === 'inversedBy') {
                $str .= PHP_EOL;

                if ($relation === 'ManyToOne' or 'OneToOne' === $relation)
                    $str .= $this->getStrSurronded(
                        ' * @JoinColumn(referencedColumnName="id", name="' . $this->name . '_id")', false
                    );
                else
                    $str .= $this->getStrSurronded(
                        ' * @JoinTable(name=TODO)', false
                    );
            }
            return $this->getCommentSurronded($str) . $this->getStrSurronded('protected $' . $this->name . ';');
        }

        $sqlName = '';
        foreach (str_split($this->name) as $char) {
            $asciiDec = ord($char);
            if ($asciiDec > 64 and 91 > $asciiDec)
                $sqlName .= '_' . strtolower($char);
            else
                $sqlName .= $char;
        }

        return $this->getCommentSurronded(
            $this->getStrSurronded(' * @Column(name="' . $sqlName . '", type="' . $this->getType()->getName() . '"' .
                ($this->getDynOpt('length') ? ', length=' . $this->getDynOpt('length') : '') . $this->getStaticOptionsStr() . ')'
                , false)
        ) . $this->getStrSurronded('protected $' . $this->name . ';');
    }
}

class Type {
    private $name;
    public $alias;
    private $requiredOptions;

    public function __construct($name, $alias = null, $reqOptions = array()) {
        $this->name = $name;
        $this->alias = $alias;
        $this->requiredOptions = is_string($reqOptions) ? [$reqOptions] : $reqOptions;
    }

    public function getNames()   { return [$this->name, $this->alias]; }
    public function getReqOpts() { return $this->requiredOptions; }
    public function getName()    { return $this->name; }
    public function isRelationship() { foreach (['OO', 'OM', 'MO', 'MM'] as $val) {
        if ($this->alias === $val) return true; }
        return false; }
}

class Entity {
    /**
     * @var Field[]
     */
    public $Fields = array();
    private $isGedmo = null;
    private $name;

    public function addField(Field $field) {
        $this->Fields[] = $field;
        if ($field->getType()->getName() === 'gedmos')
            $this->isGedmo = true;
    }
    public function getIsGedmo() {
        return $this->isGedmo;
    }

    public function getBuilder() { return new Builder($this); }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
}

class ConsoleManager {
    /**
     * @var Type[]
     */
    private $Types;
    /**
     * @var Entity
     */
    private $Entity;

    /**
     * @var Colors
     */
    private $Color;

    public function __construct() {
        $this->Color = new Colors();

        $this->Types = [
            new Type('integer', 'int'),
            new Type('float'),
            new Type('string', 'str', 'length'),
            new Type('boolean', 'bool'),
            new Type('date'),
            new Type('datetime'),
            new Type('OneToOne', 'OO', ['targetEntity', 'leader(1/0)']),
            new Type('OneToMany', 'OM', 'targetEntity'),
            new Type('ManyToMany', 'MM', ['targetEntity', 'leader(1/0)']),
            new Type('ManyToOne', 'MO', ['targetEntity']),
            new Type('gedmos', 'gedmo', 'datekind'),
        ];

        //$this->Entity->debug();
    }

    public function prepareEntity() {
        $this->Entity = new \Entity();

        $this->present();
        $this->askName();
        echo PHP_EOL;
        $this->resumeTypes();
        echo PHP_EOL;

        $state = null;
        while ($state !== 'stop')
            $state = $this->askField($state);

        return $this->Entity;
    }

    private function askName() {
        $this->Entity->setName(
            $this->ask('EntityName')
        );
    }

    private function ask($whut, $canQuit = false) {
        $nbSpaces = strlen('             Types              ');
        $toAsk = $whut . ($canQuit ? ' (ENTER pour quit)':'') . ': ';

        $moreSpaces = $nbSpaces - strlen($toAsk);
        $TMP = '';
        while ($moreSpaces -- > 0) $TMP .= ' ';

        $this->echoLORED($TMP . $toAsk);

        return $this->scanf();
    }

    private function echoLORED($str, $isTitle = false) {
        echo $this->Color->getColoredString($str, $isTitle ? 'white' : 'light_gray', 'blue');
    }
    private function present() {
        $this->echoLORED(
            '                                ' . PHP_EOL .
            '       Création d\'entité        ' . PHP_EOL .
            '                                ' . PHP_EOL
            , true);
    }
    private function resumeTypes() {
        $str =
            ///'                                ' . PHP_EOL .
            '              Types             ' . PHP_EOL .
            '                                ' . PHP_EOL
        ;
        $nbSpaces = strlen('             Types              ');
        foreach ($this->Types as $type) {
            $resume = ' ' . $type->getName() . ' (' . $type->alias . ')';
            $moreSpaces = $nbSpaces - strlen($resume);
            while ($moreSpaces -- > 0) $resume .= ' ';
            $str .= $resume . PHP_EOL;
        }

        $this->echoLORED($str . PHP_EOL);
    }

    private function askField($name = null) {
        if (! is_string($name)) {
            $name = $this->ask('Nom du field', true);
            if ($name === '')
                return 'stop';
        }

        $field = new Field();
        $field->setName($name);

        $strType = $this->ask('Field type');
        $type = $this->getType($strType);
        if ($type === null) {
            echo 'Type non valide fdp' . PHP_EOL;
            return $name;
        }
        $field->setType($type);

        foreach ($type->getReqOpts() as $opt) {
            $field->setDynOpt($opt, $this->ask($type->getName() . ' ' . $opt));
        }

        $options = $this->ask('Options (' . implode(' ', Field::getStaticOptions()) . ')');
        if (strlen($options)) {
            foreach (Field::getStaticOptions() as $opt) {
                if (strpos($options, $opt) !== false) {
                    $field->$opt = true;
                }
            }
        }

        $this->Entity->addField($field);
        echo PHP_EOL;
        return true;
    }

    private function getType($strType) {
        foreach ($this->Types as $type) {
            foreach ($type->getNames() as $validName) {
                if ($strType === $validName)
                    return $type;
            }
        }
        return null;
    }

    private function scanf($EOL = false) {
        if ($EOL) echo PHP_EOL;
        $handle = fopen("php://stdin","r");
        $line   = fgets($handle);
        $resp   = substr($line, 0, strlen($line) - 1);
        fclose($handle);
        return $resp;
    }
}

// TODO -- Une opt --desv pour display en rose
// http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
class Colors {
    private $foreground_colors = array();
    private $background_colors = array();

    public function __construct() {
        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}