<?php
require_once 'form_elements.php';
require_once 'inline_elements.php';
require_once 'block_elements.php';

class Yf2eTest2
{
    private static $doctype = "<!DOCTYPE html>";

    private static $indent_unit = "    ";
    private static $indent_level = 0;

    private static $title = "Responsive Search";
    private static $subtitle = "A Test Project for Web Frontend Engineering";

    private static $jscript = "yf2etest2.js";

    private static $db_server = "mysql:host=mysqlstagingasia.cnvitttjgspz.ap-southeast-1.rds.amazonaws.com;dbname=yf2etest2";
    private static $db_uesrname = "yf2etest2";
    private static $db_password = "yf2etest2";

    private $html;
    private static $db;

    private static function query_db($lead_char) {
        $sql = "SELECT name, description
                FROM words
                WHERE lead_char = :lead_char";
        $params = array("lead_char"=>$lead_char);

        try {
            self::$db = new PDO(self::$db_server, self::$db_uesrname, self::$db_password);
        } catch (PDOException $connect_e) {
            // TODO: handle database connection failure
        }

        try {
            $db_query = self::$db->prepare($sql);
        } catch (PDOException $prepare_e) {
            // TODO: handle query preparation error
        }

        if ($db_query) {
            $query_successful = $db_query->execute($params);
        } else {
            // TODO: handle query preparation failure
        }

        if ($query_successful) {
            $words = $db_query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // TODO: handle query execution failure
        }
        
        self::$db = NULL;

        return $words;        
    }

    public static function Fetch($query_value) {
        $words = self::query_db($query_value);

        $results = array();
        foreach ($words as $word) {
            $item = array("name"=>$word['name'],
                          "description"=>$word['description']);
            array_push($results, $item);
        }        

        return $results;
    }

    public function __construct($html_title) {
        $this->html = new HtmlElement($html_title);
        
        $this->script = new ScriptElement(self::$jscript);

        $this->application_title = new H2Element(self::$title);
        $this->application_subtitle = new PElement(self::$subtitle);
        
        $this->searchbox = new FormElement("index.php");
        $input = $this->searchbox->push_input(InputElement::$TextType, "search", "", "");
        $input->id("searchbox");
        $input->placeholder("Enter Keyword");

        $this->suggest_label = new PElement("Suggestions: ");
        $this->suggest_label->classes("label");

        $this->suggest_list = new OlElement();
        $this->suggest_list->id("suggestlist");

        $this->result_label = new PElement("Results: ");
        $this->result_label->classes("label");        

        $this->result_list = new DlElement();
        $this->result_list->id("resultlist");

        $this->body_pushes();

        $this->html->attach_style("yf2etest2_layout.css");
        $this->html->attach_style("yf2etest2_color.css");
        $this->html->attach_style("yf2etest2_typeface.css");

        $this->html->attach_scriptentry("main()");       
    }

    public function render() {
        $doc = $this->html->render(self::$indent_unit, self::$indent_level);
        return self::$doctype . "\n" . $doc;
    }

    // TODO: encapsulate this into HtmlElement
    private function body_pushes() {
        $this->html->body_push($this->script);

        $header_div = new DivElement();
        $header_div->id("header");

        $header_div->push($this->application_title);
        $header_div->push($this->application_subtitle);
        $header_div->push($this->searchbox);

        $body_div = new DivElement();
        $body_div->id("body");

        $body_div->push($this->suggest_label);
        $body_div->push($this->suggest_list);
        $body_div->push($this->result_label);
        $body_div->push($this->result_list);

        $document_div = new DivElement();
        $document_div->id("document");

        $document_div->push($header_div);
        $document_div->push($body_div);

        $this->html->body_push($document_div);        
    }
}
?>