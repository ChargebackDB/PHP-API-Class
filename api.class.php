<?php
	

$apiClass = new ChargeBackAPI(array("apiKey"=>"12345ABC", "debug"=>true));

echo "Email:<BR>";
echo $apiClass->searchDatabaseEmail("typroducts24@gmail.com");
echo "<hr>IP";
echo $apiClass->searchDatabaseIP("127.0.10.15");
echo "<hr>Username";
echo $apiClass->searchDatabaseUsername("tyisobred");
echo "<hr>PayPal Payer ID";
echo $apiClass->searchDatabasePayPalID("USD4444");
echo "<hr>";
echo "<hr>Txn ID";
echo $apiClass->searchDatabaseTxnID("USD4444");
echo "<hr>";
echo "<br>Submitting: . . . <br>";
$submitArray = array(
        "amt"           => 199.90,
        "currency"      => "USD",
        "processor"     => "pp",
        "username"      => "tyisobred",
        "email"         => "typroducts24@gmail.com",
        "pp_email"      => "typroducts24@gmail.com",
        "ip"            => "127.0.10.15",
        "pp_PayerID"    => "pp_Hdgh4555HGs",
        "notes"         => "Testing new API Class",
        "timestamp"     => time() - 86400
    );
echo $apiClass->submitReport($submitArray);
echo "<hr>";


// Future proofing for future use
define('API_METHOD_POST', 1);
define('API_METHOD_PUT', 2);
define('API_METHOD_GET', 3);
define('API_METHOD_DELETE', 4);

class ChargeBackAPI
{
	private $endPointUrl;
	private $timeout = 10;
    private $debug = false;
    private $advDebug = false; // Adds Extra details, WILL break production code
    private $ApiVersion = "1.0";

    private $response;
    private $responseCode;

    
    private $apiKey;
    private $searchURL;
    private $submitURL;

    /**
     * Class constructor.
     *
     * @param array $options Array of options containing an apiKey and optional parameters. 
     *                       Can be also an string if you want to use an apiKey.
     */
    public function __construct($options)
    {
        // For retro-compatibility purposes check if $options is a string,
        // so if a user passes a string we use it as the app key.
        if (is_string($options)) 
        {
            $this->apiKey = $options;
        } 
        elseif (is_array($options)) 
        {
            if(!empty($options['apiKey']))
            {
                $this->apiKey = $options['apiKey'];
            }
            else 
            {
                throw new Exception('You need to specify an API key');
            }

            // Check for custom parameters
            if(!empty($options['debug']))
            {
                $this->debug = $options['debug'];
            }
            if(!empty($options['advDebug']))
            {
                $this->advDebug = $options['advDebug'];
            }
            if(!empty($options['timeout']))
            {
                $this->timeout = $options['timeout'];
            }
            if(!empty($options['apiVersion']))
            {
                $this->apiKey = $options['apiVersion'];
            }
        } 
        else 
        {
            throw new Exception('You must supply at least an API Key String');
        }

        $this->endPointUrl = 'https://chargebackdb.com/api/' . $this->ApiVersion . '/api.php';

        $this->searchURL = $this->endPointUrl . '?action=searchCB';
        $this->submitURL = $this->endPointUrl . '?action=submitReport';
    }


    /**
     * Submits a report to the database 
     *
     * @param array $report Array of the report. Values required: amt, currency, processor, username, email, ip, timestamp, pp_email, pp_PayerID. 
     * @return string JSON or null The result of the API Call
     */
    public function submitReport($report)
    {
        if(empty($report['amt']) || empty($report['currency']) || empty($report['processor']) || empty($report['username']) || empty($report['email']) || empty($report['pp_email']) || empty($report['ip']) || empty($report['pp_PayerID']) || empty($report['timestamp']))
        {
            throw new Exception('Missing parameter for submission');
        }

        if(strtoupper($report['currency']) != 'USD')
        {
            throw new Exception('Currency must be USD');
        }

        if(strtolower($report['processor']) != 'pp')
        {
            throw new Exception('Processor must be PayPal');
        }

        $hashedReport = array();

        $hashedReport['amt'] = $report['amt'];
        $hashedReport['currency'] = $report['currency'];
        $hashedReport['processor'] = $report['processor'];
        $hashedReport['username'] = $this->hashInput(trim(strtoupper($report['username'])));
        $hashedReport['email'] = $this->hashInput(trim(strtoupper($report['email'])));
        $hashedReport['pp_email'] = $this->hashInput(trim(strtoupper($report['pp_email'])));
        $hashedReport['ip'] = $this->hashInput(trim(strtoupper($report['ip'])));
        $hashedReport['pp_PayerID'] = $this->hashInput($report['pp_PayerID']);
        $hashedReport['timestamp'] = $report['timestamp'];

        if(!empty($report['pp_fName']))
        {
            $hashedReport['pp_fName'] = $this->hashInput(trim(strtoupper($report['pp_fName'])));
        }
        if(!empty($report['pp_lName']))
        {
            $hashedReport['pp_lName'] = $this->hashInput(trim(strtoupper($report['pp_lName'])));
        }
        if(!empty($report['notes']))
        {
            $hashedReport['notes'] = $report['notes'];
        }
        if(!empty($report['txnID']))
        {
            $hashedReport['txnID'] = $report['txnID'];
        }


        $this->runAPICall($this->submitURL, $hashedReport);
        return $this->getResponse();
    }


