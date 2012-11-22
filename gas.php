<?php
class gas{
	/**
	 * The url of the gas.js script. Set with gas::script()
	 * @var string
	 */
	protected $script = '//cdnjs.cloudflare.com/ajax/libs/gas/1.10.1/gas.min.js';

	/**
	 * The UA numbers for the google analytics account to be used. Set with gas::ua().
	 * @var string
	 */
	protected $ua = array();

	/**
	 * The domains that we're tracking. This will most commanly be the single domain you're tracking
	 * but may include more for cross-domain tracking. Set with gas::domain
	 * @var array
	 */
	protected $domain = array();

	/**
	 * Array containing all of the options that will be pushed with _gas.push()
	 * @var array
	 */
	protected $push = array(
		'_setAccount' => array(),
		'_setDomainName' => array(),
	);

	/**
	 * An array of the special few gas methods that can be called multiple times. push() needs
	 * to handle these a little bit differently so we keep track of which ones they are here.
	 * @var array
	 */
	protected $multi_methods = array(
		'_setAccount',
		'_setDomainName',
	);

	/**
	 * If $methods is a string then we assume it is a method to be called with _gas.push(). If it's
	 * an array then we assume it's an array of methods to be set with _gas.push(). The $methods
	 * array should conform to the standards of the gas::push() method array structure.
	 * @param mixed $opts [description]
	 */
	public function __construct($methods = false){
		if($methods){
			$this->push($methods);
		}
	}

	/**
	 * The __toString() magic method ensures that we can call echo on a Gas object and 
	 * output the gas.js snippet. This method just wraps the Gas::tag() method.
	 * @return string
	 */
	public function __toString(){
		return $this->tag();
	}

	/**
	 * constructs and returns the full gas script tag based on the options set so far
	 * @return [type]
	 */
	protected function tag(){
		$tag = print_r($this->push, true);
		return $tag;
	}

	/**
	 * Add method calls to the tag.
	 * Available method calls are documented here : https://github.com/CardinalPath/gas
	 *
	 * If the $method parameter is a string we will assume it's a single method to be pushed
	 * with no options. If you're using push() to set a single method call you can use the $opts
	 * parameter to specify an option or multiple options with an associative array.
	 * 
	 * If $method is an array we assume it's an array of methods with or without
	 * options. Array structure should match the following format : 
	 *
	 * $methods = array(
	 *     '_methodName1', //this is a method call with no options
	 *     '_methodName2' => 'val', //this is a method call to a method that only takes a single option
	 *     '_methodName3' => array( //this is a method call that can take one or more options.
	 *         'opt1' => 'val',     
	 *         'opt2' => 'val'
	 *     ),
	 * );
	 * 
	 * @param  mixed $method
	 * @return void
	 */
	public function push($method, $opts = null){
		if(!is_array($method)){
			if(!$this->multi_method($method, $opts)){
				$this->push[$method] = $opts;
			}
		}else{
			foreach($method as $key=>$val){
				if(is_numeric($key)){
					if(!multi_method($val)){
						$this->push[$val] = null;
					}
				}else{
					if(!multi_method($key, $val)){
						$this->push[$key] = $val;
					}
				}
			}
		}
	}

	/**
	 * Some gas methods can be called multiple times per page. multi_method() is called by
	 * push() so that push() can effectively handle these special cases.
	 * @todo  use regex to match keys so we can match things like 'custom._setAccount'
	 * @param  string  $method
	 * @param  mixed $opts 
	 * @return boolean
	 */
	protected function multi_method($method, $opts = false){
		$multi_detected = false;

		if(in_array($method, $this->multi_methods)){
			$multi_detected = true;
			$this->push[$method][] = $opts;
		}

		return $multi_detected;
	}

	/**
	 * Getter and Setter for the url to the gas.js script.
	 * @param  mixed $script - if this is set we will set $this->script to the new value
	 * @return string
	 */
	public function script($script = false){
		if($script){
			$this->script = $script;
		}
		return $this->script;
	}

	/**
	 * Getter and Setter for the google analytics UA numbers.
	 *
	 * gas supports using multiple accounts, so if you call ua() multiple times it'll keep
	 * adding ua numbers instead of overwriting the previous one.
	 *
	 * If $ua is a string it's assumed you're adding a single tracker with this call. If $name
	 * is set then the $name string will be used in the tag to give you a reference to the tracker
	 * obj in the javascript. IE, if $ua is 'UA-XXXXX-3' and $name is 'custom' then the gas tag will
	 * look like this : _gas.push(['custom._setAccount', 'UA-XXXXX-3']);
	 *
	 * If $ua is an array then it's assumed that you're adding multiple ua numbers. The array should
	 * be formatted like this : 
	 * $ua = array(
	 * 	   'UA-XXXXX-1', //adding a ua number without a name
	 * 	   'custom' => 'UA-XXXXX-2', //adding a ua number with a name
	 * )
	 * 
	 * @param  boolean $ua [description]
	 * @return mixed (array or string)
	 */
	public function ua($ua = false, $name = false){
		if($ua){
			if(!is_array($ua)){
				if($name){
					$ua = array($name => $ua);
				}
				$this->ua[] = $ua;
				$this->push('_setAccount', $ua);
			}else{
				$this->ua = array_merge($this->ua, $ua);
				foreach($ua as $key=>$val){
					$name = '';
					if(!is_numeric($key)){
						$name .= $key . '.';
					}
					$this->push($name . '_setAccount', $val);
				}
			}
		}

		if(count($this->ua)){
			$ua = (count($this->ua) > 1) ? $this->ua : $this->ua[0];
		}else{
			$ua = false;
		}

		return $ua;
	}

	/**
	 * Getter and setter for the domain name value
	 *
	 * gas can be used to track across multiple domains. If $domain is a string then it's assumed
	 * you're adding one domain to the domains list to be tracked. If $domain is an array it's
	 * assume that it will be an array of domains that will be tracked. The array will follow this
	 * structure : 
	 * $doamin = array(".domain1.com", ".domain2.com");
	 * 
	 * @param  boolean $domain [description]
	 * @return [type]
	 */
	public function domain($domain = false){
		if($domain){
			if(!is_array($domain)){
				$this->domain[] = $domain;
				$this->push('_setDomainName', $domain);
			}else{
				$this->domain = array_merge($this->domain, $domain);

				$this->push('_setAllowLinker', true);
				foreach($domain as $val){
					$this->push('_setDomainName', $val);					
				}
				$this->push("_gasMultiDomain", 'click');
			}
		}

		if(count($this->domain)){
			$domain = (count($domain) > 1) ? ($this->domain) : ($this->domain[0]);
		}else{
			$domain = false;
		}

		return $domain;
	}
}
?>