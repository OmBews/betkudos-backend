<?php

namespace App\Blockchain;

class Bitcoin
{
    private $username;
    private $password;
    private $host;
    private $port;

    public $status;
    public $error;
    public $raw_response;
    public $response;

    private $id = 0;

    public function __construct($username, $password, $host, $port)
    {
        $this->username      = $username;
        $this->password      = $password;
        $this->host          = $host;
        $this->port          = $port;
    }

    public function btc_request($method, $params=array())
    {
        $this->status       = null;
        $this->error        = null;
        $this->raw_response = null;
        $this->response     = null;
        $params = array_values($params);
        $this->id++;

        $request = json_encode(array(
            'method' => $method,
            'params' => $params,
            'id'     => $this->id
        ));

        $curl    = curl_init("http://{$this->host}:{$this->port}");
        $options = array(
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $this->username . ':' . $this->password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $request
        );

        if (ini_get('open_basedir')) {
            unset($options[CURLOPT_FOLLOWLOCATION]);
        }

        curl_setopt_array($curl, $options);

        $this->raw_response = curl_exec($curl);
        $this->response     = json_decode($this->raw_response, true);
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $curl_error = curl_error($curl);

        curl_close($curl);
        //echo "RAW RESPONSE: ".$this->raw_response."\r\n";

        if (!empty($curl_error)) {
            $this->error = $curl_error;
        }

        if (!$this->response) 
        {
            return false;
        }

        if ($this->response['error']) {
            $this->error = $this->response['error']['message'];
            //echo $this->response['error']['message'];
        } elseif ($this->status != 200) {
            switch ($this->status) {
                case 400:
                    $this->error = 'HTTP_BAD_REQUEST';
                    break;
                case 401:
                    $this->error = 'HTTP_UNAUTHORIZED';
                    break;
                case 403:
                    $this->error = 'HTTP_FORBIDDEN';
                    break;
                case 404:
                    $this->error = 'HTTP_NOT_FOUND';
                    break;
            }
        }

        if ($this->error) {
            return false;
        }

        return $this->response['result'];
    }

    public function getinfo()
    {
        return $this->btc_request(__FUNCTION__);
    }

    public function gethashespersec()
    {
        return $this->btc_request(__FUNCTION__);
    }

    public function getgenerate()
    {
        return $this->btc_request(__FUNCTION__);
    }

    public function getdifficulty()
    {
        return $this->btc_request(__FUNCTION__);
    }

    public function getconnectioncount()
    {
        return $this->btc_request(__FUNCTION__);
    }

    public function getblocktemplate($params)
    {
        return $this->btc_request(__FUNCTION__,array($params));
    }

    public function getnewaddress()
    {
        return $this->btc_request(__FUNCTION__);
    }

    public function getaccount($address)
    {
        return $this->btc_request(__FUNCTION__,array($address));
    }

    public function getblockhash($num = 0)
    {
        return $this->btc_request(__FUNCTION__,array($num));
    }

    public function getblockcount()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function getblock($hash)
    {
        return $this->btc_request(__FUNCTION__,array($hash,2));
    }

    public function getbestblockhash()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function getbalance($account = '*', $confs = 0)
    {
        return $this->btc_request(__FUNCTION__,array($account, $confs));
    }

    public function getaddressesbyaccount($account = '')
    {
        return $this->btc_request(__FUNCTION__,array($account));
    }

    public function getaddednodeinfo($bool, $params)
    {
        return $this->btc_request(__FUNCTION__,array($bool, $params));
    }

    public function addnode($node, $opts = 'add')
    {
        return $this->btc_request(__FUNCTION__,array($node, $opts));
    }

    public function getaccountaddress($account = '')
    {
        return $this->btc_request(__FUNCTION__,array($account));
    }

    public function encryptwallet($passphrase)
    {
        return $this->btc_request(__FUNCTION__,array($passphrase));
    }

    public function dumpprivkey($address)
    {
        return $this->btc_request(__FUNCTION__,array($address));
    }

    public function decoderawtransaction($hex)
    {
        return $this->btc_request(__FUNCTION__,array($hex));
    }

    public function createrawtransaction($transactions, $amount)
    {
        return $this->btc_request(__FUNCTION__,array($transactions, $amount));
    }

    public function createmultisig($num, $keys)
    {
        return $this->btc_request(__FUNCTION__,array($num, $keys));
    }

    public function backupwallet($path)
    {
        return $this->btc_request(__FUNCTION__,array($path));
    }

    public function addmultisigaddress($num, $keys, $account = null)
    {
        return isset($account) ? $this->btc_request(__FUNCTION__,array($num, $keys, $account)) :$this->btc_request(__FUNCTION__,array($num, $keys));
    }

    public function getmininginfo()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function getpeerinfo()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function getrawchangeaddress()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function getrawmempool()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function getrawtransaction($transaction, $verbos = 0)
    {
        return $this->btc_request(__FUNCTION__,array($transaction, $verbos));
    }