    /**
     * Searches database for the given Email.
     *
     * @param string $term What is being searched
     * @return string JSON or null The result of the API Call
     */
    public function searchDatabaseEmail($term)
    {
        $data = array("searchType" => "email", "searchTerm" => $this->hashInput(trim(strtoupper($term))));

        return $this->runAPICall($this->searchURL, $data);
    }


    /**
     * Searches database for the given Username.
     *
     * @param string $term What is being searched
     * @return string JSON or null The result of the API Call
     */
    public function searchDatabaseUsername($term)
    {
        $data = array("searchType" => "username", "searchTerm" => $this->hashInput(trim(strtoupper($term))));

        return $this->runAPICall($this->searchURL, $data);
    }


    /**
     * Searches database for the given IP. 
     *
     * @param string $term What is being searched
     * @return string JSON or null The result of the API Call
     */
    public function searchDatabaseIP($term)
    {
        $data = array("searchType" => "ip", "searchTerm" => $this->hashInput(trim(strtoupper($term))));

        return $this->runAPICall($this->searchURL, $data);
    }


    /**
     * Searches database for the given uniqiue ayPal Payer ID. This is the most determinant factor to decide trustworthiness.
     *
     * @param string $term What is being searched
     * @return string JSON or null The result of the API Call
     */
    public function searchDatabasePayPalID($term)
    {
        $data = array("searchType" => "pp_PayerID", "searchTerm" => $this->hashInput(trim(strtoupper($term))));

        return $this->runAPICall($this->searchURL, $data);
    }

    /**
     * Searches database for the given TxnID. This function is only going to return a value if the TxnID belongs to your account.
     *
     * @param string $term What is being searched
     * @return string JSON or null The result of the API Call
     */
    public function searchDatabaseTxnID($term)
    {
        $data = array("searchType" => "txnID", "searchTerm" => $this->hashInput(trim(strtoupper($term))));

        return $this->runAPICall($this->searchURL, $data);
    }


    /**
     * This function communicates with the ChargebackDB API.
     *
     * @param string $url
     * @param string $data Must be an array of data
     * @param int $method See constants defined at the beginning of the class
     * @return string JSON or null
     */
    private function runAPICall($url, $data = null, $method = 'API_METHOD_POST')
    {
    	$data['key'] = $this->apiKey;
        $headerData = array();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Don't print the result
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);

        
        if (empty($this->apiKey))
        {
        	throw new Exception("No API Key found");
        }

        if ($this->advDebug) {
            curl_setopt($curl, CURLOPT_HEADER, true); // Display headers
            curl_setopt($curl, CURLINFO_HEADER_OUT, true); // Display output headers
            curl_setopt($curl, CURLOPT_VERBOSE, true); // Display communication with server
        }

        if ($method == 'API_METHOD_POST') {
            curl_setopt($curl, CURLOPT_POST, true);
        } elseif ($method == 'API_METHOD_PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($method == 'API_METHOD_DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        if (!is_null($data) && ($method == 'API_METHOD_POST' || $method == 'API_METHOD_PUT')) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        if (sizeof($headerData) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headerData);
        }

        try {
            $this->response = curl_exec($curl);
            $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($this->debug || $this->advDebug) {
                $info = curl_getinfo($curl);
                echo '<pre>';
                print_r($info);
                echo '</pre>';
                if ($info['http_code'] == 0) {
                    echo '<br>cURL error num: ' . curl_errno($curl);
                    echo '<br>cURL error: ' . curl_error($curl);
                }
                echo '<br>Sent info:<br><pre>';
                print_r($data);
                echo '</pre>';
            }
        } catch (Exception $ex) {
            if ($this->debug || $this->advDebug) {
                echo '<br>cURL error num: ' . curl_errno($curl);
                echo '<br>cURL error: ' . curl_error($curl);
            }
            echo 'Error on cURL';
            $this->response = null;
        }

        curl_close($curl);

        return $this->response;
    }

    private function getResponse()
    {
    	return $this->response;
    }

    private function hashInput($input)
    {
    	$hashedVal = $input;
        for($i = 1; $i < 10; $i++)
        {
            $hashedVal = hash("sha256", "Charg" . $i . "eSalt" . $hashedVal . "BackSalt!@$");
        }
        return $hashedVal;
    }






}

?>
