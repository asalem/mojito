<?php
interface Renderable
{
    public function render($indent_unit, $indent_level);
}

class TextElement implements Renderable
{
    public $content;

    public function __construct($text_content) {
        $this->content = htmlentities($text_content);
    }

    public function render($indent_unit, $indent_level) {
        $indent = str_repeat($indent_unit, $indent_level);
        $output = $indent . $this->content . "\n";
        return $output;
    }
}

abstract class MarkupElement implements Renderable
{
    public static $OPENING = "<";
    public static $CLOSING = ">";

    public static $EMPTYCLOSING = " />";
    public static $PAIREDCLOSING = "</";

    public static $ATTROPEN = '="';
    public static $ATTRCLOSE = '"';

    public static $IDATTR = "id";
    public static $CLASSATTR = "class";

    public static $SEPARATOR = " ";

    protected $name;
    protected $attributes;
    protected $children;

    public function __construct() {
        $this->name = NULL;
        $this->attributes = array(self::$IDATTR=>"", self::$CLASSATTR=>"");
        $this->children = NULL;
    }

    private function render_attributes() {
        $attribute_pieces = array();
        foreach ($this->attributes as $name=>$value) {
            $attr = "";

            if ($value != "") {
                $attr = $name . self::$ATTROPEN . $value . self::$ATTRCLOSE;
            } else {
                // attribute value not specified, render nothing
            }

            array_push($attribute_pieces, $attr);
        }
        $attribute_rendering = implode(self::$SEPARATOR, $attribute_pieces);
        return $attribute_rendering;
    }

    private function render_empty($opening, $indent) {
        $rendering = $opening . self::$EMPTYCLOSING;
        $formatted = $indent . $rendering . "\n";
        return $formatted;
    }

    private function render_paired($opening, $indent_unit, $indent_level) {
        $indent = str_repeat($indent_unit, $indent_level);

        $openline = $opening . self::$CLOSING;

        $midlines = "";
        $child_level = $indent_level + 1;

        foreach ($this->children as $child) {
            $midlines .= $child->render($indent_unit, $child_level);
        }

        $closeline = self::$PAIREDCLOSING . $this->name . self::$CLOSING;

        $formatted_open = $indent . $openline . "\n";
        $formatted_mid = $midlines;
        $formatted_close = $indent . $closeline . "\n";

        $formatted = $formatted_open . $formatted_mid . $formatted_close;        
        return $formatted;
    }

    public function render($indent_unit, $indent_level) {
        $indent = str_repeat($indent_unit, $indent_level);
        $name = $this->name;

        $opening = self::$OPENING . $name;
        $attribute = $this->render_attributes();

        if ($attribute != "") {
            $opening .= self::$SEPARATOR . $attribute;
        } else {
            // attribute is empty and has nothing te be put into opening
            $opening = $opening; 
        }

        if ($this->children === NULL) {
            $output = $this->render_empty($opening, $indent);
            return $output;
        } else {
            // shorten variable names to follow the 80-column rule
            $unit = $indent_unit;
            $level = $indent_level;
            $output = $this->render_paired($opening, $unit, $level);
            return $output;
        }
    }

    public function set_id($value_string) {
        $value = htmlentities($value_string);
        $this->attributes[self::$IDATTR] = $value;
    }

    public function add_class($value_string) {
        $value = htmlentities($value_string);
        $existing = $this->attributes[self::$CLASSATTR];

        if (strpos($value, self::$SEPARATOR)) {
            // class value cannot contain space, do not add it
            return FALSE;
        } else if (strpos($existing, $value_string)) {
            // class already exists, do nothing
            return FALSE;
        } else {
            $added = $existing . self::$SEPARATOR . $value;
            $existing = $added;
            return TRUE;
        }
    }

    public function push(Renderable $element) {

        if ($this->children == NULL) {
            // this is an empty element and cannot have children
        } else {
            array_push($this->children, $element);
        }
    }    
}

class AElement extends MarkupElement
{
    public static $TAGNAME = "a";

    public static $HREFATTR = "href";
    public static $TARGETATTR = "target";

    public function __construct($href_url, $link_text) {
        $this->name = self::$TAGNAME;
        $this->children = array();        

        $this->attributes[self::$HREFATTR] = $href_url;
        $this->attributes[self::$TARGETATTR] = "";

        $content_element = new TextElement($link_text);
        array_push($this->children, $content_element);
    }
}

class BodyElement extends MarkupElement
{
    public static $TAGNAME = "body";

    public function __construct(MarkupElement $parent) {
        $this->name = self::$TAGNAME;
        $this->children = array();
    }
}

class BrElement extends MarkupElement
{
    public static $TAGNAME = "br";

    public function __construct() {
        $this->name = self::$TAGNAME;
    }
}

class CharsetMetaElement extends MarkupElement
{
    public static $TAGNAME = "meta";

    public static $CHARSETATTR = "charset";

    public function __construct(HeadElement $parent, $charset_value) {
        $this->name = self::$TAGNAME;
        $this->attributes[self::$CHARSETATTR] = $charset_value;
    }
}

class DivElement extends MarkupElement
{
    public static $TAGNAME = "div";

    public function __construct() {
        $this->name = self::$TAGNAME;
        $this->children = array();
    }
}

class FormElement extends MarkupElement
{
    public static $TAGNAME = "form";

    public static $ACTIONATTR = "action";
    public static $METHODATTR = "method";
    public static $ENCTYPEATTR = "enctype";

