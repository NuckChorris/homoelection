<?php

/*
 * TODO:
 * Argument checking
 * lowercase keys (with name fields for proper shit)
 * finish voting
 * nominate --> volunteer (nominate is forced)
 */

class Election {
	public $name = '';
	public $expires = 0;
	public $votes = array();
	public $nominees = array();
	public $lock = false;
	public $stop = false;

	function __construct($name, $expires) {
		$this->name = $name;

		$this->expires = $expires;
	}
}

class Nominee {
	public $name = '';
	public $election = null;
	public $votes = array();
	public $score = 0;

	public function __construct($name, &$election) {
		$this->name = $name;
		$this->election = &$election;
		$election->nominees[$name] = &$this;
	}

	public function vote ($user) {
		echo "\nVOTIN'\n\n";
		if ($this->election->expires > time()) {
			$this->election->votes[$user] = &$this->nominee;
			$this->votes[] = array(
				$user,
				time()
			);
			$this->score++;
			return true;
		} else {
			return false;
		}
	}
}

function plural($num) {
	if ($num != 1)
		return "s";
}

function getTimeLeft($date) {
	$diff = $date - time();
	if ($diff<60)
		return "in " . $diff . " second" . plural($diff);
	$diff = round($diff/60);
	if ($diff<60)
		return "in " . $diff . " minute" . plural($diff);
	$diff = round($diff/60);
	if ($diff<24)
		return "in " . $diff . " hour" . plural($diff);
	$diff = round($diff/24);
	if ($diff<7)
		return "in " . $diff . " day" . plural($diff);
	$diff = round($diff/7);
	if ($diff<4)
		return "in " . $diff . " week" . plural($diff);
	return "on " . date("F j, Y", $date);
}

class HomoElection extends extension {
	public $name = 'HomoElection';
	public $version = 1;
	public $about = 'An elections system!  Now with 99% more homo!';
	public $status = true;
	public $author = 'nuckchorris0';

	public $docsUrl = 'https://github.com/NuckChorris/homoelection/blob/master/README.md#commands';

	private $elections = array();
	private $votes = array();

	function init() {
		$this->addCmd('election', 'c_election', 75);
		$this->addCmd('elections', 'c_elections', 75);
		$this->addCmd('nominate', 'c_nominate', 50);
		$this->addCmd('nominees', 'c_nominees', 50);
		$this->addCmd('vote', 'c_vote');

		$this->cmdHelp('election', 'Make elections!');
		$this->cmdHelp('nominate', 'Nominate things!');
		$this->cmdHelp('vote', 'Do shit!');

		$this->load_elections();
		print_r($this->elections);
	}

	function c_election($ns, $from, $message, $target) {
		$cmd = args($message, 1);
		$name = args($message, 2);
		switch ($cmd) {
			case 'create':
				$length = args($message, 3, true);
				$stahp = strtolower(args($message, substr_count($message, ' '))) == '-stahp';

				if ($stahp)
					$length = substr($length, 0, strrpos($length, ' '));

				$this->elections[$name] = new Election($name, strtotime($length));
				$this->elections[$name]->stop = $stahp;

				$this->dAmn->say($ns, "{$from}: A new election for {$name} has been started " . 
				            ($stahp ? "(and subsequently stopped) " : "") . "with a length of " . 
				            $length . ".");

				break;
			case 'delete':
				unset($this->elections[$name]);

				$this->dAmn->say($ns, "{$from}: The {$name} election has been deleted.");
				break;
			case 'stop':
				$this->elections[$name]->stop = true;

				$this->dAmn->say($ns, "{$from}: The {$name} election has been stopped.");
				break;
			case 'start':
				$this->elections[$name]->stop = false;
				$lock = strtolower(args($message, 3)) == '-lock';

				if ($lock)
					$this->elections[$name]->lock = true;

				$this->dAmn->say($ns, "{$from}: The {$name} election has been started " . 
				             ($lock ? "and locked" : ""). ".");
				break;
			case 'lock':
				$this->elections[$name]->lock = true;

				$this->dAmn->say($ns, "{$from}: The {$name} election has been locked.");
				break;
			case 'unlock':
				$this->elections[$name]->lock = false;

				$this->dAmn->say($ns, "{$from}: The {$name} election has been unlocked.");
				break;
			case 'reset':
				$this->elections[$name]->votes = array();
				foreach($this->elections[$name]->nominees as $key => $val) {
					$val->votes = array();
					$val->score = 0;
				}
				$this->dAmn->say($ns, "{$from}: The {$name} election has been reset.");
				break;
			default:
				$this->dAmn->say($ns, "{$from}: For help using the {$this->name} module, read " . 
				                      "<a href=\"{$this->docsUrl}\">the docs</a>.");
		}
		$this->save_elections();
	}

	function c_elections($ns, $from, $message, $target) {
		$all = strtolower(args($message, 1)) == '-all';

		$out = "";
		foreach ($this->elections as $key => $val) {
			if ($all || ($val->stop || $val->lock)) {
				$out .= $val->name
				      . ($val->stop ? " :thumb82665473:" : "")
				      . ($val->lock ? " :lock:" : "")
				      . " &bull; "
				      . getTimeLeft($val->expires) . " &bull; "
				      . implode($val->votes)
				      . "\n";
			}
		}
		$this->dAmn->say($ns, $out);
	}

	function c_nominate($ns, $from, $message, $target) {
		$election = $this->elections[args($message, 1)];
		$nominee = args($message, 2, true);
		new Nominee($from, $election);

		$this->dAmn->say($ns, "{$from}: Congratulations you are now partaking in the {$election->name} race!");
	}

	function c_nominees($ns, $from, $message, $target) {
		$election = $this->elections[args($message, 1)];

		print_r($election);

		$out = "";
		foreach ($election->nominees as $key => $val) {
			$votes = array();
			foreach ($val->votes as $i => $vote) {
				$votes[] = implode($vote, '&middot;');
			}
			$out = $val->name . " &bull; " . $val->score . " &bull; " . implode($votes, ', ') . "\n";
		}
		$this->dAmn->say($ns, $out);
	}

	function c_vote($ns, $from, $message, $target) {
		$election = $this->elections[args($message, 1)];
		$nominee = $election->nominees[args($message, 2)];

		if ($nominee->vote($from))
			$this->dAmn->say($ns, "{$from}: Your vote counts!");
		else
			$this->dAmn->say($ns, "{$from}: FAILURE!!");
	}

	function load_elections() {
		$this->elections = $this->Read('elections', 0);
		$this->elections = empty($this->elections) ? array() : $this->elections;
	}

	function save_elections() {
		if(empty($this->elections)) $this->Unlink('elections');
		else $this->Write('elections', $this->elections, 0);
	}
}

new HomoElection($core);

?>