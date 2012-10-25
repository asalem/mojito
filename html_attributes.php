<?php
interface Hashable
{
    public function name();
    public function value();
}

class IdAttribute
{
    public static $NAME = "id";

    public $value;

    public function __construct() {
        $this->value = "";
    }
}

class ClassAttribute
{
    public static $NAME = "class";

    public $values;

    public function __construct() {
        $this->values = array();
    }
}

class ActionAttribute implements Hashable
{
    public static $NAME = "action";

    private $value;

    public function __construct($handler_url) {
        $this->value = $handler_url;
    }

    public function name() {
        return ActionAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }
}

class AltAttribute implements Hashable
{
    public static $NAME = "alt";

    private $value;

    public function __construct($alt_value) {
        $this->value = $alt_value;
    }

    public function name() {
        return AltAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }    
}

class HrefAttribute implements Hashable
{
    public static $NAME = "href";

    private $value;

    public function __construct($href_value) {
        $this->value = $href_value;
    }

    public function name() {
        return HrefAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }    
}

class NameAttribute implements Hashable
{
    public static $NAME = "name";

    private $value;

    public function __construct($name_value) {
        $this->value = $name_value;
    }

    public function name() {
        return NameAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }
}

class MetaAttribute implements Hashable
{
    public static $NAMES = array(
                           "charset",
                           "name",
                           "content",
                           "http-equiv");

    private $name;
    private $value;

    public function __construct($name, $value) {
        if (in_array($name, MetaAttribute::$NAMES)) {
            $this->name = $name;
            $this->value = $value;
        } else {
            echo "[html_attributes.php] Error: invalid MetaAttribute name";
            $this->name = "";
            $this->value = "";            
        }
    }

    public function name() {
        return $this->name;
    }

    public function value() {
        return $this->value;
    }    
}

class RelAttribute implements Hashable
{
    public static $NAME = "rel";

    private $value;

    public function __construct($rel_value) {
        $this->value = $rel_value;
    }

    public function name() {
        return RelAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }    
}

class SrcAttribute implements Hashable
{
    public static $NAME = "src";

    private $value;

    public function __construct($src_value) {
        $this->value = $src_value;
    }

    public function name() {
        return SrcAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }    
}

class TitleAttribute implements Hashable
{
    public static $NAME = "title";

    private $value;

    public function __construct($title_value) {
        $this->value = $title_value;
    }

    public function name() {
        return TitleAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }    
}

class TypeAttribute implements Hashable
{
    public static $NAME = "type";

    private $value;

    public function __construct($type_value) {
        $this->value = $type_value;
    }

    public function name() {
        return TypeAttribute::$NAME;
    }

    public function value() {
        return $this->value;
    }    
}

class HtmlAttributes
{
    private $id;
    private $class;
    private $other;

    public function __construct() {
        $this->id = new IdAttribute();
        $this->class = new ClassAttribute();
        $this->other = array();
    }

    public function get_id() {
        return $this->id->value;
    }

    public function set_id($id_value) {
        $this->id->value = $id_value;
    }

    public function is_class($class_value) {
        return in_array($class_value, $this->class);
    }

    public function get_classes() {
        return $this->class->values;
    }

    public function add_class($value_to_add) {
        $classes = $this->class->values;

        if (strpos($value_to_add, " ")) {
            return false;
        } else if (in_array($value_to_add, $classes)) {
            return true;
        } else {
            array_push($this->class->values, $value_to_add);
            return true;
        }
    }

    public function add(Hashable $attribute) {
        $adding_name = $attribute->name();
        $adding_value = $attribute->value();

        foreach ($this->other as $existing_attribute) {
            $existing_name = $existing_attribute->name();

            // do not allow attribute overwriting
            if ($adding_name == $existing_name) {
                return false;
            }
        }

        array_push($this->other, $attribute);
        return true;
    }

    public function get($attribute_name) {
        $value = "";

        foreach ($this->other as $attribute) {

            if ($attribute->name() == $attribute_name) {
                return $attribute->value();
            } else {
                // $attribute is not the one being asked for
            }
        }

        return $value;
    }

    public function names() {
        $all_names = array();

        $id_value = $this->id->value;

        if ($this->id->value != "") {
            array_push($all_names, IdAttribute::$NAME);
        } else {
            // id not specified, do not push its name into the array
        }

        if (count($this->class->values) > 0) {
            array_push($all_names, ClassAttribute::$NAME);
        } else {
            // class not specified, do not push it into the array
        }
        
        foreach ($this->other as $attribute) {
            $name = $attribute->name();
            array_push($all_names, $name);
        }

        return $all_names;
    }
}
?>