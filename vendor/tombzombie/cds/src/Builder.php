<?php
namespace CDS;
class Builder {
	private $template;
	private $cds;
	private $data;
	private $dataStorage;
	private $rules;

	public function __construct($template, $cds, $data) {
		if (file_exists('tmp/' . $cds)) {
			$this->rules = json_decode(file_get_contents('tmp/' . $cds), true);
		}
		else {
			$this->cds = new Sheet(file_get_contents($cds));
			$this->rules = $this->cds->parse();
			file_put_contents('tmp/' . $cds, json_encode($this->rules));
		}

		$this->template = new Template($template);
		$this->cds = new Sheet($cds);
		$this->data = $data;
		$this->dataStorage = new \SplObjectStorage();
	}

	public function output() {
		foreach ($this->rules as $rule) {
			$this->template->addHook($rule['query'], new Hook\Rule($rule, $this->data, $this->dataStorage));	
		}
		
		return $this->template->output();
	}
}

