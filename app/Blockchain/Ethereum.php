<?php

namespace App\Blockchain;

class Ethereum
{
    private $host;
    private $port;
    private $version;

    public $status;
    public $error;
    public $raw_response;
    public $response;

    private $id = 0;

    public function __construct($host, $port, $version = "2.0")
    {

        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
    }

    public function eth_request($method, $params = array(), $params2 = NULL)
    {
        $this->status = null;
        $this->error = null;
        $this->raw_response = null;
        $this->response = null;


        $params = array_values($params);

        if (!is_null($params2)) {
            array_push($params, $params2);
        }

        $this->id++;

        $request = json_encode(array(
            'method' => $method,
            'params' => $params,
            'id' => $this->id,
            'jsonrpc' => $this->version
        ));

        $curl = curl_init("http://{$this->host}:{$this->port}");
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $request
        );

        curl_setopt_array($curl, $options);

        $this->raw_response = curl_exec($curl);
        $this->response = json_decode($this->raw_response, true);
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);

        curl_close($curl);

        if (!empty($curl_error)) {
            $this->error = $curl_error;
        }

        if (isset($this->response['error'])) {
            $this->error = $this->response['error']['message'];
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

    function decode_hex($input)
    {
        if (substr($input, 0, 2) == '0x')
            $input = substr($input, 2);

        if (preg_match('/[a-f0-9]+/', $input))
            return hexdec($input);

        return $input;
    }

    function web3_clientVersion()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function web3_sha3($input)
    {
        return $this->eth_request(__FUNCTION__, array($input));
    }

    function net_version()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function net_listening()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function net_peerCount()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_protocolVersion()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_coinbase()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_mining()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_hashrate()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_gasPrice()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_accounts()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_blockNumber($decode_hex = TRUE)
    {
        $block = $this->eth_request(__FUNCTION__);

        if ($decode_hex)
            $block = $this->decode_hex($block);

        return $block;
    }

    function eth_getBalance($address, $block = 'latest', $decode_hex = TRUE)
    {
        $balance = $this->eth_request(__FUNCTION__, array($address, $block));

        if ($decode_hex)
            $balance = $this->decode_hex($balance);

        return $balance;
    }

    function eth_getStorageAt($address, $at, $block = 'latest')
    {
        return $this->eth_request(__FUNCTION__, array($address, $at, $block));
    }

    function eth_getTransactionCount($address, $block = 'latest', $decode_hex = FALSE)
    {
        $count = $this->eth_request(__FUNCTION__, array($address, $block));

        if ($decode_hex)
            $count = $this->decode_hex($count);

        return $count;
    }

    function eth_getBlockTransactionCountByHash($tx_hash)
    {
        return $this->eth_request(__FUNCTION__, array($tx_hash));
    }

    function eth_getBlockTransactionCountByNumber($tx = 'latest')
    {
        return $this->eth_request(__FUNCTION__, array($tx));
    }

    function eth_getUncleCountByBlockHash($block_hash)
    {
        return $this->eth_request(__FUNCTION__, array($block_hash));
    }

    function eth_getUncleCountByBlockNumber($block = 'latest')
    {
        return $this->eth_request(__FUNCTION__, array($block));
    }

    function eth_getCode($address, $block = 'latest')
    {
        return $this->eth_request(__FUNCTION__, array($address, $block));
    }

    function eth_sign($address, $input)
    {
        return $this->eth_request(__FUNCTION__, array($address, $input));
    }

    function eth_sendTransaction($transaction)
    {
        if (!$transaction instanceof EthereumTransaction) {
            throw new \ErrorException('Transaction object expected');
        } else {
            return $this->eth_request(__FUNCTION__, $transaction->toArray());
        }
    }

    function eth_call($message, $block)
    {
        if (!$message instanceof EthereumMessage) {
            throw new \ErrorException('Message object expected');
        } else {
            return $this->eth_request(__FUNCTION__, $message->toArray(), $block);
        }
    }

    function eth_estimateGas($message, $block)
    {
        if (!$message instanceof EthereumMessage) {
            throw new \ErrorException('Message object expected');
        } else {
            return $this->eth_request(__FUNCTION__, $message->toArray());
        }
    }

    function eth_getBlockByHash($hash, $full_tx = TRUE)
    {
        return $this->eth_request(__FUNCTION__, array($hash, $full_tx));
    }

    function eth_getBlockByNumber($block = 'latest', $full_tx = TRUE)
    {
        return $this->eth_request(__FUNCTION__, array($block, $full_tx));
    }

    function eth_getTransactionByHash($hash)
    {
        return $this->eth_request(__FUNCTION__, array($hash));
    }

    function eth_getTransactionByBlockHashAndIndex($hash, $index)
    {
        return $this->eth_request(__FUNCTION__, array($hash, $index));
    }

    function eth_getTransactionByBlockNumberAndIndex($block, $index)
    {
        return $this->eth_request(__FUNCTION__, array($block, $index));
    }

    function eth_getTransactionReceipt($tx_hash)
    {
        return $this->eth_request(__FUNCTION__, array($tx_hash));
    }

    function eth_getUncleByBlockHashAndIndex($hash, $index)
    {
        return $this->eth_request(__FUNCTION__, array($hash, $index));
    }

    function eth_getUncleByBlockNumberAndIndex($block, $index)
    {
        return $this->eth_request(__FUNCTION__, array($block, $index));
    }

    function eth_getCompilers()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_compileSolidity($code)
    {
        return $this->eth_request(__FUNCTION__, array($code));
    }

    function eth_compileLLL($code)
    {
        return $this->eth_request(__FUNCTION__, array($code));
    }

    function eth_compileSerpent($code)
    {
        return $this->eth_request(__FUNCTION__, array($code));
    }

    function eth_newFilter($filter, $decode_hex = FALSE)
    {
        if (!$filter instanceof EthereumFilter) {
            throw new \ErrorException('Expected a Filter object');
        } else {
            $id = $this->eth_request(__FUNCTION__, $filter->toArray());

            if ($decode_hex)
                $id = $this->decode_hex($id);

            return $id;
        }
    }

    function eth_newBlockFilter($decode_hex = FALSE)
    {
        $id = $this->eth_request(__FUNCTION__);

        if ($decode_hex)
            $id = $this->decode_hex($id);

        return $id;
    }

    function eth_newPendingTransactionFilter($decode_hex = FALSE)
    {
        $id = $this->eth_request(__FUNCTION__);

        if ($decode_hex)
            $id = $this->decode_hex($id);

        return $id;
    }

    function eth_uninstallFilter($id)
    {
        return $this->eth_request(__FUNCTION__, array($id));
    }

    function eth_getFilterChanges($id)
    {
        return $this->eth_request(__FUNCTION__, array($id));
    }

    function eth_getFilterLogs($id)
    {
        return $this->eth_request(__FUNCTION__, array($id));
    }

    function eth_getLogs($filter)
    {
        if (!$filter instanceof EthereumFilter) {
            throw new \ErrorException('Expected a Filter object');
        } else {
            return $this->eth_request(__FUNCTION__, $filter->toArray());
        }
    }

    function eth_getWork()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function eth_submitWork($nonce, $pow_hash, $mix_digest)
    {
        return $this->eth_request(__FUNCTION__, array($nonce, $pow_hash, $mix_digest));
    }

    function db_putString($db, $key, $value)
    {
        return $this->eth_request(__FUNCTION__, array($db, $key, $value));
    }

    function personal_sendTransaction($from, $to, $gas, $gasPrice, $value, $data, $passphrase)
    {
        $tx = new EthereumTransaction($to, $data, $from, $gas, $gasPrice, $value);
        return $this->eth_request(__FUNCTION__, $tx->toArray(), $passphrase);
    }

    function personal_newAccount($password)
    {
        return $this->eth_request(__FUNCTION__, array($password));
    }

    function db_getString($db, $key)
    {
        return $this->eth_request(__FUNCTION__, array($db, $key));
    }

    function db_putHex($db, $key, $value)
    {
        return $this->eth_request(__FUNCTION__, array($db, $key, $value));
    }

    function db_getHex($db, $key)
    {
        return $this->eth_request(__FUNCTION__, array($db, $key));
    }

    function shh_version()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function shh_post($post)
    {
        if (!is_a($post, 'Whisper_Post')) {
            throw new ErrorException('Expected a Whisper post');
        } else {
            return $this->eth_request(__FUNCTION__, $post->toArray());
        }
    }

    function shh_newIdentinty()
    {
        return $this->eth_request(__FUNCTION__);
    }

    function shh_hasIdentity($id)
    {
        return $this->eth_request(__FUNCTION__);
    }

    function shh_newFilter($to = NULL, $topics = array())
    {
        return $this->eth_request(__FUNCTION__, array(array('to' => $to, 'topics' => $topics)));
    }

    function shh_uninstallFilter($id)
    {
        return $this->eth_request(__FUNCTION__, array($id));
    }

    function shh_getFilterChanges($id)
    {
        return $this->eth_request(__FUNCTION__, array($id));
    }

    function shh_getMessages($id)
    {
        return $this->eth_request(__FUNCTION__, array($id));
    }
}
