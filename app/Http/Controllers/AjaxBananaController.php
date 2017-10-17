<?PHP
	
	namespace bananaCalc\Http\Controllers;
	
	use Illuminate\Http\Request;

	class AjaxBananaController extends Controller {
		public function calculate(Request $request) {
			$pack_sizes = \Config::get('banana.pack_sizes');
			$data = array();
			$data['num_bananas_required'] = (int) $request->input('num_bananas');
			if ($data['num_bananas_required'] > 0) {
				$pack_calc = new PackCalculator($pack_sizes);
				$pack_calc->setRequiredAmount($data['num_bananas_required']);
				$data['results'] = $pack_calc->getResults();
				return view('banana_results')->with('data', $data);
			}
		}
	}


	/*
		PACK SIZE CALCULATOR
	*/
	class PackCalculator {
		
		// VARS
		private $pack_amounts = array();
		private $required_amount = 0;
		private $remaining_required = 0;
		private $pack_results = array();
		
		// CONSTRUCTOR
		public function __construct ($pack_amounts = array()) {
			if (is_array($pack_amounts)) {
				$this->pack_amounts = $pack_amounts;
			}
		}
		
		// SET REQUIRED AMOUNT
		public function setRequiredAmount ($req_amount) {
			if (is_numeric($req_amount)) {
				$this->required_amount = (int) $req_amount;
				$this->remaining_required = $this->required_amount;
				$this->pack_results = array();
				$this->calculatePacks();
			}
		}
		
		// RETURN RESULTS
		public function getResults () {
			return $this->pack_results;
		}
		
		// PERFORM CALCULATION
		private function calculatePacks () {
			if ($this->required_amount > 0) {
				rsort($this->pack_amounts);
				$results = array();
				$remainder_req = $this->required_amount;
				foreach ($this->pack_amounts as $ps) {
					$f = floor($remainder_req / $ps);
					if ($f > 0 && $remainder_req >= 0) {
						$results[$ps] = $f;
						$remainder_req -= ($ps * $f);
					}
				}
				sort($this->pack_amounts);
				if ($remainder_req > 0) {
					$results[$this->pack_amounts[0]] = (isset($results[$this->pack_amounts[0]])) ?  $results[$this->pack_amounts[0]] + 1 : 1;
				}
				for ($i = 0; $i < count($this->pack_amounts); $i++) {
					if (isset($results[$this->pack_amounts[$i]])) {
						if ($results[$this->pack_amounts[$i]] == 2 && isset($this->pack_amounts[$i + 1])) {
							if (($this->pack_amounts[$i] * 2) == $this->pack_amounts[$i + 1]) {
								$results[$this->pack_amounts[$i + 1]] = (isset($results[$this->pack_amounts[$i + 1]])) ? $results[$this->pack_amounts[$i + 1]] + 1 : 1;
								unset($results[$this->pack_amounts[$i]]);
							}
						}
					}
				}
				$this->pack_results = $results;
			}
		}
	}