    public function getreceivedbyaccount($account = '' , $confs = 1)
    {
        return $this->btc_request(__FUNCTION__,array($account, $confs));
    }

    public function getreceivedbyaddress($address , $confs = 1)
    {
        return $this->btc_request(__FUNCTION__,array($address, $confs));
    }

    public function gettransaction($transaction)
    {
        return $this->btc_request(__FUNCTION__,array($transaction));
    }

    public function gettxout($transaction, $num, $mempool = true)
    {
        return $this->btc_request(__FUNCTION__,array($transaction, $num, $mempool));
    }

    public function gettxoutsetinfo()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function getwork($data = null)
    {
        return isset($data) ? $this->btc_request(__FUNCTION__,array($data)) : $this->btc_request(__FUNCTION__,array());
    }

    public function help($cmd)
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function importprivkey($privkey, $label = null, $rescan = true)
    {
        return isset($label) ? $this->btc_request(__FUNCTION__,array($privkey, $label, $rescan)) : $this->btc_request(__FUNCTION__,array($privkey));
    }

    public function keypoolrefill()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function listaddressgroupings()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function listreceivedbyaccount($confs = 1, $empty = false)
    {
        return $this->btc_request(__FUNCTION__,array($confs, $empty));
    }

    public function listreceivedbyaddress($confs = 1, $empty = false)
    {
        return $this->btc_request(__FUNCTION__,array($confs, $empty));
    }

    public function listsinceblock($blockhash = null, $confs = 1)
    {
        return isset($blockhash) ? $this->btc_request(__FUNCTION__,array($blockhash, $confs)) : $this->btc_request(__FUNCTION__,array());
    }

    public function listtransactions($account = null, $count = 10, $from = 0)
    {
        return isset($account) ? $this->btc_request(__FUNCTION__,array($account, $count, $from)) : $this->btc_request(__FUNCTION__,array());
    }

    public function listunspent($minconf = 1, $maxconf = 999999)
    {
        return $this->btc_request(__FUNCTION__,array($minconf, $maxconf));
    }

    public function listlockunspent()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function lockunspent($lock, $objects = NULL)
    {
        return isset($objects) ? $this->btc_request(__FUNCTION__,array($lock, $objects)) : $this->btc_request(__FUNCTION__,array($lock));
    }

    public function move($from, $to, $amount, $minconf = 1, $comment = '')
    {
        return $this->btc_request(__FUNCTION__,array($from, $to, $amount, $minconf, $comment));
    }

    public function sendfrom($from, $address, $amount, $minconf = 1, $comment = '', $to_comment = '')
    {
        return $this->btc_request(__FUNCTION__,array($address, $amount, $minconf, $comment, $to_comment));
    }

    public function sendmany($from, $to, $minconf = 1, $comment = '')
    {
        return $this->btc_request(__FUNCTION__,array($from, $to, $minconf, $comment));
    }

    public function sendrawtransaction($hex)
    {
        return $this->btc_request(__FUNCTION__,array($hex));
    }

    public function sendtoaddress($address, $amount, $comment = '', $comment_to = '')
    {
        return $this->btc_request(__FUNCTION__,array($address, $amount, $comment, $comment_to));
    }

    public function setaccount($address, $account)
    {
        return $this->btc_request(__FUNCTION__,array($address, $account));
    }

    public function setgenerate($gen, $limit = -1)
    {
        return $this->btc_request(__FUNCTION__,array($gen, $limit));
    }

    public function settxfee($amount)
    {
        return $this->btc_request(__FUNCTION__,array($amount));
    }

    public function signmessage($address, $message)
    {
        return $this->btc_request(__FUNCTION__,array($address, $message));
    }

    public function signrawtransaction($hex, $transactions = null, $privkey = null)
    {
        return isset($transactions) ? $this->btc_request(__FUNCTION__,array($hex, $transactions, $privkey)) : $this->btc_request(__FUNCTION__,array($hex));
    }

    public function stop()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function submitblock($hex, $params = null)
    {
        return isset($params) ? $this->btc_request(__FUNCTION__,array($hex, $params)) : $this->btc_request(__FUNCTION__,array($hex));
    }

    public function validateaddress($address)
    {
        return $this->btc_request(__FUNCTION__,array($address));
    }

    public function verifymessage($address, $sig, $message)
    {
        return $this->btc_request(__FUNCTION__,array($address, $sig, $message));
    }

    public function walletlock()
    {
        return $this->btc_request(__FUNCTION__,array());
    }

    public function walletpassphrase($passphrase, $timeout)
    {
        return $this->btc_request(__FUNCTION__,array($passphrase, $timeout));
    }

    public function walletpassphrasechange($old, $new)
    {
        return $this->btc_request(__FUNCTION__,array($old, $new));
    }
}