    public function __construct($handler_url) {
        $this->name = self::$TAGNAME;
        $this->children = array();

        $this->attributes[self::$ACTIONATTR] = $handler_url;
        $this->attributes[self::$METHODATTR] = "";
        $this->attributes[self::$ENCTYPEATTR] = "";        
    }

    public function push_textinput($input_name, $input_value) {
        $input = new TextInputElement($this, $input_name, $input_value);
        array_push($this->children, $input);
        return $input;
    }
}

class H1Element extends MarkupElement
{
    public static $TAGNAME = "h1";

    public function __construct($content_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();

        $content = new TextElement($content_string);
        array_push($this->children, $content);
    }
}

class H2Element extends MarkupElement
{
    public static $TAGNAME = "h2";

    public function __construct($content_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();        

        $content = new TextElement($content_string);
        array_push($this->children, $content);
    }
}

class H3Element extends MarkupElement
{
    public static $TAGNAME = "h3";

    public function __construct($content_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();           

        $content = new TextElement($content_string);
        array_push($this->children, $content);
    }
}

class H4Element extends MarkupElement
{
    public static $TAGNAME = "h4";

    public function __construct($content_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();

        $content = new TextElement($content_string);
        array_push($this->children, $content);
    }
}

class H5Element extends MarkupElement
{
    public static $TAGNAME = "h5";

    public function __construct($content_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();

        $content = new TextElement($content_string);
        array_push($this->children, $content);
    }
}

class H6Element extends MarkupElement
{
    public static $TAGNAME = "h6";

    public function __construct($content_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();

        $content = new TextElement($content_string);
        array_push($this->children, $content);
    }
}

class HeadElement extends MarkupElement
{
    public static $TAGNAME = "head";

    public function __construct(MarkupElement $parent, $charset, $title) {
        $this->name = self::$TAGNAME;
        $this->children = array();        

        $charset = new CharsetMetaElement($this, $charset);
        $title = new TitleElement($this, $title);

        array_push($this->children, $charset);
        array_push($this->children, $title);
    }
}

class HrElement extends MarkupElement
{
    public static $TAGNAME = "hr";

    public function __construct() {
        $this->name = self::$TAGNAME;
    }
}

class HtmlElement extends MarkupElement
{   
    public static $TAGNAME = "html";

    public static $CHARSET = "UTF-8";

    private $head;
    private $body;

    public function __construct($title_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();        

        $this->head = new HeadElement($this, self::$CHARSET, $title_string);
        $this->body = new BodyElement($this);

        array_push($this->children, $this->head);
        array_push($this->children, $this->body);
    }

    public function head_push(Renderable $element) {
        $this->head->push($element);
    }

    public function body_push(Renderable $element) {
        $this->body->push($element);
    }
}

class ImgElement extends MarkupElement
{
    public static $TAGNAME = "img";

    public static $SRCATTR = "src";
    public static $ALTATTR = "alt";
    public static $TITLEATTR = "title";

    public function __construct($src_value, $alt_value) {
        $this->name = self::$TAGNAME;

        $this->attributes[self::$SRCATTR] = $src_value;
        $this->attributes[self::$ALTATTR] = $alt_value;
    }
}

class TextInputElement extends MarkupElement
{
    public static $TAGNAME = "input";
    public static $TYPENAME = "text";

    public static $TYPEATTR = "type";
    public static $NAMEATTR = "name";
    public static $VALUEATTR = "value";
    public static $MAXLENGTHATTR = "maxlength";

    public function __construct(FormElement $form, $name_value, $init_value) {
        $this->name = self::$TAGNAME;

        $this->attributes[self::$TYPEATTR] = self::$TYPENAME;
        $this->attributes[self::$NAMEATTR] = $name_value;
        $this->attributes[self::$VALUEATTR] = $init_value;
        $this->attributes[self::$MAXLENGTHATTR] = "";        
    }

    public function set_maxlength($integer_value) {
        $this->attributes[self::$MAXLENGTHATTR] = $integer_value;
    }
}

class LinkElement extends MarkupElement
{
    public static $TAGNAME = "link";

    public static $HREFATTR = "href";
    public static $TYPEATTR = "type";
    public static $RELATTR = "rel";

    public function __construct($href_value, $type_value, $rel_value) {
        $this->name = self::$TAGNAME;

        $this->attributes[self::$HREFATTR] = $href_value;
        $this->attributes[self::$TYPEATTR] = $type_value;
        $this->attributes[self::$RELATTR] = $rel_value;
    }
}

class PElement extends MarkupElement
{
    public static $TAGNAME = "p";

    public function __construct($content_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();        

        $text = new TextElement($content_string);     
        array_push($this->children, $text);
    }

    public function push_text($content_string) {
        $text = new TextElement($content_string);
        array_push($this->children, $text);
    }

    public function push_break() {
        $break = new BrElement();
        array_push($this->children, $break);
    }
}

class ScriptElement extends MarkupElement
{
    public static $TAGNAME = "script";

    public static $SRCATTR = "src";

    public function __construct($script_url) {
        $this->name = self::$TAGNAME;
        $this->children = array();

        $this->attributes[self::$SRCATTR] = $script_url;
    }
}

class SpanElement extends MarkupElement
{
    public static $TAGNAME = "span";

    public function __construct() {
        $this->name = self::$TAGNAME;
        $this->children = array();
    }
}

class TitleElement extends MarkupElement
{
    public static $TAGNAME = "title";

    public function __construct(HeadElement $parent, $title_string) {
        $this->name = self::$TAGNAME;
        $this->children = array();        

        $content_element = new TextElement($title_string);
        array_push($this->children, $content_element);
    }
}
?>