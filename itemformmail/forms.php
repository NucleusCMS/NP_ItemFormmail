<?php
class FORMFACTORY extends BaseActions {
	// Formbased options
	var $options;

	function __construct() {
		$this->BaseActions();

		$this->mode = "normal";

		$this->actions = array("iform", "uform");

		$this->parser = new silentPARSER($this->actions, $this);

		// echo alternative
		$this->ob =& $this->parser->ob;



	}

}

/*
	silentPARSER extends nucleus/libs/PARSER.php
*/
class silentPARSER extends PARSER {
	/**
	 * Parses the given contents and return it
	 */

	// echo alternative
	var $ob;

	function __construct($allowedActions, &$handler, $delim = '(<!%|%!>)', $pdelim = ',') {
		$this->PARSER($allowedActions, &$handler, $delim , $pdelim);
	}
	function parse(&$contents) {

		$pieces = preg_split('/'.$this->delim.'/',$contents);

		$maxidx = sizeof($pieces);
		for ($idx = 0;$idx<$maxidx;$idx++) {
			// $this->ob alternative output bufferring
			$this->ob .= $pieces[$idx];
			$idx++;
			$this->doAction($pieces[$idx]);
		}
	}
	function doAction($action) {
		global $manager;

		if (!$action) return;

		// split into action name + arguments
		if (strstr($action,'(')) {
			$paramStartPos = strpos($action, '(');
			$params = substr($action, $paramStartPos + 1, strlen($action) - $paramStartPos - 2);
			$action = substr($action, 0, $paramStartPos);
			$params = explode ($this->pdelim, $params);

			// trim parameters
			// for PHP versions lower than 4.0.6:
			//   - add // before '$params = ...'
			//   - remove // before 'foreach'
			$params = array_map('trim',$params);
			// foreach ($params as $key => $value) { $params[$key] = trim($value); }
		} else {
			// no parameters
			$params = array();
		}


		$actionlc = strtolower($action);

		// skip execution of skinvars while inside an if condition which hides this part of the page
		if (!$this->handler->if_currentlevel && ($actionlc != 'else') && ($actionlc != 'endif') && (substr($actionlc,0,2) != 'if'))
			return;


		if (in_array($actionlc, $this->actions) || $this->norestrictions ) {
			// when using PHP versions lower than 4.0.5, uncomment the line before
			// and comment the call_user_func_array call
			//$this->call_using_array($action, $this->handler, $params);

			call_user_func_array(array(&$this->handler,'parse_' . $actionlc), $params);
		} else {
			// redirect to plugin action if possible
			if (in_array('plugin', $this->actions) && $manager->pluginInstalled('NP_'.$action))
				$this->doAction('plugin('.$action.$this->pdelim.implode($this->pdelim,$params).')');
			else
				// for silent "echo" no use.
				// echo '<b>DISALLOWED (' , $action , ')</b>';
				$this->ob .= func_get_arg(0);
		}

	}
}
?>
