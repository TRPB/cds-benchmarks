<?php
namespace CDS;
class Template {
	private $hooks = [];
	private $document;

	public function __construct($doc) {
		if ($doc instanceof \DomDocument) $this->document = $doc;
		else {
			$this->document = new \DomDocument;
			$this->document->loadXML($doc);
		}
	
		$this->xpath = new \DomXPath($this->document);
	}
	
	public function addHook($xpath, $hook) {
		$this->hooks[] = [$xpath, $hook];
	}

	private function processHook($query, $hook, $filter = '') {
		foreach ($this->xpath->query($query . $filter) as $element) $hook->run($element);
	}

	public function output() {
		//Now process tags with child nodes, which will have had any variables already replaced
		foreach ($this->hooks as list($query, $hook)) $this->processHook($query, $hook);

		//Generate the document by taking only the childnodes of the template, ignoring the <template> and </template> tags
		//TODO: Is there a faster way of doing this without string manipulation on the output or this loop through childnodes?
		$output = '';
		foreach ($this->document->documentElement->childNodes as $node) $output .= $this->document->saveXML($node, LIBXML_NOEMPTYTAG);

		//repair empty tags. Browsers break on <script /> and <div /> so can't avoid LIBXML_NOEMPTYTAG but they also break on <base></base> so repair them
		$output = str_replace(['></img>', '></br>', '></meta>', '></base>', '></link>', '></hr>', '></input>'], ' />', $output);
		return trim($output);
	}
}